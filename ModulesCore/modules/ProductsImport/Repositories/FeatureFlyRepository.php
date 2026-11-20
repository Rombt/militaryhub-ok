<?php

namespace ProductsImport\Repositories;

use Database;
use RuntimeException;

class FeatureFlyRepository
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function findById($featureId)
    {
        $this->db->query(
            "SELECT id
             FROM db_features
             WHERE id = ?
             LIMIT 1",
            $featureId
        );

        $result = $this->db->result();

        if (!$result) {
            return null;
        }

        return (int) $result->id;
    }

    public function findByExternalId($externalId)
    {
        $this->db->query(
            "SELECT id
             FROM db_features
             WHERE external_id = ?
             LIMIT 1",
            $externalId
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
            "INSERT INTO db_features SET ?%",
            $data
        );

        if ($this->db->affected_rows() !== 1) {
            throw new RuntimeException(
                'Не удалось создать свойство на fly.'
            );
        }

        $featureId = $this->db->insert_id();

        if (!$featureId) {
            throw new RuntimeException(
                'Не удалось получить ID созданного свойства.'
            );
        }

        return (int) $featureId;
    }

    public function update($featureId, array $data)
    {
        $this->db->query(
            "UPDATE db_features
             SET ?%
             WHERE id = ?",
            $data,
            $featureId
        );
    }

    public function upsertLanguage(
        $languageId,
        $featureId,
        $name
    ) {
        $this->db->query(
            "INSERT INTO db_lang_features
                (lang_id, feature_id, name)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE
                name = VALUES(name)",
            $languageId,
            $featureId,
            $name
        );
    }
}
