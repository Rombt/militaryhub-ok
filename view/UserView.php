<?php
/*
 * Author: Andrii K (andrey.kovt@gmail.com)
 * Date: 08.02.2022
 * Time: 09:58:00
 */

require_once('View.php');

class UserView extends View {

    /*Отображение личного кабинета пользователя*/
    public function fetch() {
        if (empty($this->user)) {
            header('Location: '.$this->config->root_url.'/'.$this->lang_link.'user/login');
            exit();
        }

        $feedback = new stdClass;
        /*Принимаем заявку с формы обратной связи*/
        if ($this->request->method('post') && $this->request->post('feedback')) {
            $feedback->name         = $this->user->name;
            $feedback->email        = $this->user->email;
            $feedback->message      = $this->request->post('message');
            $feedback->user_id      = $this->user->id;
            $feedback->ip           = $_SERVER['REMOTE_ADDR'];
            $feedback->lang_id      = $_SESSION['lang_id'];
            $captcha_code           = $this->request->post('captcha_code');

            $this->design->assign('message', $feedback->message);

            /*Валидация данных клиента*/
            if (!$this->validate->is_name($feedback->name, true)) {
                $this->design->assign('error', 'empty_name');
            } elseif (!$this->validate->is_email($feedback->email)) {
                $this->design->assign('error', 'empty_email');
            } elseif (!$this->validate->is_comment($feedback->message, true)) {
                $this->design->assign('error', 'empty_text');
            } elseif ($this->settings->captcha_feedback && !$this->validate->verify_captcha('captcha_feedback', $captcha_code)) {
                $this->design->assign('error', 'captcha');
            } elseif ($feedback_id = $this->feedbacks->add_feedback($feedback)) {
                $this->design->assign('message_sent', true);

                // Отправляем email
                $this->notify->email_feedback_admin($feedback_id);
            } else {
                $this->design->assign('error', 'unknown error');
            }
        }

        $limit = 500;
        $id = $this->request->get('id', 'integer');

        if (!empty($_COOKIE['wished_products'])) {
            $products_ids = explode(',', $_COOKIE['wished_products']);
            $products_ids = array_reverse($products_ids);
        } else {
            $products_ids = array();
        }

        $products = array();

        if (count($products_ids)) {
            foreach ($this->products->get_products(array('id'=>$products_ids, 'visible'=>1)) as $p) {
                $products[$p->id] = $p;
            }
            if (!empty($products)) {
                $this->products->tiny_products($products);
            }
        }

        // Содержимое списка избранного
        $this->design->assign('wished_products', $products);

        /*Обновление данных клиеньа*/
        if ($this->request->method('post') && $this->request->post('user_save')) {
            $user = new stdClass();
            $user->name       = $this->request->post('name');
            $user->email      = $this->request->post('email');
            $user->phone      = $this->request->post('phone');
            $user->address    = $this->request->post('address');
            $user->image      = $this->user->image;
            $password         = $this->request->post('password');

            if (!($this->user->birthday) && $this->request->post('birthday')) {
                $user->birthday = date('Y-m-d', strtotime($this->request->post('birthday')));
            } elseif (!empty($this->user->birthday)) {
                $user->birthday = $this->user->birthday;
            }

            // Загрузить файлы
            if (!empty($_FILES['image']['tmp_name']) && !empty($_FILES['image']['name'])) {
                $attachment_tmp_name = $_FILES['image']['tmp_name'];
                $attachment_name = $_FILES['image']['name'];
                move_uploaded_file($attachment_tmp_name, $this->config->root_dir.'files/user_img/'.$attachment_name);
                $user->image = $attachment_name;
            }
            
            $this->design->assign('name', $user->name);
            $this->design->assign('email', $user->email);
            $this->design->assign('phone', $user->phone);
            $this->design->assign('address', $user->address);
            $this->design->assign('birthday', $user->birthday);
            $this->design->assign('image', $user->image);

            $this->db->query('SELECT count(*) as count FROM __users WHERE email=? AND id!=?', $user->email, $this->user->id);
            $user_exists = $this->db->result('count');

            /*Валидация данных*/
            if ($user_exists) {
                $this->design->assign('error', 'user_exists');
            } elseif (!$this->validate->is_name($user->name, true)) {
                $this->design->assign('error', 'empty_name');
            } elseif (!$this->validate->is_email($user->email)) {
                $this->design->assign('error', 'empty_email');
            } elseif (!$this->validate->is_phone($user->phone, true)) {
                $this->design->assign('error', 'empty_phone');
            } elseif (!$this->validate->is_address($user->address)) {
                $this->design->assign('error', 'empty_address');
            } elseif ($user_id = $this->users->update_user($this->user->id, $user)) {
                $this->user = $this->users->get_user(intval($user_id));
                $this->design->assign('user', $this->user);
            } else {
                $this->design->assign('error', 'unknown error');
            }
            
            if (!empty($password)) {
                $this->users->update_user($this->user->id, array('password' => $password));
            }
        } else {
            // Передаем в шаблон
            $this->design->assign('name', $this->user->name);
            $this->design->assign('image', $this->user->image);
            $this->design->assign('email', $this->user->email);
            $this->design->assign('phone', $this->user->phone);
            $this->design->assign('address', $this->user->address);
            $this->design->assign('birthday', $this->user->birthday);
            $this->design->assign('image', $this->user->image);
        }

        if ($this->request->method('post') && $this->request->post('referral')) {
            $this->translations->debug = (bool)$this->config->debug_translation;
            $translations = $this->translations->get_translations(array('lang'=>$this->language->label));

            $result = [
                'success' => 0,
                'data' => [],
                'error' => '',
            ];

            $referral = new stdClass();
            $referral->user_id = $this->user->id;
            $referral->code = $this->ref->generate_id();
            $referral->name = $referral->code;
            $referral->value = intval($this->settings->user_referral_value);
            $referral->value_referrer = intval($this->settings->user_referral_value_referrer);
            $referral->type = 'percentage';
            $expire = intval($this->settings->user_referral_expire);
            $referral->expire = $this->request->post('infinity', 'boolean') === false ? date('Y-m-d', strtotime("+{$expire} days")) : null;
            $referral->single = $this->request->post('infinity', 'boolean') === true ? 0 : 1;

            if ($this->ref->count_items(array('user_id' => (int)$referral->user_id, 'from_date' => date('Y-m-d'), 'to_date' => date('Y-m-d', strtotime('+1 days')))) > 5) {
                $result['error'] = $translations->error_referral_limit_reached;
            } elseif (!$referral->single && $this->ref->count_items(array('user_id' => (int)$referral->user_id, 'single' => (int)$referral->single, 'valid' => 1)) > 0) {
                $result['error'] = $translations->error_referral_infinity_limit_reached;
            } elseif ($this->ref->count_items(array('id' => (string)$referral->code)) > 0) {
                $result['error'] = $translations->error_referral_code_exists;
            } elseif ($referral->id = $this->ref->add_item($referral)) {
                $referral->valid = 1;
                $this->design->assign('referral', $referral);

                $result['success'] = 1;
                $result['data'][] = [
                    'name' => $referral->name,
                    'value' => $referral->value,
                    'value_referrer' => $referral->value_referrer,
                    'type' => $referral->type,
                    'expire' => $this->design->date_modifier($referral->expire),
                    'url' => $this->config->root_url . '/' . $this->lang_link . 'r/' . $referral->code,
                    'html' => $this->design->fetch('user_referral.tpl'),
                ];
            } else {
                $result['error'] = $translations->error_referral_wrong_error;
            }

            if ($this->request->post('ajax', 'boolean')) {
                header("Content-type: application/json; charset=UTF-8");
                header("Cache-Control: must-revalidate");
                header("Pragma: no-cache");
                header("Expires: -1");
                print json_encode($result, JSON_UNESCAPED_UNICODE);
                exit();
            } elseif ($this->request->method('post') && !empty($result['success'])) {
                header('Location: ' . $this->config->root_url . '/' . $this->lang_link . $this->current_url);
                exit();
            }
        }

        /*Выборка истории заказов клиента*/
        $orders = $this->orders->get_orders(array('user_id' => $this->user->id));
        $all_status = $this->orderstatus->get_status();
        if ($all_status) {
            $orders_status = array();
            foreach ($all_status as $status_item) {
                $orders_status[$status_item->id] = $status_item;
            }
        }
        $this->design->assign('orders_status', $orders_status);
        $this->design->assign('orders', $orders);

        /* История бонусов пользователя */
        $bonuses = $this->users->get_users_bonuses(array('user_id' => $this->user->id, 'type' => ['order']));
        $this->design->assign('bonuses', $bonuses);

        /* История бонусов пользователя */
        $referrals = $this->ref->get_items(array('user_id' => $this->user->id, 'sort' => 'single_created'));
        $this->design->assign('referrals', $referrals);

        $this->design->assign('request_infinity_referral', empty($this->ref->count_items(array('user_id' => $this->user->id, 'single' => 0, 'valid' => 1))));

        $this->design->assign('meta_title', $this->user->name);

        return $this->design->fetch('user.tpl');
    }
}
