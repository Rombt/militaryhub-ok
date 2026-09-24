<?php
/*
 * Author: Andrii K (andrey.kovt@gmail.com)
 * Date: 07.02.2022
 * Time: 17:46:59
 */

require_once 'Okay.php';

class Variants extends Okay
{

    /*Выборка вариантов*/
    public function get_variants($filter = array(), $count = false)
    {
        // По умолчанию
        $limit = 100000;
        $page = 1;
        $joins = '';
        $where = '1';
        $group_by = '';
        $order = 'v.position, v.id';
        $lang_sql = $this->languages->get_query(array('object' => 'variant'));
        $select = $this->db->placehold(
            "v.id, 
                v.product_id,
                v.weight,
                v.size,
                v.color,
                v.price, 
                NULLIF(v.compare_price, 0) as compare_price, 
                NULLIF(v.promo_price, 0) as promo_price, 
                v.sku, 
                IFNULL(v.stock, ?) as stock, 
                (v.stock IS NULL) as infinity, 
                v.attachment, 
                v.position, 
                v.currency_id, 
                v.feed,
                v.feed_rozetka,
                v.feed_epicentr,
                v.feed_prom,
                v.feed_rss,
                v.image,
                c.rate_from, 
                c.rate_to,
                ROUND(IF(?, IF(p.bonuses > 0, p.bonuses, IFNULL(v.price, 0)*?/100), 0), 2) as bonuses,
                p.main_category_id,
                $lang_sql->fields", $this->settings->max_order_amount, $this->settings->users_bonuses_on, (float)$this->settings->products_bonuses
        );

        if ($count === true) {
            $select = "COUNT(DISTINCT v.id) as count";
        }

        if (defined('IS_CLIENT')) {
            $order = 'IF(stock=0, 0, 1) DESC, v.position, v.id';
        }

        $joins .= "left join __currencies as c on(c.id=v.currency_id)";

        if (isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
        }

        if (isset($filter['page'])) {
            $page = max(1, intval($filter['page']));
        }

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);

        if (empty($filter['product_id']) && empty($filter['id'])) {
            return array();
        }

        if (!empty($filter['product_id'])) {
            $where .= $this->db->placehold(' AND v.product_id in(?@) ', (array)$filter['product_id']);
        }

        if (!empty($filter['id'])) {
            $where .= $this->db->placehold(' AND v.id in(?@) ', (array)$filter['id']);
        }

        if (!empty($filter['in_stock'])) {
            $where .= $this->db->placehold(' AND (v.stock>0 OR v.stock IS NULL)');
        }

        if (!empty($filter['max_stock'])) {
            $where .= $this->db->placehold(' AND (v.stock IS NOT NULL AND v.stock BETWEEN 1 AND ?)', intval($filter['max_stock']));
        }

        if (!empty($filter['size'])) {
            $where .= $this->db->placehold(' AND v.size in(?@) ', (array)$filter['size']);
        }

        if (!empty($filter['color'])) {
            $where .= $this->db->placehold(' AND v.color in(?@) ', (array)$filter['color']);
        }

        if (!empty($filter['sort'])) {
            switch ($filter['sort']) {
            case 'position':
                $order = 'v.position';
                break;
            case 'image':
                $order = '(SELECT count(distinct i.id) > 0 FROM __images i WHERE i.variant_id = v.id LIMIT 1) DESC, IF(stock=0, 0, 1) DESC, v.position, v.id';
                break;
            default:
                $order = 'IF(stock=0, 0, 1) DESC, v.position, v.id';
            }
        }

        if (!empty($order)) {
            $order = "ORDER BY $order";
        }

        // При подсчете нам эти переменные не нужны
        if ($count === true) {
            $order = '';
            $group_by = '';
            $sql_limit = '';
        }

