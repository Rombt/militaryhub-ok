<?php
chdir(dirname(__DIR__));
require_once('api/Okay.php');

class RozetkaCategories extends Okay {
    public function fetch() {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        $this->rozetka->sync_api_categories_flat();
        return true;
    }
}

$results = new RozetkaCategories();
$results->fetch();
exit();
