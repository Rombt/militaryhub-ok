<?php

namespace ProductsImport;

use Okay;
use ModulesCore\modules\ProductsImport\Snapshot\SnapshotLoader;
use ModulesCore\modules\ProductsImport\DatabaseManager;
use ModulesCore\modules\ProductsImport\Services\ImportService;
use ModulesCore\modules\ProductsImport\Services\ImportResetService;
use ModulesCore\modules\ProductsImport\Services\ImportRollbackService;
use ModulesCore\modules\ProductsImport\Importers\LanguageImporter;
use ModulesCore\modules\ProductsImport\Importers\BrandImporter;
use ModulesCore\modules\ProductsImport\Repositories\BrandFlyRepository;
use ModulesCore\modules\ProductsImport\Repositories\BrandMapRepository;
use ModulesCore\modules\ProductsImport\Importers\CategoryImporter;
use ModulesCore\modules\ProductsImport\Importers\FeatureImporter;
use ModulesCore\modules\ProductsImport\Importers\FeatureValueImporter;
use ModulesCore\modules\ProductsImport\Importers\ProductImporter;
use ModulesCore\modules\ProductsImport\Importers\ProductLanguageImporter;
use ModulesCore\modules\ProductsImport\Importers\VariantImporter;
use ModulesCore\modules\ProductsImport\Importers\VariantLanguageImporter;
use ModulesCore\modules\ProductsImport\Repositories\LanguageFlyRepository;
use ModulesCore\modules\ProductsImport\Repositories\CategoryFlyRepository;
use ModulesCore\modules\ProductsImport\Repositories\FeatureFlyRepository;
use ModulesCore\modules\ProductsImport\Repositories\FeatureValueFlyRepository;
use ModulesCore\modules\ProductsImport\Repositories\ProductFlyRepository;
use ModulesCore\modules\ProductsImport\Repositories\FlyVariantRepository;
use ModulesCore\modules\ProductsImport\Repositories\CategoryMapRepository;
use ModulesCore\modules\ProductsImport\Repositories\FeatureMapRepository;
use ModulesCore\modules\ProductsImport\Repositories\FeatureValueMapRepository;
use ModulesCore\modules\ProductsImport\Repositories\ProductMapRepository;
use ModulesCore\modules\ProductsImport\Repositories\VariantMapRepository;
use ModulesCore\modules\ProductsImport\Importers\ImageImporter;
use ModulesCore\modules\ProductsImport\Repositories\ImageFlyRepository;
use ModulesCore\modules\ProductsImport\Repositories\ImageMapRepository;
use ModulesCore\modules\ProductsImport\Repositories\ImageTransferRepository;
use ModulesCore\modules\ProductsImport\Services\ImageTransferService;



class ProductsImport extends Okay
{
    protected $errors = [];

    private $sourceDb;
    private $targetDb;
    private $databaseManager;
    private $importService;
    private $resetService;
    private $rollbackService;
    private $imageTransferRepository;
    private $importConfig;
    private $filesystemConfig;
    private $imageTransferService;
    private $imageFlyRepository;
    private $initializationFailed = false;

