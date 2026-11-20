<?php

namespace ProductsImport;

use ModulesCore\ModuleLoader;

class ProductsImportBackend
{

    public function __construct()
    {

        ModuleLoader::addMenu(
            [
                'left_rmbt_products_import' => [
                    'left_rmbt_products_import' => array( 'ProductsImport' )
                ]
            ]
        );


        $lang_file = 'ModulesCore/modules/ProductsImport/langs/ru.php';
        if (! file_exists($lang_file)) {
            return;
        }

        ModuleLoader::addBackendTranslations(include $lang_file);
        


    }
}
