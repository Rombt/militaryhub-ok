<?php

namespace ModulesCore\modules\ProductsImport\Repositories;

use Database;
use RuntimeException;

class VariantMapRepository
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Возвращает fly variant_id по sportfly variant_id.
     */
    public function findFlyVariantId($sportflyVariantId)
    {
        $this->db->query(
            "SELECT fly_variant_id
             FROM __rmbt_variant_import_map
             WHERE sportfly_variant_id = ?
             LIMIT 1",
            $sportflyVariantId
        );

        $result = $this->db->result();

        if (!$result) {
            return null;
        }

        return (int) $result->fly_variant_id;
    }

    /**
     * Сохраняет соответствие вариантов.
     */
    public function save($sportflyVariantId, $flyVariantId)
    {
        $result = $this->db->query(
            "INSERT INTO __rmbt_variant_import_map
                (sportfly_variant_id, fly_variant_id)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE
                fly_variant_id = VALUES(fly_variant_id)",
            $sportflyVariantId,
            $flyVariantId
        );

        if ($result === false) {
            throw new RuntimeException(
                'Не удалось сохранить соответствие вариантов: ' .
                $sportflyVariantId . ' -> ' . $flyVariantId
            );
        }
    }

    /**
     * Удаляет соответствие варианта.
     */
    public function delete($sportflyVariantId)
    {
        $this->db->query(
            "DELETE FROM __rmbt_variant_import_map
             WHERE sportfly_variant_id = ?",
            $sportflyVariantId
        );

        if ($this->db->affected_rows() === false) {
            throw new RuntimeException(
                'Не удалось удалить соответствие варианта: ' .
                $sportflyVariantId
            );
        }
    }
}
