<?php

require_once('backend/core/IndexAdmin.php');

class FeedManagerBackend extends IndexAdmin
{
    use FeedManagerUtils;

    public $feed_manager_config;

    public function __construct()
    {

        $backend_translations = $this->backend_translations;
        if (! isset($backend_translations)) {
            return;
        }

        $lang_file = 'ModulesCore/modules/FeedManager/langs/ru.php';
        if (! file_exists($lang_file)) {
            return;
        }
        include_once $lang_file;

        $newMenuItems = [
            'left_rmbt_feed_manager' => array(
                'left_rmbt_feed_google' => array( 'FeedGoogle' ),
                'left_rmbt_feed_prom' => array( 'FeedProm' ),
                'left_rmbt_feed_rozetka' => array( 'FeedRozetka' ),
            ),
        ];
        $this->addToLeftMenu($newMenuItems);

        // echo '****************';
        // exit;

        // $this->promotions_config = require 'ModulesCore/modules/FeedManager/feed_manager_config.php';

        // возможность обращения к статическим методам этого класса напрямую из шаблонов
        // $this->design->smarty->registerClass( "FeedManagerUtils", "FeedManagerUtils" );


        parent::__construct();
    }
}
