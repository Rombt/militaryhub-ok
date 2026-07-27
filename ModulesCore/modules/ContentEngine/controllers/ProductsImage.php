<?php

//todo добавить поле в которое пользователь сможет вводить  url сайта на котором нужно будет выполнять сбор картинок и введённый URL положить в файл задания
//todo добавить поле "type": "collect_product_photos"


class ProductsImage extends Okay
{
    protected $errors = [];
    protected static $db_pi;
    protected $generatedAt;
    protected $cache_folder =  __DIR__ .  '/../cache';
    protected $cache_tasks_folder;
    protected $cache_added_imgs_folder;
    protected $cache_files = [];
    protected $cache_added_imgs = [];

    public function __construct()
    {
        // $this->config = require __DIR__ . '/../config.php';
        self::$db_pi = new Database();
        $this->cache_tasks_folder = $this->cache_folder . '/tasks';
        $this->cache_added_imgs_folder = $this->cache_folder . '/added_imgs';
    }

    public function fetch()
    {

        set_time_limit(300);

        $this->getTasks();
        $this->getResults();


        $latest_tasks_file = $this->cache_files[array_key_last($this->cache_files)];
        if ($latest_tasks_file) {
            $latest_task = $this->loadJsonFile(
                $latest_tasks_file,
                $this->cache_tasks_folder
            );
        }


        if ($this->request->post('start_task')) {
            $this->generatedAt = date('Y-m-d_H-i');
            $date = $this->request->post('rmbt-date') ?? '';
            $result = [];
            $meta = [];


            if ($this->request->post('rmbt-products-id')) {
                $arr_products_id = $this->normalizeArrStr(explode(',', $this->request->post('rmbt-products-id')));

                $result = $this->getProductsByIds($arr_products_id);
            } elseif ($this->request->post('rmbt-brands')) {
                $arr_brands = $this->normalizeArrStr(explode(',', $this->request->post('rmbt-brands')));

                $result = $this->getProductsByBrands($arr_brands, $date);
            } else {
                $result = $this->getProductsByDate($date);
            }

            $jsonTask = $this->getTaskCollectPictures($result);
            $uniqueSkuCount = [];
            foreach ($jsonTask as $brandId => $data) {
                $uniqueSkus = array_unique(array_column($data['products'], 'sku'));
                $uniqueSkuCount[$data['brand_name']] = count($uniqueSkus);
            }

            $this->cacheTask($jsonTask);

            $meta = [
                'tasks' => $this->cache_files,
                'uniqueSkuCount' => $uniqueSkuCount
            ];

            $this->jsonResponse([
                'generated_at' => $this->generatedAt,
                'data' => $jsonTask,
                'meta' => $meta,
                'errors' => $this->errors
            ]);

            exit;
        } elseif ($this->request->post('download_task')) {
            $file_name = $this->request->post('task_name');
            $task = $this->loadJsonFile(
                $file_name,
                $this->cache_tasks_folder
            );

            $this->jsonResponse([
            'imgs_selected' => empty($this->errors),
            'task' => $task,
            'errors'  => $this->errors
            ]);
        } elseif ($this->request->post('del_task')) {
            $ok = $this->delJsonFile($this->request->post('task_name'), $this->cache_tasks_folder);

            $this->jsonResponse([
                'task' => $ok ? 'is_del' : 'error',
                'errors' => $this->errors ?: []
            ]);
        } elseif ($this->request->post('add_photo_to_products')) {
            $imagesBySku = [];

            $data = json_decode($_POST['add_photo_to_products'] ?? '', true);


            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->errors[] = 'JSON decode error: ' . json_last_error_msg();
            }
            if (!is_array($data) || empty($data['root'])) {
                $this->errors[] = 'Invalid results structure';
            }

            $imagesByProductId = [];
            if (empty($this->errors)) {
                $imagesByProductId = $this->collectImagesByProductId($data['root']);
            }

            if (count($imagesByProductId) == 0) {
                $this->errors[] = "Id products don`t received";
            }

            $totalSkus   = count($imagesBySku);

            $addedImages = [];
            $addedImages = $this->addImagesToProducts($imagesByProductId);
            $saveAddedImages = $this->saveAddedImages($addedImages);

            if (!$saveAddedImages) {
                $this->errors[] = "Failed to save added images to file";
            }

            $this->jsonResponse([
            'result_is_received' => empty($this->errors),
            'total_added_images'       => count($addedImages),
            'total_skus'         => $totalSkus,
            'errors'             => $this->errors
            ]);
        } elseif ($this->request->post('del_photo_from_products')) {
            $fileName = $_POST['file_name'] ?? '';

            $imgsId = $this->loadJsonFile($fileName, $this->cache_added_imgs_folder);

            foreach ($imgsId as $imageId) {
                $this->products->delete_image($imageId);
            }

            $ok = $this->delJsonFile($fileName, $this->cache_added_imgs_folder);


            $this->jsonResponse([
            'imgs_was_deleted' => empty($this->errors),
            'file_del' => $ok ? 'is_del' : 'error',
            'errors'             => $this->errors
            ]);
        } elseif ($this->request->post('download_addedImgs')) {
            $file_name = $this->request->post('addedImgs_name');
            $results = $this->loadJsonFile(
                $file_name,
                $this->cache_added_imgs_folder
            );

            $this->jsonResponse([
            'imgs_selected' => empty($this->errors),
            'results' => $results,
            'errors'  => $this->errors
            ]);
        } elseif ($this->request->post('del_addedImgs_file')) {
            $ok = $this->delJsonFile($this->request->post('addedImgs_name'), $this->cache_added_imgs_folder);

            $this->jsonResponse([
                'del_addedImgs' => $ok ? 'is_del' : 'error',
                'errors' => $this->errors ?: []
            ]);
        }

