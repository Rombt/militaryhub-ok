<?php

require_once('Okay.php');
require_once('../vendor/autoload.php');

class Google extends Okay {
    public function get($id_token, $gClientId = '') {
	    if (!$id_token) return false;

        $gClientId = !empty($gClientId) ? $gClientId : $this->settings->gClientId;
        $client = new Google_Client(['client_id' => $gClientId]);

        $response = $client->verifyIdToken($id_token);
        $client->revokeToken();

    	return json_encode($response);
    }

    public function auth($access_token, $data, $param = []) {
        if (!$access_token || empty($data)) return false;
        $user_id = 0;
        $code = 400;
        $error = '';

        $data = json_decode($data);
        $param['user_id'] = !empty($param['user_id']) ? $param['user_id'] : '';
        $param['phone'] = preg_replace("~[^\d]~", "", $param['phone']);
        if (empty($data->phone) && !empty($param['phone'])) $data->phone = trim($param['phone']);
        if (!empty($param['user_id'])) {
            $user = $this->users->get_user($data->sub, 'google');            
            if (!empty($user) && $user->id !== $param['user_id']) {
                $code = 201;
                $error = $this->translations->oauth_user_exists;
            } else {
                $this->users->update_user(intval($param['user_id']), [
                    'google_id' => $data->sub,
                    'google_json' => json_encode($data, JSON_UNESCAPED_UNICODE),
                ]);
                $code = 200;
            }
        } else {
            $this->db->query("SELECT u.id, u.image FROM __users u WHERE u.google_id=? LIMIT 1", $data->sub);
            $user = $this->db->result();            
            if ($user_id = $user->id) {
                $this->users->update_user($user_id, [
                    'last_ip' => $_SERVER['REMOTE_ADDR'],
                    'google_json' => json_encode($data, JSON_UNESCAPED_UNICODE),
                ]);
            } elseif (!empty($data->email) || !empty($data->phone)) {
                $this->db->query("SELECT u.id, u.name, u.phone, u.image FROM __users u WHERE u.email=? AND (u.google_id=? OR u.google_id IS NULL) LIMIT 1", $data->email, '');
                $user = $this->db->result();
                if (empty($user) && !empty($data->phone)) {
                    $this->db->query("SELECT u.id, u.name, u.email, u.image FROM __users u WHERE u.phone=? AND (u.google_id=? OR u.google_id IS NULL) LIMIT 1", $data->phone, '');
                    $user = $this->db->result();
                    if ($user_id = $user->id) {
                        $this->users->update_user($user_id, [
                            'name' => !empty($user->name) ? $user->name : trim($data->family_name . ' ' . $data->given_name),
                            'email' => !empty($user->email) ? $user->email : $data->email,
                            'last_ip' => $_SERVER['REMOTE_ADDR'],
                            'google_id' => $data->sub,
                            'google_json' => json_encode($data, JSON_UNESCAPED_UNICODE),
                        ]);
                    }
                } elseif ($user_id = $user->id) {
                    $this->users->update_user($user_id, [
                        'name' => !empty($user->name) ? $user->name : trim($data->family_name . ' ' . $data->given_name),
                        'phone' => !empty($user->phone) ? $user->phone : $data->phone,
                        'last_ip' => $_SERVER['REMOTE_ADDR'],
                        'google_id' => $data->sub,
                        'google_json' => json_encode($data, JSON_UNESCAPED_UNICODE),
                    ]);
                } else {
                    $code = 400;
                    $error = $this->translations->error_phone;
                }
                if (!$user_id) {
                    $user_id = $this->users->add_user([
                        'name' => (string)trim($data->family_name . ' ' . $data->given_name),
                        'email' => (string)$data->email,
                        'phone' => (string)$data->phone,
                        'password' => base64_encode(uniqid(time())),
                        'group_id' => intval($this->settings->registration_group),
                        'last_ip' => $_SERVER['REMOTE_ADDR'],
                        'created' => date("Y-m-d H:i:s"),
                        'google_id' => $data->sub,
                        'google_json' => json_encode($data, JSON_UNESCAPED_UNICODE),
                    ]);
                    if (!$user_id && empty($error)) {
                        $code = 201;
                        $error = $this->translations->oauth_user_not_found;
                    }
                }
            } else {
                $code = 400;
                $error = $this->translations->error_email;
            }
        }

        if (!empty($user->image) && !is_file($this->config->root_dir . $this->config->original_users_dir . $user->image)) {
            unset($user->image);
        }
        if ($user_id && empty($user->image) && !empty($data->picture)) {
            $image = $this->config->root_dir . $this->config->original_users_dir . uniqid($this->validate->uuid()) . '.jpg';
            if (copy($data->picture, $image)) {
                $this->users->update_user(intval($user_id), array('image' => basename($image)));
            }
        }

        // if ($user_id && !empty($_SESSION['referral_id'])) {
        //     $this->ref->update_user_items($user_id, array('referral_id' => intval($_SESSION['referral_id'])));
        // }

        return [
            'user_id' => $user_id,
            'code' => $user_id ? 200 : $code,
            'error' => $error,
        ];
    }

    public function remove($id) {
        if (!$id) return false;

        $this->users->update_user(intval($id), [
            'google_id' => NULL,
            'google_json' => NULL,
        ]);

        return true;
    }
}
