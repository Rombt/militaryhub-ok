<?php
/**
 * Created by PhpStorm.
 * User: AndriiK
 * Date: 17.01.2020
 * Time: 16:15
 */

require_once('Okay.php');

class Rozetka extends Okay {

    private static $root_url = 'https://api-seller.rozetka.com.ua';
    private $is_wrong_params = 0;
    private $user;
    private $password;
    private $access_token;

    public static $table_items = '__categories_rozetka';

    public function __construct() {
        parent::__construct();

        $this->user = $this->settings->rozetka_user;
//        $this->user = 'New_sport';
        $this->password = $this->settings->rozetka_pass;
//        $this->password = '0VFG46l2bj2D';

        $this->access_token = $this->settings->rozetka_access_token;
    }

    public function get_items($filter = array(), $count = false, $one = false) {
        $limit = 100;
        $page = 1;
        $joins = '';
        $where = '1';
        $group_by = 'r.id';
        $order = 'r.id';

        $fields = [
            'DISTINCT r.id',
            'r.category_id',
            'r.parent_id',
            'r.name',
            'r.exclude',
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
                $where .= $this->db->placehold(' AND r.id=? ', intval($filter['id']));
            } else {
                $where .= $this->db->placehold(' AND r.name=? ', $filter['id']);
            }
        }

        if (!empty($filter['category_id'])) {
            $where .= $this->db->placehold(' AND r.category_id=? ', intval($filter['category_id']));
        }

        if (!empty($filter['parent_id'])) {
            $where .= $this->db->placehold(' AND r.parent_id=? ', intval($filter['parent_id']));
        }
        
        if (!empty($filter['keyword'])) {
            $filter['keyword'] = $this->db->escape($filter['keyword']);
            $filter['keyword'] = trim($filter['keyword']);
            $where .= $this->db->placehold(" AND r.name LIKE ?", '%' . $filter['keyword'] . '%');
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
        $filter['id'] = $id;

        return $this->get_items($filter, false, true);
    }

    public function add_item($item) {
        $item = (array)$item;

        $query = $this->db->placehold("INSERT INTO " . self::$table_items . " SET ?%", $item);
        $this->db->query($query);

        return $this->db->insert_id();
    }

    public function update_item($id, $item) {
        $item = (array)$item;

        $query = $this->db->placehold("UPDATE " . self::$table_items . " SET ?% WHERE id = ? LIMIT 1", $item, (int)$id);
        $this->db->query($query);

        return $id;
    }

    public function delete_item($id) {
        if (!empty($id)) {
            $query = $this->db->placehold("DELETE FROM " . self::$table_items . " WHERE id = ? LIMIT 1", intval($id));
            $this->db->query($query);
        }
    }

    private function autorization() {
        $this->log("info", "Started Rozetka::autorization");
        $attempts = 0;
        do {
            $response = $this->curl('/sites', 'POST', array(), array('username' => $this->user, 'password' => base64_encode($this->password)));
            if ($response->success === true) {
                break;
            }
            $attempts++;
        } while ($attempts < 3);

        if ($response->success !== true) {
            $this->log("error", "Rozetka autorization gone wrong");
            throw new Exception('Rozetka autorization gone wrong');
        }

        if (empty($response->content->access_token)) {
            $this->log("error", "Rozetka access_token missing");
            throw new Exception('Rozetka access_token missing');
        }

        $this->settings->rozetka_access_token = $response->content->access_token;
        $this->access_token = $this->settings->rozetka_access_token;
    }

    public function get_api_categories($params = array()) {
        $attempts = 0;
        do {
            $response = $this->curl('/market-categories/search', 'GET', array("Authorization: Bearer {$this->access_token}"), $params);

            if ($response->success === true) {
                break;
            } else {
                $this->autorization();
                $attempts++;
            }
        } while ($attempts < 3);

        if ($response->success !== true) {
            throw new Exception('Rozetka API unavailable');
        }

        if (empty($response->content->marketCategorys)) {
            return $response->content->_meta;
        }

        foreach ($response->content->marketCategorys as $marketCategory) {
            if ($category = $this->get_item(null, ['category_id' => $marketCategory->category_id])) {
                $this->update_item($category->id, [
                    'name' => $marketCategory->name,
                    'parent_id' => (int)$marketCategory->parent_id,
                    'exclude' => !empty($marketCategory->exclude),
                ]);
            } else {
                $new = new stdClass();
                $new->category_id = (int)$marketCategory->category_id;
                $new->name = (string)$marketCategory->name;
                $new->parent_id = (int)$marketCategory->parent_id;
                $new->exclude = !empty($marketCategory->exclude);
                $this->add_item($new);
            }
        }

        return $response->content->_meta;
    }

    private function curl($url, $type = 'GET', $header = array(), $params = array()) {
        if ($this->is_wrong_params) {
            return false;
        }

        $header['Content-Type'] = 'application/x-www-form-urlencoded';

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_URL, self::$root_url . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $output = curl_exec($ch);
        $code = curl_getinfo($ch);
        curl_close($ch);

        return json_decode($output);
    }