    public function __construct()
    {
        parent::__construct();

        try {
            // Конфигурация также должна находиться внутри try:
            // её отсутствие или некорректная структура не должны ронять админку.
            $config = include __DIR__ . '/../config.php';

            if (!is_array($config) || empty($config['filesystem'])) {
                throw new \RuntimeException(
                    'Файл config.php не содержит корректную конфигурацию filesystem.'
                );
            }

            $this->filesystemConfig = $config['filesystem'];
            $this->importConfig = $config['import'] ?? [];
            $this->importConfig['source_prefix'] = $config['tables']['source_prefix'] ?? null;

            // Подключения к базам данных.
            $this->databaseManager = new DatabaseManager($config);
            $this->sourceDb = $this->databaseManager->getSource();
            $this->targetDb = $this->databaseManager->getTarget();

            // Сервис переноса изображений.
            $this->imageTransferService = new ImageTransferService(
                $this->filesystemConfig['source_files_url'],
                $this->filesystemConfig['target_files_root'],
                $config['download'] ?? []
            );

            // Репозитории.
            $featureMapRepository = new FeatureMapRepository(
                $this->targetDb
            );

            $this->imageTransferRepository = new ImageTransferRepository(
                $this->targetDb
            );

            $this->imageFlyRepository = new ImageFlyRepository(
                $this->targetDb
            );

            // Сервис импорта.
            $this->importService = new ImportService(
                new LanguageImporter(
                    new LanguageFlyRepository($this->targetDb)
                ),
                new BrandImporter(
                    new BrandFlyRepository($this->targetDb),
                    new BrandMapRepository($this->targetDb),
                    $this->imageTransferService
                ),
                new CategoryImporter(
                    new CategoryFlyRepository($this->targetDb),
                    new CategoryMapRepository($this->targetDb),
                    $featureMapRepository
                ),
                new FeatureImporter(
                    new FeatureFlyRepository($this->targetDb),
                    $featureMapRepository
                ),
                new FeatureValueImporter(
                    new FeatureValueFlyRepository($this->targetDb),
                    new FeatureValueMapRepository($this->targetDb)
                ),
                new ProductImporter(
                    new ProductFlyRepository($this->targetDb),
                    new ProductMapRepository($this->targetDb)
                ),
                new ProductLanguageImporter(
                    new ProductFlyRepository($this->targetDb)
                ),
                new VariantImporter(
                    new FlyVariantRepository($this->targetDb),
                    new VariantMapRepository($this->targetDb)
                ),
                new VariantLanguageImporter(
                    new FlyVariantRepository($this->targetDb)
                ),
                new ImageImporter(
                    new ImageFlyRepository($this->targetDb),
                    new ImageMapRepository($this->targetDb)
                )
            );

            $this->rollbackService = new ImportRollbackService(
                $this->targetDb,
                $this->filesystemConfig['target_files_root'],
                (int)($this->importConfig['rollback_max_rows_per_table'] ?? 100000)
            );
            $this->rollbackService->ensureTables();

            // Сервис сброса данных импорта.
            $this->resetService = new ImportResetService($this->targetDb, !empty($this->importConfig['allow_full_reset']));
        } catch (\Throwable $e) {
            $this->initializationFailed = true;

            $this->errors[] =
                'Не удалось инициализировать модуль импорта. '
                . 'Проверьте настройки подключения к базам данных и конфигурацию. '
                . 'Подробности записаны в PHP error log.';

            error_log(
                '[ProductsImport] Initialization error: '
                . $e->getMessage()
                . ' in ' . $e->getFile()
                . ':' . $e->getLine()
            );
        }

        // fetch() вызывается в любом случае: при ошибке он покажет страницу
        // с сообщением, а не обратится к неинициализированным зависимостям.
        $this->fetch();
    }


