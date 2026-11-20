<?php

namespace ProductsImport;

use Database;
use mysqli;
use RuntimeException;

class DatabaseManager
{
    private $source;
    private $target;

    public function __construct(array $config)
    {
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

        $this->target = new Database();

        $this->ensureTables();
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
    }
}
