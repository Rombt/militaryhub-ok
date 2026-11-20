<?php

namespace ProductsImport\Repositories;

use Database;
use RuntimeException;

class FeatureValueMapRepository
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Возвращает fly value_id по sportfly value_id.
     */
    public function findFlyFeatureValueId($sportflyValueId)
    {
        $this->db->query(
            "SELECT fly_feature_value_id
             FROM __rmbt_feature_value_import_map
             WHERE sportfly_feature_value_id = ?
             LIMIT 1",
            $sportflyValueId
        );

        $result = $this->db->result();

        if (!$result) {
            return null;
        }

        return (int) $result->fly_feature_value_id;
    }

    /**
     * Сохраняет соответствие значения.
     */
    public function save($sportflyValueId, $flyValueId)
    {
        $this->db->query(
            "INSERT INTO __rmbt_feature_value_import_map
                (sportfly_feature_value_id, fly_feature_value_id)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE
                fly_feature_value_id = VALUES(fly_feature_value_id)",
            $sportflyValueId,
            $flyValueId
        );
    }

    /**
     * Удаляет соответствие значения.
     */
    public function delete($sportflyValueId)
    {
        $this->db->query(
            "DELETE FROM __rmbt_feature_value_import_map
             WHERE sportfly_feature_value_id = ?",
            $sportflyValueId
        );

        if ($this->db->affected_rows() === false) {
            throw new RuntimeException(
                'Не удалось удалить соответствие значения свойства: ' .
                $sportflyValueId
            );
        }
    }
}
