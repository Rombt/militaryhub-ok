<?php

namespace ModulesCore\modules\ProductsImport\Services;

class ImportResetService
{
    private $db;
    private $allowFullReset;

    public function __construct($db, bool $allowFullReset = false)
    {
        $this->db = $db;
        $this->allowFullReset = $allowFullReset;
    }

    /**
     * Полностью очищает данные, создаваемые/используемые импортом.
     */
    public function reset(): void
    {
        if (!$this->allowFullReset) {
            throw new \RuntimeException('Полный сброс запрещён настройкой allow_full_reset в config.php.');
        }
        $tables = [
            // Связи товаров
            '__products_categories',
            '__products_features_values',

            // Переводы
            '__lang_variants',
            '__lang_products',
            '__lang_categories',
            '__lang_features_values',
            '__lang_features',

            // Варианты и товары
            '__variants',
            '__products',

            // Изображения
            '__images',

            // Категории
            '__categories_features',
            '__categories',

            // Значения и характеристики
            '__features_values',
            '__features',

            // Таблицы соответствия Sportfly → militaryhub
            '__rmbt_variant_import_map',
            '__rmbt_product_import_map',
            '__rmbt_category_import_map',
            '__rmbt_brand_import_map',
            '__rmbt_feature_value_import_map',
            '__rmbt_feature_import_map',
            '__rmbt_image_import_map',

            // Бренды каталога (используются импортом)
            '__brands',

            // Таблица результатов импорта
            '__rmbt_image_transfer',
        ];

        // Preflight prevents partial truncation when a table is missing.
        // All names are resolved using the module's target_prefix, never the CMS prefix.
        foreach ($tables as $table) {
            $physicalName = $this->db->table(substr($table, 2));
            $this->db->query('SHOW TABLES LIKE ?', $physicalName);
            if (!$this->db->num_rows()) {
                throw new \RuntimeException('Сброс отменён: отсутствует таблица ' . $physicalName);
            }
        }

        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');

        try {
            foreach ($tables as $table) {
                $this->db->query('TRUNCATE TABLE `' . $table . '`');
            }
        } finally {
            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
        }
    }
}
