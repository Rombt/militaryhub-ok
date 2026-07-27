<?php

require_once('api/Okay.php');

class ImportBanlistLogAdmin extends Okay {

    /*Лог импорта товаров*/
    public function fetch() {
        $categoriesPage = max(1, $this->request->get('categories-page', 'integer'));
        $categoriesPageCount = $this->rozetka->get_rozetka_log_pages('categories');
        $brandsPage = max(1, $this->request->get('brands-page', 'integer'));
        $brandsPageCount = $this->rozetka->get_rozetka_log_pages('brands');

        $this->design->assign('categories_pages_count', $categoriesPageCount);
        $this->design->assign('current_categoreis_page', $categoriesPage);
        $this->design->assign('brands_pages_count', $categoriesPageCount);
        $this->design->assign('current_brands_page', $brandsPageCount);

        $categoriesLogs = $this->rozetka->get_rozetka_logs('categories', $categoriesPage);
        $brandsLogs = $this->rozetka->get_rozetka_logs('brands', $brandsPage);
        if(!empty($categoriesLogs) && empty($categoriesLogs['error'])) {
            $categoriesIds = [];
            foreach ($categoriesLogs as $log) {
                $categoriesIds[] = $log->data->id;
            }
            $categoriesData = $this->categories->get_categories(['id'=>$categoriesIds]);

            $categoriesData = array_column($categoriesData, null, 'id' );
            foreach ($categoriesLogs as $categoryLog) {
                if (!empty($categoriesData[$categoryLog->data->id])) {
                    $categoryLog->category = $categoriesData[$categoryLog->data->id];
                }
            }
        }
        $this->design->assign('categories_logs', $categoriesLogs);
        if(!empty($brandsLogs) && empty($brandsLogs['error'])) {
            $brandsIds = [];
            foreach ($brandsLogs as $log) {
                $brandsIds[] = $log->data->id;
            }
            $brandsData = $this->brands->get_brands(['id'=>$brandsIds]);

            $brandsData = array_column($brandsData, null, 'id' );
            foreach ($brandsLogs as $brandLog) {
                if (!empty($brandsData[$brandLog->data->id])) {
                    $brandLog->brand = $brandsData[$brandLog->data->id];
                }
            }
        }
        $this->design->assign('brands_logs', $brandsLogs);

        return $this->design->fetch('import_banlist_log.tpl');
    }

}
