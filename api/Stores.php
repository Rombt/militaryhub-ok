<?php

require_once('Okay.php');

class Stores extends Okay {
    public function get_stores($filter = array(), $count = false, $one = false) {
        $limit = 25;
        $page  = 1;
        $joins = '';
        $where = '1';
        $group_by = '';
        $order = 's.position';
        
        $lang_sql = $this->languages->get_query(array('object' => 'store'));
        
        $select = "DISTINCT s.id,
        s.enabled,
        s.last_modify,
        s.position,
        $lang_sql->fields";
        
        if ($count === true) {
            $select = "COUNT(DISTINCT s.id) as count";
        }

        if (isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
        }

        if (isset($filter['page'])) {
            $page = max(1, intval($filter['page']));
        }

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        if (isset($filter['enabled'])) {
            $where .= $this->db->placehold(' AND s.enabled=?', intval($filter['enabled']));
        }

        if (isset($filter['id'])) {
            if (is_int($filter['id'])) {
                $where .= $this->db->placehold(' AND s.id = ?', (int)$filter['id']);
            } else {
                $where .= $this->db->placehold(' AND s.id in (?@)', (array)$filter['id']);
            }
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
        
        $query = $this->db->placehold("SELECT $select
            FROM __stores s
            $lang_sql->join
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
            return $this->db->result();
        } else {
            return $this->db->results();
        }
    }

    public function count_store($filter = array()) {
        return $this->get_stores($filter, true);
    }

    public function get_store($id) {
        if (empty($id)) {
            return false;
        }
        
        $filter = ['id' => $id];

        return $this->get_stores($filter, false, true);
    }

    public function add_store($store) {
        $store = (object)$store;

        // Проверяем есть ли мультиязычность и забираем описания для перевода
        $result = $this->languages->get_description($store, 'store');
        
        $query = $this->db->placehold('INSERT INTO __stores SET ?%', $store);
        if (!$this->db->query($query)) {
            return false;
        }
        
        $id = $this->db->insert_id();
        
        // Если есть описание для перевода. Указываем язык для обновления
        if (!empty($result->description)) {
            $this->languages->action_description($id, $result->description, 'store');
        }
        
        $this->db->query("UPDATE __stores SET position=id WHERE id=?", intval($id));
        return $id;
    }

    public function update_store($id, $store) {
        $store = (object)$store;
        // Проверяем есть ли мультиязычность и забираем описания для перевода
        $result = $this->languages->get_description($store, 'store');
        
        $query = $this->db->placehold("UPDATE __stores SET ?% WHERE id in(?@)", $store, (array)$id);
        $this->db->query($query);
        
        // Если есть описание для перевода. Указываем язык для обновления
        if (!empty($result->description)) {
            $this->languages->action_description($id, $result->description, 'store', $this->languages->lang_id());
        }
        return $id;
    }

    /*Удаление способа доставки*/
    public function delete_store($id) {
        if (!empty($id)) {
            $query = $this->db->placehold("DELETE FROM __stores WHERE id=? LIMIT 1", intval($id));
            $this->db->query($query);
            $this->db->query("DELETE FROM __lang_stores WHERE store_id=?", intval($id));
        }
    }    
}
