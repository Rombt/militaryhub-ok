<?php
/*
 * Author: Andrii K (andrey.kovt@gmail.com)
 * Date: 08.02.2022
 * Time: 12:15:10
 */

class TurboSMS extends Okay {
    private $isWrongParams = 0;
    private $apiEnabled;
    private $senderTurboSMS;
    private $loginTurboSMS;
    private $passwordTurboSMS;
    private $users_fields = ['id', 'name', 'phone', 'email', 'address', 'bonuses', 'validation_code', 'removed_bonuses'];

    public function __construct() {
        parent::__construct();

        $this->apiEnabled = $this->settings->turbosms_enabled;
        $this->senderTurboSMS = $this->settings->turbosms_sender;
        $this->loginTurboSMS = $this->settings->turbosms_login;
        $this->passwordTurboSMS = $this->settings->turbosms_pass;

        if (!class_exists('SOAPClient') || (empty($this->senderTurboSMS) || empty($this->loginTurboSMS) || empty($this->passwordTurboSMS) || (!$this->apiEnabled || ($this->settings->turbosms_test_mode && empty($_SESSION['admin']))))) {
            $this->isWrongParams = 1;
        }
    }

    public function order_user($order_id) {
        if ($this->isWrongParams
            || !($order = $this->orders->get_order(intval($order_id))) || empty($order->phone)
            || !($message = $this->settings->turbosms_messages['order'])
        ) {
            return false;
        }
        
        $order->phone = $this->format_phone($order->phone);

        $parts = [];
        foreach ($order as $field => $value) {
            if (!in_array($field, $this->users_fields)) {
                continue;
            }
            $parts["{\${$field}}"] = (string)$value;
        }
        $parts['{$order_id}'] = $order->id;

        $message = strtr($message, $parts);
        $message = preg_replace('/\{\$[^\$]*\}/', '', $message);
        $message = preg_replace('/\h{2,}/', ' ', $message);
        $message = trim($message);

        $res = $this->send_soap($order->phone, $message);
        if (isset($res->success)) {
            return $res->success;
        }

        return false;
    }

    public function registration_user($user) {
        if ($this->isWrongParams
            || empty($user->phone)
            || !($message = $this->settings->turbosms_messages['register'])
        ) {
            return false;
        }

        $user->phone = $this->format_phone($user->phone);

        $parts = [];
        foreach ($user as $field => $value) {
            if (!in_array($field, $this->users_fields)) {
                continue;
            }
            $parts["{\${$field}}"] = (string)$value;
        }
        $parts['{$password}'] = $user->validation_code;

        $message = strtr($message, $parts);
        $message = preg_replace('/\{\$[^\$]*\}/', '', $message);
        $message = preg_replace('/\h{2,}/', ' ', $message);
        $message = trim($message);

        $res = $this->send_soap($user->phone, $message);
        if (isset($res->success)) {
            return $res->success;
        }

        return false;
    }

    public function user_birthday($user_id) {
        if ($this->isWrongParams
            || !($user = $this->users->get_user(intval($user_id)))
            || empty($user->phone) || empty($user->birthday)
            || date('m', strtotime($user->birthday)) !== date('m') || date('d', strtotime($user->birthday)) !== date('d')
            || !($message = $this->settings->turbosms_messages['birthday'])
        ) {
            return false;
        }

        $user->phone = $this->format_phone($user->phone);

        $parts = [];
        foreach ($user as $field => $value) {
            if (!in_array($field, $this->users_fields)) {
                continue;
            }
            $parts["{\${$field}}"] = (string)$value;
        }

        $message = strtr($message, $parts);
        $message = preg_replace('/\{\$[^\$]*\}/', '', $message);
        $message = preg_replace('/\h{2,}/', ' ', $message);
        $message = trim($message);

        $res = $this->send_soap($user->phone, $message);
        if (isset($res->success)) {
            return $res->success;
        }

        return false;
    }

    public function user_bonuses_removed($user_id) {
        $notify = intval($this->settings->users_bonuses_delete_notify);
        if ($this->isWrongParams
            || !($user = $this->users->get_user(intval($user_id), '', array('has_bonuses_auto_removed' => 1, 'bonuses_removed' => ['from' => $notify, 'to' => $notify])))
            || empty($user->phone) || empty($user->bonuses)
            || !($message = $this->settings->turbosms_messages['bonuses_removed'])
        ) {
            return false;
        }

        $user->phone = $this->format_phone($user->phone);

        $parts = [];
        foreach ($user as $field => $value) {
            if (!in_array($field, $this->users_fields)) {
                continue;
            }
            $parts["{\${$field}}"] = (string)$value;
        }

        $message = strtr($message, $parts);
        $message = preg_replace('/\{\$[^\$]*\}/', '', $message);
        $message = preg_replace('/\h{2,}/', ' ', $message);
        $message = trim($message);

        $res = $this->send_soap($user->phone, $message);
        if (isset($res->success)) {
            return $res->success;
        }

        return false;
    }

    public function get_users_fields() {
        $btr = $this->design->get_var('btr');
        $this->db->query("SHOW COLUMNS FROM __users");
        $fields = [];
        foreach ($this->db->results() as $result) {
            if (!in_array($result->Field, $this->users_fields)) {
                continue;
            }
            $lang = $btr->{"general_{$result->Field}"};
            $fields["{\${$result->Field}}"] = !empty($lang) ? $lang : $result->Field;
        }
        return $fields;
    }

    private function send_soap($phone, $message) {
        if ($this->isWrongParams || empty($phone) || empty($message)) {
            return false;
        }

        try {
            $client = new SoapClient('http://turbosms.in.ua/api/wsdl.html');
            $client->Auth([
                'login' => $this->loginTurboSMS,
                'password' => $this->passwordTurboSMS,
            ]);

            // $message = iconv('windows-1251', 'utf-8', $message);
            $phone = $this->format_phone($phone);
            $result = $client->SendSMS([
                'sender' => $this->senderTurboSMS,
                'destination' => $phone,
                'text' => $message,
            ]);
            $res = 1;
            $status = $result->SendSMSResult->ResultArray[0] . PHP_EOL;
            file_put_contents('log/sms.log', json_encode($result, JSON_UNESCAPED_UNICODE) . ' ' . $status, FILE_APPEND);
        } catch (Exception $e) {
            $res = 0;
            $status = 'Ошибка: ' . $e->getMessage() . PHP_EOL;
            file_put_contents('log/sms.log', $status, FILE_APPEND);
        }

        return (object)[
            'success' => $res,
            'status' => $status,
        ];
    }

    private function format_phone($phone) {
        $phone = preg_replace('~[^0-9]+~', '', $phone);

        if (strlen($phone) == 11) {
            $phone = '3' . $phone;
        } elseif (strlen($phone) == 10) {
            $phone = '38' . $phone;
        } elseif (strlen($phone) == 9) {
            $phone = '380' . $phone;
        }
        // if (preg_match("~380[1-9]{9}~", $phone)) {
        //     $phone = '+' . $phone;
        // } elseif (preg_match("~80([1-9]){9}~", $phone)) {
        //     $phone = '+3' . $phone;
        // } elseif (preg_match("~0[1-9]{9}~", $phone)) {
        //     $phone = '+38' . $phone;
        // } elseif (preg_match("~[1-9]{9}~", $phone)) {
        //     $phone = '+380' . $phone;
        // }

        return $phone;
    }

}