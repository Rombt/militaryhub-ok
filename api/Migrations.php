<?php
/**
 * Created by PhpStorm.
 * User: AndriiK
 * Date: 12.04.2020
 * Time: 19:41
 */

require_once('Database.php');
require_once('Settings.php');

class Migrations {
    public function __construct() {
        $this->db = new Database();
        $this->settings = new Settings();
    }

    protected function get_items($filter = array(), $count = false, $one = false) {
        $limit = 1000;
        $page  = 1;
        $joins = '';
        $where = '1';
        $group_by = '';
        $order = 'm.id';
        $fields = [
            'm.id',
            'm.name',
            'm.created',
        ];

        if ($count === true) {
            $fields = ['COUNT(DISTINCT m.id) as count'];
        }

        if (isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
        }

        if (isset($filter['page'])) {
            $page = max(1, intval($filter['page']));
        }

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        if (isset($filter['id'])) {
            $where .= $this->db->placehold(' AND m.id >= ?', (int)$filter['id']);
        }

        if (!empty($filter['created'])) {
            $where .= $this->db->placehold(' AND m.created > ?', $filter['created']);
        }

        if (!empty($group_by)) {
            $group_by = "GROUP BY $group_by";
        }

        if (!empty($order)) {
            $order = "ORDER BY $order";
        }

        if ($count === true) {
            $order      = '';
            $group_by   = '';
            $sql_limit  = '';
        }

        $fields = implode(',', $fields);

        $query = $this->db->placehold("SELECT $fields
            FROM __migrations m
            $joins
            WHERE 
                $where
                $group_by
                $order
                $sql_limit
        ");
        $this->db->query($query);

        if ($count === true) {
            return $this->db->result('count');
        } elseif ($one === true) {
            return $this->db->result('name');
        } else {
            return $this->db->results('name');
        }
    }

    protected function count_items($filter = array()) {
        return $this->get_items($filter, true);
    }

    protected function get_item($id, $filter = array()) {
        $filter['id'] = $id;
        $filter['limit'] = 1;

        return $this->get_items($filter, false, true);
    }

    protected function add_item($item) {
        $item = (object)$item;
        $query = $this->db->placehold("INSERT IGNORE INTO __migrations SET ?%", $item);
        $this->db->query($query);
        return $this->db->insert_id();
    }
}