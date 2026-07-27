<?php

require_once 'ModulesCore/modules/FeedManager/controllers/FeedManager.php';


class FeedRozetka extends FeedManager
{

    protected $errors = [];
    protected static $db_fr;

    public function __construct()
    {
        self::$db_fr = new Database();
        
        $this->initTokenDictionaryTable();

        parent::__construct();
    }


    public function fetch()
    {

        $result = [];
        $success_massage = '';

        if ($this->request->post('rmbt-date')) {
            $date = $this->request->post('rmbt-date');
            
            $sql = "
                UPDATE __variants v
                INNER JOIN __products p ON p.id = v.product_id
                SET v.feed_rozetka = IF(p.created >= ?, 1, 0);
            ";

            $query = self::$db_fr->placehold($sql, $date);
            $result = self::$db_fr->query($query);
            if (!$result) {
                $this->errors[] = 'Ошибка применения изменений';
            }
        } 
        
        if ($this->request->post('rmbt_names_match')) {

            $sql = "
                UPDATE __variants v
                INNER JOIN __products p ON p.id = v.product_id
                INNER JOIN (
                    SELECT MAX(id) AS keep_id, name
                    FROM __products
                    GROUP BY name
                    HAVING COUNT(*) > 1
                ) latest ON latest.name = p.name
                SET v.feed_rozetka = 0
                WHERE p.id != latest.keep_id;
            ";

            $query = self::$db_fr->placehold($sql);
            $result = self::$db_fr->query($query);
            if (!$result) {
                $this->errors[] = 'Ошибка применения изменений';
            }

        }

        if ($this->request->post('rmbt-date') || $this->request->post('rmbt_names_match')) {
            $this->jsonResponse(
                [
                'rmbt_names_match' => $this->request->post('rmbt_names_match'),
                'result' => $result,
                'errors' => $this->errors
                ]
            );
        } elseif ($this->request->post('rmbt-date')) {
            $this->errors = 'Дата должна быть указана!';
        }


        if ($this->request->post('get_property_values')) {

            /**
             * Пользователь на фронте в пле property-values 
             * получает все варианты значений указанного свойства
             * нажав на кнопку "получить значения свойств"
             *  при этом в поле 
             *      abbr - токен 
             *      full - соответствие
             * если поле full пусто пользователь заполняет его в ручную
             * после того как все нужные поля full заполнены 
             * пользователь нажимает кнопку "Добавить соответствия в БД" 
             */        

            $offset = $this->request->post('offset');
            $offset = ($offset === null || $offset === '') ? 0 : $offset;

            $size_batch = $this->request->post('size_batch');
            $size_batch = ($size_batch === null || $size_batch === '') ? null : $size_batch;

            $arr_tokens = [];
            if ($this->request->post('feature_Ids')) {
                $feature_Ids = $this->request->post('feature_Ids');
            }

            if ($this->request->post('values_without_match')) {
                $arr_tokens = $this->getValuesWithoutMatch($feature_Ids, $offset, $size_batch);

                $arr_tokens_count = count($arr_tokens);
                if ($arr_tokens_count > 0 ) {
                     $success_massage = "Получено $arr_tokens_count свойств у которых нет соответствий";
                }
            } else {
                $arr_tokens = $this->getAllPropertyValues($feature_Ids, $offset, $size_batch);

                $arr_tokens_count = count($arr_tokens);
                if ($arr_tokens_count > 0 ) {
                     $success_massage = "Получено $arr_tokens_count свойств";
                }
            }

            $this->jsonResponse(
                [
                    'result' => $arr_tokens,
                    'success_massage' =>  $success_massage,
                    'errors' => $this->errors
                ]
            );
        } elseif ($this->request->post('save_token_dictionary')) {
            
            if ($this->request->post('data')) {

                $json = $this->request->post('data');
                $rows = json_decode($json, true);
                if ($this->request->post('feature_Ids')) {
                    $feature_Ids = $this->request->post('feature_Ids');
                }
    
                if (!is_array($rows)) {
                    throw new \Exception('Invalid JSON');
                }
    
                $result = $this->importFullMatchesToDictionary($rows, $feature_Ids);
                $success_massage = "В словарь соответствий добавлено " . $result['qty_matches'] . ' токенов';
            } 
           
            $this->jsonResponse(
                [
                    'result' => $result,
                    'success_massage' =>  $success_massage,
                    'errors' => $this->errors
                ]
            );

        }


        $this->design->assign('errors', $this->errors);
        $this->design->assign('success_massage', $success_massage);
        $this->design->assign('feed_name', 'Rozetka Feed');
        return $this->design->fetch('feed_rozetka.tpl');
    }

