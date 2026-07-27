<?php
/**
 * Created by PhpStorm.
 * User: AndriiK
 * Date: 14.11.2018
 * Time: 14:04
 */

require_once('api/Okay.php');

class SettingsJustInAdmin extends Okay {
    public function fetch()
    {
        if ($this->request->method('POST')) {
            $this->settings->justin_login = $this->request->post('justin_login');
            $this->settings->justin_pass = $this->request->post('justin_pass');
            $this->settings->justin_api_key = $this->request->post('justin_api_key');

            if (!empty($justin_city = $this->request->post('justin_city')))
                $this->settings->justin_city = $justin_city;
            if (!empty($justin_ware = $this->request->post('justin_ware')))
                $this->settings->justin_ware = $justin_ware;

            $this->design->assign('message_success', 'saved');
        }

        $justin_address = $this->ji->get_address($this->settings->justin_city, $this->settings->justin_ware, true);
        $this->design->assign('justin_address', $justin_address);
        $this->design->assign('justin_city', $justin_address->CityDescription);
        $this->design->assign('justin_ware', $justin_address->Description);

        $btr_languages = array();
        foreach ($this->languages->lang_list() as $label=>$l) {
            if (file_exists("backend/lang/".$label.".php")) {
                $btr_languages[$l->name] = $l->label;
            }
        }

        $this->design->assign('btr_languages', $btr_languages);
        return $this->design->fetch('settings_justin.tpl');
    }
}