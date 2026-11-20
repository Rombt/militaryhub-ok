<?php

namespace ProductsImport\Repositories;

use Database;
use RuntimeException;

class ImageFlyRepository
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Возвращает изображение Fly по ID.
     */
    public function findById(int $imageId): ?int
    {
        $this->db->query(
            "SELECT id
             FROM db_images
             WHERE id = ?
             LIMIT 1",
            $imageId
        );

        $result = $this->db->result();

        if (!$result) {
            return null;
        }

        return (int) $result->id;
    }

    /**
     * Возвращает все изображения товара Fly.
     *
     * @return array
     */
    public function findByProductId(int $productId): array
    {
        $this->db->query(
            "SELECT id, name, product_id, variant_id, filename, position
             FROM db_images
             WHERE product_id = ?
             ORDER BY position, id",
            $productId
        );

        $results = $this->db->results();

        if (!$results) {
            return [];
        }

        return $results;
    }

    /**
     * Создаёт изображение на Fly.
     *
     * Возвращает ID созданного изображения.
     */
    public function create(array $data): int
    {
        $this->db->query(
            "INSERT INTO db_images SET ?%",
            $data
        );

        if ($this->db->affected_rows() !== 1) {
            throw new RuntimeException(
                'Не удалось создать изображение на fly.'
            );
        }

        $imageId = $this->db->insert_id();

        if (!$imageId) {
            throw new RuntimeException(
                'Не удалось получить ID созданного изображения.'
            );
        }

        return (int) $imageId;
    }

    /**
     * Обновляет изображение на Fly.
     */
    public function update(int $imageId, array $data): void
    {
        $this->db->query(
            "UPDATE db_images
             SET ?%
             WHERE id = ?",
            $data,
            $imageId
        );

        if ($this->db->affected_rows() === false) {
            throw new RuntimeException(
                'Не удалось обновить изображение на fly: ' .
                $imageId
            );
        }
    }

    /**
     * Удаляет изображение с Fly.
     */
    public function delete(int $imageId): void
    {
        $this->db->query(
            "DELETE FROM db_images
             WHERE id = ?",
            $imageId
        );

        if ($this->db->affected_rows() === false) {
            throw new RuntimeException(
                'Не удалось удалить изображение на fly: ' .
                $imageId
            );
        }
    }

    public function findAllForTransfer(): array
    {
        $this->db->query(
            "SELECT id, filename
            FROM db_images
            WHERE filename != ''
            ORDER BY id"
        );

        $results = $this->db->results();

        return $results ?: [];
    }
}
