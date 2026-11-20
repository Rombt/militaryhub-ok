<?php

namespace ProductsImport\Importers;

use ProductsImport\Snapshot\Snapshot;
use ProductsImport\Repositories\FeatureValueFlyRepository;
use ProductsImport\Repositories\FeatureValueMapRepository;

class FeatureValueImporter
{
    private $featureValueFlyRepository;
    private $featureValueMapRepository;

    public function __construct(
        FeatureValueFlyRepository $featureValueFlyRepository,
        FeatureValueMapRepository $featureValueMapRepository
    ) {
        $this->featureValueFlyRepository =
            $featureValueFlyRepository;

        $this->featureValueMapRepository =
            $featureValueMapRepository;
    }

    /**
     * Синхронизирует все значения свойств.
     *
     * Возвращает:
     *
     * [
     *     sportflyValueId => flyValueId
     * ]
     */
    public function import(
        Snapshot $snapshot,
        array $featureMap,
        array $languageMap
    ) {
        $valueMap = [];

        foreach ($snapshot->getFeaturesValues() as $sportflyValue) {
            $sportflyValueId = (int) $sportflyValue->id;
            $sportflyFeatureId = (int) $sportflyValue->feature_id;

            if (!isset($featureMap[$sportflyFeatureId])) {
                continue;
            }

            $flyFeatureId =
                $featureMap[$sportflyFeatureId];

            $flyValueId =
                $this->featureValueMapRepository
                ->findFlyFeatureValueId(
                    $sportflyValueId
                );

            if ($flyValueId) {
                $exists =
                    $this->featureValueFlyRepository
                    ->findById($flyValueId);

                if (!$exists) {
                    $this->featureValueMapRepository
                        ->delete($sportflyValueId);

                    $flyValueId = null;
                }
            }

            if (!$flyValueId) {
                $flyValueId =
                    $this->featureValueFlyRepository
                    ->findByFeatureAndValue(
                        $flyFeatureId,
                        $sportflyValue->value
                    );
            }

            $data = [
                'feature_id' => $flyFeatureId,
                'value'      => $sportflyValue->value,
                'translit'   => $sportflyValue->translit,
                'position'   => $sportflyValue->position,
                'to_index'   => $sportflyValue->to_index,
            ];

            if ($flyValueId) {
                $this->featureValueFlyRepository->update(
                    $flyValueId,
                    $data
                );
            } else {
                $flyValueId =
                    $this->featureValueFlyRepository
                    ->create($data);
            }

            $this->featureValueMapRepository->save(
                $sportflyValueId,
                $flyValueId
            );

            $valueMap[$sportflyValueId] = $flyValueId;

            $this->importLanguages(
                $snapshot,
                $sportflyValueId,
                $flyValueId,
                $flyFeatureId,
                $languageMap
            );
        }

        return $valueMap;
    }

    private function importLanguages(
        Snapshot $snapshot,
        $sportflyValueId,
        $flyValueId,
        $flyFeatureId,
        array $languageMap
    ) {
        $languages =
        $snapshot->getFeatureValueLanguages(
            $sportflyValueId
        );

        foreach ($languages as $language) {
            $sportflyLanguageId =
            (int) $language->lang_id;

            if (!isset($languageMap[$sportflyLanguageId])) {
                continue;
            }

            $flyLanguageId =
            $languageMap[$sportflyLanguageId];

            $data = [
            'lang_id'          => $flyLanguageId,
            'feature_id'       => $flyFeatureId,
            'feature_value_id' => $flyValueId,
            'value'            => $language->value,
            ];

            $existing = $this->featureValueFlyRepository
                ->findLanguage(
                    $flyLanguageId,
                    $flyValueId
                );

            if ($existing) {
                $this->featureValueFlyRepository->updateLanguage(
                    $flyLanguageId,
                    $flyValueId,
                    [
                    'feature_id' => $flyFeatureId,
                    'value'      => $language->value,
                    ]
                );
            } else {
                $this->featureValueFlyRepository
                    ->createLanguage($data);
            }
        }
    }
}
