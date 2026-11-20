<?php

namespace ProductsImport\Importers;

use ProductsImport\Repositories\FlyVariantRepository;
use ProductsImport\Snapshot\Snapshot;

class VariantLanguageImporter
{
    private FlyVariantRepository $flyRepository;

    public function __construct(FlyVariantRepository $flyRepository)
    {
        $this->flyRepository = $flyRepository;
    }

    public function import(
        Snapshot $snapshot,
        array $variantMap,
        array $languageMap
    ): void {
        if (!$variantMap) {
            return;
        }

        foreach ($variantMap as $sourceVariantId => $flyVariantId) {
            $languages = $snapshot->getVariantLanguages($sourceVariantId);

            foreach ($languages as $language) {
                $sourceLanguageId = (int) $language->lang_id;

                $flyLanguageId = $languageMap[$sourceLanguageId] ?? null;

                if ($flyLanguageId === null) {
                    continue;
                }

                $this->flyRepository->upsertLanguage(
                    $flyLanguageId,
                    $flyVariantId,
                    $language->name,
                    $language->units
                );
            }
        }
    }
}
