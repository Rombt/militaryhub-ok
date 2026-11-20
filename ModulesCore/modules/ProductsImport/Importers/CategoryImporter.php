<?php

namespace ProductsImport\Importers;

use ProductsImport\Snapshot\Snapshot;
use ProductsImport\Repositories\CategoryFlyRepository;
use ProductsImport\Repositories\CategoryMapRepository;
use ProductsImport\Repositories\FeatureMapRepository;

class CategoryImporter
{
    private $categoryFlyRepository;
    private $categoryMapRepository;
    private $featureMapRepository;

    public function __construct(
        CategoryFlyRepository $categoryFlyRepository,
        CategoryMapRepository $categoryMapRepository,
        FeatureMapRepository $featureMapRepository
    ) {
        $this->categoryFlyRepository = $categoryFlyRepository;
        $this->categoryMapRepository = $categoryMapRepository;
        $this->featureMapRepository = $featureMapRepository;
    }

    /**
     * Синхронизирует категории и восстанавливает иерархию в два прохода:
     * 1. Создаёт или обновляет все категории.
     * 2. Устанавливает связи с родительскими категориями.
     *
     * Возвращает карту соответствий:
     *
     * [
     *     sportflyCategoryId => flyCategoryId
     * ]
     */
    public function import(
        Snapshot $snapshot,
        array $languageMap
    ) {
        $categoryMap = [];

        $categories = $snapshot->getCategories();

        /**
         * Первый проход.
         *
         * Создаём или обновляем все категории.
         * Пока не устанавливаем родительские связи.
         */
        foreach ($categories as $sportflyCategory) {
            $sportflyCategoryId = (int) $sportflyCategory->id;

            $flyCategoryId =
            $this->categoryMapRepository
            ->findFlyCategoryId($sportflyCategoryId);

            $data = $this->prepareCategoryData(
                $sportflyCategory,
                0
            );

            if ($flyCategoryId) {
                $exists =
                    $this->categoryFlyRepository
                    ->findById($flyCategoryId);

                if (!$exists) {
                    $this->categoryMapRepository
                        ->delete($sportflyCategoryId);

                    $flyCategoryId = null;
                }
            }

            if ($flyCategoryId) {
                $this->categoryFlyRepository->update(
                    $flyCategoryId,
                    $data
                );
            } else {
                $flyCategoryId =
                $this->categoryFlyRepository->create($data);

                $this->categoryMapRepository->save(
                    $sportflyCategoryId,
                    $flyCategoryId
                );
            }

            $categoryMap[$sportflyCategoryId] = $flyCategoryId;

            $this->importLanguages(
                $snapshot,
                $sportflyCategoryId,
                $flyCategoryId,
                $languageMap
            );

            $this->importFeatures(
                $snapshot,
                $sportflyCategoryId,
                $flyCategoryId
            );
        }

        /**
         * Второй проход.
         *
         * Восстанавливаем иерархию категорий.
         */
        foreach ($categories as $sportflyCategory) {
            $sportflyCategoryId = (int) $sportflyCategory->id;

            $parentSportflyCategoryId =
            (int) $sportflyCategory->parent_id;

            $parentFlyCategoryId = 0;

            if (
                $parentSportflyCategoryId > 0 &&
                isset($categoryMap[$parentSportflyCategoryId])
            ) {
                $parentFlyCategoryId =
                $categoryMap[$parentSportflyCategoryId];
            }

            $flyCategoryId = $categoryMap[$sportflyCategoryId];

            $this->categoryFlyRepository->update(
                $flyCategoryId,
                [
                'parent_id' => $parentFlyCategoryId
                ]
            );
        }

        return $categoryMap;
    }

    private function prepareCategoryData(
        $category,
        $parentFlyCategoryId
    ) {
        return [
            'parent_id'          => $parentFlyCategoryId,
            'name'               => $category->name,
            'name_h1'            => $category->name_h1,
            'meta_title'         => $category->meta_title,
            'meta_keywords'      => $category->meta_keywords,
            'meta_description'   => $category->meta_description,
            'annotation'         => $category->annotation,
            'description'        => $category->description,
            'url'                => $category->url,
            'image'              => $category->image,
            'position'           => $category->position,
            'visible'            => $category->visible,
            'featured'           => $category->featured,
            'external_id'        => $category->external_id,
            'level_depth'        => $category->level_depth,
            'auto_meta_title'    => $category->auto_meta_title,
            'auto_meta_keywords' => $category->auto_meta_keywords,
            'auto_meta_desc'     => $category->auto_meta_desc,
            'auto_description'   => $category->auto_description,
            'created'            => $category->created,
            'attach_brand_id'    => $category->attach_brand_id,
            'parent_menu_name'   => $category->parent_menu_name,
        ];
    }

    private function importLanguages(
        Snapshot $snapshot,
        $sportflyCategoryId,
        $flyCategoryId,
        array $languageMap
    ) {
        $languages =
            $snapshot->getCategoryLanguages(
                $sportflyCategoryId
            );

        foreach ($languages as $language) {
            $sportflyLanguageId = (int) $language->lang_id;

            if (!isset($languageMap[$sportflyLanguageId])) {
                continue;
            }

            $flyLanguageId =
                $languageMap[$sportflyLanguageId];

            $data = [
                'lang_id'    => $flyLanguageId,
                'category_id' => $flyCategoryId,
                'name'       => $language->name,
                'name_h1'    => $language->name_h1,
                'meta_title' => $language->meta_title,
                'meta_keywords' => $language->meta_keywords,
                'meta_description' => $language->meta_description,
                'annotation' => $language->annotation,
                'description' => $language->description,
            ];

            $existing =
                $this->categoryFlyRepository
                ->findLanguage(
                    $flyLanguageId,
                    $flyCategoryId
                );

            if ($existing) {
                $this->categoryFlyRepository->updateLanguage(
                    $flyLanguageId,
                    $flyCategoryId,
                    $data
                );
            } else {
                $this->categoryFlyRepository->createLanguage(
                    $data
                );
            }
        }
    }

    private function importFeatures(
        Snapshot $snapshot,
        $sportflyCategoryId,
        $flyCategoryId
    ) {
        $sportflyFeatureIds =
            $snapshot->getCategoryFeatureIds(
                $sportflyCategoryId
            );

        $this->categoryFlyRepository
            ->detachFeatures($flyCategoryId);

        foreach ($sportflyFeatureIds as $sportflyFeatureId) {
            $flyFeatureId =
                $this->featureMapRepository
                ->findFlyFeatureId(
                    $sportflyFeatureId
                );

            if (!$flyFeatureId) {
                continue;
            }

            $this->categoryFlyRepository
                ->attachFeature(
                    $flyCategoryId,
                    $flyFeatureId
                );
        }
    }
}
