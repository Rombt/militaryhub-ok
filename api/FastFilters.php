<?php

require_once('Okay.php');

class FastFilters extends Okay {
    
    /*Выборка записи для изменения быстрофильтра*/
    public function get_fastfilter($id) {
        if(gettype($id) == 'string') {
            $where = $this->db->placehold('AND ff.url=? ', $id);
        } else {
            $where = $this->db->placehold('AND ff.id=? ', intval($id));
        }
        
        $query = "SELECT 
                ff.id, 
                ff.url, 
                ff.body
            FROM __fast_filters ff 
            WHERE 
                1 
                $where 
            LIMIT 1
        ";
       
        $this->db->query($query);
        return $this->db->result();
    }

    /*Выборка всех изменений быстрофильтров*/
    public function get_fastfilters($filter = array(), $count = false) {
        // По умолчанию
        $limit = 100;
        $page = 1;
        $joins = '';
        $where = '1';
        $group_by = '';
        $order = '';
        $select = "ff.id, 
                ff.url, 
                ff.body";

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
            FROM __fast_filters ff      
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
            $seo_comments = array();
            foreach($this->db->results() as $seo_comment) {
                $seo_comments[$seo_comment->id] = $seo_comment;
            }
            return $seo_comments;
        }
    }

    /*Добавление страницы*/
    public function add_fastfilter($fast_filter) {
        $fast_filter = (object)$fast_filter;
       
        $query = $this->db->placehold('INSERT INTO __fast_filters SET ?%', $fast_filter);
        if(!$this->db->query($query)) {
            return false;
        }
        
        $id = $this->db->insert_id();
        return $id;
    }

    /*Обновление страницы*/
    public function update_fastfilter($id, $fastfilter) {
        $fastfilter = (object)$fastfilter;
       
        $query = $this->db->placehold('UPDATE __fast_filters SET ?% WHERE id in (?@)', $fastfilter, (array)$id);
		
        if(!$this->db->query($query)) {
            return false;
        }
        
        return $id;
    }

    /*Удаление страницы*/
    public function delete_fastfilter($id) {
        if(!empty($id)) {
            $query = $this->db->placehold("DELETE FROM __fast_filters WHERE id=? LIMIT 1", intval($id));
            if($this->db->query($query)) {
                return true;
            }
        }
        return false;
    }
}
