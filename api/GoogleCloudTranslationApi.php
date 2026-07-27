<?php
require_once('Okay.php');

class GoogleCloudTranslationApi extends Okay
{

    /*Язык на который будем переводить*/
    private $outputLanguage = 'ru';
    private $outputLanguageDb;

    /*Язык с которого будем переводить*/
    private $sourceLanguage = 'ua';
    private $sourceLanguageDb;
    /**
     * @var mixed|null
     */
    private $startTime;

    private $url = 'https://translation.googleapis.com/language/translate/v2';
    private $apiKey = 'AIzaSyAJeGnrL8yI-EGlbs2cyTNts95oDvQ9aZ0';
    /**
     * @var object
     */
    private $model;

    /*если 0 то ограничение по итерациям не выполняется
    если >0 то количиество итерацый будет ограничено*/
    private $numberIterations = 0;
    /**
     * @var mixed|null
     */
    private $timeLimit = 28; //в секундах

    public function __construct()
    {
        parent::__construct();
        /*Получим информацию о языках в бд*/
        $this->outputLanguageDb = $this->languages->get_language($this->outputLanguage);
        $this->sourceLanguageDb = $this->languages->get_language($this->sourceLanguage);

        /*если не удалось получить даныне*/
        if (!$this->outputLanguageDb || !$this->sourceLanguageDb) {
            return false;
        }

    }

    /**
     * @param null $model
     * @param $startTime
     */
    public function translation($modelName, $startTime)
    {
        $this->startTime = $startTime;

        if (empty($modelName)) {
            return;
        }
        $this->model = null;

        switch ($modelName) {
            case 'FeaturesValues':
                $this->model = (object)[
                    'langField' => 'value',
                    'fieldId' => 'feature_value_id',
                    'limit' => 50,
                    'table' => '__lang_features_values'
                ];
                break;

            case 'Features':
                $this->model = (object)[
                    'langField' => 'name',
                    'fieldId' => 'feature_id',
                    'limit' => 50,
                    'table' => '__lang_features'
                ];
                break;
        }
        $this->translationModel();
    }


    private function isTimeAvailable()
    {
        return (microtime(true) - $this->startTime) < $this->timeLimit;
    }

