<?php

namespace ModulesCore\modules\ProductsImport\Repositories;

use Database;
use RuntimeException;

class ProductMapRepository
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Возвращает fly product_id по sportfly product_id.
     */
    public function findFlyProductId($sportflyProductId)
    {
        $this->db->query(
            "SELECT fly_product_id
             FROM __rmbt_product_import_map
             WHERE sportfly_product_id = ?",
            $sportflyProductId
        );

        $result = $this->db->result();

        if (!$result) {
            return null;
        }

        return (int) $result->fly_product_id;
    }

    /**
     * Сохраняет соответствие товаров.
     */
    public function save($sportflyProductId, $flyProductId)
    {
        $this->db->query(
            "INSERT INTO __rmbt_product_import_map
                (sportfly_product_id, fly_product_id)
             VALUES (?, ?)",
            $sportflyProductId,
            $flyProductId
        );

        if ($this->db->affected_rows() !== 1) {
            throw new RuntimeException(
                'Не удалось сохранить соответствие товаров: ' .
                $sportflyProductId . ' -> ' . $flyProductId
            );
        }
    }

    /**
     * Удаляет соответствие товара.
     */
    public function delete($sportflyProductId)
    {
        $this->db->query(
            "DELETE FROM __rmbt_product_import_map
             WHERE sportfly_product_id = ?",
            $sportflyProductId
        );

        if ($this->db->affected_rows() === false) {
            throw new RuntimeException(
                'Не удалось удалить соответствие товара: ' .
                $sportflyProductId
            );
        }
    }
}
