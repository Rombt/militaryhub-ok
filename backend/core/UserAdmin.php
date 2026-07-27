<?php
/*
 * Author: Andrii K (andrey.kovt@gmail.com)
 * Date: 08.02.2022
 * Time: 15:14:28
 */

require_once('api/Okay.php');

class UserAdmin extends Okay {
    
    public function fetch() {
        $user = new stdClass();

        /*Прием данных о пользователе*/
        if ($this->request->method('post')) {
            $user->id = $this->request->post('id', 'integer');
            $user->name = $this->request->post('name');
            $user->email = $this->request->post('email');
            $user->phone = $this->request->post('phone');
            $user->address = $this->request->post('address');
            $user->group_id = $this->request->post('group_id');
            if ($birthday = $this->request->post('birthday')) {
                $user->birthday = date('Y-m-d', strtotime($birthday));
            } else {
                $user->birthday = null;
            }

            /*Не допустить одинаковые email пользователей*/
            if (empty($user->name)) {
                $this->design->assign('message_error', 'empty_name');
            } elseif (empty($user->email)) {
                $this->design->assign('message_error', 'empty_email');
            } elseif (($u = $this->users->get_user($user->email)) && $u->id!=$user->id) {
                $this->design->assign('message_error', 'login_exists');
            } else {
                /*Обновление пользователя*/
                $user->id = $this->users->update_user($user->id, $user);
                $this->design->assign('message_success', 'updated');
                $user = $this->users->get_user(intval($user->id));

                if ($user->id && ($bonuses = $this->request->post('bonuses', 'float')) && ($manager = $this->managers->get_manager())
                    && $this->users->add_user_bonus($user->id, $manager->id, $bonuses, 'manager_' . time())
                ) {
                    if ($bonuses > 0) {
                        $_SESSION['message_success'] = 'bonuses_added';
                    } elseif ($bonuses < 0) {
                        $_SESSION['message_success'] = 'bonuses_deleted';
                    }
                    header('Location: ' . $this->config->root_url . $_SERVER['REQUEST_URI']);
                    exit();
                }
            }
        }

        $id = $this->request->get('id', 'integer');
        if (!empty($id)) {
            $user = $this->users->get_user(intval($id));
        }

        /*История заказов пользователя*/
        if (!empty($user)) {
            $this->design->assign('user', $user);
            
            $orders = $this->orders->get_orders(array('user_id'=>$user->id));
            $this->design->assign('orders', $orders);
        
        }

        $groups = $this->users->get_groups();
        $this->design->assign('groups', $groups);

        return $this->design->fetch('user.tpl');
    }
    
}
