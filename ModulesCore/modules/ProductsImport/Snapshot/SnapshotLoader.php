<?php

namespace ModulesCore\modules\ProductsImport\Snapshot;

use mysqli;
use RuntimeException;

/**
 * Загружает необходимые данные Sportfly и формирует Snapshot.
 */
class SnapshotLoader
{
    private $db;
    private $sourcePrefix;

    private $categoryId;
    private $includeSubcategories;
    private $productLimit;
    private $productOffset;
    private $newestFirst;
    private $selectionStats = [];

    public function __construct(mysqli $db, array $config = [])
    {
        $this->db = $db;
        $this->sourcePrefix = $config['source_prefix'] ?? null;
        if (!is_string($this->sourcePrefix) || !preg_match('/^[A-Za-z0-9_]*$/D', $this->sourcePrefix)) {
            throw new RuntimeException('Некорректный source_prefix в настройках SnapshotLoader.');
        }
        $this->categoryId = (int) ($config['source_category_id'] ?? 250);
        $this->includeSubcategories = (bool) ($config['include_subcategories'] ?? true);
        foreach (['product_limit', 'product_offset'] as $key) {
            $value = $config[$key] ?? 0;
            if (!is_int($value) || $value < 0) {
                throw new RuntimeException('Настройка ' . $key . ' должна быть целым неотрицательным числом.');
            }
        }
        if (isset($config['newest_first']) && !is_bool($config['newest_first'])) {
            throw new RuntimeException('Настройка newest_first должна быть true или false.');
        }
        $this->productLimit = $config['product_limit'] ?? 0;
        $this->productOffset = $config['product_offset'] ?? 0;
        $this->newestFirst = $config['newest_first'] ?? true;
    }

