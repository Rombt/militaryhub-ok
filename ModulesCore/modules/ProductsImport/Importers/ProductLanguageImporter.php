<?php

namespace ModulesCore\modules\ProductsImport\Importers;

use ModulesCore\modules\ProductsImport\Repositories\ProductFlyRepository;
use ModulesCore\modules\ProductsImport\Snapshot\Snapshot;

class ProductLanguageImporter
{
    private ProductFlyRepository $flyRepository;

    public function __construct(ProductFlyRepository $flyRepository)
    {
        $this->flyRepository = $flyRepository;
    }

    public function import(
        Snapshot $snapshot,
        int $sourceProductId,
        int $flyProductId,
        array $languageMap
    ): void {
        $languages = $snapshot->getProductLanguages($sourceProductId);

        foreach ($languages as $language) {
            $sourceLanguageId = (int) $language->lang_id;

            $flyLanguageId = $languageMap[$sourceLanguageId] ?? null;

            if ($flyLanguageId === null) {
                continue;
            }

            $this->flyRepository->upsertLanguage(
                $flyLanguageId,
                $flyProductId,
                [
                    'name' => $language->name,
                    'annotation' => $language->annotation,
                    'description' => $language->description,
                    'meta_title' => $language->meta_title,
                    'meta_keywords' => $language->meta_keywords,
                    'meta_description' => $language->meta_description,
                    'special' => $language->special,
                ]
            );
        }
    }
}
