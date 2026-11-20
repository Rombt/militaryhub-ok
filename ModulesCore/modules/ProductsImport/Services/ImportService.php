<?php

namespace ProductsImport\Services;

use ProductsImport\Snapshot\Snapshot;
use ProductsImport\Importers\LanguageImporter;
use ProductsImport\Importers\CategoryImporter;
use ProductsImport\Importers\FeatureImporter;
use ProductsImport\Importers\FeatureValueImporter;
use ProductsImport\Importers\ProductImporter;
use ProductsImport\Importers\ProductLanguageImporter;
use ProductsImport\Importers\VariantImporter;
use ProductsImport\Importers\VariantLanguageImporter;
use ProductsImport\Importers\ImageImporter;

class ImportService
{
    private $languageImporter;
    private $categoryImporter;
    private $featureImporter;
    private $featureValueImporter;

    private ProductImporter $productImporter;
    private ProductLanguageImporter $productLanguageImporter;
    private VariantImporter $variantImporter;
    private VariantLanguageImporter $variantLanguageImporter;
    private ImageImporter $imageImporter;

    public function __construct(
        LanguageImporter $languageImporter,
        CategoryImporter $categoryImporter,
        FeatureImporter $featureImporter,
        FeatureValueImporter $featureValueImporter,
        ProductImporter $productImporter,
        ProductLanguageImporter $productLanguageImporter,
        VariantImporter $variantImporter,
        VariantLanguageImporter $variantLanguageImporter,
        ImageImporter $imageImporter,
    ) {
        $this->languageImporter = $languageImporter;
        $this->categoryImporter = $categoryImporter;
        $this->featureImporter = $featureImporter;
        $this->featureValueImporter = $featureValueImporter;

        $this->productImporter = $productImporter;
        $this->productLanguageImporter = $productLanguageImporter;
        $this->variantImporter = $variantImporter;
        $this->variantLanguageImporter = $variantLanguageImporter;
        $this->imageImporter = $imageImporter;
    }

    /**
     * Импортирует зависимости из готового Snapshot в Fly.
     *
     * @return array
     */
    public function importDependencies(Snapshot $snapshot)
    {
        /*
         * 1. Языки.
         *
         * sportfly language_id => fly language_id
         */
        $languageMap = $this->languageImporter->import($snapshot);

        /*
         * 2. Свойства.
         *
         * sportfly feature_id => fly feature_id
         */
        $featureMap = $this->featureImporter->import(
            $snapshot,
            $languageMap
        );

        /*
         * 3. Значения свойств.
         *
         * sportfly feature_value_id => fly feature_value_id
         */
        $featureValueMap = $this->featureValueImporter->import(
            $snapshot,
            $featureMap,
            $languageMap
        );

        /*
         * 4. Категории.
         */
        $categoryMap = $this->categoryImporter->import(
            $snapshot,
            $languageMap
        );

        return [
            'languageMap'     => $languageMap,
            'featureMap'      => $featureMap,
            'featureValueMap' => $featureValueMap,
            'categoryMap'     => $categoryMap,
        ];
    }

    /**
     * Импортирует товары и их варианты из готового Snapshot в Fly.
     *
     * Запросов к Sportfly здесь нет.
     *
     * @return array
     */
    public function importProducts(
        Snapshot $snapshot,
        array $languageMap,
        array $categoryMap,
        array $featureValueMap
    ) {
        $products = $snapshot->getProducts();

        $results = [
            'total'   => count($products),
            'created' => 0,
            'updated' => 0,
            'errors'  => 0,
            'items'   => [],
        ];

        foreach ($products as $sourceProduct) {
            $sourceProductId = (int) $sourceProduct->id;

            try {
                /*
                 * 1. Товар.
                 */

                // $flyProductId = $this->productImporter->import($sourceProduct, $snapshot, $categoryMap);


                $productResult = $this->productImporter->import(
                    $sourceProduct,
                    $snapshot,
                    $categoryMap,
                    $featureValueMap
                );

                $flyProductId = $productResult['fly_product_id'];

                if ($productResult['status'] === 'created') {
                    $results['created']++;
                } elseif ($productResult['status'] === 'updated') {
                    $results['updated']++;
                }


                /*
                 * 2. Языковые данные товара.
                 */
                $this->productLanguageImporter->import($snapshot, $sourceProductId, $flyProductId, $languageMap);

                /*
                 * 3. Варианты товара.
                 */
                $variantMap = $this->variantImporter->import($snapshot, $sourceProductId, $flyProductId);

                /*
                 * 4. Языковые данные вариантов.
                 */
                $this->variantLanguageImporter->import(
                    $snapshot,
                    $variantMap,
                    $languageMap
                );

                /*
                * 5. Изображения товара.
                */
                $this->imageImporter->import(
                    $snapshot,
                    $sourceProductId,
                    $flyProductId,
                    $variantMap
                );

                $results['items'][] = [
                    'sportfly_product_id' => $sourceProductId,
                    'fly_product_id'      => $flyProductId,
                    'product_name'        => $sourceProduct->name,
                    'product_created'     => $sourceProduct->created,
                    'product_last_modify' => $sourceProduct->last_modify,
                    'status'              => 'success',
                ];
            } catch (\Throwable $e) {
                $results['errors']++;

                $results['items'][] = [
                    'sportfly_product_id' => $sourceProductId,
                    'product_name'        => $sourceProduct->name,
                    'product_created'     => $sourceProduct->created,
                    'product_last_modify' => $sourceProduct->last_modify,
                    'status'              => 'error',
                    'message'             => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
