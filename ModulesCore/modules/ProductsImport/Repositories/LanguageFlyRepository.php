<?php

namespace ProductsImport\Repositories;

use Database;
use RuntimeException;

class LanguageFlyRepository
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function findByLabel($label)
    {
        $this->db->query(
            "SELECT id
             FROM db_languages
             WHERE label = ?
             LIMIT 1",
            $label
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
            "INSERT INTO db_languages SET ?%",
            $data
        );

        if ($this->db->affected_rows() !== 1) {
            throw new RuntimeException(
                'Не удалось создать язык на fly.'
            );
        }

        $languageId = $this->db->insert_id();

        if (!$languageId) {
            throw new RuntimeException(
                'Не удалось получить ID созданного языка.'
            );
        }

        return (int) $languageId;
    }

    public function update($languageId, array $data)
    {
        $this->db->query(
            "UPDATE db_languages
             SET ?%
             WHERE id = ?",
            $data,
            $languageId
        );
    }
}
