<?php
/*
 * Author: Andrii K (andrey.kovt@gmail.com)
 * Date: 09.02.2022
 * Time: 09:31:05
 */

require_once('Okay.php');

class Referrals extends Okay {
    public static $table_items = '__referrals';

    public function __construct() {
        parent::__construct();
    }

    public function get_items($filter = array(), $count = false, $one = false) {
        $limit = 1000;
        $page = 1;
        $joins = '';
        $where = '1';
        $group_by = '';
        $order = 'r.position';

        $fields = [
            'r.id',
            'r.user_id',
            'r.name',
            'r.code',
            'r.type',
            'r.value',
            'r.value_referrer',
            'r.expire',
            'r.single',
            'r.usages',
            'r.visible',
            'r.position',
            'r.created',
            'r.last_modify',
            '((DATE(NOW()) <= DATE(r.expire) OR r.expire IS NULL) AND (r.usages=0 OR NOT r.single)) AS valid',
            $this->db->placehold('(SELECT SUM(ub.bonuses) FROM __users_bonuses ub WHERE ub.user_id = r.user_id AND ub.object_id = r.id AND type LIKE ?) as bonuses', 'referral%'),
        ];

        if (isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
        }

        if (isset($filter['page'])) {
            $page = max(1, intval($filter['page']));
        }

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);

        if (isset($filter['id'])) {
            if (is_int($filter['id'])) {
                $where .= $this->db->placehold(' AND r.id = ?', intval($filter['id']));
            } else {
                $where .= $this->db->placehold(' AND r.code = ?', $filter['id']);
            }
        }

        if (!empty($filter['visible'])) {
            $where .= $this->db->placehold(' AND r.visible = ?', intval($filter['visible']));
        }

        if (isset($filter['single'])) {
            $where .= $this->db->placehold(' AND r.single = ?', intval($filter['single']));
        }

        if (isset($filter['valid'])) {
            if ($filter['valid']) {
                $where .= $this->db->placehold(' AND ((DATE(NOW()) <= DATE(r.expire) OR r.expire IS NULL) AND (r.usages = 0 OR NOT r.single))');
            } else {
                $where .= $this->db->placehold(' AND NOT ((DATE(NOW()) <= DATE(r.expire) OR r.expire IS NULL) AND (r.usages = 0 OR NOT r.single))');
            }
        }

        if (!empty($filter['keyword'])) {
            $filter['keyword'] = $this->db->escape($filter['keyword']);
            $filter['keyword'] = trim($filter['keyword']);
            $where .= $this->db->placehold(" AND (r.name LIKE ? OR r.url LIKE ?)", '%' . $filter['keyword'] . '%', '%' . $filter['keyword'] . '%');
        }

        if (!empty($filter['user_id'])) {
            $where .= $this->db->placehold(' AND r.user_id in (?@)', (array)$filter['user_id']);
        }

        if (!empty($filter['from_date']) || !empty($filter['to_date'])) {
            if (!empty($filter['from_date'])) {
                $from = date('Y-m-d', strtotime($filter['from_date']));
            } else {
                $from = '1970-01-01'; /*если стартовой даты нет, берем время с эпохи UNIX*/
            }
            if (!empty($filter['to_date'])) {
                $to = date('Y-m-d', strtotime($filter['to_date']));
            } else {
                $to = date('Y-m-d'); /*если конечной даты нет, берем за дату "сегодня"*/
            }
            $where .= $this->db->placehold(" AND (r.created BETWEEN ? AND ?)", $from, $to);
        }

        if (!empty($filter['sort'])) {
            switch ($filter['sort']) {
                case 'random': {
                    $order = 'RAND()';
                    break;
                }
                case 'name': {
                    $order = 'r.name';
                    break;
                }
                case 'position': {
                    $order = 'r.position';
                    break;
                }
                case 'created': {
                    $order = 'valid DESC, r.id DESC';
                    break;
                }
                case 'single_created': {
                    $order = 'valid DESC, r.single, r.id DESC';
                    break;
                }
                default: {
                    $order = 'r.position';
                }
            }
        }

        if (!empty($group_by)) {
            $group_by = "GROUP BY $group_by";
        }

        if (!empty($order)) {
            $order = "ORDER BY $order";
        }

        if ($count === true) {
            $fields = ['COUNT(DISTINCT r.id) as count'];
            $order = '';
            $group_by = '';
            $sql_limit = '';
        }

        $fields = implode(',', $fields);

        $query = $this->db->placehold("SELECT $fields
            FROM " . self::$table_items . " r
            $joins
            WHERE $where
            $group_by
            $order
            $sql_limit");
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

    public function get_item($id, $filter = array()) {
        if (empty($id)) return false;

        $filter['id'] = $id;

        return $this->get_items($filter, false, true);
    }

    public function add_item($item) {
        if (empty($item->single)) {
            $item->single = 0;
        }
        $query = $this->db->placehold("INSERT INTO __referrals SET ?%", $item);
        
        if ($this->db->query($query)) {
            return $this->db->insert_id();
        }

        return false;
    }

    public function update_item($id, $item) {
        $query = $this->db->placehold("UPDATE __referrals SET ?% WHERE id in(?@) LIMIT ?", $item, (array)$id, count((array)$id));
        $this->db->query($query);
        return $id;
    }

    public function delete_item($id) {
        if (!empty($id)) {
            $query = $this->db->placehold("DELETE FROM __referrals WHERE id = ? LIMIT 1", intval($id));
            $this->db->query($query);
            
            $query = $this->db->placehold("UPDATE __orders SET referral_id = null WHERE referral_id = ?", intval($id));
            $this->db->query($query);

            $query = $this->db->placehold("DELETE FROM __users_referrers WHERE referral_id = ?", intval($id));
            $this->db->query($query);

            $query = $this->db->placehold("DELETE FROM __users_bonuses WHERE object_id = ? AND type LIKE ?", intval($id), 'referral%');
            $this->db->query($query);
        }
    }

    public function update_user_items($user_id, $settings) {
        $settings = (array)$settings;
        
        $query = $this->db->placehold("SELECT id FROM __users_referrers WHERE user_id = ? LIMIT 1", intval($user_id));
        $this->db->query($query);
        
        if (!$id = $this->db->result('id')) {
            $settings['user_id'] = intval($user_id);
            $query = $this->db->placehold("INSERT IGNORE INTO __users_referrers SET ?%", (array)$settings);
            $this->db->query($query);
            $id = $this->db->insert_id();
        // } else {
        //     unset($settings['user_id']);
        //     $query = $this->db->placehold("UPDATE __users_referrers SET ?% WHERE id = ? LIMIT 1", $settings, intval($id));
        //     $this->db->query($query);
        }

        return $id;
    }

    public function generate_id() {
        $t = microtime(true);
        $micro = sprintf("%06d", ($t - floor($t)) * 1000000);
        $date = new DateTime(date('Y-m-d H:i:s.' . $micro . ' ' . $this->config->db_timezone, $t));
        $now = $date->format('ymdhisu');
        $code = strtoupper(dechex(intval($now)));
        return $code;
    }

}
