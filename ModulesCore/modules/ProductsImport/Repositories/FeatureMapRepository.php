<?php

namespace ModulesCore\modules\ProductsImport\Repositories;

use Database;
use RuntimeException;

class FeatureMapRepository
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Возвращает fly feature_id по sportfly feature_id.
     */
    public function findFlyFeatureId($sportflyFeatureId)
    {
        $this->db->query(
            "SELECT fly_feature_id
             FROM __rmbt_feature_import_map
             WHERE sportfly_feature_id = ?
             LIMIT 1",
            $sportflyFeatureId
        );

        $result = $this->db->result();

        if (!$result) {
            return null;
        }

        return (int) $result->fly_feature_id;
    }

    /**
     * Сохраняет соответствие свойства.
     */
    public function save($sportflyFeatureId, $flyFeatureId)
    {
        $this->db->query(
            "INSERT INTO __rmbt_feature_import_map
                (sportfly_feature_id, fly_feature_id)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE
                fly_feature_id = VALUES(fly_feature_id)",
            $sportflyFeatureId,
            $flyFeatureId
        );
    }

    /**
     * Удаляет соответствие свойства.
     */
    public function delete($sportflyFeatureId)
    {
        $this->db->query(
            "DELETE FROM __rmbt_feature_import_map
             WHERE sportfly_feature_id = ?",
            $sportflyFeatureId
        );

        if ($this->db->affected_rows() === false) {
            throw new RuntimeException(
                'Не удалось удалить соответствие свойства: ' .
                $sportflyFeatureId
            );
        }
    }
}