    /**
     * Получает токены значений характеристики, отсутствующие
     * в таблице словаря нормализации.
     *
     * Метод:
     * 1. Получает значения из таблицы __features_values
     * 2. Разбивает значения на отдельные токены
     * 3. Удаляет дубликаты токенов
     * 4. Проверяет наличие токенов в таблице
     *    sfly_rmbt_feature_token_dictionary
     * 5. Возвращает только отсутствующие токены
     *
     * Проверка выполняется:
     * - с учётом feature_id
     * - в case-sensitive режиме
     *
     * Пример:
     * "син/біл-черв"
     * =>
     * [
     *     ['feature_id' => 100, 'abbr' => 'син',  'full' => ''],
     *     ['feature_id' => 100, 'abbr' => 'біл',  'full' => ''],
     *     ['feature_id' => 100, 'abbr' => 'черв', 'full' => '']
     * ]
     *
     * @param int      $featureId   ID
     *                              характеристики.
     * @param int      $batchOffset Смещение выборки.
     * @param int|null $batchSize   Размер
     *                              пакета.
     *                              null =
     *                              обработка
     *                              до
     *                              конца
     *                              массива.
     *
     * @return array<int, array{
     *     feature_id:int,
     *     abbr:string,
     *     full:string
     * }>
     */
    public function getValuesWithoutMatch($featureId, $batchOffset = 0, $batchSize = null)
    {
        // todo: добавить обработку нескольких feature_id

        $featureId   = (int)$featureId;
        $batchOffset = max(0, (int)$batchOffset);

        if ($batchSize !== null) {
            $batchSize = max(1, (int)$batchSize);
        }

        $sql = "
                SELECT
                    value AS feature_value,
                    feature_id
                FROM __features_values
                WHERE feature_id = ?
                GROUP BY value, feature_id
            ";

        if ($batchSize !== null) {
            $sql .= "
                LIMIT {$batchOffset}, {$batchSize}
            ";
        }

        $query = self::$db_fr->placehold($sql, $featureId);

        if (!$query) {

            $this->errors[] = "
                Ошибка подготовки SQL-запроса.
                feature_id = {$featureId}
            ";

            return [];
        }

        if (!self::$db_fr->query($query)) {

            $this->errors[] = "
                Ошибка выполнения SQL-запроса.
                feature_id = {$featureId}
            ";

            return [];
        }

        $result = self::$db_fr->results();

        if (!is_array($result)) {

            $this->errors[] = "
                Ошибка чтения результата из БД.
                feature_id = {$featureId}
            ";

            return [];
        }

        if (empty($result)) {
            return [];
        }

        /**
         * Токенизатор:
         * - точка должна быть частью токена
         * - "св.сер." => ["св.", "сер."]
         * - "рожев." и "рожев" — разные токены
         */
        $tokenPattern = '/[^\s,;\/-]+\.?/u';

        $uniqueTokens = [];

        foreach ($result as $index => $row) {

            if (!isset($row->feature_value)) {

                $this->errors[] = "
                    Элемент #{$index}: отсутствует feature_value.
                    feature_id = {$featureId}
                ";

                continue;
            }

            $featureValue = trim((string)$row->feature_value);

            if ($featureValue === '') {
                continue;
            }

            preg_match_all($tokenPattern, $featureValue, $matches);

            if (!isset($matches[0]) || !is_array($matches[0])
            ) {
                $this->errors[] =" Элемент #{$index}: ошибка токенизации. value = {$featureValue} ";
                continue;
            }

            $parts = $matches[0];

            if (empty($parts)) {
                continue;
            }

            foreach ($parts as $abbr) {

                $abbr = trim($abbr);

                if ($abbr === '') {
                    continue;
                }

                /**
                 * Уникальность:
                 * feature_id + token
                 */
                $uniqueKey = $row->feature_id . '|' . $abbr;

                if (isset($uniqueTokens[$uniqueKey])) {
                    continue;
                }

                $uniqueTokens[$uniqueKey] = [
                        'feature_id' => (int)$row->feature_id,
                        'abbr'       => $abbr,
                        'full'       => '',
                    ];
            }
        }

        if (empty($uniqueTokens)) {
            return [];
        }

        /**
         * Получаем список уникальных токенов
         */
        $tokens = [];

        foreach ($uniqueTokens as $item) {
            $tokens[] = $item['abbr'];
        }

        $tokens = array_values(array_unique($tokens));

        if (empty($tokens)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($tokens), '?'));

