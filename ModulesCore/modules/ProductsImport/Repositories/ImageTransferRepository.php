<?php

namespace ModulesCore\modules\ProductsImport\Repositories;

use Database;
use RuntimeException;

class ImageTransferRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Создаёт запись о новом запуске Product Import.
     *
     * @return int ID запуска Product Import
     */
    public function createProductImport(): int
    {
        $result = $this->db->query(
            "INSERT INTO __rmbt_image_transfer
                (product_import_status, image_transfer_status, started_at)
            VALUES (?, ?, NOW())",
            'running',
            'pending'
        );

        if ($result === false) {
            throw new RuntimeException(
                'Не удалось создать запись Product Import.'
            );
        }

        $id = $this->db->insert_id();

        if (!$id) {
            throw new RuntimeException(
                'Не удалось получить ID Product Import.'
            );
        }

        return (int) $id;
    }

    /**
     * Завершает Product Import.
     *
     * @param int    $productImportId
     * @param string $status
     */
    public function completeProductImport(
        int $productImportId,
        string $status
    ): void {
        $result = $this->db->query(
            "UPDATE __rmbt_image_transfer
             SET product_import_status = ?
             WHERE id = ?",
            $status,
            $productImportId
        );

        if ($result === false) {
            throw new RuntimeException(
                'Не удалось обновить статус Product Import: '
                . $productImportId
            );
        }
    }

    /**
     * Возвращает последний запуск Product Import.
     *
     * @return object|null
    */
    public function findLastProductImport(): ?object
    {
        $this->db->query(
            "SELECT *
            FROM __rmbt_image_transfer
            ORDER BY id DESC
            LIMIT 1"
        );

        $result = $this->db->result();

        return $result ?: null;
    }

    /**
     * Возвращает последний успешно завершённый Product Import.
     *
     * @return object|null
     */
    public function findLastSuccessfulProductImport(): ?object
    {
        $this->db->query(
            "SELECT *
             FROM __rmbt_image_transfer
             WHERE product_import_status = ?
             ORDER BY id DESC
             LIMIT 1",
            'success'
        );

        $result = $this->db->result();

        return $result ?: null;
    }

    /**
     * Обновляет статистику Image Transfer.
    */
    public function updateImageTransferStatistics(
        int $productImportId,
        int $total,
        int $transferred,
        int $skipped,
        int $errors
    ): void {
        $result = $this->db->query(
            "UPDATE __rmbt_image_transfer
            SET total = ?,
                transferred = ?,
                skipped = ?,
                errors = ?
            WHERE id = ?",
            $total,
            $transferred,
            $skipped,
            $errors,
            $productImportId
        );

        if ($result === false) {
            throw new RuntimeException(
                'Не удалось обновить статистику Image Transfer: '
                . $productImportId
            );
        }
    }

    /**
     * Завершает Image Transfer.
    */
    public function completeImageTransfer(
        int $productImportId,
        string $status
    ): void {
        $result = $this->db->query(
            "UPDATE __rmbt_image_transfer
            SET image_transfer_status = ?,
                finished_at = NOW()
            WHERE id = ?",
            $status,
            $productImportId
        );

        if ($result === false) {
            throw new RuntimeException(
                'Не удалось завершить Image Transfer: '
                . $productImportId
            );
        }
    }
}
