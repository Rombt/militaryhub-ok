<?php

namespace ModulesCore\modules\ProductsImport;

use Database;
use mysqli;
use RuntimeException;

class DatabaseManager
{
    private $source;
    private $target;
    private $sourcePrefix;
    private $targetPrefix;

    public function __construct(array $config)
    {
        $this->sourcePrefix = $this->validatePrefix($config['tables']['source_prefix'] ?? null, 'source_prefix');
        $this->targetPrefix = $this->validatePrefix($config['tables']['target_prefix'] ?? null, 'target_prefix');
        $this->source = new mysqli(
            $config['source']['host'],
            $config['source']['username'],
            $config['source']['password'],
            $config['source']['database']
        );

        if ($this->source->connect_error) {
            throw new RuntimeException(
                'Не удалось подключиться к source: ' .
                $this->source->connect_error
            );
        }

        
        $this->target = new SafeDatabase($this->targetPrefix);

        $this->ensureTables();
    }

    private function validatePrefix($value, string $setting): string
    {
        if (!is_string($value) || !preg_match('/^[A-Za-z0-9_]*$/D', $value)) {
            throw new RuntimeException('Укажите корректный tables.' . $setting . ' в config.php.');
        }
        return $value;
    }

    public function sourceTable(string $name): string
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/D', $name)) {
            throw new RuntimeException('Некорректное имя исходной таблицы.');
        }
        return $this->sourcePrefix . $name;
    }

    public function targetTable(string $name): string
    {
        return $this->target->table($name);
    }

    /** Проверка обязательных колонок до массовой записи товаров. */
    public function assertProductsSchema(): void
    {
        $required = [
            'url', 'brand_id', 'name', 'annotation', 'description', 'visible',
            'position', 'meta_title', 'meta_keywords', 'meta_description',
            'created', 'featured', 'external_id', 'rating', 'votes', 'special',
            'main_category_id', 'main_image_id', 'main_order'
        ];
        $result = $this->target->query('SHOW COLUMNS FROM __products');
        if ($result === false) {
            throw new RuntimeException('Не удалось проверить структуру таблицы products.');
        }
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[$row['Field']] = true;
        }
        $missing = array_values(array_filter($required, function ($column) use ($columns) {
            return !isset($columns[$column]);
        }));
        if ($missing) {
            throw new RuntimeException('В целевой таблице products отсутствуют столбцы: ' . implode(', ', $missing));
        }
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function closeSource()
    {
        if ($this->source instanceof mysqli) {
            $this->source->close();
            $this->source = null;
        }
    }

    /**
     * Создаёт служебные таблицы модуля.
     */
    private function ensureTables()
    {
        $queries = [

            // Соответствие товаров
            "
            CREATE TABLE IF NOT EXISTS __rmbt_product_import_map (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                sportfly_product_id INT UNSIGNED NOT NULL,
                fly_product_id INT UNSIGNED NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_sportfly_product_id (sportfly_product_id),
                UNIQUE KEY uq_fly_product_id (fly_product_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
            ",

            // Соответствие категорий
            "
            CREATE TABLE IF NOT EXISTS __rmbt_category_import_map (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                sportfly_category_id INT UNSIGNED NOT NULL,
                fly_category_id INT UNSIGNED NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_sportfly_category_id (sportfly_category_id),
                UNIQUE KEY uq_fly_category_id (fly_category_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
            ",

            // Соответствие брендов (many source brands -> one existing target brand is allowed)
            "
            CREATE TABLE IF NOT EXISTS __rmbt_brand_import_map (
                sportfly_brand_id INT UNSIGNED NOT NULL,
                fly_brand_id INT UNSIGNED NOT NULL,
                PRIMARY KEY (sportfly_brand_id),
                KEY idx_fly_brand_id (fly_brand_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
            ",

            // Соответствие вариантов
            "
            CREATE TABLE IF NOT EXISTS __rmbt_variant_import_map (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                sportfly_variant_id INT UNSIGNED NOT NULL,
                fly_variant_id INT UNSIGNED NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_sportfly_variant_id (sportfly_variant_id),
                UNIQUE KEY uq_fly_variant_id (fly_variant_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
            ",

            // Соответствие свойств
            "
            CREATE TABLE IF NOT EXISTS __rmbt_feature_import_map (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                sportfly_feature_id INT UNSIGNED NOT NULL,
                fly_feature_id INT UNSIGNED NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_sportfly_feature_id (sportfly_feature_id),
                UNIQUE KEY uq_fly_feature_id (fly_feature_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
            ",

            // Соответствие значений свойств
            "
            CREATE TABLE IF NOT EXISTS __rmbt_feature_value_import_map (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                sportfly_feature_value_id INT UNSIGNED NOT NULL,
                fly_feature_value_id INT UNSIGNED NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_sportfly_feature_value_id (sportfly_feature_value_id),
                UNIQUE KEY uq_fly_feature_value_id (fly_feature_value_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
            ",

            // Соответствие изображений товаров
            "
            CREATE TABLE IF NOT EXISTS __rmbt_image_import_map (
                sportfly_image_id INT NOT NULL,
                fly_image_id INT NOT NULL,
                PRIMARY KEY (sportfly_image_id),
                KEY fly_image_id (fly_image_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
            ",

            // Состояние переносов файлов изображений
            "
            CREATE TABLE IF NOT EXISTS __rmbt_image_transfer (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                product_import_status VARCHAR(32) NOT NULL,
                image_transfer_status VARCHAR(32) NOT NULL,
                total INT UNSIGNED NOT NULL DEFAULT 0,
                transferred INT UNSIGNED NOT NULL DEFAULT 0,
                skipped INT UNSIGNED NOT NULL DEFAULT 0,
                errors INT UNSIGNED NOT NULL DEFAULT 0,
                started_at DATETIME NULL,
                finished_at DATETIME NULL,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3
            "
        ];

        foreach ($queries as $query) {
            if (!$this->target->query($query)) {
                throw new RuntimeException(
                    'Не удалось создать таблицу импорта: ' .
                    $this->target->error()
                );
            }
        }

        // Idempotent migration of module-owned tables only.
        // Never alter OkayCMS core tables (products_features_values, etc.).
        $this->migrateModulePrimaryKeys();
    }

    private function migrateModulePrimaryKeys(): void
    {
        $owned = [
            'rmbt_product_import_map' => ['id'],
            'rmbt_category_import_map' => ['id'],
            'rmbt_brand_import_map' => ['sportfly_brand_id'],
            'rmbt_variant_import_map' => ['id'],
            'rmbt_feature_import_map' => ['id'],
            'rmbt_feature_value_import_map' => ['id'],
            'rmbt_image_import_map' => ['sportfly_image_id'],
            'rmbt_image_transfer' => ['id'],
        ];
        foreach ($owned as $table => $columns) {
            $physical = $this->targetTable($table);
            if (!preg_match('/^[A-Za-z0-9_]+$/D', $physical)) {
                throw new RuntimeException('Некорректное имя таблицы миграции.');
            }
            $name = '`' . $physical . '`';
            $keys = $this->target->query("SHOW KEYS FROM $name WHERE Key_name = 'PRIMARY'");
            if ($keys === false) {
                throw new RuntimeException('Не удалось проверить первичный ключ ' . $physical . ': ' . $this->target->error());
            }
            if ($keys->num_rows > 0) {
                continue;
            }
            $fields = $this->target->query("SHOW COLUMNS FROM $name");
            if ($fields === false) {
                throw new RuntimeException('Не удалось проверить столбцы ' . $physical);
            }
            $existing = [];
            while ($field = $fields->fetch_assoc()) {
                $existing[$field['Field']] = $field;
            }
            foreach ($columns as $column) {
                if (!isset($existing[$column])) {
                    throw new RuntimeException('Миграция ' . $physical . ': отсутствует поле ' . $column);
                }
                if (strtoupper($existing[$column]['Null']) !== 'NO') {
                    throw new RuntimeException('Миграция ' . $physical . ': ключевое поле допускает NULL: ' . $column);
                }
            }
            $quoted = array_map(static fn($column) => '`' . $column . '`', $columns);
            $group = implode(',', $quoted);
            $duplicates = $this->target->query("SELECT $group FROM $name GROUP BY $group HAVING COUNT(*) > 1 LIMIT 1");
            if ($duplicates === false || $duplicates->num_rows > 0) {
                throw new RuntimeException('Миграция ' . $physical . ': обнаружены дубликаты ключей либо ошибка проверки.');
            }
            // Adding a PK to existing AUTO_INCREMENT columns preserves existing values.
            if (!$this->target->query("ALTER TABLE $name ADD PRIMARY KEY ($group)")) {
                throw new RuntimeException('Не удалось добавить PRIMARY KEY в ' . $physical . ': ' . $this->target->error());
            }
        }
    }
}
