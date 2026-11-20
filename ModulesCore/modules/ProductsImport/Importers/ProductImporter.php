<?php

namespace ProductsImport\Importers;

use ProductsImport\Repositories\ProductFlyRepository;
use ProductsImport\Repositories\ProductMapRepository;
use ProductsImport\Snapshot\Snapshot;

class ProductImporter
{
    private ProductFlyRepository $flyRepository;
    private ProductMapRepository $mapRepository;

    public function __construct(
        ProductFlyRepository $flyRepository,
        ProductMapRepository $mapRepository
    ) {
        $this->flyRepository = $flyRepository;
        $this->mapRepository = $mapRepository;
    }

    public function import(
        \stdClass $sourceProduct,
        Snapshot $snapshot,
        array $categoryMap,
        array $featureValueMap,
        array $brandMap
    ): array {
        $sourceProductId = (int) $sourceProduct->id;

        $flyProductId = $this->mapRepository->findFlyProductId($sourceProductId);

        if ($flyProductId !== null) {
            $this->flyRepository->update(
                $flyProductId,
                $this->prepareUpdateData($sourceProduct, $categoryMap, $brandMap, $snapshot)
            );

            $status = 'updated';
        } else {
            $flyProductId = $this->flyRepository->create(
                $this->prepareCreateData($sourceProduct, $categoryMap, $brandMap, $snapshot)
            );

            $this->mapRepository->save(
                $sourceProductId,
                $flyProductId
            );

            $status = 'created';
        }

        $this->importCategories(
            $snapshot,
            $sourceProductId,
            $flyProductId,
            $categoryMap
        );
        
        $this->importFeatureValues(
            $snapshot,
            $sourceProductId,
            $flyProductId,
            $featureValueMap
        );

        return [
            'fly_product_id' => $flyProductId,
            'status' => $status,
        ];
    }


    public function setMainImageId(int $productId, int $imageId): void
    {
        $this->flyRepository->update($productId, ['main_image_id' => $imageId]);
    }

    private function prepareCreateData(
        \stdClass $product,
        array $categoryMap,
        array $brandMap,
        Snapshot $snapshot
    ): array {
        $sourceMainCategoryId = (int) $product->main_category_id;

        $mainCategoryId = $categoryMap[$sourceMainCategoryId] ?? null;
        if (!$mainCategoryId) {
            foreach ($snapshot->getProductCategoryLinks((int) $product->id) as $link) {
                if (isset($categoryMap[(int) $link->category_id])) {
                    $mainCategoryId = $categoryMap[(int) $link->category_id];
                    break;
                }
            }
        }
        $sourceBrandId = (int) $product->brand_id;
        if ($sourceBrandId > 0 && !isset($brandMap[$sourceBrandId])) {
            throw new \RuntimeException('Не сопоставлен бренд ' . $sourceBrandId . ' товара ' . $product->id);
        }
        $targetBrandId = $sourceBrandId ? $brandMap[$sourceBrandId] : 0;

        return [
        'url' => $product->url,
        'brand_id' => $targetBrandId,
        'name' => $product->name,
        'annotation' => $product->annotation,
        'description' => $product->description,
        'visible' => $product->visible,
        'position' => $product->position,
        'meta_title' => $product->meta_title,
        'meta_keywords' => $product->meta_keywords,
        'meta_description' => $product->meta_description,
        'created' => $product->created,
        'featured' => $product->featured,
        'external_id' => $product->external_id,
        'rating' => $product->rating,
        'votes' => $product->votes,
        'special' => $product->special,
        'main_category_id' => $mainCategoryId,
        'main_order' => 0,
        ];
    }

    private function prepareUpdateData(
        \stdClass $product,
        array $categoryMap,
        array $brandMap,
        Snapshot $snapshot
    ): array {
        $sourceMainCategoryId = (int) $product->main_category_id;

        $mainCategoryId = $categoryMap[$sourceMainCategoryId] ?? null;
        if (!$mainCategoryId) {
            foreach ($snapshot->getProductCategoryLinks((int) $product->id) as $link) {
                if (isset($categoryMap[(int) $link->category_id])) {
                    $mainCategoryId = $categoryMap[(int) $link->category_id];
                    break;
                }
            }
        }
        $sourceBrandId = (int) $product->brand_id;
        if ($sourceBrandId > 0 && !isset($brandMap[$sourceBrandId])) {
            throw new \RuntimeException('Не сопоставлен бренд ' . $sourceBrandId . ' товара ' . $product->id);
        }
        $targetBrandId = $sourceBrandId ? $brandMap[$sourceBrandId] : 0;

        return [
        'url' => $product->url,
        'brand_id' => $targetBrandId,
        'name' => $product->name,
        'annotation' => $product->annotation,
        'description' => $product->description,
        'visible' => $product->visible,
        'position' => $product->position,
        'meta_title' => $product->meta_title,
        'meta_keywords' => $product->meta_keywords,
        'meta_description' => $product->meta_description,
        'created' => $product->created,
        'featured' => $product->featured,
        'external_id' => $product->external_id,
        'rating' => $product->rating,
        'votes' => $product->votes,
        'special' => $product->special,
        'main_category_id' => $mainCategoryId,
        ];
    }

    private function importCategories( Snapshot $snapshot, int $sourceProductId, int $flyProductId, array $categoryMap ): void
    {
        // Сначала полностью очищаем старые связи товара с категориями.
        $this->flyRepository->deleteProductCategories($flyProductId);

        $categoryLinks = $snapshot->getProductCategoryLinks($sourceProductId);

        foreach ($categoryLinks as $categoryLink) {
            $sourceCategoryId = (int) $categoryLink->category_id;

            if (!isset($categoryMap[$sourceCategoryId])) {
                continue;
            }

            $flyCategoryId = (int) $categoryMap[$sourceCategoryId];

            $position = isset($categoryLink->position)
            ? (int) $categoryLink->position
            : 0;

            $this->flyRepository->attachCategory(
                $flyProductId,
                $flyCategoryId,
                $position
            );
        }
    }

    private function importFeatureValues(
        Snapshot $snapshot,
        int $sourceProductId,
        int $flyProductId,
        array $featureValueMap
    ): void {
        // Полностью удаляем старые связи товара с характеристиками.
        $this->flyRepository->deleteProductFeatureValues($flyProductId);

        $sourceValueIds = $snapshot->getProductFeatureValueIds(
            $sourceProductId
        );

        foreach ($sourceValueIds as $sourceValueId) {
            $sourceValueId = (int) $sourceValueId;

            if (!isset($featureValueMap[$sourceValueId])) {
                continue;
            }

            $flyValueId = (int) $featureValueMap[$sourceValueId];

            $this->flyRepository->attachFeatureValue(
                $flyProductId,
                $flyValueId
            );
        }
    }


}
