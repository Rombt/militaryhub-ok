<?php

namespace ProductsImport\Repositories;

use Database;
use RuntimeException;

class ProductFlyRepository
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Возвращает товар Fly по его ID.
     */
    public function findById($productId)
    {
        $this->db->query(
            "SELECT id
             FROM db_products
             WHERE id = ?
             LIMIT 1",
            $productId
        );

        $result = $this->db->result();

        if (!$result) {
            return null;
        }

        return (int) $result->id;
    }

    /**
     * Возвращает ID бренда Fly.
     */
    public function getFlyBrandId()
    {
        static $brandId = null;

        if ($brandId !== null) {
            return $brandId;
        }

        $this->db->query(
            "SELECT id
             FROM db_brands
             WHERE name = ?
             LIMIT 1",
            'Fly'
        );

        $result = $this->db->result();

        if (!$result) {
            throw new RuntimeException(
                'Бренд Fly не найден на fly.'
            );
        }

        $brandId = (int) $result->id;

        return $brandId;
    }

    /**
     * Создаёт товар на Fly.
     *
     * Возвращает ID созданного товара.
     */
    public function create(array $data)
    {
        $this->db->query(
            "INSERT INTO db_products SET ?% ",
            $data
        );

        if ($this->db->affected_rows() !== 1) {
            throw new RuntimeException(
                'Не удалось создать товар на fly.'
            );
        }

        $productId = $this->db->insert_id();

        if (!$productId) {
            throw new RuntimeException(
                'Не удалось получить ID созданного товара.'
            );
        }

        return $productId;
    }

    /**
     * Обновляет товар на Fly.
     */
    public function update($productId, array $data)
    {
        $this->db->query(
            "UPDATE db_products SET ?% WHERE id=?",
            $data,
            $productId
        );

        if ($this->db->affected_rows() === false) {
            throw new RuntimeException(
                'Не удалось обновить товар на fly: ' .
                $productId
            );
        }
    }

    /**
     * Создаёт или обновляет языковые данные товара.
     */
    public function upsertLanguage(
        $languageId,
        $productId,
        $language
    ) {
        $this->db->query(
            "SELECT product_id
             FROM db_lang_products
             WHERE lang_id = ?
               AND product_id = ?
             LIMIT 1",
            $languageId,
            $productId
        );

        $exists = $this->db->result();

        $data = [
            'lang_id'          => $languageId,
            'product_id'       => $productId,
            'name'             => $language['name'],
            'annotation'       => $language['annotation'],
            'description'      => $language['description'],
            'meta_title'       => $language['meta_title'],
            'meta_keywords'    => $language['meta_keywords'],
            'meta_description' => $language['meta_description'],
            'special'          => $language['special'],
        ];

        if ($exists) {

            $this->db->query(
                "UPDATE db_lang_products
                 SET ?%
                 WHERE lang_id = ?
                   AND product_id = ?",
                $data,
                $languageId,
                $productId
            );

        } else {

            $this->db->query(
                "INSERT INTO db_lang_products SET ?%",
                $data
            );
        }
    }

    /**
     * Привязывает товар к категории.
     */
    public function attachCategory(
        int $productId,
        int $categoryId,
        int $position = 0
    ): void {
        $this->db->query(
            "INSERT INTO db_products_categories
            (product_id, category_id, position)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE
            position = VALUES(position)",
            $productId,
            $categoryId,
            $position
        );
    }

    public function deleteProductCategories(int $productId): void
    {
        $this->db->query(
            "DELETE FROM db_products_categories
         WHERE product_id = ?",
            $productId
        );
    }

    /**
     * Удаляет все характеристики товара.
     */
    public function deleteProductFeatureValues(int $productId): void
    {
        $this->db->query(
            "DELETE FROM db_products_features_values
            WHERE product_id = ?",
            $productId
        );
    }

    /**
     * Привязывает значение характеристики к товару.
     */
    public function attachFeatureValue(
        int $productId,
        int $valueId
    ): void {
        $this->db->query(
            "INSERT IGNORE INTO db_products_features_values
                (product_id, value_id)
            VALUES (?, ?)",
            $productId,
            $valueId
        );
    }



}
