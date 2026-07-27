<?php

require_once('Okay.php');

class NovaPoshta extends Okay {
    private $types = ['WarehouseWarehouse', 'WarehouseDoors'];

    public function getTypes() {
        $results = [];
        foreach ($this->types as $type) {
            $results[] = [
                'id' => $type,
                'name' => $this->translations->{"novaposhta_type_$type"},
            ];
        }
        return $results;
    }

	public function np_city($npcity = '', $filter = array()) {
        $limit = 25;
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
            $where .= $this->db->placehold(' AND city_description LIKE ?', $filter['keyword'] . '%');
        }

        $this->db->query("SELECT city_description as Description, city_ref as Ref 
        FROM __addresses_novaposhta
        WHERE (
            1
            $where
            AND enabled = 1
        ) OR city_ref = ?
        GROUP BY city_ref
        ORDER BY city_description
        $sql_limit", strval($npcity));

        return $this->db->results();
    }

    public function np_ware($npcity, $npware = '', $filter = array()) {
        if (!$npcity) return false;

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
            $where .= $this->db->placehold(' AND ware_description LIKE ?', '%' . $filter['keyword'] . '%');
        }

        if (!empty($npware)) {
            $where .= $this->db->placehold(' AND ware_ref = ?', strval($npware));
        }

        $this->db->query("SELECT city_description as CityDescription, city_ref as CityRef, ware_description as Description, ware_ref as Ref, enabled
        FROM __addresses_novaposhta
        WHERE (
            1
            $where
            AND city_ref = ?
            AND enabled = 1
        ) OR ware_ref = ?
        GROUP BY ware_ref
        ORDER BY number
        $sql_limit", strval($npcity), strval($npware));

        return $this->db->results();
    }

