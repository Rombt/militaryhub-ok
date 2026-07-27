<?php
/*
 * Author: Andrii K (andrey.kovt@gmail.com)
 * Date: 08.02.2022
 * Time: 09:59:00
 */

require_once('Okay.php');

class Users extends Okay
{

    // осторожно, при изменении соли испортятся текущие пароли пользователей
    private $salt = '8e86a279d6e182b3c811c559e6b15484';

    /*Выборка пользователей*/
    public function get_users($filter = array(), $count = false, $one = false) {
        // По умолчанию
        $limit = 1000;
        $page = 1;
        $joins = '';
        $where = '1';
        $group_by = 'u.id';
        $order = 'u.name';
        $fields = [
            'u.id',
            'u.email',
            'u.password',
            'u.name',
            'u.phone',
            'u.address',
            'u.birthday',
            'u.group_id',
            'u.last_ip',
            'u.created',
            'u.image',
            'u.q_comment',
            'u.facebook_id',
            'u.facebook_json',
            'u.google_id',
            'u.google_json',
            'g.discount',
            'g.name as group_name',
            'ur.referral_id',
            '(SELECT SUM(ub.bonuses) FROM __users_bonuses ub WHERE ub.user_id = u.id) as bonuses',
        ];

        if ($count === true) {
            $fields = ['COUNT(DISTINCT u.id) as count'];
        }

        if (isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
        }

        if (isset($filter['page'])) {
            $page = max(1, intval($filter['page']));
        }

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);

        $joins .= " LEFT JOIN __groups g ON u.group_id=g.id";
        $joins .= " LEFT JOIN __users_referrers ur ON ur.user_id = u.id";

        if (isset($filter['id'])) {
            if (gettype($filter['id']) == 'string' && empty($filter['type'])) {
                $where .= $this->db->placehold(' AND u.email = ? ', $filter['id']);
            } elseif (isset($filter['type']) && $filter['type'] == 'google') {
                $where .= $this->db->placehold(' AND u.google_id = ? ', $filter['id']);
            } elseif (isset($filter['type']) && $filter['type'] == 'facebook') {
                $where .= $this->db->placehold(' AND u.facebook_id = ? ', $filter['id']);
            } else {
                $where .= $this->db->placehold(' AND u.id = ? ', intval($filter['id']));
            }
        }

