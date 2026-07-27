<?php

require_once('backend/core/IndexAdmin.php');

class ContentEngineBackend extends IndexAdmin
{
    public $feed_manager_config;

    public function __construct()
    {

        $backend_translations = $this->backend_translations;
        if (! isset($backend_translations)) {
            return;
        }

        $lang_file = 'ModulesCore/modules/ContentEngine/langs/ru.php';
        if (! file_exists($lang_file)) {
            return;
        }
        include_once $lang_file;

        $newMenuItems = [
            'left_rmbt_content_engine' => array(
                'left_rmbt_products_image' => array( 'ProductsImage' ),
                'left_rmbt_products_text' => array( 'ProductsText' ),
            ),
        ];
        $this->addToLeftMenu($newMenuItems);


        parent::__construct();
    }
}