    private function curl($text)
    {

        $params = [
            'q' => $text,
            "source" => $this->sourceLanguageDb->href_lang,
            "target" => $this->outputLanguage
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url . '?key=' . $this->apiKey,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/plain'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    private function getData($limit)
    {
        $query = $this->db->placehold('SELECT * FROM ' . $this->model->table . ' 
                WHERE `automatic_translation_done` = 0 AND `lang_id`= ? 
                LIMIT ?', $this->outputLanguageDb->id, $limit);

        $this->db->query($query);

        return $this->db->results();
    }

    private function updateTranslation($field, $value, $obj)
    {
        if (empty($field) || empty($value) || empty($obj) || !is_array($obj)) {
            return;
        }

        $query = $this->db->placehold('UPDATE ' . $this->model->table . " SET ?% 
                        WHERE `lang_id`= ? AND `$field` = ? LIMIT 1",
            (array)$obj, (int)$this->outputLanguageDb->id, (int)$value);

        $this->db->query($query);

    }

    private function translationModel()
    {

        if (
            !isset($this->model->langField) ||
            !isset($this->model->fieldId) ||
            !isset($this->model->limit) ||
            !isset($this->model->table)
        ) {
            return;
        }


        $iterations = 0;
        while ($this->isTimeAvailable()) {
            $iterations++;
            if ($this->numberIterations > 0 && $iterations > $this->numberIterations) {
                return;
            }

            /*Получим значения свойств у которых нет переводов*/
            $items = $this->getData($this->model->limit);
            $arrayForTranslation = [];
            foreach ($items as $item) {
                $value = $item->{$this->model->langField};
                //если значение число то пропустим его
                if (is_numeric($value) || empty($value)) {
                    $this->updateTranslation(
                        $this->model->fieldId, $item->{$this->model->fieldId}, ['automatic_translation_done' => 1]);

                    continue;
                }
                $arrayForTranslation[] = $value;
            }

            if (empty($items) || empty($arrayForTranslation)) {
                return;
            }

            /*выполним запрос перевода*/
            $response = $this->curl($arrayForTranslation);
            if (!$this->update($response, $items)) {
                return;
            };

        }

    }

    public function translationProducts($startTime)
    {
        $this->startTime = $startTime;
        $iterations = 0;
        while ($this->isTimeAvailable()) {

            $iterations++;
            if ($this->numberIterations > 0 && $iterations > $this->numberIterations) {
                return;
            }

            $this->model = (object)[
                'limit' => 1,
                'table' => '__lang_products'
            ];

            /*Получим продукт*/
            $query = $this->db->placehold(
                'SELECT p.id,lp.* FROM `__products`p 
                    INNER JOIN __lang_products lp ON lp.product_id = p.id AND lp.automatic_translation_done=0 AND lp.lang_id=? 
                    INNER JOIN __variants v ON v.product_id = p.id
                    WHERE p.visible  
                    ORDER BY v.feed_epicentr DESC
                    LIMIT ?
'
                , $this->outputLanguageDb->id, $this->model->limit);

            $this->db->query($query);

            $langProducts = $this->db->results();

            if ($langProducts) {
                $productRu = array_shift($langProducts);
                $query = $this->db->placehold(
                    'SELECT * FROM  __lang_products lp  
                    WHERE lp.lang_id=? AND lp.product_id=? LIMIT ? '
                    , $this->sourceLanguageDb->id, $productRu->id, $this->model->limit);

                $this->db->query($query);
                $productUa = $this->db->result();
                if (empty($productRu->name) && !empty($productUa->name)) {
                    $productRu->name = $productUa->name;
                }
                if (empty($productRu->annotation) && !empty($productUa->annotation)) {
                    $productRu->annotation = $productUa->annotation;
                }
                if (empty($productRu->description) && !empty($productUa->description)) {
                    $productRu->description = $productUa->description;
                }
                if (empty($productRu->meta_title) && !empty($productUa->meta_title)) {
                    $productRu->meta_title = $productUa->meta_title;
                }
                if (empty($productRu->meta_keywords) && !empty($productUa->meta_keywords)) {
                    $productRu->meta_keywords = $productUa->meta_keywords;
                }
                if (empty($productRu->meta_description) && !empty($productUa->meta_description)) {
                    $productRu->meta_description = $productUa->meta_description;
                }

            }

//            var_dump($productRu->id, $iterations);
            if (empty($productRu)) {
                return;
            }

            /*получим id вариантов продукта*/
            $query = $this->db->placehold('SELECT id FROM __variants WHERE  product_id = ?', $productRu->id);
            $this->db->query($query);

            $variantsIds = $this->db->results('id');
            /*получим получим переводы вариантов*/
            if ($variantsIds) {

                $this->model = (object)[
                    'langField' => 'name',
                    'fieldId' => 'variant_id',
                    'table' => '__lang_variants'
                ];

                $query = $this->db->placehold(
                    'SELECT * FROM __lang_variants 
                        WHERE `automatic_translation_done` = 0  AND variant_id in (?@)'
                    , (array)$variantsIds);
                $this->db->query($query);

                $langVariants = [];
                $variants = [];
                foreach ($this->db->results() as $result) {
                    $variants[$result->variant_id] = $result;
                    $langVariants[$result->variant_id][$result->lang_id] = $result;
                }
                $variants = array_values($variants);

                $arrayForTranslation = [];
                foreach ($variants as $variant) {
                    $langVariantRu = $langVariants[$variant->variant_id][$this->outputLanguageDb->id];
                    $langVariantUa = $langVariants[$variant->variant_id][$this->sourceLanguageDb->id];
                    if (!empty($langVariantRu->name)) {
                        $arrayForTranslation[] = $langVariantRu->name;
                    } else {
                        $arrayForTranslation[] = $langVariantUa->name;
                    }
                }
                if (empty($variants || empty($arrayForTranslation))) {
                    continue;
                }

                /*выполним запрос перевода вариантов*/
                $response = $this->curl($arrayForTranslation);

                if (
                    isset($response->data->translations) &&
                    is_array($response->data->translations) &&
                    count($variants) == count($response->data->translations) &&
                    $this->isTimeAvailable()
                ) {
                    foreach ($response->data->translations as $index => $translation) {
                        /*если время не доступно то выход с цыклов*/
                        if (!$this->isTimeAvailable()) {
                            return false;
                        }

                        if (isset($variants[$index]) && isset($translation->translatedText)) {
                            $item = $variants[$index];
                            $newItem = [
                                'automatic_translation_done' => 1,
                                $this->model->langField => $translation->translatedText,
                            ];

                            $this->updateTranslation($this->model->fieldId, $item->{$this->model->fieldId}, $newItem);
                        }
                    }
                    $arrayForTranslation = [
                        $productRu->name,
                        $productRu->annotation,
                        $productRu->description,
                        $productRu->meta_title,
                        $productRu->meta_keywords,
                        $productRu->meta_description,
                    ];

                    if (empty($productRu || empty($arrayForTranslation))) {
                        continue;
                    }
                    /*выполним запрос перевода продукта*/
                    $response = $this->curl($arrayForTranslation);

                    if (
                        isset($response->data->translations) &&
                        is_array($response->data->translations) && count($response->data->translations) == count($arrayForTranslation)
                    ) {
                        $this->model = (object)[
                            'table' => '__lang_products'
                        ];

                        $translations = $response->data->translations;
                        $newItem = [
                            'automatic_translation_done' => 1,
                            'name' => $translations[0]->translatedText,
                            'annotation' => $translations[1]->translatedText,
                            'description' => $translations[2]->translatedText,
                            'meta_title' => $translations[3]->translatedText,
                            'meta_keywords' => $translations[4]->translatedText,
                            'meta_description' => $translations[5]->translatedText,
                        ];

                        $this->updateTranslation('product_id', $productRu->product_id, $newItem);

                    }

                };

            }
        }
    }

    private function update($response, $items): bool
    {
        if (
            isset($response->data->translations) &&
            is_array($response->data->translations) &&
            count($items) == count($response->data->translations) &&
            $this->isTimeAvailable()
        ) {
            foreach ($response->data->translations as $index => $translation) {
                /*если время не доступно то выход с цыклов*/
                if (!$this->isTimeAvailable()) {
                    return false;
                }

                if (isset($items[$index]) && isset($translation->translatedText)) {
                    $item = $items[$index];
                    $newItem = [
                        'automatic_translation_done' => 1,
                        $this->model->langField => $translation->translatedText,
                    ];
                    $this->updateTranslation($this->model->fieldId, $item->{$this->model->fieldId}, $newItem);
                }
            }
            return true;
        }
        return false;

    }


}