        $query = $this->db->placehold(
            "SELECT $select
            FROM __variants v
            LEFT JOIN __products p ON p.id = v.product_id 
            $lang_sql->join
            $joins
            WHERE 
                $where
                $group_by
                $order 
                $sql_limit
        "
        );
        $this->db->query($query);
        if ($count === true) {
            return $this->db->result('count');
        } else {
            $variants = $this->db->results();
            if (defined('IS_CLIENT') && !empty($variants)) {
                $category_id = $this->settings->restricted_bonuses_category;
                $restricted_bonuses_category = $category_id ? $this->categories->get_category(intval($category_id)) : null;
                foreach ($variants as $row) {
                    if ($row->rate_from != $row->rate_to && $row->currency_id) {
                        $row->price = $row->price * $row->rate_to / $row->rate_from;
                        $row->compare_price = $row->compare_price * $row->rate_to / $row->rate_from;
                        $row->promo_price = $row->promo_price * $row->rate_to / $row->rate_from;
                    }

                    $category = $this->categories->get_category(intval($row->main_category_id));
                    $row->bonuses *= $restricted_bonuses_category && in_array($category->id, $restricted_bonuses_category->children) ? 0 : 1;
                }
            }
            return $variants;
        }
    }

    /*Выборка конкретного варианта*/
    public function get_variant($id)
    {
        if (empty($id)) {
            return false;
        }
        $variant_id_filter = $this->db->placehold('AND v.id=?', intval($id));

        $lang_sql = $this->languages->get_query(array('object' => 'variant'));
        $query = $this->db->placehold(
            "SELECT 
                v.id, 
                v.product_id,
                v.weight,
                v.size,
                v.color,
                v.price, 
                NULLIF(v.compare_price, 0) as compare_price, 
                NULLIF(v.promo_price, 0) as promo_price,
                v.sku, 
                IFNULL(v.stock, ?) as stock, 
                (v.stock IS NULL) as infinity, 
                v.attachment, 
                v.currency_id, 
                v.feed,
                v.feed_rozetka,
                v.feed_epicentr,
                v.feed_prom,
                v.feed_rss,
                v.image,
                c.rate_from, 
                c.rate_to, 
                ROUND(IF(?, IF(p.bonuses > 0, p.bonuses, IFNULL(v.price, 0)*?/100), 0), 2) as bonuses,
                $lang_sql->fields
            FROM __variants v
            LEFT JOIN __products p ON p.id = v.product_id 
            $lang_sql->join
            left join __currencies as c on(c.id=v.currency_id) 
            WHERE 
                1 
                $variant_id_filter 
            LIMIT 1
        ", $this->settings->max_order_amount, $this->settings->users_bonuses_on, (float)$this->settings->products_bonuses
        );

        $this->db->query($query);
        $variant = $this->db->result();
        if (defined('IS_CLIENT') && $variant->id) {
            if ($variant->rate_from != $variant->rate_to && $variant->currency_id) {
                $variant->price = $variant->price * $variant->rate_to / $variant->rate_from;
                $variant->compare_price = $variant->compare_price * $variant->rate_to / $variant->rate_from;
                $variant->promo_price = $variant->promo_price * $variant->rate_to / $variant->rate_from;
            }
        }
        return $variant;
    }

    public function update_variant($id, $variant)
    {
        $variant = (object)$variant;
        $result = $this->languages->get_description($variant, 'variant');

        $v = (array)$variant;
        if (!empty($v)) {
            $query = $this->db->placehold("UPDATE __variants SET ?% WHERE id=? LIMIT 1", $variant, intval($id));
            $this->db->query($query);
        }

        if (!empty($result->description)) {
            $this->languages->action_description($id, $result->description, 'variant', $this->languages->lang_id());
        }

        return $id;
    }

    public function add_variant($variant)
    {
        $variant = (object)$variant;
        $result = $this->languages->get_description($variant, 'variant');

        $query = $this->db->placehold("INSERT INTO __variants SET ?%", $variant);
        $this->db->query($query);
        $variant_id = $this->db->insert_id();
        $this->db->query("UPDATE __variants SET position=id WHERE id=?", $variant_id);

        if (!empty($result->description)) {
            $this->languages->action_description($variant_id, $result->description, 'variant');
        }

        return $variant_id;
    }

    public function delete_variant($ids)
    {
        $ids = (array)$ids;
        if (!empty($ids)) {
            $this->delete_attachment($ids);
            $query = $this->db->placehold("DELETE FROM __variants WHERE id IN (?@) ", $ids);
            $this->db->query($query);
            $this->db->query('UPDATE __purchases SET variant_id=NULL WHERE variant_id IN (?@) ', $ids);
            $this->db->query("DELETE FROM __lang_variants WHERE variant_id IN (?@) ", $ids);
        }
    }

    public function delete_attachment($ids)
    {
        $ids = (array)$ids;
        $query = $this->db->placehold("SELECT id, attachment FROM __variants WHERE id IN (?@) AND attachment !=''", $ids);
        $this->db->query($query);
        $results = (array)$this->db->results();

        foreach ($results as $result) {
            $query = $this->db->placehold("SELECT 1 FROM __variants WHERE attachment=? AND id!=?", $result->filename, $result->id);
            $this->db->query($query);
            $exists = $this->db->num_rows();
            if (!empty($result->filename) && $exists == 0) {
                @unlink($this->config->root_dir . '/' . $this->config->downloads_dir . $result->filename);
            }
            $this->update_variant($result->id, array('attachment' => null));
        }
    }

    public function get_images($filter = array())
    {
        if (!$filter['product_id']) { return false;
        }

        $where = '1';
        $group_by = 'i.id';
        $order = 'i.position';

        if (isset($filter['product_id'])) {
            $where .= $this->db->placehold(" AND i.product_id in (?@) ", (array)$filter['product_id']);
        }

        if (isset($filter['variant_id'])) {
            $where .= $this->db->placehold(" AND v.id in (?@) ", (array)$filter['variant_id']);
        }

        if (!empty($filter['color'])) {
            $where .= $this->db->placehold(" AND v.color in (?@) ", (array)$filter['color']);
        }

        if (isset($filter['group_by'])) {
            switch ($filter['group_by']) {
            case 'variant':
                $group_by = 'i.id, v.id';
                break;
            case 'variant_color':
                $group_by = 'v.id';
                break;
            }
        }

        if (!empty($group_by)) {
            $group_by = "GROUP BY $group_by";
        }

        if (!empty($order)) {
            $order = "ORDER BY $order";
        }

        $query = $this->db->placehold(
            "SELECT
            DISTINCT i.id,
            i.name,
            i.product_id,
            v.id as variant_id,
            i.filename,
            i.position,
            v.color
        FROM __images i
        INNER JOIN __variants v ON v.product_id = i.product_id AND (
            v.id = i.variant_id 
            OR (
                (i.variant_id = 0 OR i.variant_id IS NULL)
                AND v.position = (
                    SELECT MIN(v2.position)
                    FROM __variants v2
                    WHERE v2.product_id = v.product_id
                )
            )
        )
        WHERE $where
        $group_by
        $order
        "
        );
        $this->db->query($query);
        return $this->db->results();
    }
}
