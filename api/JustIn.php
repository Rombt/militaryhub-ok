<?php
/**
 * Created by PhpStorm.
 * User: AndriiK
 * Date: 21.05.2019
 * Time: 14:58
 */

require_once('Okay.php');

class JustIn extends Okay {
    public function get_address($city_id, $ware_id = '', $full = false) {
        if (empty($city_id)) return false;

        $ware_filter = '';
        if (!empty($ware_id)) {
            $ware_filter = $this->db->placehold("AND external_id=?", $ware_id);
        }

        if (!$full) {
            $visible_filter = $this->db->placehold("AND enabled=1");
        } else {
            $visible_filter = '';
        }

        $query = $this->db->placehold("SELECT
            DISTINCT external_id as Ref, name as Description, city_external_id as CityRef, city as CityDescription
            FROM __addresses_justin
            WHERE city_external_id=?
              $ware_filter
              $visible_filter
            LIMIT 1", $city_id);
        $this->db->query($query);
        return $this->db->result();
    }

    public function get_cities($filter = array()) {
        $limit = 100;
        $page = 1;
        $or_where = [];
        $where = '';

        if(isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
        }
        if(isset($filter['page'])) {
            $page = max(1, intval($filter['page']));
        }
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        if (isset($filter['city_external_id'])) {
            $or_where[] = $this->db->placehold("city_external_id = ?", $filter['city']);
        }
        $or_where = implode(' OR ', $or_where);

        if (isset($filter['keyword'])) {
            $filter['keyword'] = trim($filter['keyword']);
            $where .= " AND city LIKE '%" . $filter['keyword'] . "%' ";
        }

        $query = $this->db->placehold("SELECT
            city as Description,
            city_external_id as Ref
        FROM __addresses_justin
        WHERE 1
            AND enabled=1 $or_where
            $where
        GROUP BY city_external_id
        ORDER BY id
        $sql_limit");
        $this->db->query($query);

        return $this->db->results();
    }

    public function get_wares($filter = array()) {
        if (!$filter['city']) return false;

        $limit = 100;
        $page = 1;
        $where = '';

        if (isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
        }
        if (isset($filter['page'])) {
            $page = max(1, intval($filter['page']));
        }
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        if (!empty($filter['keyword'])) {
            $where .= $this->db->placehold(' AND name LIKE ?', '%' . $filter['keyword'] . '%');
        }

        $this->db->query("SELECT
            name as Description,
            street,
            house,
            external_id as Ref
        FROM __addresses_justin
        WHERE (
            1
            $where
            AND city_external_id = ?
            AND enabled = 1
        ) OR external_id = ?
        GROUP BY external_id
        ORDER BY id
        $sql_limit", strval($filter['city']), strval($filter['ware']));
        return $this->db->results();
    }

    public function get_api_wares() {
        $justin_login = $this->settings->justin_login;
        $justin_pass = $this->settings->justin_pass;
        $justin_api_key = $this->settings->justin_api_key;
        if (empty($justin_login) || empty($justin_pass) || empty($justin_api_key)) return false;

        require_once('JustInLib.php');
        $JustIn = new JustInLib($justin_login, $justin_pass, $justin_api_key);
        $results = $JustIn->getDepartments();
        if (!empty($results['data'])) {
            foreach ($results['data'] as &$item) {
                if (isset($item['fields']) && !empty($item['fields'])) {
                    $ware = new stdClass();
                    $ware->code = isset($item['fields']['Depart']) ? strval($item['fields']['code']) : null;

                    $ware->name = isset($item['fields']['Depart']) ? strval($item['fields']['Depart']['descr']) : null;
                    $ware->external_id = isset($item['fields']['Depart']) ? strval($item['fields']['Depart']['uuid']) : null;

                    $ware->region = isset($item['fields']['region']) ? strval($item['fields']['region']['descr']) : null;
                    $ware->region_external_id = isset($item['fields']['Depart']) ? strval($item['fields']['region']['uuid']) : null;

                    $ware->city = isset($item['fields']['city']) ? strval($item['fields']['city']['descr']) : null;
                    $ware->city_external_id = isset($item['fields']['Depart']) ? strval($item['fields']['city']['uuid']) : null;

                    $ware->street = isset($item['fields']['street']) ? strval($item['fields']['street']['descr']) : null;
                    $ware->street_external_id = isset($item['fields']['Depart']) ? strval($item['fields']['street']['uuid']) : null;

                    $ware->house = isset($item['fields']['houseNumber']) ? strval($item['fields']['houseNumber']) : null;
                    $ware->weight_limit = isset($item['fields']['weight_limit']) ? floatval($item['fields']['weight_limit']) : null;
                    $ware->lat = isset($item['fields']['lat']) ? floatval($item['fields']['lat']) : null;
                    $ware->lng = isset($item['fields']['lng']) ? floatval($item['fields']['lng']) : null;
                    $ware->data = isset($item['fields']) ? json_encode($item['fields']) : null;

                    if (empty($ware->code)
                        || empty($ware->name) || empty($ware->external_id)
                        || empty($ware->region) || empty($ware->region_external_id)
                        || empty($ware->city) || empty($ware->city_external_id)
                        || empty($ware->street) || empty($ware->street_external_id) || empty($ware->house)
                        || empty($ware->weight_limit) || empty($ware->lat) || empty($ware->lng)) {
                        continue;
                    }

                    if (!empty($ware->external_id)) {
                        $this->db->query("SELECT id FROM __addresses_justin WHERE external_id=?", $ware->external_id);
                        if ($id = $this->db->result('id')) {
                            $query = $this->db->placehold("UPDATE __addresses_justin SET ?% WHERE id=? LIMIT 1", $ware, intval($id));
                            $this->db->query($query);
                        } else {
                            $query = $this->db->placehold("INSERT IGNORE INTO __addresses_justin SET ?%", $ware);
                            $this->db->query($query);
                        }
                    }
                }
            }
        }

        return json_encode($results);
    }
}