        if (isset($filter['group_id'])) {
            $where .= $this->db->placehold(' AND u.group_id in(?@)', (array)$filter['group_id']);
        }

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            $keyword_filter = ' ';
            foreach ($keywords as $keyword) {
                $keyword_filter .= $this->db->placehold('AND (
                    u.name LIKE "%' . $this->db->escape(trim($keyword)) . '%" 
                    OR u.email LIKE "%' . $this->db->escape(trim($keyword)) . '%" 
                    OR u.last_ip LIKE "%' . $this->db->escape(trim($keyword)) . '%" 
                ) ');
            }
            $where .= $keyword_filter;
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
            $where .= $this->db->placehold(" AND (u.created BETWEEN ? AND ?)", $from, $to);
        }

        if (isset($filter['ip'])) {
            $where .= $this->db->placehold(' AND u.last_ip in(?@)', (array)$filter['ip']);
        }

        if (isset($filter['user_exist'])) {
            $email = '';
            if (!empty($filter['email'])) {
                $email = $this->db->placehold(" OR u.email = ?", (string)$filter['email']);
            }
            $where .= $this->db->placehold(" AND (u.phone = ? $email)", (string)$filter['phone']);
        }

        if (isset($filter['has_birthday'])) {
            $where .= $this->db->placehold(' AND u.birthday IS NOT NULL');
            if (!empty($filter['bonuses_type'])) {
                list($type) = explode('_', (string)$filter['bonuses_type']);
                $where .= $this->db->placehold(" AND (SELECT count(*) FROM __users_bonuses WHERE user_id = u.id AND type = ? AND object_id = CONCAT(DATE_FORMAT(NOW(), '%Y'), DATE_FORMAT(u.birthday, '%m%d'))) = ?", (string)$type, intval($filter['has_birthday']));
                if ($filter['bonuses_type'] !== $type) {
                    $where .= $this->db->placehold(" AND (SELECT count(*) = 0 FROM __users_bonuses WHERE user_id = u.id AND type = ? AND object_id = CONCAT(DATE_FORMAT(NOW(), '%Y'), DATE_FORMAT(u.birthday, '%m%d'))) = ?", (string)$filter['bonuses_type'], intval($filter['has_birthday']));
                }
            }

            if (!empty($filter['birthday'])) {
                switch ($filter['bonuses_type']) {
                    case 'birthday':
                        $where .= $this->db->placehold(" AND (DATE_FORMAT(CURDATE(), '%m%d') BETWEEN DATE_FORMAT(u.birthday - INTERVAL ? DAY, '%m%d') AND DATE_FORMAT(u.birthday + INTERVAL ? DAY, '%m%d'))", intval($filter['birthday']['from']), intval($filter['birthday']['to']));
                        break;
                    case 'birthday_removed':
                        $fields[] = $this->db->placehold("IFNULL((SELECT SUM(bonuses)
                            FROM __users_bonuses
                            WHERE
                                user_id = u.id
                                AND type = ?
                                AND bonuses > 0
                                AND object_id = CONCAT( DATE_FORMAT(NOW(), '%Y'), DATE_FORMAT(u.birthday, '%m%d'))
                        ), 0.00) as pushed_bonuses", 'birthday');
                        $fields[] = $this->db->placehold("IFNULL((SELECT SUM(ABS(bonuses))
                            FROM __users_bonuses
                            WHERE
                                user_id = u.id
                                AND bonuses < 0
                                AND created BETWEEN ubb.created AND CONCAT(DATE_FORMAT(NOW(), '%Y'), '-', DATE_FORMAT(u.birthday, '%m-%d')) + INTERVAL ? DAY
                        ), 0.00) as used_bonuses", intval($filter['birthday']['to']));
                        $joins .= $this->db->placehold(" INNER JOIN __users_bonuses ubb ON ubb.user_id = u.id AND ubb.type = ?", 'birthday');
                        $where .= $this->db->placehold(" AND (DATE_FORMAT(CURDATE(), '%m%d') >= DATE_FORMAT(u.birthday + INTERVAL ? DAY, '%m%d'))", intval($filter['birthday']['to']));
                        $where .= $this->db->placehold(" AND (SELECT SUM(bonuses)
                            FROM __users_bonuses
                            WHERE
                                user_id = u.id
                                AND type = ?
                                AND bonuses > 0
                                AND object_id = CONCAT( DATE_FORMAT(NOW(), '%Y'), DATE_FORMAT(u.birthday, '%m%d'))
                        ) > IFNULL((SELECT SUM(ABS(bonuses))
                            FROM __users_bonuses
                            WHERE
                                user_id = u.id
                                AND bonuses < 0
                                AND created BETWEEN ubb.created AND CONCAT(DATE_FORMAT(NOW(), '%Y'), '-', DATE_FORMAT(u.birthday, '%m-%d')) + INTERVAL ? DAY
                        ), 0.00)", 'birthday', intval($filter['birthday']['to']));
                        break;
                }
            }
        }

        if (!empty($filter['birthday_now'])) {
            $where .= $this->db->placehold(' AND (MONTH(u.birthday) = ? AND DAY(u.birthday) = ?)', date('m', strtotime($filter['birthday_now'])), date('d', strtotime($filter['birthday_now'])));
        }

        if (!empty($filter['sms'])) {
            switch ($filter['sms']) {
                case 'birthday':
                    $where .= $this->db->placehold(" AND (SELECT 1 FROM __users_bonuses WHERE user_id = u.id AND object_id = CONCAT(DATE_FORMAT(NOW(), '%Y'), DATE_FORMAT(u.birthday, '%m%d')) AND type = ? AND sms = 0) = 1", (string)$filter['sms']);
                    break;
                default:
                    $where .= $this->db->placehold(" AND (SELECT count(*) = 0 FROM __users_bonuses WHERE user_id = u.id AND type = ? AND sms = 1) = 1", (string)$filter['sms']);
            }
        }

        if (isset($filter['has_bonuses_auto_removed'])) {
            $from = intval($this->settings->users_bonuses_delete);
            $to = intval($this->settings->users_bonuses_delete);
            if (!empty($filter['bonuses_removed']) && (!empty($filter['bonuses_removed']['from']) || !empty($filter['bonuses_removed']['to']))) {
                if (!empty($filter['bonuses_removed']['from'])) {
                    $from -= intval($filter['bonuses_removed']['from']);
                }
                if (!empty($filter['bonuses_removed']['to'])) {
                    $to -= intval($filter['bonuses_removed']['to']);
                }
            }
            $fields[] = $this->db->placehold("(
                GREATEST(
                    SUM(CASE WHEN ub.bonuses > 0 AND ub.type <> ? AND TIMESTAMP(ub.created) < TIMESTAMP(NOW() - INTERVAL ? DAY) THEN ub.bonuses ELSE 0 END)
                    +
                    SUM(CASE WHEN ub.bonuses < 0 AND ub.type <> ? AND TIMESTAMP(ub.created) >= TIMESTAMP(NOW() - INTERVAL ? DAY) THEN ub.bonuses ELSE 0 END)
                    +
                    SUM(CASE WHEN ub.bonuses < 0 AND ub.type = ? THEN ub.bonuses ELSE 0 END)
                , 0)
            ) as removed_bonuses", 'auto_removed', intval($from), 'auto_removed', intval($to), 'auto_removed');
            $joins .= $this->db->placehold(' INNER JOIN __users_bonuses ub ON ub.user_id = u.id');
            $where .= $this->db->placehold(" AND (
                SELECT GREATEST(
                    SUM(CASE WHEN bonuses > 0 AND type <> ? AND TIMESTAMP(created) < TIMESTAMP(NOW() - INTERVAL ? DAY) THEN bonuses ELSE 0 END)
                    +
                    SUM(CASE WHEN bonuses < 0 AND type <> ? AND TIMESTAMP(created) >= TIMESTAMP(NOW() - INTERVAL ? DAY) THEN bonuses ELSE 0 END)
                    +
                    SUM(CASE WHEN bonuses < 0 AND type = ? THEN bonuses ELSE 0 END)
                , 0) > 0
                FROM __users_bonuses
                WHERE user_id = u.id
            ) = ?", 'auto_removed', intval($from), 'auto_removed', intval($to), 'auto_removed', (int)$filter['has_bonuses_auto_removed']);
            $where .= $this->db->placehold(' AND (SELECT MAX(o.date) FROM __orders o WHERE o.user_id = u.id) < NOW() - INTERVAL ? DAY', intval($from));
        }

        if (!empty($filter['sort'])) {
            switch ($filter['sort']) {
                case 'date':
                    $order = 'u.created DESC';
                    break;
                case 'name':
                    $order = 'u.name';
                    break;
                case 'email':
                    $order = 'u.email';
                    break;
                case 'cnt_order':
                    $order = "(select count(o.id) as count from __orders o where o.user_id = u.id) DESC";
                    break;
                case 'cnt_bonus':
                    $order = "(select sum(ub.bonuses) as bonuses from __users_bonuses ub where ub.user_id = u.id) DESC";
                    break;
            }
        }

        if (!empty($group_by)) {
            $group_by = "GROUP BY $group_by";
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

        $fields = implode(', ', $fields);
        $query = $this->db->placehold("SELECT $fields
            FROM __users u
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

    /*Подсчет пользователей*/
    public function count_users($filter = array()) {
        return $this->get_users($filter, true);
    }

    /*Выборка конкретного пользователя*/
    public function get_user($id, $type = '', $filter = array()) {
        if (empty($id)) {
            return false;
        }

        $filter['id'] = $id;
        $filter['type'] = $type;

        $user = $this->get_users($filter, false, true);
        if (empty($user)) {
            return false;
        }
        $user->discount *= 1; // Убираем лишние нули, чтобы было 5 вместо 5.00
        return $user;
    }

    /*Добавление пользователя*/
    public function add_user($user)
    {
        $user = (array)$user;
        unset($user['validation_code']);

        $where = '1';

        if (isset($user['password'])) {
            $user['password'] = md5($this->salt . $user['password'] . md5($user['password']));
        }

        $where .= $this->db->placehold(" AND u.phone = ?", $user['phone']);
        if (!empty($user['email'])) {
            $where .= $this->db->placehold(" AND u.email = ?", $user['email']);
        }

        $query = $this->db->placehold("SELECT count(*) as count FROM __users u WHERE $where", $user['phone']);
        $this->db->query($query);

        if ($this->db->result('count') > 0) {
            return false;
        }

        $query = $this->db->placehold("INSERT INTO __users SET ?%", $user);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /*Обновление пользователя*/
    public function update_user($id, $user)
    {
        $user = (array)$user;
        if (isset($user['password'])) {
            $user['password'] = md5($this->salt . $user['password'] . md5($user['password']));
        }
        $query = $this->db->placehold("UPDATE __users SET ?% WHERE id=? LIMIT 1", $user, intval($id));
        $this->db->query($query);
        return $id;
    }

    /*Обновление количество отзывов*/
    public function update_user_comm($id)
    {
        $query = $this->db->placehold("UPDATE __users SET q_comment = q_comment + 1 WHERE id=? LIMIT 1", intval($id));
        $this->db->query($query);
        return $id;
    }

    /*Удаление пользователя*/
    public function delete_user($id)
    {
        if (!empty($id)) {
            $query = $this->db->placehold("UPDATE __orders SET user_id=0 WHERE user_id=?", intval($id));
            $this->db->query($query);

            $query = $this->db->placehold("DELETE FROM __users WHERE id=? LIMIT 1", intval($id));
            if ($this->db->query($query)) {
                $this->db->query("DELETE FROM __users_bonuses WHERE user_id = ?", intval($id));
                return true;
            }
        }
        return false;
    }

    /*Выборка групп пользователей*/
    public function get_groups()
    {
        // Выбираем группы
        $query = $this->db->placehold("SELECT g.id, g.name, g.discount FROM __groups AS g ORDER BY g.discount");
        $this->db->query($query);
        return $this->db->results();
    }

    /*Выборка группы пользователей */
    public function get_group($id)
    {
        // Выбираем группу
        $query = $this->db->placehold("SELECT * FROM __groups WHERE id=? LIMIT 1", $id);
        $this->db->query($query);
        $group = $this->db->result();

        return $group;
    }

    /*Добавление группы пользователей*/
    public function add_group($group)
    {
        $query = $this->db->placehold("INSERT INTO __groups SET ?%", $group);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /*Обновление группы пользователей*/
    public function update_group($id, $group)
    {
        $query = $this->db->placehold("UPDATE __groups SET ?% WHERE id=? LIMIT 1", $group, intval($id));
        $this->db->query($query);
        return $id;
    }

    /*Удаление группы пользователей*/
    public function delete_group($id)
    {
        if (!empty($id)) {
            $query = $this->db->placehold("UPDATE __users SET group_id=NULL WHERE group_id=? LIMIT 1", intval($id));
            $this->db->query($query);

            $query = $this->db->placehold("DELETE FROM __groups WHERE id=? LIMIT 1", intval($id));
            if ($this->db->query($query)) {
                return true;
            }
        }
        return false;
    }

    /*Проверка пароля*/
    public function check_password($phone, $password)
    {
        $encpassword = md5($this->salt . $password . md5($password));
        $query = $this->db->placehold("SELECT id FROM __users WHERE phone=? AND password=? LIMIT 1", $phone, $encpassword);
        $this->db->query($query);
        if ($id = $this->db->result('id')) {
            return $id;
        }
        return false;
    }

    public function add_user_bonus($user_id, $object_id, $bonuses = 0.00, $type = 'order', $sms = 0) {
        if (empty($user_id) || !isset($object_id)) {
            return false;
        }

        $query = $this->db->placehold("REPLACE INTO __users_bonuses SET user_id = ?, object_id = ?, type = ?, bonuses = ?, sms = ?", intval($user_id), intval($object_id), (string)$type, floatval($bonuses), intval($sms));
        if ($this->db->query($query)) {
            return true;
        }

        return false;
    }

    public function update_user_bonus($user_id, $object_id, $params, $type = 'order') {
        if (empty($user_id) || empty($object_id) || empty($params)) {
            return false;
        }

        $query = $this->db->placehold("UPDATE __users_bonuses SET ?% WHERE user_id = ? AND object_id = ? AND type = ?", (array)$params, intval($user_id), intval($object_id), (string)$type);
        if ($this->db->query($query)) {
            return true;
        }

        return false;
    }

    public function remove_user_bonuses($user_id, $bonuses, $type = 'removed', $object_id = null) {
        if (empty($user_id) || empty($bonuses)) {
            return false;
        }

        if (!isset($object_id)) {
            $object_id = time();
        }
        $query = $this->db->placehold("INSERT INTO __users_bonuses SET user_id = ?, object_id = ?, type = ?, bonuses = ?", intval($user_id), intval($object_id), (string)$type, floatval($bonuses));
        $this->db->query($query);

        return true;
    }

    public function delete_users_bonuses() {
        $bonuses_delete = intval($this->settings->users_bonuses_delete);
        $query = $this->db->placehold("REPLACE INTO __users_bonuses (user_id, object_id, type, bonuses)
        SELECT
            u.id,
            u.id,
            CONCAT('auto_', UNIX_TIMESTAMP(), '_removed'),
            (
                GREATEST(
                    SUM(CASE WHEN ub.bonuses > 0 AND DATE(ub.created) < DATE(NOW() - INTERVAL ? DAY) THEN ub.bonuses ELSE 0 END)
                    +
                    SUM(CASE WHEN ub.bonuses < 0 AND DATE(ub.created) < DATE(NOW()) THEN ub.bonuses ELSE 0 END)
                    +
                    (SELECT SUM(bonuses) FROM __users_bonuses WHERE type LIKE 'auto_%' AND user_id = u.id),
                    0
                ) * -1
            ) as bonuses
        FROM __users u
        INNER JOIN __users_bonuses ub ON ub.user_id = u.id
        WHERE 1
            AND ub.type NOT LIKE 'auto_%'
            AND (
                SELECT GREATEST(
                    SUM(CASE WHEN bonuses > 0 AND DATE(created) < DATE(NOW() - INTERVAL ? DAY) THEN bonuses ELSE 0 END)
                    +
                    SUM(CASE WHEN bonuses < 0 AND DATE(created) < DATE(NOW()) THEN bonuses ELSE 0 END)
                    +
                    (SELECT SUM(bonuses) FROM __users_bonuses WHERE type LIKE 'auto_%' AND user_id = u.id),
                    0
                ) > 0
                FROM __users_bonuses
                WHERE user_id = u.id
            ) = 1
            AND (SELECT MAX(o.date) FROM __orders o WHERE o.user_id = u.id) < NOW() - INTERVAL ? DAY
        GROUP BY u.id", (int)$bonuses_delete, (int)$bonuses_delete, (int)$bonuses_delete);
        $this->db->query($query);
        return $this->db->affected_rows();
    }

    public function get_users_bonuses($filter = array()) {
        if (empty($filter['user_id'])) {
            return false;
        }

        $where = '1';

        if (isset($filter['user_id'])) {
            $where .= $this->db->placehold(' AND user_id = ?', intval($filter['user_id']));
        }

        if (isset($filter['type'])) {
            $where .= $this->db->placehold(' AND type in (?@)', (array)($filter['type']));
        }

        $query = $this->db->placehold("SELECT user_id, object_id, type, bonuses, created FROM __users_bonuses WHERE $where ORDER BY created DESC LIMIT 100");
        $this->db->query($query);

        return $this->db->results();
    }

}
