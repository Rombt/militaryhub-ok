<?php

namespace ModulesCore\modules\ProductsImport\Importers;

use ModulesCore\modules\ProductsImport\Repositories\ProductFlyRepository;
use ModulesCore\modules\ProductsImport\Repositories\ProductMapRepository;
use ModulesCore\modules\ProductsImport\Snapshot\Snapshot;

class ProductImporter
{
    private ProductFlyRepository $flyRepository;
    private ProductMapRepository $mapRepository;
    private array $warnings = [];

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
        $this->warnings = [];
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
            'warnings' => $this->warnings,
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
            $this->warnings[] = [
                'field' => 'brand_id',
                'source_value' => $sourceBrandId,
                'reason' => 'Не сопоставлен бренд; товар сохранён без назначения нового бренда',
            ];
        }
        $targetBrandId = $sourceBrandId > 0 ? ($brandMap[$sourceBrandId] ?? 0) : 0;

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
            $this->warnings[] = [
                'field' => 'brand_id',
                'source_value' => $sourceBrandId,
                'reason' => 'Не сопоставлен бренд; товар сохранён без назначения нового бренда',
            ];
        }
        $targetBrandId = $sourceBrandId > 0 ? ($brandMap[$sourceBrandId] ?? 0) : 0;

        $data = [
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
        if ($sourceBrandId > 0 && !isset($brandMap[$sourceBrandId])) {
            // Do not erase an already correct brand on a temporary mapping failure.
            unset($data['brand_id']);
        }
        return $data;
    }

    private function importCategories( Snapshot $snapshot, int $sourceProductId, int $flyProductId, array $categoryMap ): void
    {
        $categoryLinks = $snapshot->getProductCategoryLinks($sourceProductId);
        foreach ($categoryLinks as $link) {
            if (!isset($categoryMap[(int)$link->category_id])) {
                $this->warnings[] = ['field' => 'category_id', 'source_value' => (int)$link->category_id,
                    'reason' => 'Категория не сопоставлена; существующие связи сохранены'];
                return;
            }
        }
        $this->flyRepository->deleteProductCategories($flyProductId);

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
        $sourceValueIds = $snapshot->getProductFeatureValueIds(
            $sourceProductId
        );
        foreach ($sourceValueIds as $valueId) {
            if (!isset($featureValueMap[(int)$valueId])) {
                $this->warnings[] = ['field' => 'feature_value_id', 'source_value' => (int)$valueId,
                    'reason' => 'Значение характеристики не сопоставлено; существующие связи сохранены'];
                return;
            }
        }
        $this->flyRepository->deleteProductFeatureValues($flyProductId);

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