    public function get_ban_list () {
        $categoriesLocalPath = $this->config->root_dir. "/backend/files/rozetka/rozetka_ban_list_categories.csv";
        $categoriesCSVUrl = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vQqHOjuMG8fd9FMF6__c9kEE6IoVvYEOKmysmJpMDVuNj-XdsAkmQp1AR34pQ0Dqg/pub?output=csv';
        $brandsCSVUrl = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vQqHOjuMG8fd9FMF6__c9kEE6IoVvYEOKmysmJpMDVuNj-XdsAkmQp1AR34pQ0Dqg/pub?output=csv&gid=672087803';
        $brandsLocalPath = $this->config->root_dir. "/backend/files/rozetka/rozetka_ban_list_brands.csv";
        $categoriesFileResult = $this->downloadBanFile($categoriesCSVUrl,$categoriesLocalPath);
        $brandsFileResult = $this->downloadBanFile($brandsCSVUrl,$brandsLocalPath);

        if (!$categoriesFileResult || !$brandsFileResult) {
            $this->log('error', "Failed to download ban list");
        }

        $this->sync_rozetka_banlist($categoriesLocalPath, [
            'local_elements_query' => "
                SELECT
                    c.id,
                    cr.category_id as name,
                    c.rozetka_exclude as is_banned
                FROM __categories c
                LEFT JOIN __variants v on v.id = c.rozetka_category_value_id
                LEFT JOIN __categories_rozetka cr ON v.rozetka_name = cr.name
                WHERE v.rozetka_name IS NOT NULL AND v.rozetka_name != ''
            ",
            'update_method' => [$this->categories, 'update_category'],
            'type'          => 'categories',
            'extract_method'=> 'extractCategoriesFromCsvCell'
        ]);
        $this->sync_rozetka_banlist($brandsLocalPath, [
            'db_table'      => '__brands',
            'id_field'      => 'id',
            'name_field'    => 'name',
            'ban_field'     => 'rozetka_exclude',
            'where'     => 'WHERE rozetka_ban_check = 1',
            'update_method' => [$this->brands, 'update_brand'],
            'type'          => 'brands',
            'extract_method'=> 'extractBrandName'
        ]);



    }

