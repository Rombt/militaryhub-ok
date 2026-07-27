<?php

chdir(dirname(__DIR__));
require_once('api/Okay.php');
error_reporting(1);

class NovaPoshtaAddress extends Okay {
    private $start_time;
    private $max_exec_time;
    private $today;
    private $is_wrong_params = 0;

    public function __construct() {
        parent::__construct();
        $this->start_time = microtime(true);
        $this->max_exec_time = min(30, ini_get('max_execution_time'));
        if (empty($this->max_exec_time)) {
            $this->max_exec_time = 30;
        }
        $this->today = time();
    }

    public function fetch() {
        if ($this->is_wrong_params) {
            return false;
        }

        $update_page = $this->settings->last_novaposhta_update_page;
        $update_date = $this->settings->last_novaposhta_update_date;
        if (strtotime($update_date) < strtotime('-1 days')) {
            $result = new stdClass();
            $result->currentPage = intval($update_page);
            $result->pageCount = 1000;

            while ($result->currentPage <= $result->pageCount) {
                $exec_time = microtime(true) - $this->start_time;
                if ($exec_time+3 >= $this->max_exec_time) {
                    return true;
                }

                if (false !== ($res = $this->np->get_cache_address(array('page' => ++$result->currentPage)))) {
                    if (!empty($res)) {
                        $result = (object)$res;
                        $this->settings->last_novaposhta_update_page = max(0, $result->currentPage);
                    } else {
                        $result->currentPage = $result->pageCount;
                    }
                } else {
                    return false;
                }
            }

            if (strtotime($update_date) < strtotime(date('Y-m-d')) && $result->currentPage > $result->pageCount) {
                $this->settings->last_novaposhta_update_page = 0;
                $this->settings->last_novaposhta_update_date = date('c');
                $query = $this->db->placehold("UPDATE __addresses_novaposhta SET enabled = 0 WHERE enabled = 1 AND modified < ?", date('Y-m-d 00:00:00', $this->today));
                $this->db->query($query);
            }
        }

        return true;
    }
}

$results = new NovaPoshtaAddress();
$results->fetch();
exit();