        $this->design->assign('tasks', json_encode($this->cache_files, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
        $this->design->assign('latest_task', json_encode($latest_task['task'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
        $this->design->assign('added_imgs', json_encode($this->cache_added_imgs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
        $this->design->assign('generated_at', $latest_task['generated_at']);
        $this->design->assign('title', 'Картинки товаров');
        $this->design->assign('errors', $this->errors);
        return $this->design->fetch('products_image.tpl');
    }

    private function addImagesToProducts(array $imagesByProductId): array
    {

        $addedImages = [];
        foreach ($imagesByProductId as $productId => $images) {
            foreach ($images as $path) {
                $path = basename($path);
                $fullPath = $this->config->root_dir . 'files/originals/' . $path;

                if (!file_exists($fullPath)) {
                     $this->errors[] = 'fullPath don`t exists ' . $fullPath;
                    continue;
                }

                $imageId = $this->products->add_image(
                    (int)$productId,
                    $path
                );
                $addedImages[] = $imageId;
            }
        }

        return $addedImages;
    }

    private function saveAddedImages(array $addedImages): bool
    {
        $dir = $this->cache_added_imgs_folder;
        $file = $dir . '/added_images_' . date('Y-m-d_H-i') . '.json';

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $json = json_encode($addedImages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return (bool)file_put_contents($file, $json);
    }

    private function collectImagesByProductId(array $data): array
    {
        $result = [];

        if (empty($data['children'])) {
            return $result;
        }

        foreach ($data['children'] as $filename => $file) {
            if (($file['type'] ?? '') !== 'file') {
                continue;
            }

            if (!preg_match('/^(\d+)_.*\./', $filename, $m)) {
                continue;
            }

            $productId = (int)$m[1];

            $result[$productId][] = $file['path'];
        }

        return $result;
    }

    protected function getResults()
    {
        if (is_dir($this->cache_added_imgs_folder)) {
            // Паттерн для файлов вида: added_images_2026-03-09_12-24.json
            $pattern = '/^added_images_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}\.json$/';

            foreach (scandir($this->cache_added_imgs_folder) as $file) {
                if (preg_match($pattern, $file)) {
                    $this->cache_added_imgs[] = $file;
                }
            }
        }
    }

    protected function loadJsonFile(string $name_file, ?string $folder = null)
    {

        // Формируем безопасный путь
        $path = rtrim($folder, '/') . '/' . basename($name_file);

        if (!is_file($path)) {
            return null;
        }

        $content = file_get_contents($path);

        if ($content === false) {
            return null;
        }

        return json_decode($content, true);
    }


    private function collectImagesBySku(array $node, array &$result = []): array
    {
        if (!isset($node['children'])) {
            return $result;
        }

        foreach ($node['children'] as $name => $item) {
            if ($item['type'] === 'file') {
                $parts = explode('_', $name);

                if (isset($parts[1])) {
                    $sku = $parts[1];

                    $result[$sku][] = $item['path'];
                }
            } elseif ($item['type'] === 'folder') {
                $this->collectImagesBySku($item, $result);
            }
        }

        return $result;
    }

    protected function delJsonFile($task_name, $folder)
    {
        $baseDir = realpath($folder);
        $file = realpath($baseDir . DIRECTORY_SEPARATOR . $task_name);

        if ($file === false || strpos($file, $baseDir . DIRECTORY_SEPARATOR) !== 0) {
            return false;
        }

        return is_file($file) && unlink($file);
    }

    protected function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }


    /**
     * Возвращает указанное задание
     */
    // protected function getTask($name_file)
    // {

    //     $path = rtrim($this->cache_tasks_folder, '/')
    //     . '/' . basename($name_file);

    //     if (!is_file($path)) {
    //         return null;
    //     }

    //     $content = file_get_contents($path);
    //     if ($content === false) {
    //         return null;
    //     }

    //     return json_decode($content, true);
    // }

    /**
     * Наполняет массив именами файлов всех задач
     */
    protected function getTasks()
    {
        if (is_dir($this->cache_tasks_folder)) {
            $pattern = '/^\d{4}-\d{2}-\d{2}_\d{2}-\d{2}\.json$/';

            foreach (scandir($this->cache_tasks_folder) as $file) {
                if (preg_match($pattern, $file)) {
                    $this->cache_files[] = $file;
                }
            }
        }
    }

    /**
     * Сохраняет задание в файл
     */
    protected function cacheTask($task)
    {

        $data_task = [
            'generated_at' => $this->generatedAt,
            'task' => $task,
        ];

        if (!is_dir($this->cache_tasks_folder)) {
            mkdir($this->cache_tasks_folder, 0755, true);
        }

        $fileName = str_replace([':', ' '], ['-', '_'], $this->generatedAt) . '.json';

        file_put_contents(
            $this->cache_tasks_folder . '/' . $fileName,
            json_encode($data_task, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );

        $this->cache_files[] = $fileName;
    }


    /**
     *
     * возвращает задание для сбора картинок в json установленного образца
     *
    */
    protected function getTaskCollectPictures($arr_products)
    {

        $tasks = [];

        foreach ($arr_products as $product) {
            $brand_id = (int)$product->brand_id;

            error_log('$product->brand_name = ' . print_r($product->brand_name, true));       //!!-!!

            if (!isset($tasks[$brand_id])) {
                $tasks[$brand_id] = [
                    'brand_id'   => (string)$product->brand_id,
                    'brand_name' => $product->brand_name,
                    'metadata'   => [
                            'target_website' => $this->getTargetWebsiteByBrand($product->brand_name),
                        ],
                    'products' => [],
                ];
            }

            // Добавляем товар в нужный бренд
            $tasks[$brand_id]['products'][] = [
                'id_product'   => (string)$product->id_product,
                'name_product' => $product->name_product,
                'sku'          => $product->sku,
                'attributes'   => [
                    'color' => $product->color,
                    'size'  => $product->size,
                ],
            ];
        }

        return $tasks;
    }


    protected function getTargetWebsiteByBrand(string $brandName): ?string
    {
        static $map = null;

        $brandNameNormalize = mb_strtolower(trim($brandName), 'UTF-8');

        if ($map === null) {
            $config = include __DIR__ . '/../config.php';

            $filePath = $config['target_websites'] ?? null;

            if (!$filePath || !file_exists($filePath)) {
                return null;
            }

            $json = file_get_contents($filePath);
            $data = json_decode($json, true);

            if (!is_array($data)) {
                return null;
            }

            // нормализуем ВСЕ ключи
            $map = [];

            foreach ($data as $key => $value) {
                $normalizedKey = mb_strtolower(trim($key), 'UTF-8');
                $map[$normalizedKey] = $value;
            }
        }

        return $map[$brandNameNormalize] ?? null;
    }


    /**
     * возвращает указанные товары
    */
    public function getProductsByIds($IDs)
    {

        $ids = array_values(array_unique(array_map('intval', $IDs)));


        if (!$ids) {
            return [];
        }

        $sql = "
            SELECT
                p.id AS id_product,
                p.name AS name_product,
                p.brand_id,
                COALESCE(b.name, '') AS brand_name,
                v.sku,
                v.color,
                v.size
            FROM __products p
            LEFT JOIN __brands b 
                ON b.id = p.brand_id 
            AND p.brand_id <> 0
            LEFT JOIN __variants v 
                ON v.product_id = p.id
            WHERE
                p.id IN (?@)
        ";


        $query = self::$db_pi->placehold($sql, $ids);
        self::$db_pi->query($query);

        return self::$db_pi->results();
    }

    /**
     * возвращает товары указанных брендов без картинок и которые младше указанной даты
     */
    public function getProductsByBrands($brands, $date = null)
    {
        $arr_brands = array_map('trim', $brands);
        $arr_brands = array_values(array_unique($arr_brands));

        if (!$arr_brands) {
            return [];
        }

        $sql = "
            SELECT
                p.id AS id_product,
                p.name AS name_product,
                p.brand_id,
                b.name AS brand_name,
                v.sku,
                v.color,
                v.size
            FROM __products p
            LEFT JOIN __brands b ON b.id = p.brand_id
            LEFT JOIN __variants v ON v.product_id = p.id
            LEFT JOIN __images i ON i.product_id = p.id
            WHERE
                b.name COLLATE utf8_general_ci IN (?@)
                AND i.id IS NULL
        ";

        $params = [$arr_brands];

        if ($date !== null) {
            $sql .= " AND p.created >= ?";
            $params[] = $date;
        }

        $query = self::$db_pi->placehold($sql, ...$params);
        self::$db_pi->query($query);

        return self::$db_pi->results();
    }

    /**
     * возвращает товары без картинок и которые младше указанной даты
     */
    public function getProductsByDate($date)
    {

        $sql = "
            SELECT
                p.id AS id_product,
                p.name AS name_product,
                p.brand_id,
                b.name AS brand_name,
                v.sku,
                v.color,
                v.size
            FROM __products p
            LEFT JOIN __brands b ON b.id = p.brand_id
            LEFT JOIN __variants v ON v.product_id = p.id
            WHERE p.created >= ?
            AND NOT EXISTS (
                SELECT 1
                FROM __images i
                WHERE i.product_id = p.id
            )
        ";

        $query = self::$db_pi->placehold($sql);
        self::$db_pi->query($query, $date);

        return self::$db_pi->results();
    }

    /**
     *
     * Возвращает указанное количество брендов отсортированных по количеству продаж в указанном порядке
     *
     */
    public function getBrandsByPurchases($amount = 20, $sortOrder = 'DESC')
    {

        $sql = "
            SELECT
                b.id AS brand_id,
                b.name AS brand_name,
                SUM(pu.amount) AS total_products_ordered
            FROM __purchases pu
            INNER JOIN __orders o
                ON o.id = pu.order_id
            INNER JOIN __products p
                ON p.id = pu.product_id
            INNER JOIN __brands b
                ON b.id = p.brand_id
            WHERE
                o.closed = 1
                AND p.brand_id IS NOT NULL
                AND p.brand_id != 0
            GROUP BY b.id
            ORDER BY total_products_ordered $sortOrder
            LIMIT ?;
            ";

        $query = self::$db_pi->placehold($sql);
        self::$db_pi->query($query, $amount);


        return self::$db_pi->results();
    }

    private function normalizeArrStr($array)
    {

        $normalized = array_map(function ($value) {
            $value = trim($value);
            $value = mb_strtolower($value, 'UTF-8');
            $value = preg_replace('/\s+/u', ' ', $value);
            $value = strip_tags($value);

            if (class_exists('Normalizer')) {
                $value = normalizer_normalize($value, Normalizer::FORM_C);
            }

            return $value;
        }, $array);

        return array_values(array_unique(array_filter($normalized)));
    }
}
