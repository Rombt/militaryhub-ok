<?php

require_once('Okay.php');

class ProductsTypes extends Okay {
    public function get_items($filter = array(), $count = false, $one = false) {
        $limit = 100;
        $page  = 1;
        $joins = '';
        $where = '1';
        $group_by = '';
        $order = 'p.position';
        $lang_sql = $this->languages->get_query(array('object' => 'products_type'));
        $select = "DISTINCT p.id, 
                p.brand_id,
                p.url, 
                p.image, 
                p.last_modify,
                p.visible,
                p.position,
                b.name as brand_name,
                $lang_sql->fields";
        
        if ($count === true) {
            $select = "COUNT(DISTINCT p.id) as count";
        }

        if (isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
        }

        if (isset($filter['page'])) {
            $page = max(1, intval($filter['page']));
        }

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        if (isset($filter['visible'])) {
            $where .= $this->db->placehold(' AND p.visible=?', intval($filter['visible']));
        }

        if (isset($filter['brand_id'])) {
            $where .= $this->db->placehold(' AND p.brand_id=?', intval($filter['brand_id']));
        }

        if (!empty($filter['url'])) {
            $where .= $this->db->placehold(' AND p.url in(?@) ', (array)$filter['url']);
        }

        if (isset($filter['id'])) {
            if (is_int($filter['id'])) {
                $where .= $this->db->placehold(' AND p.id = ?', (int)$filter['id']);
            } elseif (is_array($filter['id']) && !empty($filter['id'])) {
                $where .= $this->db->placehold(' AND p.id in (?@) ', (array)$filter['id']);
            } else {
                $where .= $this->db->placehold(' AND p.url = ?', (string)$filter['id']);
            }
        }

        if (!empty($filter['group_by'])) {
            switch ($filter['group_by']) {
                case 'url':
                    $group_by = 'p.url';
                    break;
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
            FROM __products_types p
            LEFT JOIN __brands b ON b.id=p.brand_id
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

    public function count_items($filter = array()) {
        return $this->get_items($filter, true);
    }

    public function get_item($id) {
        if (empty($id)) {
            return false;
        }
        
        $filter = ['id' => $id];
        
        return $this->get_items($filter, false, true);
    }

    public function add_item($item) {
        $item = (object)$item;
        $item->url = preg_replace("/[\s]+/ui", '', $item->url);
        $item->url = strtolower(preg_replace("/[^0-9a-z]+/ui", '', $item->url));
        if (empty($item->url)) {
            $item->url = $this->translit_alpha($item->name);
        }
        
        $result = $this->languages->get_description($item, 'products_type');

        $this->db->query("INSERT INTO __products_types SET ?%", $item);
        $id = $this->db->insert_id();
        $this->db->query("UPDATE __products_types SET position=id WHERE id=? LIMIT 1", $id);
        
        if (!empty($result->description)) {
            $this->languages->action_description($id, $result->description, 'products_type');
        }

        return $id;
    }

    public function update_item($id, $item) {
        $item = (object)$item;

        $result = $this->languages->get_description($item, 'products_type');

        $query = $this->db->placehold("UPDATE __products_types SET ?% WHERE id=? LIMIT 1", $item, intval($id));
        $this->db->query($query);
        
        if (!empty($result->description)) {
            $this->languages->action_description($id, $result->description, 'products_type', $this->languages->lang_id());
        }
        
        return $id;
    }

    public function delete_item($id) {
        if (!empty($id)) {
            // $this->image->delete_image($id, 'image', 'products_type', $this->config->original_items_dir, $this->config->resized_items_dir);
            $query = $this->db->placehold("DELETE FROM __products_types WHERE id=? LIMIT 1", $id);
            $this->db->query($query);
            $this->db->query("DELETE FROM __lang_products_types WHERE products_type_id=?", $id);
            $this->db->query("UPDATE __categories SET attach_brand_id=0 WHERE attach_brand_id=?", $id);
        }
    }
    
}
