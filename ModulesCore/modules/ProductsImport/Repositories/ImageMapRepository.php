<?php

namespace ModulesCore\modules\ProductsImport\Repositories;

use Database;
use RuntimeException;

class ImageMapRepository
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Возвращает fly image_id по sportfly image_id.
     */
    public function findFlyImageId(int $sportflyImageId): ?int
    {
        $this->db->query(
            "SELECT fly_image_id
             FROM __rmbt_image_import_map
             WHERE sportfly_image_id = ?
             LIMIT 1",
            $sportflyImageId
        );

        $result = $this->db->result();

        if (!$result) {
            return null;
        }

        return (int) $result->fly_image_id;
    }

    /**
     * Возвращает sportfly image_id по fly image_id.
     */
    public function findSportflyImageId(int $flyImageId): ?int
    {
        $this->db->query(
            "SELECT sportfly_image_id
             FROM __rmbt_image_import_map
             WHERE fly_image_id = ?
             LIMIT 1",
            $flyImageId
        );

        $result = $this->db->result();

        if (!$result) {
            return null;
        }

        return (int) $result->sportfly_image_id;
    }

    /**
     * Сохраняет соответствие изображений.
     */
    public function save(
        int $sportflyImageId,
        int $flyImageId
    ): void {
        $result = $this->db->query(
            "INSERT INTO __rmbt_image_import_map
                (sportfly_image_id, fly_image_id)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE
                fly_image_id = VALUES(fly_image_id)",
            $sportflyImageId,
            $flyImageId
        );

        if ($result === false) {
            throw new RuntimeException(
                'Не удалось сохранить соответствие изображений: ' .
                $sportflyImageId . ' -> ' . $flyImageId
            );
        }
    }

    /**
     * Удаляет соответствие по Sportfly image_id.
     */
    public function delete(int $sportflyImageId): void
    {
        $this->db->query(
            "DELETE FROM __rmbt_image_import_map
             WHERE sportfly_image_id = ?",
            $sportflyImageId
        );

        if ($this->db->affected_rows() === false) {
            throw new RuntimeException(
                'Не удалось удалить соответствие изображения: ' .
                $sportflyImageId
            );
        }
    }

    /**
     * Удаляет соответствие по Fly image_id.
     */
    public function deleteByFlyImageId(int $flyImageId): void
    {
        $this->db->query(
            "DELETE FROM __rmbt_image_import_map
             WHERE fly_image_id = ?",
            $flyImageId
        );

        if ($this->db->affected_rows() === false) {
            throw new RuntimeException(
                'Не удалось удалить соответствие изображения Fly: ' .
                $flyImageId
            );
        }
    }
}
