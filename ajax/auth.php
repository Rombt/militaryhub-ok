<?php /**
 * Created by PhpStorm.
 * User: AndriiK
 * Date: 18.06.2018
 * Time: 14:10
 */

if (!empty($_SERVER['HTTP_USER_AGENT'])){
    session_name(md5($_SERVER['HTTP_USER_AGENT']));
}
session_start();
require_once('../api/Okay.php');
define('IS_CLIENT', true);

class AuthAjax extends Okay {
    public function fetch() {
        if (!$this->request->method('post')) return false;

        $user_id = 0;
        $code = 400;
        $type = $this->request->post('type', 'string');
        $id = $this->request->post('id', 'integer');
        $furl = $this->request->post('url');
        $s_user_id = $this->request->post('user_id', 'integer');
        $access_token = $this->request->post('access_token', 'string');
        $phone = preg_replace("~[^\d]~", "", $this->request->post('phone'));

        if ($type == "facebook") {
            if (!$id || !$access_token) die('404');

            $data = $this->facebook->get($id, $access_token);
            $result = $this->facebook->auth($id, $access_token, $data, ['user_id' => $_SESSION['user_id'], 'phone' => $phone]);

            if (!empty($result)) {
                $user_id = $result['user_id'];
                $code = $result['code'];
                $error = $result['error'];
            }
        }

        if ($type == "google") {
            if (!$access_token) die('404');

            $data = $this->google->get($access_token);
            $result = $this->google->auth($access_token, $data, ['user_id' => $_SESSION['user_id'], 'phone' => $phone]);

            if (!empty($result)) {
                $user_id = $result['user_id'];
                $code = $result['code'];
                $error = $result['error'];
            }
        }

        $url = $this->config->root_url . ($this->lang_link ? '/' . $this->lang_link : '');
        if (!isset($_SESSION['user_id'])) {
            if (!empty($furl)) {
                $url .= $furl;
            } elseif (!empty($_SESSION['last_visited_page'])) {
                $url = $_SESSION['last_visited_page'];
            } else {
                $url = '';
            }
        } else {
            $url .= (!$this->lang_link ? '/' : '') . 'user';
        }
        if ($user_id) {
            $_SESSION['user_id'] = $user_id;
            $code = 200;
        }

        if ($type == "fb_remove" && !empty($id)) {
            $this->facebook->remove(intval($id));
            $code = 200;
        }

        if ($type == "g_remove" && !empty($id)) {
            $this->google->remove(intval($id));
            $code = 200;
        }

        return [
            'status' => $code,
            'url' => $url,
            'error' => !empty($error) ? $error : '',
        ];
    }
}

$results = new AuthAjax();
if ($result = $results->fetch()) {
    header("Content-type: application/json; charset=UTF-8");
    header("Cache-Control: must-revalidate");
    header("Pragma: no-cache");
    header("Expires: -1");
    print json_encode($result);
}
exit();