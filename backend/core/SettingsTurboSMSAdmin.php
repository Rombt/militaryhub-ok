<?php
/*
 * Author: Andrii K (andrey.kovt@gmail.com)
 * Date: 08.02.2022
 * Time: 12:42:42
 */

require_once('api/Okay.php');

class SettingsTurboSMSAdmin extends Okay {
    public function fetch() {
        if ($this->request->method('POST')) {
            $this->settings->turbosms_enabled = $this->request->post('turbosms_enabled', 'boolean');
            $this->settings->turbosms_test_mode = $this->request->post('turbosms_test_mode', 'boolean');
            $this->settings->turbosms_sender = $this->request->post('turbosms_sender');
            $this->settings->turbosms_login = $this->request->post('turbosms_login');
            $this->settings->turbosms_pass = $this->request->post('turbosms_pass');
            $this->settings->update('turbosms_messages', $this->request->post('turbosms_messages'));

            $this->design->assign('message_success', 'saved');
        }

        $btr_languages = array();
        foreach ($this->languages->lang_list() as $label=>$l) {
            if (file_exists("backend/lang/".$label.".php")) {
                $btr_languages[$l->name] = $l->label;
            }
        }
        $this->design->assign('btr_languages', $btr_languages);
        
        $this->design->assign('sms_orders_fields', $this->sms->get_users_fields());

        return $this->design->fetch('settings_turbosms.tpl');
    }
}