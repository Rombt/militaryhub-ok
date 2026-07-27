<?php

require_once('Okay.php');

class MetaChanges extends Okay {
    
    /*Выборка записи для изменения мета*/
    public function get_metachange($id) {
        if(gettype($id) == 'string') {
            $where = $this->db->placehold('AND mc.url=? ', $id);
        } else {
            $where = $this->db->placehold('AND mc.id=? ', intval($id));
        }
        
        $query = "SELECT 
                mc.id, 
                mc.url, 
                mc.meta_title,
				mc.meta_description,
				mc.meta_h1
            FROM __meta_changes mc 
            WHERE 
                1 
                $where 
            LIMIT 1
        ";
       
        $this->db->query($query);
        return $this->db->result();
    }

    /*Выборка всех изменений мета*/
    public function get_metachanges($filter = array(), $count = false) {
        // По умолчанию
        $limit = 100;
        $page = 1;
        $joins = '';
        $where = '1';
        $group_by = '';
        $order = '';
        $select = "mc.id, 
                mc.url, 
                mc.meta_title,
				mc.meta_description,
				mc.meta_h1";

        if ($count === true) {
            $select = "COUNT(DISTINCT f.id) as count";
        }

        if(isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
        }

        if(isset($filter['page'])) {
            $page = max(1, intval($filter['page']));
        }

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        if (!empty($order)) {
            $order = "ORDER BY $order";
        }

        // При подсчете нам эти переменные не нужны
        if ($count === true) {
            $order      = '';
            $group_by   = '';
            $sql_limit  = '';
        }

        $query = $this->db->placehold("SELECT $select
            FROM __meta_changes mc      
            WHERE 
                $where
                $group_by
                $order 
                $sql_limit
        ");
	
        $this->db->query($query);
        if ($count === true) {
            return $this->db->result('count');
        } else {
            $meta_changes = array();
            foreach($this->db->results() as $meta_change) {
                $meta_changes[$meta_change->id] = $meta_change;
            }
            return $meta_changes;
        }
    }

    /*Добавление страницы*/
    public function add_metachange($meta_change) {
        $meta_change = (object)$meta_change;
       
        $query = $this->db->placehold('INSERT INTO __meta_changes SET ?%', $meta_change);
        if(!$this->db->query($query)) {
            return false;
        }
        
        $id = $this->db->insert_id();
        return $id;
    }

    /*Обновление страницы*/
    public function update_metachange($id, $meta_change) {
        $meta_change = (object)$meta_change;
       
        $query = $this->db->placehold('UPDATE __meta_changes SET ?% WHERE id in (?@)', $meta_change, (array)$id);
		
        if(!$this->db->query($query)) {
            return false;
        }
        
        return $id;
    }

    /*Удаление страницы*/
    public function delete_metachange($id) {
        if(!empty($id)) {
            $query = $this->db->placehold("DELETE FROM __meta_changes WHERE id=? LIMIT 1", intval($id));
            if($this->db->query($query)) {
                return true;
            }
        }
        return false;
    }
}
