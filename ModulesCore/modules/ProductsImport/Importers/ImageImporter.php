<?php

namespace ProductsImport\Importers;

use ProductsImport\Repositories\ImageFlyRepository;
use ProductsImport\Repositories\ImageMapRepository;
use ProductsImport\Snapshot\Snapshot;

class ImageImporter
{
    private ImageFlyRepository $flyRepository;
    private ImageMapRepository $mapRepository;

    public function __construct(
        ImageFlyRepository $flyRepository,
        ImageMapRepository $mapRepository
    ) {
        $this->flyRepository = $flyRepository;
        $this->mapRepository = $mapRepository;
    }

    /**
     * Синхронизирует изображения товара из Snapshot с Fly.
     *
     * Возвращает соответствие:
     *
     * [
     *     sportflyImageId => flyImageId
     * ]
     */
    public function import(
        Snapshot $snapshot,
        int $sourceProductId,
        int $flyProductId,
        array $variantMap
    ): array {
        $imageIds = $snapshot->getImageIds($sourceProductId);

        $imageMap = [];
        $sourceImageIds = [];

        foreach ($imageIds as $sourceImageId) {
            $image = $snapshot->getImage($sourceImageId);

            if ($image === null) {
                continue;
            }

            $sourceImageId = (int) $image->id;

            // Изображение действительно существует в Sportfly.
            $sourceImageIds[$sourceImageId] = true;

            $flyImageId = $this->mapRepository->findFlyImageId(
                $sourceImageId
            );

            if ($flyImageId !== null) {
                $existingImageId = $this->flyRepository->findById(
                    $flyImageId
                );

                if ($existingImageId === null) {
                    $this->mapRepository->delete($sourceImageId);
                    $flyImageId = null;
                }
            }

            $variantId = $this->resolveVariantId(
                $image,
                $variantMap
            );

            /*
             * Изображение относится к варианту, который не был
             * перенесён/не имеет соответствия.
             *
             * Не удаляем существующее изображение на Fly и
             * не создаём новое.
             */
            if (
                (int) $image->variant_id > 0
                && $variantId === null
            ) {
                continue;
            }

            $data = [
                'name'       => $image->name,
                'product_id' => $flyProductId,
                'variant_id' => $variantId ?? 0,
                'filename'   => $image->filename,
                'position'   => (int) $image->position,
            ];

            if ($flyImageId !== null) {
                $this->flyRepository->update(
                    $flyImageId,
                    $data
                );
            } else {
                $flyImageId = $this->flyRepository->create($data);
            }

            $this->mapRepository->save(
                $sourceImageId,
                $flyImageId
            );

            $imageMap[$sourceImageId] = $flyImageId;
        }

        $this->removeDeletedImages(
            $flyProductId,
            $sourceImageIds
        );

        return $imageMap;
    }

    private function resolveVariantId(
        \stdClass $image,
        array $variantMap
    ): ?int {
        $sourceVariantId = (int) $image->variant_id;

        if ($sourceVariantId === 0) {
            return 0;
        }

        return isset($variantMap[$sourceVariantId])
            ? (int) $variantMap[$sourceVariantId]
            : null;
    }

    /**
     * Удаляет с Fly изображения, которых больше нет
     * у товара в Sportfly.
     */
    private function removeDeletedImages(
        int $flyProductId,
        array $sourceImageIds
    ): void {
        $flyImages = $this->flyRepository->findByProductId(
            $flyProductId
        );

        foreach ($flyImages as $flyImage) {
            $flyImageId = (int) $flyImage->id;

            $sourceImageId =
                $this->mapRepository->findSportflyImageId(
                    $flyImageId
                );

            /*
             * У изображения есть связь с Sportfly,
             * но самого изображения больше нет в Sportfly.
             */
            if ($sourceImageId !== null && !isset($sourceImageIds[$sourceImageId])) {
                $this->flyRepository->delete($flyImageId);

                $this->mapRepository->deleteByFlyImageId(
                    $flyImageId
                );
            }
        }
    }
}
