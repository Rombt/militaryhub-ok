<?php

require_once 'ModulesCore/modules/FeedManager/controllers/FeedManager.php';

class FeedGoogle extends FeedManager
{
    use FeedManagerUtils;

    // static $db_promo;

    public function __construct()
    {


        // self::$db_promo = new Database();
        parent::__construct();



        // $this->createTable( '/../migrations/google_feed_1.sql', '__temp_google_feed_1' );     // создать таблицу
        // $this->importFromCsv( 'D:/web/sportfly.local/ModulesCore/modules/FeedManager/google_feed_1.csv', '__temp_google_feed_1' ); // дом
        // $this->importFromCsv( 'G:/web/sportfly-local/ModulesCore/modules/FeedManager/csv/google_feed_1.csv', '__temp_google_feed_1' );


        // $structure_table = $this->get_structure_table( '__categories' );
        // $structure_table = $this->get_structure_table('__orders');
        // print_r( $structure_table );

        // $query = "
        //  SELECT p.*
        //  FROM __products p
        //  INNER JOIN __temp_google_feed_1 t
        //      ON p.name = t.Name
        //  WHERE p.visible = 1
        // ";

        // если товар есть в __temp_google_feed_1 но его нет в __products он попадёт в выборку но с пустыми ячейками кроме "mane"
        // $query = "
        //      SELECT
        //          p.*,
        //          t.Name
        //      FROM __temp_google_feed_1 t
        //      LEFT JOIN __products p
        //          ON p.name = t.Name
        //          AND p.visible = 1
        //  ";


        // все товары с сайта без описания
        // $query = "SELECT *
        //  FROM __products
        //  WHERE description IS NULL OR description = '';
        // ";

        // товары которые есть на сайте и которых нет в гугл не работает!!
        // $query = "SELECT *
        //  FROM __products p
        //  WHERE NOT EXISTS (
        //      SELECT 1
        //      FROM __temp_google_feed_1 t
        //      WHERE LOWER(TRIM(t.Name)) = LOWER(TRIM(p.name))
        //  );
        // ";

        // $query = "SELECT v.sku
        //  FROM __variants v
        //  JOIN __products p ON p.id = v.product_id
        //  WHERE p.created < '2022-01-01';
        //  ";

        // все товары которые старше 2021 года
        // $query = "SELECT
        //  v.sku,
        //  p.id AS product_id,
        //  p.created
        //  FROM __products p
        //  JOIN __variants v ON v.product_id = p.id
        //  WHERE p.created < '2022-01-01';
        // ";

        // все товары которые старше 2021 года с параметром "visible"
        // $query = "SELECT
        //  v.sku,
        //  p.id AS product_id,
        //  p.created,
        //  p.name,
        //  p.visible
        //  FROM __products p
        //  JOIN __variants v ON v.product_id = p.id
        //  WHERE p.created < '2022-01-01';
        // ";

        // все активные товары которые старше 2021 года
        // $query = "SELECT
        //  v.sku,
        //  p.id AS product_id,
        //  p.created,
        //  p.name,
        //  p.visible
        //  FROM __products p
        //  JOIN __variants v ON v.product_id = p.id
        //  WHERE p.created < '2022-01-01'
        //


        // все активные товары категории '163' старше 2021 года
        // $query = "SELECT
        //  p.id AS product_id,
        //  p.created,
        //  p.name,
        //  p.visible
        //  FROM __products p
        //  WHERE p.created < '2022-01-01'
        //  AND p.visible = 1
        //  AND p.main_category_id = '163';
        // ";

        // Все существующие категории
        // $query = "SELECT
        //  c.id,
        //  c.parent_id,
        //  c.name,
        //  c.google_name
        //  FROM __categories c
        // ";

        // Все поля таблицы  __categories
        // $query = "SELECT COLUMN_NAME
        //  FROM INFORMATION_SCHEMA.COLUMNS
        //  WHERE TABLE_NAME = '__categories';
        // ";

        // $this->exportQueryToCSV($query);

        // Добавить колонку facebook_name в таблицу __categories, не потеряв данные, нужно использовать SQL-команду ALTER TABLE.
        // ALTER TABLE `__categories`ADD COLUMN `facebook_name` VARCHAR(255) NOT NULL DEFAULT '' AFTER `rozetka_name`;

        // Добавить колонку facebook_name в таблицу __categories, не потеряв данные, нужно использовать SQL-команду ALTER TABLE.
        // ALTER TABLE `__categories`ADD COLUMN `facebook_name` VARCHAR(255) NOT NULL DEFAULT '' AFTER `rozetka_name`;



        // заполняю тестовыми данными
        // $csv_path = 'ModulesCore/modules/FeedManager/csv/test__All_categories_of_the_site_27.05.25.csv';
        // $this->importCsvToDb( 'id', $csv_path, '__categories', [ 'facebook_name' ] );


        // получить файл запросом CURL из командной строки и сохранить его на рабочий стол
        // invoke-WebRequest "https://sportfly.com.ua/rozetka_new.xml" -OutFile "$env:USERPROFILE\desktop\feeds\rozetka_new.xml"
        // invoke-WebRequest "https://sportfly.com.ua/rozetka.xml" -OutFile "$env:USERPROFILE\desktop\feeds\rozetka.xml"
        // invoke-webrequest "https://sportfly.com.ua/prom.xml" -OutFile "$env:USERPROFILE\desktop\feeds\prom.xml"
        // invoke-WebRequest "https://sportfly.com.ua/rss.xml" -OutFile "$env:USERPROFILE\desktop\feeds\google.xml"

        // для локального компа
        // Invoke-WebRequest "http://local.sportfly/rozetka_new.xml" -OutFile "$env:USERPROFILE\desktop\feeds_local\rozetka_new.xml"
        // Invoke-WebRequest "http://local.sportfly/rozetka.xml" -OutFile "$env:USERPROFILE\desktop\feeds_local\rozetka.xml"

        // Invoke-WebRequest "http://local.sportfly/prom.xml" -OutFile "$env:USERPROFILE\desktop\feeds_local\prom.xml"
        
        // тоже но для Command Prompt (cmd.exe)
        // curl "https://sportfly.com.ua/rss.xml" -o "%USERPROFILE%\Desktop\feeds\google.xml"
        // curl "https://sportfly.com.ua/rozetka_new.xml" -o "%USERPROFILE%\Desktop\feeds\rozetka_new.xml"
        // curl "https://sportfly.com.ua/rozetka.xml" -o "%USERPROFILE%\Desktop\feeds\rozetka.xml"


        /**
 * Формирование отчётов о категориях сайта 
*/


        // общая статистика по категориям
        // $query = "
        //     SELECT
        //         COUNT(*) AS total_categories,

        //         SUM(c.parent_id = 0) AS level_1_categories,

        //         SUM(c.rozetka_exclude = 1) AS rozetka_excluded_categories,

        //         SUM(c.visible = 0) AS hidden_categories,

        //         SUM(
        //             NOT EXISTS (
        //                 SELECT 1
        //                 FROM __products_categories pc
        //                 WHERE pc.category_id = c.id
        //             )
        //         ) AS categories_without_products,

        //         SUM(
        //             NOT EXISTS (
        //                 SELECT 1
        //                 FROM __products_categories pc
        //                 INNER JOIN __products p
        //                     ON p.id = pc.product_id
        //                 WHERE pc.category_id = c.id
        //                 AND p.created >= '2019-11-01'
        //             )
        //         ) AS categories_without_products_after_2019_11_01

        //     FROM __categories c;
        // ";
        // $this->exportQueryToCSV($query, 'categories_overall_status', 'ModulesCore/modules/FeedManager/csv/Reports_by_Category');


        /*
            категории 
                скрытые 
                без продуктов
                без продуктов созданных после 2019-11-01        
        */
        // $query = "
        //     SELECT
        //         c.id,
        //         c.name,
        //         'hidden_categories' AS reason,
        //         NULL AS products_count
        //     FROM __categories c
        //     WHERE c.visible = 0

        //     UNION ALL

        //     SELECT
        //         c.id,
        //         c.name,
        //         'categories_without_products' AS reason,
        //         0 AS products_count
        //     FROM __categories c
        //     WHERE NOT EXISTS (
        //         SELECT 1
        //         FROM __products_categories pc
        //         WHERE pc.category_id = c.id
        //     )

        //     UNION ALL

        //     SELECT
        //         c.id,
        //         c.name,
        //         'categories_without_products_after_2019_11_01' AS reason,
        //         COUNT(DISTINCT pc.product_id) AS products_count
        //     FROM __categories c
        //     LEFT JOIN __products_categories pc
        //         ON pc.category_id = c.id
        //     LEFT JOIN __products p
        //         ON p.id = pc.product_id
        //         AND p.created >= '2019-11-01'
        //     GROUP BY c.id, c.name
        //     HAVING COUNT(p.id) = 0;
        // ";
        // $this->exportQueryToCSV($query, 'categories_not_in_use', 'ModulesCore/modules/FeedManager/csv/Reports_by_Category');

        
        /**
         * Проверка состояния привязки категорий сайта 
         * к категориям Розетки: 
         *      наличие маппинга, 
         *      корректность связей 
         *      выявление некорректных или отсутствующих соответствий.
         * rozetka_category_exclude:
         *      NO_LINK        У категории сайта нет связи с Rozetka
         *      BROKEN_LINK    В категории хранится ID, но такого значения уже нет
         *      UNIQUE_LINK    Категория Rozetka используется только одной категорией сайта
         *      SHARED_LINK    Категория Rozetka используется несколькими категориями сайта
         *      UNKNOWN        Защитный fallback
         */
        // $query = "
        //     SELECT
        //         c.id AS site_category_id,
        //         c.parent_id AS site_parent_id,
        //         c.name AS site_category_name,

        //         COALESCE(fv.rozetka_name, lfv.value, fv.value) AS rozetka_category_name,
        //         c.rozetka_category_value_id AS rozetka_category_id,

        //         c.rozetka_exclude,

        //         CASE
        //             WHEN c.rozetka_exclude = 1 THEN 'CATEGORY_BLOCKED'
        //             WHEN c.rozetka_category_value_id IS NULL THEN 'NO_ROZETKA_LINK'
        //             WHEN fv.id IS NULL THEN 'BROKEN_ROZETKA_LINK'
        //             ELSE 'OK'
        //         END AS rozetka_ban_check,



        //         CASE
        //             WHEN c.rozetka_category_value_id IS NULL THEN 'NO_LINK'
        //             WHEN fv.id IS NULL THEN 'BROKEN_LINK'
        //             WHEN linked_cats.cnt = 1 THEN ''
        //             WHEN linked_cats.cnt > 1 THEN ''
        //             /*WHEN linked_cats.cnt = 1 THEN 'UNIQUE_LINK'*/
        //             /*WHEN linked_cats.cnt > 1 THEN 'SHARED_LINK'*/
        //             ELSE 'UNKNOWN'
        //         END AS rozetka_category_exclude,

        //         linked_cats.cnt AS linked_categories_count

        //     FROM __categories c

        //     LEFT JOIN __features_values fv
        //         ON fv.id = c.rozetka_category_value_id

        //     LEFT JOIN __lang_features_values lfv
        //         ON lfv.feature_value_id = fv.id
        //         AND lfv.lang_id = 1

        //     LEFT JOIN (
        //         SELECT
        //             rozetka_category_value_id,
        //             COUNT(*) AS cnt
        //         FROM __categories
        //         WHERE rozetka_category_value_id IS NOT NULL
        //         GROUP BY rozetka_category_value_id
        //     ) linked_cats
        //         ON linked_cats.rozetka_category_value_id = c.rozetka_category_value_id

        //     ORDER BY c.parent_id, c.position, c.id;
        // ";
        // $this->exportQueryToCSV($query, 'rozetka_categories_mapping_status', 'ModulesCore/modules/FeedManager/csv/Reports_by_Category');
    
        /**
         * Покажет товары которым не задана ни одна категория
         */
        // $query = "
        //     SELECT
        //         p.id,
        //         p.name,
        //         p.created
        //     FROM __products p
        //     LEFT JOIN __products_categories pc
        //         ON pc.product_id = p.id
        //     WHERE
        //         p.created >= '2022-01-01'
        //         AND pc.category_id IS NULL
        //     ORDER BY p.created DESC;
        // ";
        // $this->exportQueryToCSV($query, 'products_without_category', 'ModulesCore/modules/FeedManager/csv/Reports_by_Category');

        /**
         * Покажет товары которые привязаны к указанной категории
         */
        
        // $query = "
        //     SELECT 
        //         p.id,
        //         p.name,
        //         p.visible,
        //         pc.category_id
        //     FROM __products_categories AS pc
        //     INNER JOIN __products AS p 
        //         ON p.id = pc.product_id
        //      WHERE pc.category_id = 263      /*id нужной категории*/
        // ";
        // $this->exportQueryToCSV($query, 'products_from_category', 'ModulesCore/modules/FeedManager/csv/Reports_by_Category');


    
        /**
         * Покажет товары которые привязаны к указанной категории а также все категории привязанные к этому товару
         */
        
        // $query = "
        //     SELECT 
        //         p.id AS product_id,
        //         p.name AS product_name,
        //         p.visible,
        //         pc2.category_id AS linked_category_id
        //     FROM __products_categories AS pc1

        //     INNER JOIN __products AS p
        //         ON p.id = pc1.product_id

        //     INNER JOIN __products_categories AS pc2
        //         ON pc2.product_id = p.id

        //     WHERE pc1.category_id = 262 /*id нужной категории*/
        //     ORDER BY p.id, pc2.category_id;
        // ";
        // $this->exportQueryToCSV($query, 'products_from_category_and_linked_categories', 'ModulesCore/modules/FeedManager/csv/Reports_by_Category');


    
        /**
         * Покажет товары с одинаковыми именами созданные после указанной даты
         */
        
        // $query = "
        //     SELECT 
        //         p.id,
        //         p.name,
        //         p.created
        //     FROM __products AS p

        //     INNER JOIN (
        //         SELECT 
        //             name
        //         FROM __products
        //         WHERE created >= '2024-01-01'
        //         GROUP BY name
        //         HAVING COUNT(*) > 1
        //     ) AS duplicates
        //         ON duplicates.name = p.name

        //     WHERE p.created >= '2022-01-01'
        //     ORDER BY p.name, p.created;
        // ";
        // $this->exportQueryToCSV($query, 'duplicate_product_names', 'ModulesCore/modules/FeedManager/csv/Reports_by_Category');

    
        /**
         * Покажет варианты с одинаковыми именами созданные после указанной даты а также поле v.feed_rozetka
         */
        
        $query = "
            SELECT 
                p.id,
                p.name,
                p.created,
                v.feed_rozetka,
                v.stock
            FROM __products AS p

            INNER JOIN __variants AS v 
                ON v.product_id = p.id

            INNER JOIN (
                SELECT 
                    name
                FROM __products
                WHERE created >= '2024-01-01'
                GROUP BY name
                HAVING COUNT(*) > 1
            ) AS duplicates
                ON duplicates.name = p.name

            WHERE p.created >= '2022-01-01'
            ORDER BY p.name, p.created;
        ";
        $this->exportQueryToCSV($query, 'duplicate_variants_names_with_feed_rozetka', 'ModulesCore/modules/FeedManager/csv/Reports_by_Category');


    
        /**
         * Покажет категории Rozetka которые вообще нигде не используются
         */
        // $query = "
        //     SELECT
        //         fv.id AS rozetka_category_id,

        //         MAX(
        //             COALESCE(fv.rozetka_name, lfv.value, fv.value)
        //         ) AS rozetka_category_name,

        //         MAX(fv.feature_id) AS feature_id,

        //         COUNT(c.id) AS linked_site_categories_count

        //     FROM __features_values fv

        //     LEFT JOIN __lang_features_values lfv
        //         ON lfv.feature_value_id = fv.id
        //         AND lfv.lang_id = 1

        //     LEFT JOIN __categories c
        //         ON c.rozetka_category_value_id = fv.id

        //     WHERE fv.feature_id = (
        //         SELECT id
        //         FROM __features
        //         WHERE url = 'category_rozetka'
        //         LIMIT 1
        //     )

        //     GROUP BY fv.id

        //     HAVING COUNT(c.id) = 0

        //     ORDER BY rozetka_category_name;

        // ";
        // $this->exportQueryToCSV($query, 'rozetka_categories_without_mapping', 'ModulesCore/modules/FeedManager/csv/Reports_by_Category');


    }





    public function fetch()
    {


        // $this->design->assign( 'promotion_name', 'Акция ' . $this->promotion_config['name'] );

        $this->design->assign('feed_name', 'Google feed');
        return $this->design->fetch('feed_manager.tpl');
    }
}
