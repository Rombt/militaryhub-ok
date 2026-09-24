<?php

require_once 'ModulesCore/modules/FeedManager/controllers/FeedManager.php';


class FeedProm extends FeedManager
{

    protected $errors = [];
    protected static $db_fp;

    public function __construct()
    {
        self::$db_fp = new Database();
        
        parent::__construct();
    }


    public function fetch()
    {

        $result = [];
        $success_massage = '';

        $feed_prom_discount = $this->settings->feed_prom_discount ?? null;
        $feed_prom_min_discount = $this->settings->feed_prom_min_discount ?? null;


        if ($this->request->post('save_feed_discount')) {

            $feed_prom_discount = $this->request->post('feed_discount');

            if ($feed_prom_discount === null || $feed_prom_discount === '') {

                $this->errors[] = 'Укажите процент скидки';

            } else {

                $feed_prom_discount = (int) $feed_prom_discount;

                if ($feed_prom_discount < 0 || $feed_prom_discount > 100) {

                    $this->errors[] = 'Процент должен быть от 0 до 100';

                } else {

                    $param = 'feed_prom_discount';

                    // Проверяем, существует ли настройка
                    $query = self::$db_fp->placehold(
                        "SELECT setting_id
                        FROM __settings
                        WHERE param = ?
                        LIMIT 1",
                        $param
                    );

                    $result = self::$db_fp->query($query);

                    if ($result) {

                        $setting = $result->fetch_object();

                        if ($setting) {

                            // Обновляем существующую настройку
                            $query = self::$db_fp->placehold(
                                "UPDATE __settings
                                SET value = ?
                                WHERE setting_id = ?",
                                $feed_prom_discount,
                                $setting->setting_id
                            );

                            if (!self::$db_fp->query($query)) {
                                $this->errors[] = 'Ошибка обновления настройки';
                            }

                        } else {

                            // Создаём новую настройку
                            $query = self::$db_fp->placehold(
                                "INSERT INTO __settings
                         SET param = ?, value = ?",
                                $param,
                                $feed_prom_discount
                            );

                            if (!self::$db_fp->query($query)) {
                                $this->errors[] = 'Ошибка создания настройки';
                            }
                        }

                    } else {

                        $this->errors[] = 'Ошибка проверки настройки';
                    }
                }
            }



            $feed_prom_min_discount = $this->request->post('feed_min_discount');

            if ($feed_prom_min_discount === null || $feed_prom_min_discount === '') {

                $this->errors[] = 'Укажите минимально допустимую скидку';

            } else {

                $feed_prom_min_discount = (int) $feed_prom_min_discount;

                if ($feed_prom_min_discount < 0 || $feed_prom_min_discount > 100) {

                    $this->errors[] = 'Минимальная скидка должна быть от 0 до 100';

                } else {

                    $param = 'feed_prom_min_discount';

                    // Проверяем, существует ли настройка
                    $query = self::$db_fp->placehold(
                        "SELECT setting_id
                        FROM __settings
                        WHERE param = ?
                        LIMIT 1",
                        $param
                    );

                    $result = self::$db_fp->query($query);

                    if ($result) {

                        $setting = $result->fetch_object();

                        if ($setting) {

                            // Обновляем существующую настройку
                            $query = self::$db_fp->placehold(
                                "UPDATE __settings
                                SET value = ?
                                WHERE setting_id = ?",
                                $feed_prom_min_discount,
                                $setting->setting_id
                            );

                            if (!self::$db_fp->query($query)) {
                                $this->errors[] = 'Ошибка обновления настройки';
                            }

                        } else {

                            // Создаём новую настройку
                            $query = self::$db_fp->placehold(
                                "INSERT INTO __settings
                                SET param = ?, value = ?",
                                $param,
                                $feed_prom_min_discount
                            );

                            if (!self::$db_fp->query($query)) {
                                $this->errors[] = 'Ошибка создания настройки';
                            }
                        }

                    } else {

                        $this->errors[] = 'Ошибка проверки настройки минимальной скидки';
                    }
                }
            }

            $this->jsonResponse(
                [
                'result' => empty($this->errors),
                'success_massage' => empty($this->errors)
                ? 'Настройка скидки сохранена'
                : '',
                'errors' => $this->errors
                ]
            );
        }


        $this->design->assign('feed_prom_discount', $feed_prom_discount);
        $this->design->assign('feed_prom_min_discount', $feed_prom_min_discount);

        $this->design->assign('errors', $this->errors);
        $this->design->assign('success_massage', $success_massage);
        $this->design->assign('feed_name', 'Prom feed');
        return $this->design->fetch('feed_prom.tpl');
    }


    protected function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }


    /**
     * Уменьшает существующую скидку на заданный процент.
     *
     * Например:
     *  текущая скидка 30%, уменьшение 20% => новая скидка 10%
     *  текущая скидка 20%, уменьшение 20% => новая скидка 0%
     *  текущая скидка 10%, уменьшение 20% => новая скидка 0%
     *
     * @param float $currentDiscount   Текущая скидка в процентах
     * @param float $feed_prom_discountReduction На сколько процентов уменьшить скидку
     *
     * @return float
     */
    public static function reduceDiscount($price_old, $price, $feed_prom_discount, $feed_prom_min_discount = 2)
    {
        $price_old = (float) $price_old;
        $price = (float) $price;
        $feed_prom_discount = (float) $feed_prom_discount;

        // Некорректная исходная цена
        if ($price_old <= 0) {
            return $price;
        }

        if ($price <= 0) {
            return $price_old;
        }

        // Текущей скидки нет
        if ($price >= $price_old) {
            return $price;
        }

        // Некорректное значение уменьшения скидки
        if ($feed_prom_discount <= 0) {
            return $price;
        }

        // Уменьшение не может превышать 100%
        $feed_prom_discount = min($feed_prom_discount, 100);

        // Текущая скидка в процентах
        $currentDiscount = (($price_old - $price) / $price_old) * 100;

        // Новая скидка
        $newDiscount = $currentDiscount - $feed_prom_discount;

        // Скидка полностью убрана
        if ($newDiscount <= 0 || $newDiscount < $feed_prom_min_discount ) {
            return $price_old;
        }

        // Новая цена с новой скидкой
        return round($price_old * (1 - $newDiscount / 100),  2);
    }

}
