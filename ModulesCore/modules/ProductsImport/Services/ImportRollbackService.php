<?php

namespace ModulesCore\modules\ProductsImport\Services;

/**
 * DEV-safe import rollback journal. Captures target rows before and after a run.
 * It never touches tables outside the explicit import allowlist.
 */
class ImportRollbackService
{
    private $db;
    private string $root;
    private int $maxRows;
    private array $tables = [
        'products_categories', 'products_features_values', 'lang_variants',
        'lang_products', 'lang_categories', 'lang_features_values', 'lang_features',
        'variants', 'products', 'images', 'categories_features', 'categories',
        'features_values', 'features', 'rmbt_variant_import_map',
        'rmbt_product_import_map', 'rmbt_category_import_map',
        'rmbt_brand_import_map', 'rmbt_feature_value_import_map',
        'rmbt_feature_import_map', 'rmbt_image_import_map', 'brands',
        'rmbt_image_transfer'
    ];
    private array $keys = [];
    private array $columnTypes = [];

    public function __construct($db, string $root, int $maxRows = 100000)
    {
        $this->db = $db;
        $this->root = rtrim($root, '/\\');
        $this->maxRows = $maxRows;
    }

    public function ensureTables(): void
    {
        $this->sql('CREATE TABLE IF NOT EXISTS __rmbt_rollback_runs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            state VARCHAR(32) NOT NULL,
            started_at DATETIME NOT NULL,
            finished_at DATETIME NULL,
            changed_rows INT UNSIGNED NOT NULL DEFAULT 0,
            file_manifest LONGTEXT NULL,
            created_files LONGTEXT NULL,
            INDEX (state)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
        $this->sql('CREATE TABLE IF NOT EXISTS __rmbt_rollback_rows (
            run_id BIGINT UNSIGNED NOT NULL,
            table_name VARCHAR(100) NOT NULL,
            row_key VARCHAR(512) NOT NULL,
            before_json LONGTEXT NULL,
            after_json LONGTEXT NULL,
            PRIMARY KEY (run_id, table_name, row_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3');
    }

    private function sql(string $query, ...$args)
    {
        if (trim($query) === '') {
            throw new \RuntimeException('Откат: попытка выполнить пустой SQL-запрос.');
        }
        try {
            $result = $args ? $this->db->queryPrepared($query, $args) : $this->db->query($query);
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                'SQL отката [' . substr(preg_replace('/\\s+/', ' ', trim($query)), 0, 220) .
                ']: ' . $e->getMessage(), 0, $e
            );
        }
        if ($result === false) {
            throw new \RuntimeException(
                'Ошибка SQL отката [' . substr(preg_replace('/\\s+/', ' ', trim($query)), 0, 220) .
                ']: ' . $this->db->error()
            );
        }
        return $result;
    }

    private function rows(string $query, ...$args): array
    {
        $result = $this->sql($query, ...$args);
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    private function quote(string $identifier): string
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/D', $identifier)) {
            throw new \RuntimeException('Некорректное имя SQL-идентификатора');
        }
        return '`' . $identifier . '`';
    }

    private function primaryKeys(string $table): array
    {
        if (isset($this->keys[$table])) return $this->keys[$table];
        $physical = $this->db->table($table);
        $result = $this->rows('SHOW KEYS FROM ' . $this->quote($physical) . " WHERE Key_name = 'PRIMARY'");
        usort($result, fn($a, $b) => (int)$a['Seq_in_index'] <=> (int)$b['Seq_in_index']);
        $keys = array_column($result, 'Column_name');
        // Legacy OkayCMS relation tables can legitimately have no primary key.
        return $this->keys[$table] = $keys;
    }

    private function keyFor(string $table, array $row): string
    {
        $key = [];
        $columns = $this->primaryKeys($table);
        if (!$columns) {
            return json_encode(['__row_hash' => hash('sha256', $this->encode($row))], JSON_THROW_ON_ERROR);
        }
        foreach ($columns as $column) $key[$column] = $row[$column];
        return json_encode($key, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    private function encode(array $row): string
    {
        return json_encode($row, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
    }

    private function snapshot(string $table): array
    {
        $physical = $this->quote($this->db->table($table));
        $result = $this->sql('SELECT * FROM ' . $physical);
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $key = $this->keyFor($table, $row);
            if (!$this->primaryKeys($table)) {
                // Multiset semantics: identical rows are separate records.
                $base = json_decode($key, true, 512, JSON_THROW_ON_ERROR);
                $ordinal = 0;
                do {
                    $base['__ordinal'] = $ordinal++;
                    $key = json_encode($base, JSON_THROW_ON_ERROR);
                } while (isset($rows[$key]));
            }
            $rows[$key] = $this->encode($row);
            if (count($rows) > $this->maxRows) {
                throw new \RuntimeException('Лимит снимка отката превышен для ' . $table);
            }
        }
        return $rows;
    }

    private function files(): array
    {
        $found = [];
        foreach (['files/brands', 'files/originals/products'] as $relative) {
            $directory = $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
            if (!is_dir($directory)) continue;
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if (!$file->isFile()) continue;
                $path = str_replace('\\', '/', substr($file->getPathname(), strlen($this->root) + 1));
                $found[$path] = true;
                if (count($found) > $this->maxRows * 5) throw new \RuntimeException('Слишком много файлов для безопасного снимка');
            }
        }
        return $found;
    }

    public function begin(): int
    {
        $this->ensureTables();
        if ($this->rows("SELECT id FROM __rmbt_rollback_runs WHERE state IN ('capturing','running','finalizing','rolling_back') LIMIT 1")) {
            throw new \RuntimeException('Предыдущая операция отката или импорта не завершена.');
        }
        $this->sql("INSERT INTO __rmbt_rollback_runs (state,started_at,file_manifest) VALUES ('capturing',NOW(),?)", $this->encode(array_keys($this->files())));
        $runId = (int)$this->db->insert_id();
        try {
            foreach ($this->tables as $table) {
                $this->primaryKeys($table);
                foreach ($this->snapshot($table) as $key => $value) {
                    $this->sql('INSERT INTO __rmbt_rollback_rows (run_id,table_name,row_key,before_json) VALUES (?,?,?,?)', $runId, $table, $key, $value);
                }
            }
            $this->sql("UPDATE __rmbt_rollback_runs SET state='running' WHERE id=?", $runId);
            return $runId;
        } catch (\Throwable $e) {
            $this->sql('DELETE FROM __rmbt_rollback_rows WHERE run_id=?', $runId);
            $this->sql('DELETE FROM __rmbt_rollback_runs WHERE id=?', $runId);
            throw $e;
        }
    }

    public function finish(int $runId): void
    {
        $this->sql("UPDATE __rmbt_rollback_runs SET state='finalizing' WHERE id=? AND state='running'", $runId);
        foreach ($this->tables as $table) {
            $before = [];
            foreach ($this->rows('SELECT row_key,before_json FROM __rmbt_rollback_rows WHERE run_id=? AND table_name=?', $runId, $table) as $row) {
                $before[$row['row_key']] = $row['before_json'];
            }
            $after = $this->snapshot($table);
            foreach ($after as $key => $value) {
                if (array_key_exists($key, $before)) {
                    if ($before[$key] === $value) {
                        $this->sql('DELETE FROM __rmbt_rollback_rows WHERE run_id=? AND table_name=? AND row_key=?', $runId, $table, $key);
                    } else {
                        $this->sql('UPDATE __rmbt_rollback_rows SET after_json=? WHERE run_id=? AND table_name=? AND row_key=?', $value, $runId, $table, $key);
                    }
                    unset($before[$key]);
                } else {
                    $this->sql('INSERT INTO __rmbt_rollback_rows (run_id,table_name,row_key,after_json) VALUES (?,?,?,?)', $runId, $table, $key, $value);
                }
            }
            // Rows in $before were deleted during the import; their after_json remains NULL.
        }
        $this->saveCreatedFiles($runId);
        $count = $this->rows('SELECT COUNT(*) AS n FROM __rmbt_rollback_rows WHERE run_id=?', $runId)[0]['n'];
        $this->sql("UPDATE __rmbt_rollback_runs SET state='ready',finished_at=NOW(),changed_rows=? WHERE id=?", (int)$count, $runId);
    }

    private function saveCreatedFiles(int $runId): void
    {
        $run = $this->rows('SELECT file_manifest FROM __rmbt_rollback_runs WHERE id=?', $runId)[0];
        $baseline = array_fill_keys(json_decode($run['file_manifest'], true, 512, JSON_THROW_ON_ERROR), true);
        $created = [];
        foreach (array_diff_key($this->files(), $baseline) as $relative => $_) {
            $path = $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
            $created[$relative] = hash_file('sha256', $path);
        }
        $this->sql('UPDATE __rmbt_rollback_runs SET created_files=? WHERE id=?', $this->encode($created), $runId);
    }

    public function refreshTransfer(): void
    {
        $run = $this->latest();
        if (!$run || $run['state'] !== 'ready') return;
        $id = (int)$run['id'];
        foreach ($this->rows('SELECT row_key FROM __rmbt_rollback_rows WHERE run_id=? AND table_name=?', $id, 'rmbt_image_transfer') as $change) {
            $key = json_decode($change['row_key'], true, 512, JSON_THROW_ON_ERROR);
            $row = $this->findRow('rmbt_image_transfer', $key);
            $this->sql('UPDATE __rmbt_rollback_rows SET after_json=? WHERE run_id=? AND table_name=? AND row_key=?',
                $row === null ? null : $this->encode($row), $id, 'rmbt_image_transfer', $change['row_key']);
        }
        $this->saveCreatedFiles($id);
    }

    public function latest(): ?array
    {
        // The newest journal entry may already have been rolled back.
        // Return the newest pending run, but never skip an active/unknown run.
        $rows = $this->rows("SELECT * FROM __rmbt_rollback_runs WHERE state <> 'rolled_back' ORDER BY id DESC LIMIT 1");
        return $rows[0] ?? null;
    }

    public function latestRecorded(): ?array
    {
        $rows = $this->rows("SELECT * FROM __rmbt_rollback_runs ORDER BY id DESC LIMIT 1");
        return $rows[0] ?? null;
    }

    public function rollback(): array
    {
        $run = $this->latest();
        if (!$run || $run['state'] !== 'ready') throw new \RuntimeException('Нет завершённого импорта, доступного для отката.');
        $id = (int)$run['id'];
        $changes = $this->rows('SELECT * FROM __rmbt_rollback_rows WHERE run_id=?', $id);
        // Check ALL journal rows before any rollback write. Report precise differences.
        $conflicts = [];
        $totalConflicts = 0;
        foreach ($changes as $change) {
            $table = $change['table_name'];
            if (!in_array($table, $this->tables, true)) {
                throw new \RuntimeException('Неизвестная таблица журнала: ' . $table);
            }
            $key = json_decode($change['row_key'], true, 512, JSON_THROW_ON_ERROR);
            $current = $this->findRow($table, $key);
            $expected = $change['after_json'] === null ? null :
                json_decode($change['after_json'], true, 512, JSON_THROW_ON_ERROR);
            if ($this->rowsEqual($table, $current, $expected)) continue;
            ++$totalConflicts;
            if (count($conflicts) < 20) {
                $conflicts[] = $this->describeConflict($table, $key, $expected, $current);
            }
        }
        if ($totalConflicts > 0) {
            throw new \RuntimeException(
                'Откат отменён ДО изменения данных. Конфликтов: ' . $totalConflicts .
                '. Первые ' . count($conflicts) . ': ' .
                json_encode($conflicts, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR)
            );
        }
        $newFiles = json_decode($run['created_files'] ?: '{}', true, 512, JSON_THROW_ON_ERROR);
        foreach ($newFiles as $relative => $expectedHash) {
            if (!preg_match('#^files/(brands|originals/products)/[A-Za-z0-9_./-]+$#D', $relative) || strpos($relative, '..') !== false) {
                throw new \RuntimeException('Недопустимый путь файла в журнале отката');
            }
            $path = $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
            if (!is_file($path) || hash_file('sha256', $path) !== $expectedHash) {
                throw new \RuntimeException('Откат отменён: файл изменён или удалён после импорта: ' . $relative);
            }
        }
        $this->sql('START TRANSACTION');
        try {
            $this->sql("UPDATE __rmbt_rollback_runs SET state='rolling_back' WHERE id=?", $id);
            // Foreign keys disabled only within this connection; no TRUNCATE/DDL.
            $this->sql('SET FOREIGN_KEY_CHECKS=0');
            foreach (array_reverse($changes) as $change) {
                $table = $change['table_name'];
                $key = json_decode($change['row_key'], true, 512, JSON_THROW_ON_ERROR);
                $before = $change['before_json'] === null ? null : json_decode($change['before_json'], true, 512, JSON_THROW_ON_ERROR);
                if ($before === null) {
                    $this->deleteRow($table, $key);
                } elseif ($change['after_json'] === null) {
                    $this->insertRow($table, $before);
                } else {
                    $this->updateRow($table, $key, $before);
                }
            }
            $this->sql("UPDATE __rmbt_rollback_runs SET state='rolled_back',finished_at=NOW() WHERE id=?", $id);
            $this->sql('COMMIT');
        } catch (\Throwable $e) {
            try {
                $this->sql('ROLLBACK');
            } catch (\Throwable $rollbackError) {
                throw new \RuntimeException(
                    'Откат SQL-транзакции завершился ошибкой: ' . $rollbackError->getMessage() .
                    '; исходная ошибка: ' . $e->getMessage(), 0, $e
                );
            }
            throw $e;
        } finally {
            $this->sql('SET FOREIGN_KEY_CHECKS=1');
        }
        $failedFiles = [];
        foreach ($newFiles as $relative => $hash) {
            $path = $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
            if (is_file($path) && hash_file('sha256', $path) === $hash && !@unlink($path)) {
                $failedFiles[] = $relative;
            }
        }
        return ['run_id' => $id, 'restored_rows' => count($changes), 'files_not_deleted' => $failedFiles];
    }

    /**
     * Normalize according to actual MySQL column types, not loose PHP equality.
     * Integer values are compared as canonical decimal strings (no float casts).
     * NULL, empty strings, decimal precision and ordinary text stay distinct.
     */
    private function columnTypes(string $table): array
    {
        if (isset($this->columnTypes[$table])) return $this->columnTypes[$table];
        $physical = $this->quote($this->db->table($table));
        $types = [];
        foreach ($this->rows('SHOW COLUMNS FROM ' . $physical) as $column) {
            $types[$column['Field']] = strtolower($column['Type']);
        }
        return $this->columnTypes[$table] = $types;
    }

    private function canonicalValue(string $table, string $column, $value)
    {
        if ($value === null) return null;
        $types = $this->columnTypes($table);
        $type = $types[$column] ?? '';
        if (preg_match('/^(tinyint|smallint|mediumint|int|integer|bigint)\b/', $type)) {
            $string = (string)$value;
            if (preg_match('/^[+-]?[0-9]+$/D', $string)) {
                $negative = isset($string[0]) && $string[0] === '-';
                $digits = ltrim(ltrim($string, '+-'), '0');
                if ($digits === '') $digits = '0';
                return $negative && $digits !== '0' ? '-' . $digits : $digits;
            }
        }
        if (preg_match('/^(decimal|numeric|dec|fixed)\\b/', $type)) {
            // Exact decimal comparison: no conversion to PHP float.
            $string = (string)$value;
            if (preg_match('/^([+-]?)([0-9]+)(?:\\.([0-9]+))?$/D', $string, $match)) {
                $integer = ltrim($match[2], '0');
                if ($integer === '') $integer = '0';
                $fraction = rtrim($match[3] ?? '', '0');
                $negative = $match[1] === '-' && ($integer !== '0' || $fraction !== '');
                return ($negative ? '-' : '') . $integer .
                    ($fraction !== '' ? '.' . $fraction : '');
            }
        }
        if (preg_match('/^(float|double|real)\\b/', $type)) {
            // These MySQL types are already approximate. Compare their
            // numeric values, but do not use an epsilon that could conceal edits.
            if (is_numeric($value)) {
                $number = (float)$value;
                if (is_finite($number)) return $number;
            }
        }
        // BIT, text and binary remain distinct; NULL is handled above.
        return is_string($value) ? $value : (is_int($value) || is_float($value) ? (string)$value : $value);
    }

    private function valuesEqual(string $table, string $column, $left, $right): bool
    {
        return $this->canonicalValue($table, $column, $left)
            === $this->canonicalValue($table, $column, $right);
    }

    private function canonicalRow(string $table, array $row): array
    {
        foreach ($row as $column => $value) {
            $row[$column] = $this->canonicalValue($table, $column, $value);
        }
        return $row;
    }

    private function rowsEqual(string $table, ?array $left, ?array $right): bool
    {
        if ($left === null || $right === null) return $left === $right;
        if (count($left) !== count($right)) return false;
        foreach ($left as $column => $value) {
            if (!array_key_exists($column, $right) ||
                !$this->valuesEqual($table, $column, $value, $right[$column])) return false;
        }
        return true;
    }

    private function describeConflict(string $table, array $key, ?array $expected, ?array $current): array
    {
        $entry = ['table' => $table, 'key' => $key];
        if ($expected === null) {
            $entry['type'] = 'unexpected_row';
            $entry['current'] = $this->compactValues($current ?? []);
        } elseif ($current === null) {
            $entry['type'] = 'missing_row';
            $entry['expected'] = $this->compactValues($expected);
        } else {
            $entry['type'] = 'changed_row';
            $diff = [];
            foreach (array_unique(array_merge(array_keys($expected), array_keys($current))) as $column) {
                if (array_key_exists($column, $expected) && array_key_exists($column, $current)
                    && $this->valuesEqual($table, $column, $expected[$column], $current[$column])) continue;
                $diff[$column] = [
                    'after_import' => $this->compactValue($expected[$column] ?? null),
                    'current' => $this->compactValue($current[$column] ?? null),
                ];
            }
            $entry['fields'] = $diff;
        }
        return $entry;
    }

    private function compactValues(array $row): array
    {
        $values = [];
        foreach ($row as $column => $value) {
            $values[$column] = $this->compactValue($value);
        }
        return $values;
    }

    private function compactValue($value)
    {
        if (!is_string($value) || strlen($value) <= 160) return $value;
        return substr($value, 0, 160) . '… [bytes=' . strlen($value) .
            ', sha256=' . hash('sha256', $value) . ']';
    }

    private function findRow(string $table, array $key): ?array
    {
        if (!$this->primaryKeys($table)) {
            $matched = 0;
            $result = $this->sql('SELECT * FROM ' . $this->quote($this->db->table($table)));
            while ($row = $result->fetch_assoc()) {
                if (hash('sha256', $this->encode($this->canonicalRow($table, $row))) === ($key['__row_hash'] ?? '')) {
                    if ($matched++ === (int)($key['__ordinal'] ?? 0)) return $row;
                }
            }
            return null;
        }
        $where = implode(' AND ', array_map(fn($column) => $this->quote($column) . '=?', array_keys($key)));
        $rows = $this->rows('SELECT * FROM ' . $this->quote($this->db->table($table)) . ' WHERE ' . $where . ' LIMIT 1', ...array_values($key));
        return $rows[0] ?? null;
    }

    private function deleteRow(string $table, array $key): void
    {
        if (!$this->primaryKeys($table)) {
            $lookup = $key;
            $lookup['__ordinal'] = 0;
            $row = $this->findRow($table, $lookup);
            if ($row === null) throw new \RuntimeException('Строка таблицы связей отсутствует: ' . $table);
            $where = implode(' AND ', array_map(fn($column) => $this->quote($column) . ' <=> ?', array_keys($row)));
            $this->sql('DELETE FROM ' . $this->quote($this->db->table($table)) . ' WHERE ' . $where . ' LIMIT 1', ...array_values($row));
            return;
        }
        $where = implode(' AND ', array_map(fn($column) => $this->quote($column) . '=?', array_keys($key)));
        $this->sql('DELETE FROM ' . $this->quote($this->db->table($table)) . ' WHERE ' . $where, ...array_values($key));
    }

    private function insertRow(string $table, array $row): void
    {
        $columns = array_keys($row);
        $this->sql('INSERT INTO ' . $this->quote($this->db->table($table)) . ' (' . implode(',', array_map([$this,'quote'], $columns)) . ') VALUES (' . implode(',', array_fill(0,count($columns),'?')) . ')', ...array_values($row));
    }

    private function updateRow(string $table, array $key, array $row): void
    {
        if (!$this->primaryKeys($table)) {
            throw new \RuntimeException('UPDATE не поддерживается для таблицы без первичного ключа: ' . $table);
        }
        $where = implode(' AND ', array_map(fn($column) => $this->quote($column) . '=?', array_keys($key)));
        $set = implode(',', array_map(fn($column) => $this->quote($column) . '=?', array_keys($row)));
        $this->sql('UPDATE ' . $this->quote($this->db->table($table)) . ' SET ' . $set . ' WHERE ' . $where, ...array_values($row), ...array_values($key));
    }
}
