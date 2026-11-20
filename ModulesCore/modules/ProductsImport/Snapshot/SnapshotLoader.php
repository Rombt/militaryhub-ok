<?php

namespace ProductsImport\Snapshot;

use mysqli;
use RuntimeException;

/**
 * Загружает необходимые данные Sportfly и формирует Snapshot.
 */
class SnapshotLoader
{
    private $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function load()
    {
        // 1. Товары
        $products = $this->fetchAll(
            "SELECT p.*
             FROM sfly_products p
             INNER JOIN sfly_brands b ON b.id = p.brand_id
             WHERE b.name = 'Fly'
             ORDER BY p.id"
        );

        $productIds = $this->getIds($products, 'id');

        // 2. Языки товаров
        $productLanguages = $this->fetchByIds(
            'sfly_lang_products',
            'product_id',
            $productIds
        );

        // 3. Товар → категории
        $productCategoryLinks = $this->fetchAllByIds(
            'SELECT product_id, category_id, position
             FROM sfly_products_categories
             WHERE product_id IN (%s)
             ORDER BY product_id, position',
            $productIds
        );

        // 4. Товар → значения характеристик.
        // feature_id получаем сразу через JOIN.
        $productFeatureLinks = $this->fetchAllByIds(
            'SELECT pfv.product_id, pfv.value_id, fv.feature_id
             FROM sfly_products_features_values pfv
             INNER JOIN sfly_features_values fv ON fv.id = pfv.value_id
             WHERE pfv.product_id IN (%s)
             ORDER BY pfv.product_id, pfv.value_id',
            $productIds
        );

        // 5. Все категории нужны для построения иерархии по parent_id.
        $allCategories = $this->fetchAll(
            'SELECT * FROM sfly_categories ORDER BY id'
        );

        $categories = $this->filterCategories(
            $allCategories,
            $this->groupBy($productCategoryLinks, 'product_id')
        );

        $categoryIds = $this->getIds($categories, 'id');

        // 6. Языки категорий
        $categoryLanguages = $this->fetchByIds(
            'sfly_lang_categories',
            'category_id',
            $categoryIds
        );

        // 7. Категория → характеристики
        $categoryFeatureLinks = $this->fetchAllByIds(
            'SELECT category_id, feature_id
             FROM sfly_categories_features
             WHERE category_id IN (%s)
             ORDER BY category_id, feature_id',
            $categoryIds
        );

        // 8. Характеристики
        $featureIds = $this->uniqueIds(
            array_merge(
                $this->getIds($categoryFeatureLinks, 'feature_id'),
                $this->getIds($productFeatureLinks, 'feature_id')
            )
        );

        $features = $this->fetchByIds(
            'sfly_features',
            'id',
            $featureIds
        );

        // 9. Языки характеристик
        $featureLanguages = $this->fetchByIds(
            'sfly_lang_features',
            'feature_id',
            $featureIds
        );

        // 10. Значения характеристик
        $valueIds = $this->getIds($productFeatureLinks, 'value_id');

        $featureValues = $this->fetchByIds(
            'sfly_features_values',
            'id',
            $valueIds
        );

        // 11. Языки значений
        $featureValueLanguages = $this->fetchByIds(
            'sfly_lang_features_values',
            'feature_value_id',
            $valueIds
        );

        // 12. Варианты
        $variants = $this->fetchAllByIds(
            'SELECT *
             FROM sfly_variants
             WHERE product_id IN (%s)
             ORDER BY product_id, id',
            $productIds
        );

        $variantIds = $this->getIds($variants, 'id');

        // 13. Изображения товаров
        $images = $this->fetchAllByIds(
            'SELECT *
            FROM sfly_images
            WHERE product_id IN (%s)
            ORDER BY product_id, position, id',
            $productIds
        );

        $imageIds = $this->getIds($images, 'id');

        // 14. Языки вариантов
        $variantLanguages = $this->fetchByIds(
            'sfly_lang_variants',
            'variant_id',
            $variantIds
        );

        // 15. Языки Sportfly
        $languages = $this->fetchAll(
            'SELECT * FROM sfly_languages ORDER BY id'
        );

        return new Snapshot(
            $this->indexById($products, 'id'),
            $this->groupBy($productLanguages, 'product_id'),
            $this->groupBy($productCategoryLinks, 'product_id'),
            $this->indexById($categories, 'id'),
            $this->groupBy($categoryLanguages, 'category_id'),
            $this->groupIds(
                $categoryFeatureLinks,
                'category_id',
                'feature_id'
            ),
            $this->indexById($features, 'id'),
            $this->groupBy($featureLanguages, 'feature_id'),
            $this->indexById($featureValues, 'id'),
            $this->groupBy(
                $featureValueLanguages,
                'feature_value_id'
            ),
            $this->groupIds(
                $productFeatureLinks,
                'product_id',
                'value_id'
            ),
            $this->indexById($variants, 'id'),
            $this->groupIds($variants, 'product_id', 'id'),
            $this->groupBy($variantLanguages, 'variant_id'),
            $this->indexById($languages, 'id'),
            $this->indexById($images, 'id'),
            $this->groupIds($images, 'product_id', 'id'),
        );
    }

