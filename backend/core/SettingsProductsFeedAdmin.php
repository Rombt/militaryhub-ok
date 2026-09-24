<?php

require_once('api/Okay.php');

class SettingsProductsFeedAdmin extends Okay {

    public function fetch () {
        if($this->request->method('POST')) {
            $this->settings->feed_prom_rozetka_relations = $this->request->post('feed_prom_rozetka_relations');
        }
        return $this->design->fetch('settings_products_feed.tpl');
    }
}