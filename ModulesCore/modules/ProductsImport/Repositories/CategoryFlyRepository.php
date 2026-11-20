<?php

namespace ModulesCore\modules\ProductsImport\Repositories;

use Database;
use RuntimeException;

class CategoryFlyRepository
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function findById($categoryId)
    {
        $this->db->query(
            "SELECT id
             FROM __categories
             WHERE id = ?
             LIMIT 1",
            $categoryId
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
            "INSERT INTO __categories SET ?%",
            $data
        );

        if ($this->db->affected_rows() !== 1) {
            throw new RuntimeException(
                'Не удалось создать категорию на fly.'
            );
        }

        $categoryId = $this->db->insert_id();

        if (!$categoryId) {
            throw new RuntimeException(
                'Не удалось получить ID созданной категории.'
            );
        }

        return (int) $categoryId;
    }

    public function update($categoryId, array $data)
    {
        $this->db->query(
            "UPDATE __categories
             SET ?%
             WHERE id = ?",
            $data,
            $categoryId
        );
    }

    public function attachFeature($categoryId, $featureId)
    {
        $this->db->query(
            "INSERT IGNORE INTO __categories_features
                (category_id, feature_id)
             VALUES (?, ?)",
            $categoryId,
            $featureId
        );
    }

    public function detachFeatures($categoryId)
    {
        $this->db->query(
            "DELETE FROM __categories_features
             WHERE category_id = ?",
            $categoryId
        );
    }

    public function findLanguage(
        $languageId,
        $categoryId
    ) {
        $this->db->query(
            "SELECT category_id
             FROM __lang_categories
             WHERE lang_id = ?
               AND category_id = ?
             LIMIT 1",
            $languageId,
            $categoryId
        );

        return $this->db->result();
    }

    public function createLanguage(array $data)
    {
        $this->db->query(
            "INSERT INTO __lang_categories SET ?%",
            $data
        );
    }

    public function updateLanguage(
        $languageId,
        $categoryId,
        array $data
    ) {
        $this->db->query(
            "UPDATE __lang_categories
             SET ?%
             WHERE lang_id = ?
               AND category_id = ?",
            $data,
            $languageId,
            $categoryId
        );
    }
}
