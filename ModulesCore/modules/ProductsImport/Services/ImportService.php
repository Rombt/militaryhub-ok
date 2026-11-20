<?php

namespace ModulesCore\modules\ProductsImport\Services;

use ModulesCore\modules\ProductsImport\Snapshot\Snapshot;
use ModulesCore\modules\ProductsImport\Importers\LanguageImporter;
use ModulesCore\modules\ProductsImport\Importers\BrandImporter;
use ModulesCore\modules\ProductsImport\Importers\CategoryImporter;
use ModulesCore\modules\ProductsImport\Importers\FeatureImporter;
use ModulesCore\modules\ProductsImport\Importers\FeatureValueImporter;
use ModulesCore\modules\ProductsImport\Importers\ProductImporter;
use ModulesCore\modules\ProductsImport\Importers\ProductLanguageImporter;
use ModulesCore\modules\ProductsImport\Importers\VariantImporter;
use ModulesCore\modules\ProductsImport\Importers\VariantLanguageImporter;
use ModulesCore\modules\ProductsImport\Importers\ImageImporter;

class ImportService
{
    private $brandImporter;
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
        BrandImporter $brandImporter,
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
        $this->brandImporter = $brandImporter;
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
        $brandMap = $this->brandImporter->import($snapshot);

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
            'brandMap'        => $brandMap,
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
        array $featureValueMap,
        array $brandMap
    ) {
        $products = $snapshot->getProducts();

        $results = [
            'total'   => count($products),
            'created' => 0,
            'updated' => 0,
            'errors'  => 0,
            'warnings' => 0,
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
                    $featureValueMap,
                    $brandMap
                );

                $flyProductId = $productResult['fly_product_id'];
                $warnings = $productResult['warnings'] ?? [];



                /*
                 * 2. Языковые данные товара.
                 */
                $this->optionalStage('product_languages', function () use ($snapshot, $sourceProductId, $flyProductId, $languageMap) {
                    $this->productLanguageImporter->import($snapshot, $sourceProductId, $flyProductId, $languageMap);
                }, null, $warnings);

                /*
                 * 3. Варианты товара.
                 */
                $variantMap = $this->optionalStage('variants', function () use ($snapshot, $sourceProductId, $flyProductId) {
                    return $this->variantImporter->import($snapshot, $sourceProductId, $flyProductId);
                }, [], $warnings);

                /*
                 * 4. Языковые данные вариантов.
                 */
                $this->optionalStage('variant_languages', function () use ($snapshot, $sourceProductId, $flyProductId, $languageMap, $variantMap) {
                    $this->variantLanguageImporter->import(
                    $snapshot,
                    $variantMap,
                    $languageMap
                );
                }, null, $warnings);

                /*
                * 5. Изображения товара.
                */
                $imageMap = $this->optionalStage('images', function () use ($snapshot, $sourceProductId, $flyProductId, $variantMap) {
                    return $this->imageImporter->import($snapshot, $sourceProductId, $flyProductId, $variantMap);
                }, [], $warnings);
                $sourceMainImageId = (int) ($sourceProduct->main_image_id ?? 0);
                if ($sourceMainImageId > 0 && isset($imageMap[$sourceMainImageId])) {
                    $this->optionalStage('main_image_id', function () use ($flyProductId, $imageMap, $sourceMainImageId) {
                        $this->productImporter->setMainImageId($flyProductId, (int)$imageMap[$sourceMainImageId]);
                    }, null, $warnings);
                }
                if ($productResult['status'] === 'created') {
                    $results['created']++;
                } elseif ($productResult['status'] === 'updated') {
                    $results['updated']++;
                }

                if ($warnings) $results['warnings']++;
                $results['items'][] = [
                    'sportfly_product_id' => $sourceProductId,
                    'fly_product_id'      => $flyProductId,
                    'product_name'        => $sourceProduct->name,
                    'product_created'     => $sourceProduct->created,
                    'product_last_modify' => $sourceProduct->last_modify,
                    'status'              => $warnings ? 'warning' : 'success',
                    'imported'            => true,
                    'warnings'            => $warnings,
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
    /**
     * Optional stages may fail independently, but SQL/storage failures must
     * remain fatal. Never pretend the product was saved if its core write fails.
     */
    private function optionalStage(string $stage, callable $callback, $fallback, array &$warnings)
    {
        try {
            return $callback();
        } catch (\mysqli_sql_exception | \PDOException $e) {
            throw $e;
        } catch (\Throwable $e) {
            // A SQL error wrapped by the module must not be swallowed.
            if (preg_match('/SQL|database|mysqli|constraint|duplicate entry|deadlock|transaction/i', $e->getMessage())) {
                throw $e;
            }
            $warnings[] = ['field' => $stage, 'reason' => $e->getMessage()];
            return $fallback;
        }
    }
}