        $sql = "
            SELECT token
            FROM sfly_rmbt_feature_token_dictionary
            WHERE feature_id = ?
            AND BINARY token IN ($placeholders)
        ";

        $params = array_merge([$featureId], $tokens);

        $query = self::$db_fr->placehold($sql, ...$params);

        if (!$query) {

            $this->errors[] = "
                Ошибка подготовки SQL-запроса словаря.
                feature_id = {$featureId}
            ";

            return [];
        }

        if (!self::$db_fr->query($query)) {

            $this->errors[] = "
                Ошибка выполнения SQL-запроса словаря.
                feature_id = {$featureId}
            ";

            return [];
        }

        $existingRows = self::$db_fr->results();

        if (!is_array($existingRows)) {

            $this->errors[] = "
                Ошибка чтения словаря из БД.
                feature_id = {$featureId}
            ";

            return [];
        }

        $existingTokens = [];

        foreach ($existingRows as $row) {

            if (!isset($row->token)) {

                $this->errors[] = "
                    Ошибка чтения token из словаря.
                    feature_id = {$featureId}
                ";

                continue;
            }

            $existingTokens[$row->token] = true;
        }

        $valuesWithoutMatch = [];

        foreach ($uniqueTokens as $item) {

            $abbr = $item['abbr'];

            if (!isset($existingTokens[$abbr])) {
                $valuesWithoutMatch[] = $item;
            }
        }

