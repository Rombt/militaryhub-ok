<?php

namespace ModulesCore\modules\ProductsImport\Importers;

use ModulesCore\modules\ProductsImport\Snapshot\Snapshot;
use ModulesCore\modules\ProductsImport\Repositories\FeatureFlyRepository;
use ModulesCore\modules\ProductsImport\Repositories\FeatureMapRepository;

class FeatureImporter
{
    private $featureFlyRepository;
    private $featureMapRepository;

    public function __construct(
        FeatureFlyRepository $featureFlyRepository,
        FeatureMapRepository $featureMapRepository
    ) {
        $this->featureFlyRepository = $featureFlyRepository;
        $this->featureMapRepository = $featureMapRepository;
    }

    /**
     * Синхронизирует все свойства из Snapshot.
     *
     * Возвращает:
     *
     * [
     *     sportflyFeatureId => flyFeatureId
     * ]
     */
    public function import(
        Snapshot $snapshot,
        array $languageMap
    ) {
        $featureMap = [];

        foreach ($snapshot->getFeatures() as $sportflyFeature) {
            $sportflyFeatureId = (int) $sportflyFeature->id;

            $flyFeatureId =
                $this->featureMapRepository
                ->findFlyFeatureId(
                    $sportflyFeatureId
                );

            if ($flyFeatureId) {
                $exists =
                    $this->featureFlyRepository
                    ->findById($flyFeatureId);

                if (!$exists) {
                    $this->featureMapRepository
                        ->delete($sportflyFeatureId);

                    $flyFeatureId = null;
                }
            }

            if (!$flyFeatureId
                && !empty($sportflyFeature->external_id)
            ) {
                $flyFeatureId =
                    $this->featureFlyRepository
                    ->findByExternalId(
                        $sportflyFeature->external_id
                    );
            }

            $data = [
                'name'               => $sportflyFeature->name,
                'position'           => $sportflyFeature->position,
                'in_filter'          => $sportflyFeature->in_filter,
                'auto_name_id'       => $sportflyFeature->auto_name_id,
                'auto_value_id'      => $sportflyFeature->auto_value_id,
                'url'                => $sportflyFeature->url,
                'external_id'        => $sportflyFeature->external_id,
                'url_in_product'     => $sportflyFeature->url_in_product,
                'to_index_new_value' => $sportflyFeature->to_index_new_value,
            ];

            if ($flyFeatureId) {
                $this->featureFlyRepository->update(
                    $flyFeatureId,
                    $data
                );
            } else {
                $flyFeatureId =
                    $this->featureFlyRepository->create($data);
            }

            $this->featureMapRepository->save(
                $sportflyFeatureId,
                $flyFeatureId
            );

            $featureMap[$sportflyFeatureId] = $flyFeatureId;

            $this->importLanguages(
                $snapshot,
                $sportflyFeatureId,
                $flyFeatureId,
                $languageMap
            );
        }

        return $featureMap;
    }

    private function importLanguages(
        Snapshot $snapshot,
        $sportflyFeatureId,
        $flyFeatureId,
        array $languageMap
    ) {
        $languages =
            $snapshot->getFeatureLanguages(
                $sportflyFeatureId
            );

        foreach ($languages as $language) {
            $sportflyLanguageId = (int) $language->lang_id;

            if (!isset($languageMap[$sportflyLanguageId])) {
                continue;
            }

            $flyLanguageId =
                $languageMap[$sportflyLanguageId];

            $this->featureFlyRepository
                ->upsertLanguage(
                    $flyLanguageId,
                    $flyFeatureId,
                    $language->name
                );
        }
    }
}
