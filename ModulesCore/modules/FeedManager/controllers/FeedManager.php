<?php

class FeedManager extends Okay
{

    public static $db_feed_man;

    public function __construct()
    {
        self::$db_feed_man = new Database();
    }

    /**
     * берёт данные из указанных колонок csv файла, передаются в массиве, и записывает в одноимённые колонки таблицы базы данных
     * primary_kye
     */
    public function importCsvToDb(string $primaryKey, string $csvPath, string $table, array $columns = [])
    {
        if (! file_exists($csvPath)) {
            error_log("File not found: $csvPath");
            return;
        }

        if (($handle = fopen($csvPath, 'r')) === false) {
            error_log("Failed to open CSV file.");
            return;
        }

        $delimiter = self::detect_csv_delimiter($handle);

        $header = fgetcsv($handle, 0, $delimiter);

        if (! $header) {
            error_log("Failed to read CSV headers.");
            fclose($handle);
            return;
        }

        // Оставляем только нужные колонки
        $columnsToUse = $columns ? array_intersect($header, $columns) : $header;

        // Убедимся, что первичный ключ в заголовках
        if (! in_array($primaryKey, $header)) {
            error_log("The CSV is missing a column with a primary key: $primaryKey");
            fclose($handle);
            return;
        }

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {

            $rowAssoc = array_combine($header, $row);

            if (! isset($rowAssoc[$primaryKey])) {
                continue;
            }

            $pkValue = self::$db_feed_man->escape($rowAssoc[$primaryKey]);

            $setParts = [];

            foreach ($columnsToUse as $col) {

                if ($col === $primaryKey) {
                    continue;
                }

                $value = array_key_exists($col, $rowAssoc) ? $rowAssoc[$col] : null;

                $escaped = is_null($value)
                    ? 'NULL'
                    : "'" . self::$db_feed_man->escape($value) . "'";

                $setParts[] = "`$col` = $escaped";
            }

            if (! empty($setParts)) {

                $updateSql = "UPDATE `$table`
                              SET " . implode(', ', $setParts) . "
                              WHERE `$primaryKey` = '$pkValue'";

                self::$db_feed_man->query($updateSql);
            }
        }

        fclose($handle);
    }

    private static function detect_csv_delimiter($handle, $delimiters = [',', ';', "\t", '|'])
    {
        $line = fgets($handle);

        rewind($handle);

        $bestDelimiter = $delimiters[0];
        $maxFields = 0;

        // Перебираем все возможные разделители
        foreach ($delimiters as $delimiter) {

            $fields = str_getcsv($line, $delimiter);

            if (count($fields) > $maxFields) {
                $maxFields = count($fields);
                $bestDelimiter = $delimiter;
            }
        }

        return $bestDelimiter;
    }

