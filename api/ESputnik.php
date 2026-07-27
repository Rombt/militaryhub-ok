<?php
/**
 * Created by PhpStorm.
 * User: AndriiK
 * Date: 31.07.2019
 * Time: 12:59
 */

require_once('Okay.php');

class ESputnik extends Okay {
    private static $root_url = 'https://esputnik.com/api/v1';
    private $url = '';
    private $user;
    private $password;
    private $locEmail;
    private $locObject;
    private $locObjectId;
    private $locData;
    private $is_wrong_params = 0;

    public function __construct() {
        parent::__construct();

        $this->user = $this->settings->esputnik_user;
        $this->password = $this->settings->esputnik_pass;

        if (empty($this->user) || empty($this->password)) {
            $this->is_wrong_params = 1;
        }
    }

    private function add_log($l) {
        $l = (object)$l;

        $query = $this->db->placehold("INSERT INTO __esputnik_messages SET ?%", $l);
        $this->db->query($query);
        $id = $this->db->insert_id();

        return $id;
    }

    private function update_log($id, $l) {
        $l = (object)$l;

        $query = $this->db->placehold("UPDATE __esputnik_messages SET ?% WHERE id in(?@) LIMIT ?", (array)$l, (array)$id, count((array)$id));
        $this->db->query($query);

        return $id;
    }

    public function get_logs($filter = array()) {
        $where = '';

        if (isset($filter['object_id'])) {
            $where .= $this->db->placehold(" AND object_id = ?", $filter['object_id']);
        }

        $query = $this->db->placehold("SELECT *
        FROM __esputnik_messages
        WHERE 1
            $where
        ORDER BY date DESC");
        $this->db->query($query);

        return $this->db->results();
    }

    public function get_log($id) {
        if (empty($id)) {
            return false;
        }
        if (is_int($id)) {
            $id_filter = $this->db->placehold('AND e.id=?', intval($id));
        } else {
            $id_filter = $this->db->placehold('AND e.request_id=?', $id);
        }

        $query = $this->db->placehold("SELECT e.*
        FROM __esputnik_messages e 
        WHERE 1
            $id_filter
        ORDER BY date DESC
        LIMIT 1");
        $this->db->query($query);
        return $this->db->result();
    }

    private function curl($url, $type = 'GET', $params = array()) {
        if ($this->is_wrong_params) {
            return false;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_URL, self::$root_url . $url);
        curl_setopt($ch,CURLOPT_USERPWD, $this->user . ':' . $this->password);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
        $output = curl_exec($ch);
//        $code = curl_getinfo($ch);
        curl_close($ch);

        return $output;
    }

    private function message_status($object, $object_id, $message_id) {
        if (empty($message_id)) return false;

        $ids = http_build_query(['ids' => $message_id]);
        $result = $this->curl('/message/status?' . $ids);
        if (($result_array = json_decode($result)) && isset($result_array->results->requestId)) {
            if (!$l_exists = $this->get_log($result_array->results->requestId)) {
                $l = new stdClass();
                $l->object = $object;
                $l->object_id = $object_id;
                $l->request_id = (string)$result_array->results->requestId;
                $l->status = '';
                $l->json = json_encode(json_decode($result));
                $l_id = $this->add_log($l);
            } else {
                $l_id = $this->update_log($l_exists->id, array('status' => $result_array->results->status));
            }
            return $l_id;
        }
        return false;
    }

    public function email($email, $subject, $html, $from) {
        $this->url = '/message/email';

        $params = new stdClass();
        $params->from = $from;
        $params->subject = $subject;
        $params->htmlText = $html;
        $params->emails = [
            $email,
        ];

        $result = $this->curl($this->url, 'POST', $params);
        $id = time();
        if (isset($result) && $result_array = json_decode($result)) {
            $l = new stdClass();
            $l->object = 'html';
            $l->object_id = intval($id);
            $l->request_id = (string)$result_array->results->requestId;
            $l->status = '';
            $l->json = json_encode(json_decode($result));
            $this->add_log($l);
            $this->message_status('html', $id, $l->request_id);

            return $id;
        }

        return false;
    }
}