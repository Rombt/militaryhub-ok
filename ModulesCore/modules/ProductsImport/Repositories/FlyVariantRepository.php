<?php

namespace ProductsImport\Repositories;

use Database;
use RuntimeException;

class FlyVariantRepository
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Возвращает вариант Fly по ID.
     */
    public function findById($variantId)
    {
        $this->db->query(
            "SELECT id
             FROM __variants
             WHERE id = ?
             LIMIT 1",
            $variantId
        );

        $result = $this->db->result();

        if (!$result) {
            return null;
        }

        return (int) $result->id;
    }

    /**
     * Ищет существующий вариант по товару и external_id.
     *
     * Нужен для совместимости с вариантами,
     * созданными до появления VariantMapRepository.
     */
    public function findByExternalId($productId, $externalId)
    {
        $this->db->query(
            "SELECT id
             FROM __variants
             WHERE product_id = ?
               AND external_id = ?
             LIMIT 1",
            $productId,
            $externalId
        );

        $result = $this->db->result();

        if (!$result) {
            return null;
        }

        return (int) $result->id;
    }

    /**
     * Создаёт вариант.
     *
     * Возвращает ID созданного варианта.
     */
    public function create(array $data)
    {
        $this->db->query(
            "INSERT INTO __variants SET ?%",
            $data
        );

        if ($this->db->affected_rows() !== 1) {
            throw new RuntimeException(
                'Не удалось создать вариант на fly.'
            );
        }

        $variantId = $this->db->insert_id();

        if (!$variantId) {
            throw new RuntimeException(
                'Не удалось получить ID созданного варианта.'
            );
        }

        return (int) $variantId;
    }

    /**
     * Обновляет вариант.
     */
    public function update($variantId, array $data)
    {
        $this->db->query(
            "UPDATE __variants
             SET ?%
             WHERE id = ?",
            $data,
            $variantId
        );
    }

    /**
     * Создаёт или обновляет языковые данные варианта.
     *
     * UNIQUE KEY: (lang_id, variant_id)
     */
    public function upsertLanguage(
        $languageId,
        $variantId,
        $name,
        $units
    ) {
        $this->db->query(
            "INSERT INTO __lang_variants
                (lang_id, variant_id, name, units)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                units = VALUES(units)",
            $languageId,
            $variantId,
            $name,
            $units
        );
    }
}