    /**
     * Выполняет запрос
     * результат экспортирует в csv файл
     */
    public static function exportQueryToCSV(
        string $query,
        string $file_name = '',
        string $folderPath = 'ModulesCore/modules/FeedManager/csv'
    ): bool {
        try {

            self::$db_feed_man->query($query);

            $results = self::$db_feed_man->results();

            if (empty($results)) {
                error_log("Module: No data returned from query.");
                return false;
            }

            if (! is_dir($folderPath)) {

                if (! mkdir($folderPath, 0755, true)) {
                    error_log("Module: Failed to create directory: $folderPath");
                    return false;
                }
            }

            $file_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $file_name);

            $prefix = $file_name === ''
                ? 'export'
                : $file_name;

            $filePath = rtrim($folderPath, DIRECTORY_SEPARATOR)
                . DIRECTORY_SEPARATOR
                . $prefix
                . '_'
                . date('Y-m-d_H-i-s')
                . '.csv';

            $fp = fopen($filePath, 'w');

            if ($fp === false) {
                error_log("Module: Failed to open file for writing: $filePath");
                return false;
            }

            // Добавим BOM для Excel
            fwrite($fp, "\xEF\xBB\xBF");

            $delimiter = ';';

            fputcsv($fp, array_keys((array) $results[0]), $delimiter);

            foreach ($results as $row) {
                fputcsv(
                    $fp,
                    array_map('html_entity_decode', (array) $row),
                    $delimiter
                );
            }

            fclose($fp);

            return true;

        } catch (\Exception $e) {

            error_log("Module: Failed to export CSV: " . $e->getMessage());

            return false;
        }
    }

    public function get_structure_table($table_name)
    {
        if (empty($table_name)) {
            return false;
        }

        // Экранируем имя таблицы
        $safe_table = '`' . preg_replace('/[^a-zA-Z0-9_]/', '', $table_name) . '`';

        try {

            self::$db_feed_man->query("SHOW CREATE TABLE $safe_table");

            $result = self::$db_feed_man->result();

            print_r($result->{'Create Table'});

            exit;

            return $result->{'Create Table'} ?? false;

        } catch (\Exception $e) {

            error_log("Module: Failed to get structure for table $table_name: " . $e->getMessage());

            return false;
        }
    }

    public function importFromCsv(
        string $filePath,
        string $table_name,
        int $length_row = 10000
    ): void {

        if (! file_exists($filePath)) {
            error_log("Module: CSV file not found at $filePath");
            return;
        }

        if (($handle = fopen($filePath, 'r')) !== false) {

            $header = fgetcsv($handle, $length_row, ",");

            if ($header === false) {

                error_log("Module: Failed to read header row from CSV file at $filePath");

                fclose($handle);

                return;
            }

            while (($data = fgetcsv($handle, $length_row, ",")) !== false) {

                $row = array_combine($header, $data);

                if (! $row) {
                    error_log("Module: Failed to combine CSV header and row data.");
                    continue;
                }

                $setClauseParts = [];
                $values = [];

                foreach ($row as $column => $value) {

                    $value = trim($value);

                    if ($value === '') {

                        $setClauseParts[] = "`$column` = 0";

                    } else {

                        $setClauseParts[] = "`$column` = ?";
                        $values[] = $value;
                    }
                }

                $setClause = implode(", ", $setClauseParts);

                try {

                    $query = self::$db_feed_man->placehold(
                        "REPLACE INTO `$table_name` SET $setClause",
                        ...$values
                    );

                    self::$db_feed_man->query($query);

                } catch (\Exception $e) {

                    error_log("Module: DB error: " . $e->getMessage());
                }
            }

            fclose($handle);

        } else {

            error_log("Module: Unable to open CSV file at $filePath");
        }
    }

    public function createTable($path_sql, $table_name)
    {
        if ($this->tableExists($table_name)) {
            return;
        }

        try {

            $sqlFile = __DIR__ . $path_sql;

            if (! file_exists($sqlFile)) {
                error_log('Module: SQL file not found: ' . $sqlFile);
                return;
            }

            $sql = file_get_contents($sqlFile);

            if (empty($sql)) {
                error_log('Module: SQL file is empty: ' . $sqlFile);
                return;
            }

            $result = self::$db_feed_man->query($sql);

            if (! $result) {
                error_log('Promotions module: Failed to execute SQL from file: ' . $sqlFile);
                return;
            }

            return true;

        } catch (\Exception $e) {

            error_log('Module: Database install error: ' . $e->getMessage());

            return;
        }
    }

    public function tableExists(string $table_name): bool
    {
        try {

            $query = self::$db_feed_man->placehold(
                "SHOW TABLES LIKE ?",
                self::$db_feed_man->escape($table_name)
            );

            self::$db_feed_man->query($query);

            return self::$db_feed_man->result() ? true : false;

        } catch (\Exception $e) {

            error_log("Module: Table check exception for '$table_name': " . $e->getMessage());

            return false;
        }
    }

    public function getPromFeed()
    {
        set_time_limit(0);

        $url = 'https://sportfly.com.ua/prom.php';
        // $url = 'http://local.sportfly/prom.php';

        $timestamp = date('d-m_H-i-s');

        $destination = 'C:/Users/Роман/Desktop/prom_feed/prom_feed_' . $timestamp . '.xml';
        // $destination = 'C:/Users/Роман/Desktop/prom_feed/local_prom_feed_' . $timestamp . '.xml';

        $ch = curl_init($url);

        curl_setopt_array(
            $ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 1800,
            CURLOPT_SSL_VERIFYPEER => true,
            ]
        );

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            die('Ошибка cURL: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpCode !== 200) {
            die('Ошибка: сервер вернул код ' . $httpCode);
        }

        file_put_contents($destination, $response);

        echo "Файл успешно сохранён: {$destination}" . PHP_EOL;
    }
}
