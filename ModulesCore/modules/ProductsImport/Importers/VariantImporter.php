<?php

namespace ProductsImport\Importers;

use ProductsImport\Repositories\FlyVariantRepository;
use ProductsImport\Repositories\VariantMapRepository;
use ProductsImport\Snapshot\Snapshot;

class VariantImporter
{
    private FlyVariantRepository $flyRepository;
    private VariantMapRepository $mapRepository;

    public function __construct(
        FlyVariantRepository $flyRepository,
        VariantMapRepository $mapRepository
    ) {
        $this->flyRepository = $flyRepository;
        $this->mapRepository = $mapRepository;
    }

    public function import(
        Snapshot $snapshot,
        int $sourceProductId,
        int $flyProductId
    ): array {
        $variantIds = $snapshot->getVariantIds($sourceProductId);

        if (!$variantIds) {
            return [];
        }

        $variantMap = [];

        foreach ($variantIds as $sourceVariantId) {
            $variant = $snapshot->getVariant($sourceVariantId);

            if ($variant === null) {
                continue;
            }

            $sourceVariantId = (int) $variant->id;

            $flyVariantId = $this->mapRepository->findFlyVariantId(
                $sourceVariantId
            );

            if ($flyVariantId !== null) {
                $existingVariant = $this->flyRepository->findById($flyVariantId);

                if ($existingVariant === null) {
                    $this->mapRepository->delete($sourceVariantId);
                    $flyVariantId = null;
                }
            }

            if ($flyVariantId === null) {
                $flyVariantId = $this->flyRepository->findByExternalId(
                    $flyProductId,
                    $variant->external_id
                );
            }

            $data = $this->prepareData($variant, $flyProductId);

            if ($flyVariantId !== null) {
                $this->flyRepository->update(
                    $flyVariantId,
                    $data
                );
            } else {
                $flyVariantId = $this->flyRepository->create($data);
            }

            $this->mapRepository->save(
                $sourceVariantId,
                $flyVariantId
            );

            $variantMap[$sourceVariantId] = $flyVariantId;
        }

        return $variantMap;
    }

    private function prepareData(\stdClass $variant, int $flyProductId): array
    {
        return [
        'product_id' => $flyProductId,
        'sku' => $variant->sku,
        'name' => $variant->name,
        'weight' => $variant->weight,
        'price' => $variant->price,
        'compare_price' => $variant->compare_price,
        'stock' => $variant->stock,
        'position' => $variant->position,
        'attachment' => $variant->attachment,
        'external_id' => $variant->external_id,
        'currency_id' => $variant->currency_id,
        'feed' => $variant->feed,
        'units' => $variant->units,
        'image' => $variant->image,
        ];
    }
}