    public function load()
    {
        // Category tree is the only source of products; never filter by brand.
        $allCategories = $this->fetchAll('SELECT * FROM __categories ORDER BY id');
        $treeIds = $this->categoryTreeIds($allCategories);
        // Отбор выполняется в SQL ДО загрузки вариантов, изображений и переводов.
        // DISTINCT исключает повторения товаров из нескольких категорий.
        $totalRows = $this->fetchAllByIds(
            'SELECT COUNT(DISTINCT pc.product_id) AS total
             FROM __products_categories pc
             INNER JOIN __products p ON p.id = pc.product_id
             WHERE pc.category_id IN (%s)',
            $treeIds
        );
        $available = (int) ($totalRows[0]->total ?? 0);
        $direction = $this->newestFirst ? 'DESC' : 'ASC';
        $query = 'SELECT DISTINCT p.* FROM __products p
             INNER JOIN __products_categories pc ON pc.product_id = p.id
             WHERE pc.category_id IN (%s)
             ORDER BY p.created ' . $direction . ', p.id ' . $direction;
        if ($this->productLimit > 0) {
            $query .= ' LIMIT ' . $this->productLimit . ' OFFSET ' . $this->productOffset;
        } elseif ($this->productOffset > 0) {
            $query .= ' LIMIT 18446744073709551615 OFFSET ' . $this->productOffset;
        }
        $products = $this->fetchAllByIds($query, $treeIds);
        $this->selectionStats = [
            'product_limit' => $this->productLimit,
            'product_offset' => $this->productOffset,
            'newest_first' => $this->newestFirst,
            'limited' => $this->productLimit > 0 || $this->productOffset > 0,
            'available' => $available,
            'selected' => count($products),
        ];
        $brands = $this->fetchByIds(
            '__brands', 'id', $this->getIds($products, 'brand_id')
        );

        $productIds = $this->getIds($products, 'id');

        // 2. Языки товаров
        $productLanguages = $this->fetchByIds(
            '__lang_products',
            'product_id',
            $productIds
        );

        // 3. Товар → категории
        $productCategoryLinks = $this->fetchAllByIds(
            'SELECT product_id, category_id, position
             FROM __products_categories
             WHERE product_id IN (%s)
             ORDER BY product_id, position',
            $productIds
        );

        $productCategoryLinks = array_values(array_filter(
            $productCategoryLinks,
            function ($link) use ($treeIds) {
                return in_array((int) $link->category_id, $treeIds, true);
            }
        ));

        // 4. Товар → значения характеристик.
        // feature_id получаем сразу через JOIN.
        $productFeatureLinks = $this->fetchAllByIds(
            'SELECT pfv.product_id, pfv.value_id, fv.feature_id
             FROM __products_features_values pfv
             INNER JOIN __features_values fv ON fv.id = pfv.value_id
             WHERE pfv.product_id IN (%s)
             ORDER BY pfv.product_id, pfv.value_id',
            $productIds
        );

        // Only the source category subtree, not unrelated ancestors or branches.
        $categories = array_values(array_filter(
            $allCategories,
            function ($category) use ($treeIds) {
                return in_array((int) $category->id, $treeIds, true);
            }
        ));

        $categoryIds = $this->getIds($categories, 'id');

        // 6. Языки категорий
        $categoryLanguages = $this->fetchByIds(
            '__lang_categories',
            'category_id',
            $categoryIds
        );

        // 7. Категория → характеристики
        $categoryFeatureLinks = $this->fetchAllByIds(
            'SELECT category_id, feature_id
             FROM __categories_features
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
            '__features',
            'id',
            $featureIds
        );

        // 9. Языки характеристик
        $featureLanguages = $this->fetchByIds(
            '__lang_features',
            'feature_id',
            $featureIds
        );

        // 10. Значения характеристик
        $valueIds = $this->getIds($productFeatureLinks, 'value_id');

        $featureValues = $this->fetchByIds(
            '__features_values',
            'id',
            $valueIds
        );

        // 11. Языки значений
        $featureValueLanguages = $this->fetchByIds(
            '__lang_features_values',
            'feature_value_id',
            $valueIds
        );

        // 12. Варианты
        $variants = $this->fetchAllByIds(
            'SELECT *
             FROM __variants
             WHERE product_id IN (%s)
             ORDER BY product_id, id',
            $productIds
        );

        $variantIds = $this->getIds($variants, 'id');

        // 13. Изображения товаров
        $images = $this->fetchAllByIds(
            'SELECT *
            FROM __images
            WHERE product_id IN (%s)
            ORDER BY product_id, position, id',
            $productIds
        );

        $imageIds = $this->getIds($images, 'id');

        // 14. Языки вариантов
        $variantLanguages = $this->fetchByIds(
            '__lang_variants',
            'variant_id',
            $variantIds
        );

        // 15. Языки Sportfly
        $languages = $this->fetchAll(
            'SELECT * FROM __languages ORDER BY id'
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
            $this->indexById($brands, 'id'),
        );
    }

    public function getSelectionStats(): array
    {
        return $this->selectionStats;
    }

    private function categoryTreeIds(array $categories)
    {
        $children = [];
        $exists = false;
        foreach ($categories as $category) {
            $id = (int) $category->id;
            if ($id === $this->categoryId) $exists = true;
            $children[(int) $category->parent_id][] = $id;
        }
        if (!$exists) {
            throw new RuntimeException('Исходная категория не найдена: ' . $this->categoryId);
        }
        $seen = [];
        $queue = [$this->categoryId];
        while ($queue) {
            $id = array_shift($queue);
            if (isset($seen[$id])) continue;
            $seen[$id] = true;
            if ($this->includeSubcategories) {
                foreach ($children[$id] ?? [] as $childId) $queue[] = $childId;
            }
        }
        return array_map('intval', array_keys($seen));
    }

    private function resolveSourceTables(string $sql): string
    {
        return preg_replace_callback('/(?<![A-Za-z0-9_])__([A-Za-z][A-Za-z0-9_]*)/', function ($m) {
            return $this->sourcePrefix . $m[1];
        }, $sql);
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

        $stmt = $this->db->prepare($this->resolveSourceTables($query));

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
        $result = $this->db->query($this->resolveSourceTables($query));

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
