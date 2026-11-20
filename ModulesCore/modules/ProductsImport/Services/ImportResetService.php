<?php

namespace ProductsImport\Services;

class ImportResetService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Полностью очищает данные, создаваемые/используемые импортом.
     */
    public function reset(): void
    {
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

            // Таблицы соответствия Sportfly → Fly
            '__rmbt_variant_import_map',
            '__rmbt_product_import_map',
            '__rmbt_category_import_map',
            '__rmbt_feature_value_import_map',
            '__rmbt_feature_import_map',
            '__rmbt_image_import_map',

            // Таблица результатов импорта
            '__rmbt_image_transfer',
        ];

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
