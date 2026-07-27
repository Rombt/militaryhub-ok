<?php

require_once('api/Okay.php');

class SettingsESputnikAdmin extends Okay {

    /* Настройки ESputnik */
    public function fetch() {
        if ($this->request->method('POST')) {
            $this->settings->esputnik_user = $this->request->post('esputnik_user');
            $this->settings->esputnik_pass = $this->request->post('esputnik_pass');
            $this->settings->esputnik_notify_from_email = $this->request->post('esputnik_notify_from_email');

            $this->design->assign('message_success', 'saved');
        }
        return $this->design->fetch('settings_esputnik.tpl');
    }

}