    private function downloadBanFile($url,$localPath)
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true
            ]
        ]);
        $csv = @file_get_contents($url, false, $context);

        if ($csv === false || strlen($csv) < 10) {
            $this->log("error", "Failed to download CSV", [
                'url' => $url,
                'http_response' => $http_response_header ?? null,
            ]);
            return false;
        }

        $dir = dirname($localPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $result = @file_put_contents($localPath, $csv);

        if ($result === false) {
            $this->log("error", "Failed to save CSV file", [
                'path' => $localPath
            ]);
            return false;
        }

        $this->log("info", "CSV downloaded and saved", [
            'path' => $localPath,
            'bytes' => strlen($csv)
        ]);

        return true;
    }

    private function log(string $type, string $message, ?array $data = [])
    {
        $time = date("Y-m-d H:i:s");
        $line = "[$time][$type] $message " . json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL;

        $file = $this->config->root_dir.'/log/rozetka/rozetka.log';
        file_put_contents($file, $line, FILE_APPEND);
    }

    private function sync_rozetka_banlist ($csvPath, $entity) {
        if (!file_exists($csvPath)) {
            $this->log("error", "CSV file not found", ['path' => $csvPath]);
            return false;
        }

        $csvFile = fopen($csvPath, "r");
        if ($csvFile === false) {
            $this->log_sync("error", "CSV file is empty or invalid", ['path' => $csvPath]);
        }
        $headers = fgetcsv($csvFile); // Read the first row as headers
        $data = [];
        while (($row = fgetcsv($csvFile)) !== false) {
            $data[] = $row[0];
        }
        fclose($csvFile);

        $local = [];
        $table = $entity['db_table'];

        if (empty($entity['local_elements_query'])) {
            $this->db->query("
                SELECT 
                    {$entity['id_field']} as id,
                    {$entity['name_field']} as name,
                    {$entity['ban_field']} as is_banned
                FROM $table
                {$entity['where']}
            ");
        } else {
            $this->db->query($entity['local_elements_query']);
        }
        $dbRows = $this->db->results();

        foreach ($dbRows as $r) {
            $norm = $this->translit_alpha($r->name);
            if ($norm) {
                $local[$norm] = $r;
            }
        }

        $unmatched = $local;

        foreach ($data as $row) {
            if (trim($row) === '') {
                continue;
            }

            $extract = $entity['extract_method'];

            $items = $this->$extract($row);

            // У брендів повертається ОДИН рядок
            if (!is_array($items)) {
                $items = $items ? [$items] : [];
            }

            foreach ($items as $nameCsv) {
                $normalized = $this->translit_alpha($nameCsv);
                if (!$normalized) continue;

                if (isset($local[$normalized])) {
                    $item = $local[$normalized];

                    if (!(int)$item->is_banned) {
                        call_user_func($entity['update_method'], $item->id, [
                            'rozetka_exclude' => 1
                        ]);

                        $this->log("update", "{$entity['type']} ban applied", [
                            'id'   => $item->id,
                            'name' => $item->name
                        ]);
                        $this->log_sync($entity['type'], [
                            'id' => $item->id,
                            'name' => $item->name,
                            'ban' => 1,
                        ]);
                    }
                    unset($unmatched[$normalized]);
                } else {
//                    $this->log("missing", "{$entity['type']} missing locally", [
//                        'csv_name' => $nameCsv
//                    ]);
                }
            }
        }
        foreach ($unmatched as $norm => $item) {
            if ($item->rozetka_exclude) {
                call_user_func($entity['update_method'], $item->id, [
                    'rozetka_exclude' => 0
                ]);
                $this->log("update", "{$entity['type']} unbanned", [
                    'id'   => $item->id,
                    'name' => $item->name
                ]);
                $this->log_sync($entity['type'], [
                    'id'  => $item->id,
                    'ban' => 0,
                ]);
            }
        }

        return true;
    }

    private function extractCategoriesFromCsvCell($cell)
    {
        $lines = preg_split('/\r\n|\r|\n/', $cell, -1, PREG_SPLIT_NO_EMPTY);
        $result = [];

        foreach ($lines as $line) {

            $line = trim($line);
            if ($line === '') continue;

            if (!preg_match('/^(.*)\s*\((\d+)\)\s*$/u', $line, $m)) {
                continue;
            }

            $name = trim($m[2]);
            if ($name === '') continue;

            $result[] = $name;
        }

        return $result;
    }

    private function extractBrandName($cell)
    {
        $name = trim($cell);

        // прибрати лапки
        $name = trim($name, "\"'");

        if ($name === '') return null;

        return $name;
    }

    private function log_sync($type, $data = []){
        $logDir = $this->config->root_dir.'/log/rozetka/';
        $logFile = $logDir . "changes_{$type}.json";

        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }

        $logs = [];
        if (file_exists($logFile)) {
            $logs = json_decode(file_get_contents($logFile), true);
            if (!is_array($logs)) {
                $logs = [];
            }
        }

        $logs[] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type'      => $type,
            'data'      => $data,
        ];

        if (count($logs) > 150) {
            $toArchive = array_splice($logs, 0, 100);

            $archiveIndex = 1;
            while (file_exists($logDir . "archive_{$type}_$archiveIndex.json")) {
                $archiveIndex++;
            }

            $archiveFile = $logDir . "archive_{$type}_$archiveIndex.json";

            $archiveData = [];
            if (file_exists($archiveFile)) {
                $archiveData = json_decode(file_get_contents($archiveFile), true) ?: [];
            }

            $archiveData = array_merge($archiveData, $toArchive);

            if (count($archiveData) > 1000) {

                // Старі 1000 залишаються тут
                $firstChunk = array_slice($archiveData, 0, 1000);

                file_put_contents(
                    $archiveFile,
                    json_encode($firstChunk, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );

                $overflow = array_slice($archiveData, 1000);

                $archiveIndex++;
                $nextArchive = $logDir . "archive_{$type}_$archiveIndex.json";

                file_put_contents(
                    $nextArchive,
                    json_encode($overflow, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );

            } else {
                file_put_contents(
                    $archiveFile,
                    json_encode($archiveData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );
            }
        }

        file_put_contents(
            $logFile,
            json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    public function get_rozetka_logs($type, $page = 1)
    {
        $logDir = $this->config->root_dir . '/log/rozetka/';

        if (!in_array($type, ['categories', 'brands'])) {
            return ['error' => 'Invalid type'];
        }

        // page 1 = current log
        if ($page == 1) {
            $file = $logDir . "changes_{$type}.json";
        } else {
            $index = $page - 1;
            $file = $logDir . "archive_{$type}_{$index}.json";
        }

        if (!file_exists($file)) {
            return [];
        }

        $data = json_decode(file_get_contents($file));

        if (!$data || !is_array($data)) {
            return [];
        }

        return $data;
    }

    public function get_rozetka_log_pages($type)
    {
        $logDir = $this->config->root_dir . '/log/rozetka/';

        if (!in_array($type, ['categories', 'brands'])) {
            return 0;
        }

        $pages = 0;

        $current = $logDir . "changes_{$type}.json";
        if (file_exists($current)) {
            $pages = 1;
        }

        $index = 1;
        while (file_exists($logDir . "archive_{$type}_{$index}.json")) {
            $pages++;
            $index++;
        }

        return $pages;
    }

    public function sync_api_categories_flat(): array
    {
        $stats = ['processed' => 0, 'created' => 0, 'updated' => 0, 'api_calls' => 0];
        $page = 1;

        do {
            $stats['api_calls']++;

            $meta = $this->get_api_categories(['page' => $page]);

            $pageCount = (int)($meta->pageCount ?? 1);
            $currentPage = (int)($meta->currentPage ?? $page);

            $page = $currentPage + 1;
        } while ($page <= $pageCount);

        return $stats;
    }
}
