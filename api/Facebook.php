<?php

require_once('Okay.php');

class Facebook extends Okay {
    protected $version = 'v5.0';
    protected $fields = ['id', 'first_name', 'last_name', 'middle_name', 'email', 'address', 'locale', 'hometown', 'picture{url}'];

    public function get($id, $access_token) {
        $url = 'https://graph.facebook.com/'.$this->version.'/'.$id.'?access_token='.$access_token.'&fields='.implode(',', $this->fields);
        return $this->curl('GET', $url, null);
    }

    public function auth($id, $access_token, $data, $param = []) {
        if (!$id || !$access_token || empty($data)) return false;
        $user_id = 0;
        $code = 400;
        $error = '';

        $data = json_decode($data);
        $param['user_id'] = !empty($param['user_id']) ? $param['user_id'] : '';
        $param['phone'] = preg_replace("~[^\d]~", "", $param['phone']);
        if (empty($data->phone) && !empty($param['phone'])) $data->phone = trim($param['phone']);
        if (!empty($param['user_id'])) {
            $user = $this->users->get_user($data->id, 'facebook');
            if (!empty($user) && $user->id !== $param['user_id']) {
                $code = 201;
                $error = $this->translations->oauth_user_exists;
            } else {
                $this->users->update_user(intval($param['user_id']), [
                    'facebook_id' => $data->id,
                    'facebook_json' => json_encode($data, JSON_UNESCAPED_UNICODE),
                ]);
                $code = 200;
            }
        } else {
            $this->db->query("SELECT u.id, u.image FROM __users u WHERE u.facebook_id=? LIMIT 1", $data->id);
            $user = $this->db->result();
            if ($user_id = $user->id) {
                $this->users->update_user(intval($user_id), [
                    'last_ip'=>$_SERVER['REMOTE_ADDR'],
                    'facebook_json' => json_encode($data, JSON_UNESCAPED_UNICODE),
                ]);
            } elseif (!empty($data->email) || !empty($data->phone)) {
                $this->db->query("SELECT u.id, u.name, u.phone, u.image FROM __users u WHERE u.email=? AND (u.facebook_id=? OR u.facebook_id IS NULL) LIMIT 1", $data->email, '');
                $user = $this->db->result();
                if (empty($user) && !empty($data->phone)) {
                    $this->db->query("SELECT u.id, u.name, u.email, u.image FROM __users u WHERE u.phone=? AND (u.facebook_id=? OR u.facebook_id IS NULL) LIMIT 1", $data->phone, '');
                    $user = $this->db->result();
                    if ($user_id = $user->id) {
                        $this->users->update_user($user_id, [
                            'name' => !empty($user->name) ? $user->name : ($data->last_name . ' ' . $data->first_name . ' ' . $data->middle_name),
                            'email' => !empty($user->email) ? $user->email : $data->email,
                            'last_ip' => $_SERVER['REMOTE_ADDR'],
                            'facebook_id' => $data->id,
                            'facebook_json' => json_encode($data, JSON_UNESCAPED_UNICODE),
                        ]);
                    }
                } elseif ($user_id = $user->id) {
                    $this->users->update_user($user_id, [
                        'name' => !empty($user->name) ? $user->name : ($data->last_name . ' ' . $data->first_name . ' ' . $data->middle_name),
                        'phone' => !empty($user->phone) ? $user->phone : $data->phone,
                        'last_ip' => $_SERVER['REMOTE_ADDR'],
                        'facebook_id' => $data->id,
                        'facebook_json' => json_encode($data, JSON_UNESCAPED_UNICODE),
                    ]);
                } else {
                    $code = 400;
                    $error = $this->translations->error_phone;
                }
                if (!$user_id) {
                    $user_id = $this->users->add_user([
                        'name' => ($data->last_name . ' ' . $data->first_name . ' ' . $data->middle_name),
                        'email' => $data->email,
                        'phone' => $data->phone,
                        'password' => base64_encode(uniqid(time())),
                        'group_id' => intval($this->settings->registration_group),
                        'enabled' => 1,
                        'last_ip' => $_SERVER['REMOTE_ADDR'],
                        'created' => date("Y-m-d H:i:s"),
                        'facebook_id' => $data->id,
                        'facebook_json' => json_encode($data, JSON_UNESCAPED_UNICODE),
                    ]);
                    if (!$user_id && empty($error)) {
                        $code = 201;
                        $error = $this->translations->oauth_user_not_found;
                    }
                }
            } else {
                $code = 400;
                $error = $this->translations->facebook_error;
            }
        }

        if (!empty($user->image) && !is_file($this->config->root_dir . $this->config->original_users_dir . $user->image)) {
            unset($user->image);
        }
        if ($user_id && empty($user->image) && !empty($data->picture->data->url)) {
            $image = $this->config->root_dir . $this->config->original_users_dir . uniqid($this->validate->uuid()) . '.jpg';
            if (copy($data->picture->data->url, $image)) {
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
            'facebook_id' => NULL,
            'facebook_json' => NULL,
        ]);

        return true;
    }

    private function curl($type, $url, $json = '') {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        if ($type == 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch);
        curl_close($ch);

        return $response;
    }
}