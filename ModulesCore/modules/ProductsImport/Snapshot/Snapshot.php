<?php

namespace ProductsImport\Snapshot;

/**
 * Полный снимок необходимых данных Sportfly.
 *
 * Snapshot не знает о БД и не содержит данных Fly.
 */
class Snapshot
{
    private $brandsById = [];
    private $productsById;
    private $productLanguagesByProductId;
    private $productCategoryLinksByProductId;
    private $categoriesById;
    private $categoryLanguagesByCategoryId;
    private $categoryFeatureIdsByCategoryId;
    private $featuresById;
    private $featureLanguagesByFeatureId;
    private $featureValuesById;
    private $featureValueLanguagesByValueId;
    private $productFeatureValueIdsByProductId;
    private $variantsById;
    private $variantIdsByProductId;
    private $variantLanguagesByVariantId;
    private $languagesById;
    private $imagesById;
    private $imageIdsByProductId;

    public function __construct(
        array $productsById,
        array $productLanguagesByProductId,
        array $productCategoryLinksByProductId,
        array $categoriesById,
        array $categoryLanguagesByCategoryId,
        array $categoryFeatureIdsByCategoryId,
        array $featuresById,
        array $featureLanguagesByFeatureId,
        array $featureValuesById,
        array $featureValueLanguagesByValueId,
        array $productFeatureValueIdsByProductId,
        array $variantsById,
        array $variantIdsByProductId,
        array $variantLanguagesByVariantId,
        array $languagesById,
        array $imagesById,
        array $imageIdsByProductId,
        array $brandsById = [],
    ) {
        $this->brandsById = $brandsById;
        $this->productsById = $productsById;
        $this->productLanguagesByProductId = $productLanguagesByProductId;
        $this->productCategoryLinksByProductId = $productCategoryLinksByProductId;
        $this->categoriesById = $categoriesById;
        $this->categoryLanguagesByCategoryId = $categoryLanguagesByCategoryId;
        $this->categoryFeatureIdsByCategoryId = $categoryFeatureIdsByCategoryId;
        $this->featuresById = $featuresById;
        $this->featureLanguagesByFeatureId = $featureLanguagesByFeatureId;
        $this->featureValuesById = $featureValuesById;
        $this->featureValueLanguagesByValueId = $featureValueLanguagesByValueId;
        $this->productFeatureValueIdsByProductId = $productFeatureValueIdsByProductId;
        $this->variantsById = $variantsById;
        $this->variantIdsByProductId = $variantIdsByProductId;
        $this->variantLanguagesByVariantId = $variantLanguagesByVariantId;
        $this->languagesById = $languagesById;
        $this->imagesById = $imagesById;
        $this->imageIdsByProductId = $imageIdsByProductId;
    }

    public function getBrands()
    {
        return $this->brandsById;
    }

    public function getImage($imageId)
    {
        return isset($this->imagesById[$imageId])
        ? $this->imagesById[$imageId]
        : null;
    }

    public function getImageIds($productId)
    {
        return isset($this->imageIdsByProductId[$productId])
        ? $this->imageIdsByProductId[$productId]
        : [];
    }

    public function getProducts()
    {
        return $this->productsById;
    }

    public function getProduct($productId)
    {
        return isset($this->productsById[$productId])
            ? $this->productsById[$productId]
            : null;
    }

    public function getProductLanguages($productId)
    {
        return isset($this->productLanguagesByProductId[$productId])
            ? $this->productLanguagesByProductId[$productId]
            : [];
    }

    public function getProductCategoryLinks($productId)
    {
        return isset($this->productCategoryLinksByProductId[$productId])
            ? $this->productCategoryLinksByProductId[$productId]
            : [];
    }

    public function getCategory($categoryId)
    {
        return isset($this->categoriesById[$categoryId])
            ? $this->categoriesById[$categoryId]
            : null;
    }

    public function getCategories()
    {
        return $this->categoriesById;
    }    

    public function getCategoryLanguages($categoryId)
    {
        return isset($this->categoryLanguagesByCategoryId[$categoryId])
            ? $this->categoryLanguagesByCategoryId[$categoryId]
            : [];
    }

    public function getCategoryFeatureIds($categoryId)
    {
        return isset($this->categoryFeatureIdsByCategoryId[$categoryId])
            ? $this->categoryFeatureIdsByCategoryId[$categoryId]
            : [];
    }

    public function getFeature($featureId)
    {
        return isset($this->featuresById[$featureId])
            ? $this->featuresById[$featureId]
            : null;
    }

    public function getFeatures()
    {
        return $this->featuresById;
    }

    public function getFeatureLanguages($featureId)
    {
        return isset($this->featureLanguagesByFeatureId[$featureId])
            ? $this->featureLanguagesByFeatureId[$featureId]
            : [];
    }

    public function getFeatureValue($valueId)
    {
        return isset($this->featureValuesById[$valueId])
            ? $this->featureValuesById[$valueId]
            : null;
    }

    public function getFeaturesValues()
    {
        return $this->featureValuesById;
    }    

    public function getFeatureValueLanguages($valueId)
    {
        return isset($this->featureValueLanguagesByValueId[$valueId])
            ? $this->featureValueLanguagesByValueId[$valueId]
            : [];
    }

    public function getProductFeatureValueIds($productId)
    {
        return isset($this->productFeatureValueIdsByProductId[$productId])
            ? $this->productFeatureValueIdsByProductId[$productId]
            : [];
    }

    public function getVariant($variantId)
    {
        return isset($this->variantsById[$variantId])
            ? $this->variantsById[$variantId]
            : null;
    }

    public function getVariantIds($productId)
    {
        return isset($this->variantIdsByProductId[$productId])
            ? $this->variantIdsByProductId[$productId]
            : [];
    }

    public function getVariantLanguages($variantId)
    {
        return isset($this->variantLanguagesByVariantId[$variantId])
            ? $this->variantLanguagesByVariantId[$variantId]
            : [];
    }

    public function getLanguage($languageId)
    {
        return isset($this->languagesById[$languageId])
            ? $this->languagesById[$languageId]
            : null;
    }

    public function getLanguages()
    {
        return $this->languagesById;
    }
}
