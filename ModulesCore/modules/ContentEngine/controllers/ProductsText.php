<?php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Middleware;
use GuzzleHttp\Pool;

class ProductsText extends Okay
{
    protected $config;
    protected static $db_igpt;
    protected $model = 'gpt-4.1-mini';  // для тестов
    // protected $model = 'gpt-4.1';
    // protected $model = 'gpt-5.2';    // слишком много токенов тратит при сравнимом качестве с 4.1
    protected $generatedAt;
    protected $cache_generations_folder =  __DIR__ .  '/../cache/generations';
    protected $cache_files = [];
    // protected $limit_products = 30;     // количество генераций в одном пуле запросов. Для нормирования нагрузки на сервер
    protected $limit_products = 10;        // для тестов

    protected $errors = [];

    public function __construct()
    {
        $this->config = require __DIR__ . '/../config.php';
        self::$db_igpt = new Database();
    }

    public function fetch()
    {

        set_time_limit(300);

        $this->getGenerations();
        $latest_generation = $this->getGeneration($this->cache_files[array_key_last($this->cache_files)]);

        if ($this->request->post('del_generation')) {
            $ok = $this->delGeneration($this->request->post('generation_name'));

            $this->jsonResponse([
                'generation' => $ok ? 'is_del' : 'error',
                'errors' => $this->errors ?: []
            ]);
        } elseif ($this->request->post('download_generation')) {
            $result = $this->getGeneration($this->request->post('generation_name'));

            $this->jsonResponse([
                'generation' => $result ?: 'error',
                'errors' => $this->errors ?: []
            ]);
        } elseif ($this->request->post('save_generation')) {
            $dt = new DateTime($this->request->post('generation_name'));
            $file_name = $dt->format('Y-m-d_H-i') . '.json';

            $result = $this->getGeneration($file_name);

            $saveResult = $this->updateProductsDescription($result['data']);

            $this->jsonResponse([
                'save_generation' => $saveResult ?: 'error',
                'errors' => $this->errors ?: []
            ]);
        } elseif ($this->request->post('data_import')) {
            $data_import = json_decode($this->request->post('data_import'));

            $preparedItems = array_map(function ($item) {
                $description = $item->content->descriptionHtml ?? '';
                $attributes  = $item->content->attributesHtml ?? '';

                // объединяем
                $fullDescription = $description;

                if (!empty($attributes)) {
                    $fullDescription .= '<br><br>' . $attributes;
                }

                return [
                    'id' => (int)$item->id,
                    'description' => $fullDescription,
                    'meta_description' => strip_tags($description),
                ];
            }, $data_import);

            $result = $this->updateProductsDescription($preparedItems);

            $this->jsonResponse([
                'data_import' => $result ?: 'error',
                'errors' => $this->errors ?: []
            ]);
        } elseif ($this->request->post('start_generation')) {
            $products = '';

            $ids = (string) $this->request->post('rmbt-products-id');
            $product_IDs = $ids !== '' ? explode(',', $ids) : [];

            $prompt_pattern = $this->request->post('rmbt-prompt');
            $date = $this->request->post('rmbt-date');

            if ($prompt_pattern == '') {
                $this->errors[] = 'Введіть промпт';
            }

            if (($date && !$this->isValidDate($date)) && empty($product_IDs)) {
                $this->errors[] = 'Введіть дату або ID продуктів';
            }

            if (!empty($product_IDs) && is_array($product_IDs)) {
                $products = $this->getProductsByIds($product_IDs);
            } else {
                $products = $this->getProductsByDate($date);
            }

            if (empty($products)) {
                 $this->errors[] = 'Товари відсутні';
            }

            $requests = function ($products) use ($prompt_pattern) {
                foreach ($products as $i => $product) {
                    $prompt = str_replace("%product_name%", $product->name, $prompt_pattern);
                    yield function () use ($prompt) {
                        return $this->fetchResponseAsync($prompt);
                    };
                }
            };

            $results = [];
            $total_tokens = 0;
            $this->generatedAt = date('Y-m-d H:i');

            $client = new \GuzzleHttp\Client([
                'timeout'         => 60,   // общее время запроса в сек
                'connect_timeout' => 20,   // таймаут установки соединения в сек
            ]);

            $pool = new Pool($client, $requests($products), [
                'concurrency' => 5, // максимум 5 одновременных запросов

                'fulfilled' => function ($res, $index) use (&$results, $products, &$total_tokens) {
                    try {
                        // Если пришёл объект ошибки вместо массива
                        if ($res instanceof \Throwable) {
                            throw $res;
                        }

                        $responseText = $res['responseText'] ?? null;
                        $totalTokens = $res['totalTokens'] ?? 0;

                        $results[$index] = [
                            'id' => $products[$index]->id,
                            'name' => $products[$index]->name,
                            'description' => $responseText,
                            'total_tokens' => $totalTokens,
                        ];

                        $total_tokens += $totalTokens;
                    } catch (\Throwable $e) {
                        $this->errors[] = [
                            'index' => $index,
                            'error' => $e->getMessage(),
                        ];
                    }
                },

                'rejected' => function ($reason, $index) {
                    // Здесь промис не выполнился (HTTP ошибка, исключение Guzzle и т.д.)
                    $message = ($reason ) ? $reason->getMessage() : (string)$reason;

                    $this->errors[] = [
                        'index' => $index,
                        'error' => $message,
                    ];
                }
            ]);

            $pool->promise()->wait();


            if (count($results) == 0) {
                $this->errors[] = 'Не відома помилка, описи не згенеровано ';
            }

            $meta['total_tokens'] = $total_tokens;
            $meta['errors'] = $this->errors;

            $this->cacheResults($results, 'product_description', $meta);
            $meta['generations'] = $this->cache_files;

            header('Content-Type: application/json');
            echo json_encode([
                    'generated_at' => $this->generatedAt,
                    'data' => $results,
                    'meta' => $meta,
                    'errors' => $this->errors
                ]);
            exit;
        }

        $this->design->assign('limit_products', $this->limit_products);
        $this->design->assign('latest_generation', json_encode($latest_generation, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
        $this->design->assign('generations', json_encode($this->cache_files, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
        $this->design->assign('title', 'Текстовый контент товара');
        $this->design->assign('errors', $this->errors);
        return $this->design->fetch('products_text.tpl');
    }

    private function updateProductsDescription(array $items): bool
    {
        if (empty($items)) {
            return false;
        }

        foreach ($items as $item) {
            if (empty($item['id'])) {
                continue;
            }

            $product = new stdClass();
            $product->id = (int)$item['id'];

            $product->description = $item['description'] ?? '';
            $product->meta_description = $item['meta_description'] ?? '';

            $this->products->update_product($product->id, $product);
        }

        return true;
    }



    protected function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function delGeneration(string $generation_name)
    {
        $baseDir = realpath($this->cache_generations_folder);
        $file = realpath($baseDir . DIRECTORY_SEPARATOR . $generation_name);

        if ($file === false || strpos($file, $baseDir . DIRECTORY_SEPARATOR) !== 0) {
            return false;
        }

        return is_file($file) && unlink($file);
    }

    protected function getGenerations()
    {

        if (is_dir($this->cache_generations_folder)) {
            $pattern = '/^\d{4}-\d{2}-\d{2}_\d{2}-\d{2}\.json$/';

            foreach (scandir($this->cache_generations_folder) as $file) {
                if (preg_match($pattern, $file)) {
                    $this->cache_files[] = $file;
                }
            }
        }
    }

    protected function getGeneration($name_file)
    {

        $path = rtrim($this->cache_generations_folder, '/')
          . '/' . basename($name_file);

        if (!is_file($path)) {
            return null;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return null;
        }


        return json_decode($content, true);
    }

    protected function fetchResponseAsync($prompt)
    {
        $api_key = $this->config['openai_api_key'];

        $stack = HandlerStack::create();
        $stack->push(function (callable $handler) use ($api_key) {
            return function ($request, array $options) use ($handler, $api_key) {
                $request = $request->withHeader('Authorization', 'Bearer ' . $api_key);
                return $handler($request, $options);
            };
        });

        $retryMiddleware = Middleware::retry(
            function ($retries, $request, ?ResponseInterface $response = null, ?Throwable $exception = null) {
                if ($retries >= 3) {
                    return false;
                }
                if ($response && $response->getStatusCode() >= 500) {
                    return true;
                }
                if ($exception instanceof RequestException) {
                    return true;
                }
                return false;
            },
            function ($retries) {
                return 1000 * pow(2, $retries);
            } // экспоненциальная задержка
        );

        $stack->push($retryMiddleware);

        $client = new Client([
        'handler' => $stack,
        'base_uri' => 'https://api.openai.com',
        'timeout' => 10,
        'http_errors' => true,
        ]);

        // Возвращаем Promise
        return $client->postAsync('/v1/responses', [
        'json' => [
        'model' => $this->model,
        'input' => $prompt,
        ]
        ])->then(
            function ($response) {
                $responseData = json_decode((string)$response->getBody(), true);
                $responseText = $responseData['output'][0]['content'][0]['text'] ?? '';
                $total_tokens = $responseData['usage']['total_tokens'] ?? '';
                return ['responseText' => $responseText, 'totalTokens' => $total_tokens, 'responseData' => $responseData];
            },
            function ($exception) {
                // Логируем, возвращаем null
                return $exception;
            }
        );
    }

    protected function cacheResults($results, $task = '', $meta = [])
    {

        $cacheData = [
            'generated_at' => $this->generatedAt,
            'task'         => $task,
            'model'        => $this->model,
            'data'         => $results,
            'meta'         => [
                'total_tokens' => $meta['total_tokens'],
            ],
        ];

        if (!is_dir($this->cache_generations_folder)) {
            mkdir($this->cache_generations_folder, 0755, true);
        }

        $fileName = str_replace([':', ' '], ['-', '_'], $this->generatedAt) . '.json';

        file_put_contents(
            $this->cache_generations_folder . '/' . $fileName,
            json_encode($cacheData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );

        $this->cache_files[] = $fileName;
    }

    protected function getProductsByDate($date)
    {
        if (!$date) {
            $this->errors[] = 'Select date!';
        }

        $query = 'SELECT * FROM `__products`
          WHERE `created` > ?
          AND `description` = ""
          LIMIT ?';


        self::$db_igpt->query($query, $date, $this->limit_products);
        $results = self::$db_igpt->results();


        if (empty($results)) {
            error_log("Module: No data returned from query.");
            return false;
        }
        return $results;
    }

    protected function getProductsByIds(array $IDs)
    {
        $ids = array_values(array_unique(array_map('intval', $IDs)));

        if (!$ids) {
            return [];
        }

        $sql = '
            SELECT *
            FROM __products
            WHERE id IN (?@)
            LIMIT ?
        ';

        self::$db_igpt->query($sql, $ids, $this->limit_products);
        $results = self::$db_igpt->results();

        return $results ?: [];
    }

    protected function isValidDate(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