    private function fetchByIds($table, $column, array $ids)
    {
        if (empty($ids)) {
            return [];
        }

        return $this->fetchAllByIds(
            sprintf(
                'SELECT * FROM %s WHERE %s IN (%%s)',
                $table,
                $column
            ),
            $ids
        );
    }

    private function fetchAllByIds($queryTemplate, array $ids)
    {
        if (empty($ids)) {
            return [];
        }

        $ids = $this->uniqueIds($ids);

        $placeholders = implode(
            ',',
            array_fill(0, count($ids), '?')
        );

        $query = sprintf(
            $queryTemplate,
            $placeholders
        );

        $stmt = $this->db->prepare($query);

        if (!$stmt) {
            throw new RuntimeException(
                'Ошибка подготовки запроса Snapshot: ' .
                $this->db->error
            );
        }

        $types = str_repeat('i', count($ids));

        $stmt->bind_param(
            $types,
            ...$ids
        );

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();

            throw new RuntimeException(
                'Ошибка выполнения запроса Snapshot: ' .
                $error
            );
        }

        $result = $stmt->get_result();
        $rows = [];

        while ($row = $result->fetch_object()) {
            $rows[] = $row;
        }

        $result->free();
        $stmt->close();

        return $rows;
    }

    private function fetchAll($query)
    {
        $result = $this->db->query($query);

        if ($result === false) {
            throw new RuntimeException(
                'Ошибка получения данных Snapshot: ' .
                $this->db->error
            );
        }

        $rows = [];

        while ($row = $result->fetch_object()) {
            $rows[] = $row;
        }

        $result->free();

        return $rows;
    }

    private function getIds(array $rows, $field)
    {
        $ids = [];

        foreach ($rows as $row) {
            if (!isset($row->{$field})) {
                continue;
            }

            $id = (int) $row->{$field};

            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return $this->uniqueIds($ids);
    }

    private function uniqueIds(array $ids)
    {
        $result = [];

        foreach ($ids as $id) {
            $id = (int) $id;

            if ($id > 0) {
                $result[$id] = $id;
            }
        }

        return array_values($result);
    }

    private function indexById(array $rows, $field)
    {
        $indexed = [];

        foreach ($rows as $row) {
            $id = (int) $row->{$field};
            $indexed[$id] = $row;
        }

        return $indexed;
    }

    private function groupBy(array $rows, $field)
    {
        $grouped = [];

        foreach ($rows as $row) {
            $id = (int) $row->{$field};
            $grouped[$id][] = $row;
        }

        return $grouped;
    }

    private function groupIds(
        array $rows,
        $groupField,
        $valueField
    ) {
        $grouped = [];

        foreach ($rows as $row) {
            $groupId = (int) $row->{$groupField};
            $valueId = (int) $row->{$valueField};

            if ($groupId <= 0 || $valueId <= 0) {
                continue;
            }

            $grouped[$groupId][$valueId] = $valueId;
        }

        foreach ($grouped as $groupId => $values) {
            $grouped[$groupId] = array_values($values);
        }

        return $grouped;
    }


    private function filterCategories(
        array $categories,
        array $productCategoryLinks
    ) {
    // Индексируем категории по ID.
        $categoriesById = [];

        foreach ($categories as $category) {
            $categoriesById[(int) $category->id] = $category;
        }

    // Собираем ID категорий, в которых находятся товары Fly.
        $requiredCategoryIds = [];

        foreach ($productCategoryLinks as $links) {
            foreach ($links as $link) {
                $categoryId = (int) $link->category_id;

                if ($categoryId > 0) {
                    $requiredCategoryIds[$categoryId] = true;
                }
            }
        }

    // Добавляем всех родителей необходимых категорий.
        foreach (array_keys($requiredCategoryIds) as $categoryId) {
            $currentCategoryId = $categoryId;

            while (isset($categoriesById[$currentCategoryId])) {
                $parentId = (int) $categoriesById[$currentCategoryId]->parent_id;

                if ($parentId <= 0 || isset($requiredCategoryIds[$parentId])) {
                    break;
                }

                $requiredCategoryIds[$parentId] = true;
                $currentCategoryId = $parentId;
            }
        }

    // Оставляем только необходимые категории.
        $filteredCategories = [];

        foreach ($requiredCategoryIds as $categoryId => $value) {
            if (isset($categoriesById[$categoryId])) {
                $filteredCategories[$categoryId] = $categoriesById[$categoryId];
            }
        }

        return $filteredCategories;
    }
}
