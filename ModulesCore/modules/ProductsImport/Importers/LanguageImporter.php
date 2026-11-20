<?php

namespace ModulesCore\modules\ProductsImport\Importers;

use ModulesCore\modules\ProductsImport\Snapshot\Snapshot;
use ModulesCore\modules\ProductsImport\Repositories\LanguageFlyRepository;

class LanguageImporter
{
    private $languageFlyRepository;

    public function __construct(
        LanguageFlyRepository $languageFlyRepository
    ) {
        $this->languageFlyRepository = $languageFlyRepository;
    }

    /**
     * Синхронизирует языки из Snapshot с Fly.
     *
     * Возвращает соответствие:
     *
     * [
     *     sportflyLanguageId => flyLanguageId
     * ]
     */
    public function import(Snapshot $snapshot)
    {
        $languageMap = [];

        foreach ($snapshot->getLanguages() as $sportflyLanguage) {
            $sportflyLanguageId = (int) $sportflyLanguage->id;

            $flyLanguageId =
                $this->languageFlyRepository->findByLabel(
                    $sportflyLanguage->label
                );

            if (!$flyLanguageId) {
                $flyLanguageId =
                    $this->languageFlyRepository->create(
                        [
                        'label' => $sportflyLanguage->label,
                        'name'  => $sportflyLanguage->name,
                        ]
                    );
            } else {
                $this->languageFlyRepository->update(
                    $flyLanguageId,
                    [
                        'label' => $sportflyLanguage->label,
                        'name'  => $sportflyLanguage->name,
                    ]
                );
            }

            $languageMap[$sportflyLanguageId] = $flyLanguageId;
        }

        return $languageMap;
    }
}