        return $valuesWithoutMatch;
    }


    /**
     * Получает значения свойства из таблицы features_values,
     * разбивает их на отдельные токены и формирует
     * нормализованный массив сокращений.
     *
     * Используется для подготовки словаря сокращений
     * и последующей нормализации значений характеристик.
     *
     * Пример:
     * "син/біл-черв" =>
     *      [
     *          ['feature_id' => 100, 'abbr' => 'син', 'full' => ''],
     *          ['feature_id' => 100, 'abbr' => 'біл', 'full' => ''],
     *          ['feature_id' => 100, 'abbr' => 'черв', 'full' => '']
     *      ]
     *
     * @param int $featureId   ID характеристики.
     * @param int $batchOffset Смещение выборки для пакетной обработки.
     * @param int $batchSize   Количество записей в пакете.
     *
     * @return array<int, array{
     *     feature_id:int,
     *     abbr:string,
     *     full:string
     *  }>
     */
    public function getAllPropertyValues($featureId, $batchOffset = 0, $batchSize = null)
    {
        // todo: добавить поддержку массива feature_id

        $featureId   = (int)$featureId;
        $batchOffset = max(0, (int)$batchOffset);

        if ($batchSize !== null) {
            $batchSize = max(1, (int)$batchSize);
        }

        $sql = "
            SELECT
                value AS feature_value,
                feature_id
            FROM __features_values
            WHERE feature_id = ?
            GROUP BY value, feature_id
        ";

        // batching теперь работает в SQL
        if ($batchSize !== null) {
            $sql .= "
                LIMIT {$batchOffset}, {$batchSize}
            ";
        }

        $query = self::$db_fr->placehold($sql, $featureId);

        if (!$query) {

            $this->errors[] = "
            Ошибка подготовки SQL-запроса.
            feature_id = {$featureId}
        ";

            return [];
        }

        if (!self::$db_fr->query($query)) {
            $this->errors[] = "
                Ошибка выполнения SQL-запроса.
                feature_id = {$featureId}
            ";
            return [];
        }

        $result = self::$db_fr->results();

        if (!is_array($result)) {
            $this->errors[] = "
                Ошибка чтения результата из БД.
                feature_id = {$featureId}
            ";
            return [];
        }

        if (empty($result)) {
            return [];
        }

        /**
         * Точка НЕ разделитель.
         * "рожев." и "рожев" — разные токены.
         */
        $splitPattern = '/[;,\/\s-]+/u';

        $tokens = [];

        /**
         * Для удаления дублей токенов.
         */
        $uniqueTokens = [];

        foreach ($result as $index => $row) {

            if (!isset($row->feature_value)) {
                $this->errors[] = "
                    Элемент #{$index}: отсутствует feature_value.
                    feature_id = {$featureId}
                ";
                continue;
            }

            $featureValue = trim((string)$row->feature_value);

            if ($featureValue === '') {
                continue;
            }

            $tokenPattern = '/[^\s,;\/-]+\.?/u';
            preg_match_all($tokenPattern, $featureValue, $matches);

            if (!isset($matches[0]) || !is_array($matches[0]) ) {
                $this->errors[] = "
                    Элемент #{$index}: ошибка токенизации.
                    value = {$featureValue}
                ";
                continue;
            }

            $parts = $matches[0];

            if ($parts === false) {
                $this->errors[] = "
                    Элемент #{$index}: ошибка preg_split().
                    value = {$featureValue}
                ";
                continue;
            }

            if (empty($parts)) {
                continue;
            }

            foreach ($parts as $abbr) {

                $abbr = trim($abbr);

                if ($abbr === '') {
                    continue;
                }

                /**
                 * Ключ уникальности:
                 * feature_id + token
                 */
                $uniqueKey = $row->feature_id . '|' . $abbr;

                if (isset($uniqueTokens[$uniqueKey])) {
                    continue;
                }

                $uniqueTokens[$uniqueKey] = true;

                $tokens[] = [
                    'feature_id' => (int)$row->feature_id,
                    'abbr'       => $abbr,
                    'full'       => '',
                ];
            }
        }

        return $tokens;
    }

    /**
     * Импортирует найденные соответствия токенов
     * в таблицу словаря нормализации.
     *
     * Метод:
     * 1. Проверяет входной массив данных
     * 2. Валидирует token и normalized_token
     * 3. Пропускает пустые значения
     * 4. Пропускает случаи, где token === normalized_token
     * 5. Проверяет существование token в словаре
     * 6. Добавляет новые соответствия
     * 7. Собирает конфликты нормализации
     *
     * Если token уже существует:
     * - и normalized_token совпадает → запись пропускается
     * - и normalized_token отличается → конфликт записывается в duplicates
     *
     * Формат входного массива:
     * [
     *     [
     *         'abbr' => 'син',
     *         'full' => 'синій'
     *     ],
     *     [
     *         'abbr' => 'черв',
     *         'full' => 'червоний'
     *     ]
     * ]
     *
     * Пример duplicates:
     * [
     *     100 => [
     *         'син' => [
     *             'existing' => 'синій',
     *             'conflicts' => ['темно-синій']
     *         ]
     *     ]
     * ]
     *
     * @param array $rows       Массив токенов
     *                          для импорта.
     * @param int   $feature_id ID характеристики.
     *
     * @return array|false
     *
     * Возвращает:
     * [
     *     'success' => bool,
     *     'duplicates' => array
     * ]
     *
     * false возвращается при некорректных входных данных.
     */
    public function importFullMatchesToDictionary( $rows, $feature_id )
    {
        // todo добавить обработку нескольких $featureId 

        if (empty($rows) || !is_array($rows)) {
            $this->errors[] = ' Пустой или некорректный массив данных ';
            return false;
        }

        $duplicates = [];
        $qtyMatches = 0;

        foreach ($rows as $index => $row) {

            if (!is_array($row)) {
                $this->errors[] = "
                    Элемент #{$index}: некорректный формат строки.
                ";
                continue;
            }

            $token = trim((string)($row['abbr'] ?? ''));
            $normalized_token = trim((string)($row['full'] ?? ''));

            if ($token === '') {
                $this->errors[] = "
                    Элемент #{$index}: пустой token.
                ";
                continue;
            }

            if ($normalized_token === '') {
                continue;
            }

            if ($token === $normalized_token) {
                continue;
            }

            $sql = "
                SELECT normalized_token
                FROM __rmbt_feature_token_dictionary
                WHERE
                    feature_id = ?
                    AND token = ?
                LIMIT 1
            ";

            $query = self::$db_fr->placehold($sql, $feature_id, $token);

            if (!$query) {
                $this->errors[] = "
                    Элемент #{$index}: ошибка подготовки SQL-запроса.
                    token = {$token}
                ";
                continue;
            }

            $result = self::$db_fr->query($query);

            if (!$result) {
                $this->errors[] = "
                    Элемент #{$index}: ошибка чтения из БД.
                    token = {$token}
                ";
                continue;
            }

            $existing = self::$db_fr->result();

            if ($existing !== null) {

                if ($existing === $normalized_token) {
                    continue;
                }

                if (!isset($duplicates[$feature_id][$token])) {
                    $duplicates[$feature_id][$token] = [
                        'existing' => $existing,
                        'conflicts' => []
                    ];
                }

                if (!in_array(
                    $normalized_token,
                    $duplicates[$feature_id][$token]['conflicts'],
                    true
                )
                ) {
                    $duplicates[$feature_id][$token]['conflicts'][] = $normalized_token;
                }

                continue;
            }

            $sql = "
                INSERT INTO __rmbt_feature_token_dictionary
                SET
                    feature_id = ?,
                    token = ?,
                    normalized_token = ?
            ";

            $query = self::$db_fr->placehold($sql, $feature_id, $token, $normalized_token);

            if (!$query) {
                $this->errors[] = "
                    Элемент #{$index}: ошибка подготовки INSERT-запроса.
                    token = {$token}
                ";
                continue;
            }

            $result = self::$db_fr->query($query);

            if (!$result) {
                $this->errors[] = "
                    Элемент #{$index}: ошибка записи в БД.
                    token = {$token}
                ";
                continue;
            }

            $qtyMatches++;
        }

        return [
            'qty_matches' => $qtyMatches,
            'success' => empty($this->errors),
            'duplicates' => $duplicates,
        ];
    }

    protected function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function initTokenDictionaryTable()
    {

        $sql = "
            CREATE TABLE IF NOT EXISTS __rmbt_feature_token_dictionary (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                feature_id INT NOT NULL,
                token VARCHAR(255) NOT NULL DEFAULT '',
                normalized_token VARCHAR(255) NOT NULL DEFAULT '',
                created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_feature_token (
                    feature_id,
                    token
                ),
                KEY idx_feature_id (feature_id),
                KEY idx_token (token)
                ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci
        ";


        self::$db_fr->query($sql);
    }


    /**
     * Нормализует значение свойства используя словарь токенов.
     *
     * Метод:
     *  1. Находит feature_id по имени свойства
     *     через таблицы __features и __lang_features
     *  2. Разбивает значение свойства на токены
     *  3. Ищет соответствия токенов в словаре
     *     __rmbt_feature_token_dictionary
     *  4. Заменяет найденные токены на normalized_token
     *  5. Неизвестные токены оставляет без изменений
     *  6. Возвращает итоговое значение в нижнем регистре
     *
     * Пример:
     *  value = normalizeFeatureValue('Колір', 'СИН/БІЛ-черв');
     *  value = 'синій білий червоний'
     *
     * @param string $featureName
     * @param string $featureValue
     *
     * @return string
     */
    public static function normalizeFeatureValue($featureName, $featureValue, $db)
    {
        static $featureIdCache = [];
        static $dictionaryCache = [];
        static $enableFeatureIds = [100];


        $featureName = trim(mb_strtolower($featureName));
        $featureValue = trim(mb_strtolower($featureValue));

        if ($featureName === '' || $featureValue === '') {
            return '';
        }

        // Получаем feature_id из кеша или БД

        if (isset($featureIdCache[$featureName])) {

            $featureId = $featureIdCache[$featureName];

        } else {

            $sql = "
                SELECT f.id
                FROM __features AS f
                WHERE LOWER(f.name) = ?
                UNION
                SELECT lf.feature_id AS id
                FROM __lang_features AS lf
                WHERE LOWER(lf.name) = ?
                LIMIT 1
            ";

            $query = $db->placehold(
                $sql,
                $featureName,
                $featureName
            );

            if (!$query) {
                return $featureValue;
            }

            $result = $db->query($query);

            if (!$result) {
                return $featureValue;
            }

            $featureId = (int)$db->result('id');

            $featureIdCache[$featureName] = $featureId;
        }

        if (empty($featureId)) {
            return $featureValue;
        }

        if (!in_array($featureId, $enableFeatureIds)) {
            return $featureValue;
        }

        // Разбиваем значение на токены

        $splitPattern = '/[\/\-\s]+/u';

        $tokens = preg_split(
            $splitPattern,
            $featureValue,
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        if ($tokens === false || empty($tokens)) {
            return $featureValue;
        }

        // Удаляем дубликаты токенов

        $tokens = array_values(array_unique($tokens));

        // Загружаем словарь из кеша или БД

        if (!isset($dictionaryCache[$featureId])) {

            $sql = "
                SELECT
                    LOWER(token) AS token,
                    LOWER(normalized_token) AS normalized_token
                FROM __rmbt_feature_token_dictionary
                WHERE feature_id = ?
            ";

            $query = $db->placehold(
                $sql,
                $featureId
            );

            if (!$query) {
                return $featureValue;
            }

            $result = $db->query($query);

            if (!$result) {
                return $featureValue;
            }

            $rows = $db->results();

            $dictionaryCache[$featureId] = [];

            if (is_array($rows)) {

                foreach ($rows as $row) {

                    if (empty($row->token)
                        || empty($row->normalized_token)
                    ) {
                        continue;
                    }

                    $dictionaryCache[$featureId][$row->token] = $row->normalized_token;
                }
            }
        }

        $dictionary = $dictionaryCache[$featureId];

        // Собираем нормализованное значение

        $normalizedTokens = [];
        $unknownTokens = [];

        foreach ($tokens as $token) {

            if (isset($dictionary[$token])) {

                $normalizedTokens[] = $dictionary[$token];

            } else {

                $normalizedTokens[] = $token;
                $unknownTokens[] = $token;
            }
        }

        return implode('/', $normalizedTokens);
    }


}
