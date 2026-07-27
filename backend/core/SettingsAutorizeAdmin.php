<?php

require_once('api/Okay.php');

class SettingsAutorizeAdmin extends Okay {

    /* Настройки ESputnik */
    public function fetch() {
        if ($this->request->method('POST')) {
            $this->settings->google_client_id = $this->request->post('google_client_id');
            $this->settings->google_client_secret = $this->request->post('google_client_secret');
            $google_enabled = $this->request->post('google_enabled', 'boolean');
            if (!$this->settings->google_client_secret || !$this->settings->google_client_secret) {
                $google_enabled = false;
            }
            $this->settings->google_enabled = $google_enabled;
            $this->settings->google_test_mode = $this->request->post('google_test_mode', 'boolean');

            $this->settings->facebook_app_id = $this->request->post('facebook_app_id');
            $this->settings->facebook_app_secret = $this->request->post('facebook_app_secret');
            $facebook_enabled = $this->request->post('facebook_enabled', 'boolean');
            if (!$this->settings->google_client_secret || !$this->settings->google_client_secret) {
                $facebook_enabled = false;
            }
            $this->settings->facebook_enabled = $facebook_enabled;
            $this->settings->facebook_test_mode = $this->request->post('facebook_test_mode', 'boolean');

            $this->design->assign('message_success', 'saved');
        }
        return $this->design->fetch('settings_autorize.tpl');
    }

}
