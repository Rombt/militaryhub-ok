<?php

namespace ModulesCore\modules\ProductsImport\Repositories;

use Database;
use RuntimeException;

class FeatureValueFlyRepository
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function findById($valueId)
    {
        $this->db->query(
            "SELECT id
             FROM __features_values
             WHERE id = ?
             LIMIT 1",
            $valueId
        );

        $result = $this->db->result();

        if (!$result) {
            return null;
        }

        return (int) $result->id;
    }

    public function findByFeatureAndValue(
        $featureId,
        $value
    ) {
        $this->db->query(
            "SELECT id
             FROM __features_values
             WHERE feature_id = ?
               AND value = ?
             LIMIT 1",
            $featureId,
            $value
        );

        $result = $this->db->result();

        if (!$result) {
            return null;
        }

        return (int) $result->id;
    }

    public function create(array $data)
    {
        $this->db->query(
            "INSERT INTO __features_values SET ?%",
            $data
        );

        if ($this->db->affected_rows() !== 1) {
            throw new RuntimeException(
                'Не удалось создать значение свойства на fly.'
            );
        }

        $valueId = $this->db->insert_id();

        if (!$valueId) {
            throw new RuntimeException(
                'Не удалось получить ID созданного значения свойства.'
            );
        }

        return (int) $valueId;
    }

    public function update($valueId, array $data)
    {
        $this->db->query(
            "UPDATE __features_values
             SET ?%
             WHERE id = ?",
            $data,
            $valueId
        );
    }

    public function findLanguage(
        $languageId,
        $valueId
    ) {
        $this->db->query(
            "SELECT feature_value_id
             FROM __lang_features_values
             WHERE lang_id = ?
               AND feature_value_id = ?
             LIMIT 1",
            $languageId,
            $valueId
        );

        return $this->db->result();
    }

    public function createLanguage(array $data)
    {
        $this->db->query(
            "INSERT INTO __lang_features_values SET ?%",
            $data
        );
    }

    public function updateLanguage(
        $languageId,
        $valueId,
        array $data
    ) {
        $this->db->query(
            "UPDATE __lang_features_values
             SET ?%
             WHERE lang_id = ?
               AND feature_value_id = ?",
            $data,
            $languageId,
            $valueId
        );
    }
}
