<?php

chdir(dirname(__DIR__));
require_once('api/Okay.php');

class RozetkaBanlist extends Okay {
    public function fetch() {
        $this->rozetka->get_ban_list();

        return true;
    }
}

$results = new RozetkaBanlist();
$results->fetch();
exit();