<?php

chdir(dirname(__DIR__));
require_once('api/Okay.php');

class JustInAddress extends Okay {
    public function fetch() {
        $today = time();

        $this->ji->get_api_wares();
        $this->db->query("UPDATE __addresses_justin SET enabled=0 WHERE modified < ?", date("Y-m-d H:i:s", time() - 60));
        $this->db->query("UPDATE __addresses_justin SET enabled=1 WHERE modified >= ?", date("Y-m-d H:i:s", $today));

        return true;
    }
}

$results = new JustInAddress();
$results->fetch();
exit();
