<?php
/**
 * Created by PhpStorm.
 * User: AndriiK
 * Date: 10.05.2018
 * Time: 23:25
 */

require_once('Okay.php');

class Wishlist extends Okay {
    public $timeout = 12 * 30 * 24 * 2600;
    public $limit = 350;

    public function get($user_id) {
        if (!$user_id) return false;

        $this->db->query("SELECT products_ids, last_modify FROM __wishlist WHERE user_id=? LIMIT 1", intval($user_id));
        return $this->db->result();
    }

    public function get_product_wishlist($product_id) {
        if (!$product_id) return false;

        $this->db->query("SELECT COUNT(id) as count
        FROM __wishlist
        WHERE products_ids = ? OR products_ids LIKE '?,%' OR products_ids LIKE '%,?,%' OR products_ids LIKE '%,?'
        ", intval($product_id), intval($product_id), intval($product_id), intval($product_id));
        return $this->db->result('count');
    }

    public function update($user_id, $ids) {
        if (!$user_id || empty($ids)) return false;

        $this->db->query("SELECT products_ids FROM __wishlist WHERE user_id=?", intval($user_id));
        if ($this->db->num_rows() > 0) {
            $query = $this->db->placehold("UPDATE __wishlist SET products_ids=?, last_modify=now() WHERE user_id=?", $ids, intval($user_id));
        } else {
            $query = $this->db->placehold("INSERT INTO __wishlist SET user_id=?, products_ids=?", intval($user_id), $ids);
        }
        $this->db->query($query);
        return true;
    }

    public function clear($user_id) {
        if (!$user_id) return false;

        $this->db->query("UPDATE __wishlist SET products_ids=?, last_modify=now() WHERE user_id=?", '', intval($user_id));
        return true;
    }
}