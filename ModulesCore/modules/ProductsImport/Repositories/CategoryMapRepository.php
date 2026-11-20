<?php

namespace ModulesCore\modules\ProductsImport\Repositories;

use Database;
use RuntimeException;

class CategoryMapRepository
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Возвращает fly category_id по sportfly category_id.
     */
    public function findFlyCategoryId($sportflyCategoryId)
    {
        $this->db->query(
            "SELECT fly_category_id
             FROM __rmbt_category_import_map
             WHERE sportfly_category_id = ?",
            $sportflyCategoryId
        );

        $result = $this->db->result();

        if (!$result) {
            return null;
        }

        return (int) $result->fly_category_id;
    }

    /**
     * Сохраняет соответствие категорий.
     */
    public function save($sportflyCategoryId, $flyCategoryId)
    {
        $this->db->query(
            "INSERT INTO __rmbt_category_import_map
                (sportfly_category_id, fly_category_id)
             VALUES (?, ?)",
            $sportflyCategoryId,
            $flyCategoryId
        );

        if ($this->db->affected_rows() !== 1) {
            throw new RuntimeException(
                'Не удалось сохранить соответствие категорий: ' .
                $sportflyCategoryId . ' -> ' . $flyCategoryId
            );
        }
    }

    /**
     * Удаляет соответствие категории.
     */
    public function delete($sportflyCategoryId)
    {
        $this->db->query(
            "DELETE FROM __rmbt_category_import_map
             WHERE sportfly_category_id = ?",
            $sportflyCategoryId
        );

        if ($this->db->affected_rows() === false) {
            throw new RuntimeException(
                'Не удалось удалить соответствие категории: ' .
                $sportflyCategoryId
            );
        }
    }
}