    public function get_address($npcity_id, $npware_id = '', $full = false) {
        if (empty($npcity_id)) return false;

        $where = '';
        
        if (!empty($npware_id)) {
            $where .= $this->db->placehold(" AND ware_ref = ?", $npware_id);
        }
        if (!$full) {
            $where .= $this->db->placehold("AND enabled = 1");
        }
        $this->db->query("SELECT city_description as CityDescription, city_ref as CityRef, ware_description as Description, ware_ref as Ref, enabled
        FROM __addresses_novaposhta 
        WHERE city_ref = ?
            $where
        LIMIT 1", $npcity_id);

        return $this->db->result();
    }

    public function calculate_price ($data = array()) {
        $key = $this->settings->np_api_key;

        if (!empty($key)) {
            $params = [
                'apiKey' => $key,
                'modelName' => 'InternetDocument',
                'calledMethod' => 'getDocumentPrice',
                'methodProperties' => [
                    'CitySender' => $this->settings->novaposhta_city,
                    'CityRecipient' => $data['city'],
                    'Weight' => (!empty($data['weights']) ? ($data['weights'] * 0.01) : 0.01),
                    'ServiceType' => $this->settings->novaposhta_deliveryType,
                    'Cost' => $data['total_price'],
                    'CargoType' => $this->settings->novaposhta_cargoType,
                ]
            ];
            if (false !== ($res = $this->curl(json_encode($params)))) {
                return json_encode($res);
            }
        }
        return false;
    }

    public function InternetDocument($data = array()) {
        $key = $this->settings->np_api_key;
        if (!empty($key)) {
            $params = [
                'apiKey' => $key,
                'modelName' => 'InternetDocument',
                'calledMethod' => 'save',
                'methodProperties' => [
                    'NewAddress' => 1,
                    'PayerType' => $this->settings->novaposhta_TypeOfPayer,
                    'PaymentMethod' => $this->settings->novaposhta_PaymentForm,
                    'DateTime' => date('d.m.Y'),
                    'CargoType' => $this->settings->novaposhta_cargoType,
                    'Weight' => $data['weights'],
                    'ServiceType' => $this->settings->novaposhta_deliveryType,
                    'SeatsAmount' => 1,
                    'Description' => $data['note'],
                    'Cost' => $data['total_price'],
                    'CitySender' => $this->settings->novaposhta_city,
                    'Sender' => $this->settings->novaposhta_sender_ref,
                    'SenderAddress' => $this->settings->novaposhta_ware,
                    'ContactSender' => $this->settings->novaposhta_contact_ref,
                    'SendersPhone' => $this->settings->requisites_phone,
                    'Recipient' => $data['Recipient'],
                    'RecipientCityName' => $data['RecipientCityName'],
                    'ContactRecipient' => $data['ContactRecipient'],
                    'RecipientsPhone' => $data['RecipientsPhone'],
                    'RecipientType' => 'PrivatePerson'
                ]
            ];

            if (false !== ($res = $this->curl(json_encode($params)))) {
                return json_encode($res);
            }

            return false;
        }
        return false;
    }

    public function contragent($action = 'save', $data = array()) {
        $key = $this->settings->np_api_key;
        if (!empty($key)) {
            $params = [
                'apiKey' => $key,
                'modelName' => 'Counterparty',
                'calledMethod' => $action,
                'methodProperties' => [
                    'Ref' => $data['Ref'],
                    'CityRef' => $data['CityRef'],
                    'FirstName' => $data['FirstName'],
                    'MiddleName' => $data['MiddleName'],
                    'LastName' => $data['LastName'],
                    'Phone' => $data['Phone'],
                    'Email' => $data['Email'],
                    'CounterpartyType' => $data['CounterpartyType'],
                    'CounterpartyProperty' => 'Recipient',
                ]
            ];
            if (false !== ($res = $this->curl(json_encode($params)))) {
                return json_encode($res);
            }

            return false;
        }
        return false;
    }

    public function contact_contragent($action, $data = array()) {
        $key = $this->settings->np_api_key;
        if (!empty($key)) {
            $params = [
                'apiKey' => $key,
                'modelName' => 'ContactPerson',
                'calledMethod' => $action,
                'methodProperties' => [
                    'CounterpartyRef' => $data['CounterpartyRef'],
                    'FirstName' => $data['FirstName'],
                    'MiddleName' => $data['MiddleName'],
                    'LastName' => $data['LastName'],
                    'Phone' => $data['Phone'],
                ]
            ];
            if (false !== ($res = $this->curl(json_encode($params)))) {
                return json_encode($res);
            }

            return false;
        }
        return false;
    }

    public function create_Address($data = array()) {
        $key = $this->settings->np_api_key;
        if (!empty($key)) {
            $params = [
                'apiKey' => $key,
                'modelName' => 'Address',
                'calledMethod' => 'save',
                'methodProperties' => [
                    'CounterpartyRef' => $data['CounterpartyRef'],
                    'StreetRef' => $data['StreetRef'],
                    'BuildingNumber' => $data['BuildingNumber'],
                    'Flat' => $data['Flat'],
                    'Note' => $data['Note'],
                ]
            ];
            if (false !== ($res = $this->curl(json_encode($params)))) {
                return json_encode($res);
            }

            return false;
        }
        return false;
    }

    public function get_cache_address($filter = array()) {
        if ($this->isWrongParameters) {
            return false;
        }

        $page = 1;
        $limit = 300;

        if (isset($filter['page'])) {
            $page = max(1, intval($filter['page']));
        }

        if (isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
        }

        $query = new stdClass();
        $query->apiKey = $this->settings->np_api_key;
        $query->modelName = 'AddressGeneral';
        $query->calledMethod = 'getWarehouses';
        $query->methodProperties = [
            'Page' => $page,
            'Limit' => $limit,
        ];

        if (false !== ($results = $this->curl(json_encode($query)))) {
            try {

                if (empty($results['info']['totalCount'])) {
                    throw new Exception('error info');
                }

                foreach ($results['data'] as $ware) {
                    $this->db->query("SELECT id FROM __addresses_novaposhta WHERE ware_ref = ? LIMIT 1", $ware['Ref']);
                    $ware_id = $this->db->result('id');

                    $store = new stdClass;
                    $store->city_ref = $ware['CityRef'];
                    $store->city_description = $ware['CityDescription'];
                    $store->ware_ref = $ware['Ref'];
                    $store->ware_description = $ware['Description'];
                    $store->sitekey = $ware['SiteKey'];
                    $store->number = $ware['Number'];
                    $store->enabled = 1;
                    $store->modified = date('Y-m-d H:i:s');

                    if ($ware_id) {
                        $query = $this->db->placehold("UPDATE __addresses_novaposhta SET ?% WHERE id = ? LIMIT 1", (object)$store, intval($ware_id));
                        $this->db->query($query);
                    } else {
                        $query = $this->db->placehold("INSERT IGNORE INTO __addresses_novaposhta SET ?%", (object)$store);
                        $this->db->query($query);
                    }
                }

                return [
                    'currentPage' => $page,
                    'pageCount' => ceil($results['info']['totalCount'] / $limit),
                ];
            } catch (Exception $e) {}
        }

        return false;
    }

    private function curl($json) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.novaposhta.ua/v2.0/json/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type => text/json"));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);

        try {
            if (!isset($result['success']) || (boolean)$result['success'] !== true) {
                throw new Exception('error success');
            }

            if (empty($result['data']) && (boolean)$result['success'] !== true) {
                throw new Exception('error success');
            }

            if (!empty($results['errors'])) {
                throw new Exception('error errors: ' . implode(', ', $results['errors']));
            }

            return $result;
        } catch (Exception $e) {}

        return false;
    }
}