    public function fetch()
    {
        // Если зависимости не созданы, запрещаем выполнять любые операции,
        // которым нужны подключения к БД и сервисы модуля.
        if ($this->initializationFailed) {
            $isAjaxAction =
                !empty($_POST['reset_import'])
                || !empty($_POST['sync_products'])
                || !empty($_POST['transfer_images'])
                || !empty($_POST['rollback_import']);

            if ($isAjaxAction) {
                header('Content-Type: application/json; charset=utf-8');

                echo json_encode(
                    [
                        'success' => false,
                        'success_message' => '',
                        'errors' => $this->errors,
                        'results' => null,
                    ],
                    JSON_UNESCAPED_UNICODE
                );

                exit;
            }

            // Обычный GET-запрос: показываем страницу модуля с ошибкой.
            $this->design->assign('product_import_status', null);
            $this->design->assign('title', 'Импорт товаров категории Мілітарі');
            $this->design->assign('errors', $this->errors);

            return $this->design->fetch('products_import.tpl');
        }

        if (!empty($_POST['rollback_import'])) {
            try {
                $result = $this->rollbackService->rollback();
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => empty($result['files_not_deleted']),
                    'success_message' => 'Откат последнего импорта выполнен.',
                    'errors' => empty($result['files_not_deleted']) ? [] : ['Некоторые файлы не удалось удалить; проверьте results.'],
                    'results' => $result,
                ], JSON_UNESCAPED_UNICODE);
            } catch (\Throwable $e) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'success_message' => '', 'errors' => [$e->getMessage()], 'results' => null], JSON_UNESCAPED_UNICODE);
            }
            exit;
        }

        /*
         * Сброс данных импорта
         */
        if (!empty($_POST['reset_import'])) {
            try {
                $this->resetService->reset();

                header(
                    'Content-Type: application/json; charset=utf-8'
                );

                echo json_encode(
                    [
                        'success' => true,
                        'success_message' => 'Данные импорта успешно сброшены.',
                        'errors' => [],
                        'results' => null,
                    ],
                    JSON_UNESCAPED_UNICODE
                );

                exit;
            } catch (\Throwable $e) {
                header(
                    'Content-Type: application/json; charset=utf-8'
                );

                echo json_encode(
                    [
                        'success' => false,
                        'success_message' => '',
                        'errors' => [
                            $e->getMessage()
                        ],
                        'results' => null,
                    ],
                    JSON_UNESCAPED_UNICODE
                );

                exit;
            }
        }

        /*
         * Синхронизация товаров
         */
        if (!empty($_POST['sync_products'])) {
            set_time_limit(0);
            ini_set('max_execution_time', '0');

            $productImportId = null;
            $rollbackRunId = null;

            try {
                $rollbackRunId = $this->rollbackService->begin();
                $productImportId = $this->imageTransferRepository->createProductImport();


                register_shutdown_function(
                    function () use ($productImportId) {

                        $error = error_get_last();

                        if (!$error) {
                            return;
                        }

                        $fatalErrors = [
                            E_ERROR,
                            E_PARSE,
                            E_CORE_ERROR,
                            E_COMPILE_ERROR,
                        ];

                        if (!in_array($error['type'], $fatalErrors, true)) {
                            return;
                        }

                        try {
                            $this->imageTransferRepository
                                ->completeProductImport(
                                    $productImportId,
                                    'completed_with_errors'
                                );
                        } catch (\Throwable $e) {
                            // Ничего не делаем.
                            // Скрипт уже завершается из-за фатальной ошибки.
                        }
                    }
                );

                /*
                 * 1. Загружаем Snapshot из Sportfly
                 */
                $snapshotLoader = new SnapshotLoader($this->sourceDb, $this->importConfig);

                $snapshot = $snapshotLoader->load();
                $selectionStats = $snapshotLoader->getSelectionStats();

                /*
                 * 2. Закрываем Sportfly DB
                 */
                $this->databaseManager->closeSource();

                /*
                 * 3. Импортируем данные из Snapshot в Fly
                 */
                $this->databaseManager->assertProductsSchema();

                $dependencyResults = $this->importService->importDependencies(
                    $snapshot
                );

                /*
                 * 4. Импортируем товары и их варианты из Snapshot в Fly
                 */
                $productResults = $this->importService->importProducts(
                    $snapshot,
                    $dependencyResults['languageMap'],
                    $dependencyResults['categoryMap'],
                    $dependencyResults['featureValueMap'],
                    $dependencyResults['brandMap']
                );

                $results = [
                    'import_settings' => $selectionStats,
                    'dependencies' => [
                        'brands' => count($dependencyResults['brandMap']),
                        'languages' => count($dependencyResults['languageMap']),
                        'features' => count($dependencyResults['featureMap']),
                        'feature_values' => count($dependencyResults['featureValueMap']),
                        'categories' => count($dependencyResults['categoryMap']),
                    ],
                    'products' => $productResults,
                ];

                $failedCount = (int) ($productResults['errors'] ?? 0);
                $hasImportErrors = !empty($this->errors) || $failedCount > 0;
                $status = $hasImportErrors ? 'completed_with_errors' : 'success';

                $this->imageTransferRepository->completeProductImport(
                    $productImportId,
                    $status
                );
                $this->rollbackService->finish($rollbackRunId);

                header(
                    'Content-Type: application/json; charset=utf-8'
                );

                echo json_encode(
                    [
                        'success' =>
                            !$hasImportErrors,

                        'success_message' =>
                            !$hasImportErrors
                                ? 'Синхронизация успешно завершена.'
                                : (($failedCount > 0 && $failedCount === (int) ($productResults['total'] ?? 0))
                                    ? 'Синхронизация не выполнена: все товары завершились ошибкой.'
                                    : 'Синхронизация завершена с ошибками.'),

                        'errors' =>
                            array_merge($this->errors, $failedCount > 0
                                ? [sprintf('Ошибок товаров: %d из %d.', $failedCount, (int) $productResults['total'])]
                                : []),

                        'results' =>
                            $results,
                    ],
                    JSON_UNESCAPED_UNICODE
                );

                exit;
            } catch (\Throwable $e) {
                error_log(
                    '[ProductsImport] Import failed: ' . $e->getMessage()
                    . ' in ' . $e->getFile() . ':' . $e->getLine()
                );

                if ($productImportId !== null) {
                    try {
                        $this->imageTransferRepository
                            ->completeProductImport(
                                $productImportId,
                                'completed_with_errors'
                            );
                    } catch (\Throwable $statusException) {
                        error_log(
                            '[ProductsImport] Status update failed: '
                            . $statusException->getMessage()
                        );
                    }
                }

                if ($rollbackRunId !== null) {
                    try {
                        $this->rollbackService->finish($rollbackRunId);
                    } catch (\Throwable $journalException) {
                        error_log('[ProductsImport] Rollback journal finalization failed: ' . $journalException->getMessage());
                    }
                }

                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'success_message' => '', 'errors' => [$e->getMessage()], 'results' => null], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }


        /*
         * Перенос изображений
         */
        if (!empty($_POST['transfer_images'])) {
            set_time_limit(0);
            ini_set('max_execution_time', '0');

            try {
                /*
                 * 1. Находим последний успешный Product Import
                 */
                $productImport = $this->imageTransferRepository
                    ->findLastSuccessfulProductImport();

                if (!$productImport) {
                    throw new \RuntimeException(
                        'Нет успешного Product Import для переноса изображений.'
                    );
                }

                $productImportId = (int) $productImport->id;

                /*
                 * 2. Получаем актуальные изображения из Fly __images
                 */
                $images = $this->imageFlyRepository
                    ->findAllForTransfer();

                /*
                 * 3. Переносим физические файлы
                 */
                $transferResults = $this->imageTransferService
                    ->transferAll($images);

                /*
                 * 4. Сохраняем статистику
                 */
                $this->imageTransferRepository
                    ->updateImageTransferStatistics(
                        $productImportId,
                        $transferResults['total'],
                        $transferResults['transferred'],
                        $transferResults['skipped'],
                        $transferResults['errors']
                    );

                /*
                 * 5. Определяем итоговый статус
                 */
                $status = $transferResults['errors'] > 0
                    ? 'completed_with_errors'
                    : 'success';

                /*
                 * 6. Завершаем Image Transfer
                 */
                $this->imageTransferRepository
                    ->completeImageTransfer(
                        $productImportId,
                        $status
                    );
                $this->rollbackService->refreshTransfer();

                header(
                    'Content-Type: application/json; charset=utf-8'
                );

                echo json_encode(
                    [
                        'success' =>
                            $transferResults['errors'] === 0,

                        'success_message' =>
                            $transferResults['errors'] === 0
                                ? 'Перенос изображений успешно завершён.'
                                : 'Перенос изображений завершён с ошибками.',

                        'results' =>
                            $transferResults,
                    ],
                    JSON_UNESCAPED_UNICODE
                );

                exit;
            } catch (\Throwable $e) {
                header(
                    'Content-Type: application/json; charset=utf-8'
                );

                echo json_encode(
                    [
                        'success' => false,
                        'errors' => [
                            $e->getMessage()
                        ],
                    ],
                    JSON_UNESCAPED_UNICODE
                );

                exit;
            }
        }


        /*
         * Вывод страницы
         */
        $productImportStatus = $this->imageTransferRepository->findLastProductImport();

        $this->design->assign(
            'product_import_status',
            $productImportStatus
        );

        $this->design->assign('rollback_run', $this->rollbackService->latest());
        $this->design->assign(
            'title',
            'Импорт товаров'
        );

        $this->design->assign(
            'errors',
            $this->errors
        );

        return $this->design->fetch('products_import.tpl');
    }
}
