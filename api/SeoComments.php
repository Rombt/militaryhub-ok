<?php

require_once('Okay.php');

class SeoComments extends Okay {
    
    /*Выборка записи для изменения комментария*/
    public function get_seocomment($id) {
        if(gettype($id) == 'string') {
            $where = $this->db->placehold('AND sc.url=? ', $id);
        } else {
            $where = $this->db->placehold('AND sc.id=? ', intval($id));
        }
        
        $query = "SELECT 
                sc.id, 
                sc.url, 
                sc.body
            FROM __seo_comments sc 
            WHERE 
                1 
                $where 
            LIMIT 1
        ";
       
        $this->db->query($query);
        return $this->db->result();
    }

    /*Выборка всех изменений комментариев*/
    public function get_seocomments($filter = array(), $count = false) {
        // По умолчанию
        $limit = 100;
        $page = 1;
        $joins = '';
        $where = '1';
        $group_by = '';
        $order = '';
        $select = "sc.id, 
                sc.url, 
                sc.body";

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
            FROM __seo_comments sc      
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
    public function add_seocomment($seo_comment) {
        $seo_comment = (object)$seo_comment;
       
        $query = $this->db->placehold('INSERT INTO __seo_comments SET ?%', $seo_comment);
        if(!$this->db->query($query)) {
            return false;
        }
        
        $id = $this->db->insert_id();
        return $id;
    }

    /*Обновление страницы*/
    public function update_seocomment($id, $seocomment) {
        $seocomment = (object)$seocomment;
       
        $query = $this->db->placehold('UPDATE __seo_comments SET ?% WHERE id in (?@)', $seocomment, (array)$id);
		
        if(!$this->db->query($query)) {
            return false;
        }
        
        return $id;
    }

    /*Удаление страницы*/
    public function delete_seocomment($id) {
        if(!empty($id)) {
            $query = $this->db->placehold("DELETE FROM __seo_comments WHERE id=? LIMIT 1", intval($id));
            if($this->db->query($query)) {
                return true;
            }
        }
        return false;
    }
}
