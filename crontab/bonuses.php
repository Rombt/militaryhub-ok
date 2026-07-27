<?php
/*
 * Author: Andrii K (andrey.kovt@gmail.com)
 * Date: 08.02.2022
 * Time: 10:58:08
 */

chdir(dirname(__DIR__));
require_once('api/Okay.php');
error_reporting(1);

class BonusesCronTab extends Okay {
    public function fetch() {

        $users = $this->users->get_users(array('has_birthday' => 0, 'bonuses_type' => 'birthday', 'birthday' => array('from' => intval($this->settings->birthday_bonuses_on), 'to' => intval($this->settings->birthday_bonuses_off))));
        foreach ($users as $user) {
            $this->users->add_user_bonus($user->id, date('Y') . date('md', strtotime($user->birthday)), intval($this->settings->birthday_bonuses), 'birthday');
        }

        $users = $this->users->get_users(array('has_birthday' => 1, 'bonuses_type' => 'birthday_removed', 'birthday' => array('from' => intval($this->settings->birthday_bonuses_on), 'to' => intval($this->settings->birthday_bonuses_off))));
        foreach ($users as $user) {
            $this->users->add_user_bonus($user->id, date('Y') . date('md', strtotime($user->birthday)), max(0, $user->pushed_bonuses - $user->used_bonuses) * -1, 'birthday_removed');
        }

        $count_users = $this->users->count_users(array('birthday_now' => date('Y-m-d', strtotime('now')), 'sms' => 'birthday'));
        if ($count_users > 0) {
            $users = $this->users->get_users(array('birthday_now' => date('Y-m-d', strtotime('now')), 'sms' => 'birthday', 'limit' => $count_users));
            while (!empty($users)) {
                $user = array_shift($users);
                if ($this->sms->user_birthday($user->id)) {
                    $this->users->update_user_bonus($user->id, date('Ymd'), array('sms' => 1), 'birthday');
                }
            }
        }

        $notify = intval($this->settings->users_bonuses_delete_notify);
        $users = $this->users->get_users(array('has_bonuses_auto_removed' => 1, 'bonuses_removed' => ['from' => $notify, 'to' => $notify], 'sms' => 'bonuses_removed'));
        while (!empty($users)) {
            $user = array_shift($users);
            if ($this->sms->user_bonuses_removed($user->id)) {
                $this->users->add_user_bonus($user->id, 0, 0, 'bonuses_removed', 1);
            }
        }

        $this->users->delete_users_bonuses();
    }
}

$results = new BonusesCronTab();
$results->fetch();
exit();
