;<? exit(); ?>

[database]

;Сервер базы данных
db_server = "localhost"

;Пользователь базы данных
db_user = "db_sportfly"

;Пароль к базе
db_password = "pVxBoz7Mqkf2VAud"

;Имя базы
db_name = "db_sportfly"

;Префикс для таблиц
db_prefix = __

;Кодировка базы данных
db_charset = utf8mb4

;Режим SQL
db_sql_mode = ""

;Смещение часового пояса
;db_timezone = +02:00

;Включить логирование нерабочих SQL запросов
sql_debug = true

[php]
error_reporting = E_ALL
php_charset = UTF8
php_locale_collate = uk_UA
php_locale_ctype = uk_UA
php_locale_monetary = uk_UA
php_locale_numeric = uk_UA
php_locale_time = uk_UA
php_timezone = Europe/Kyiv
debug_mode = false
dev_debug_mode = false

[smarty]

smarty_compile_check = true
smarty_caching = false
smarty_cache_lifetime = 0
smarty_debugging = false
smarty_html_minify = false
smarty_security = true
debug_translation = false

[images]
;Указываем какую библиотеку использовать для нарезки изображений. Варианты: gregwar_image, imagick или gd
resize_library = gregwar_image

;Директория оригиналов изображений
original_images_dir = files/originals/

;Директория миниатюр
resized_images_dir = files/products/

;Файл изображения с водяным знаком
watermark_file = backend/files/watermark/watermark.png

;Изображения оригиналов и нарезок фоток блога
original_blog_dir = files/blog/
resized_blog_dir = files/blog_resized/

;Изображения оригиналов и нарезок фоток брендов
original_brands_dir = files/brands/
resized_brands_dir = files/brands_resized/

;Изображения оригиналов и нарезок фоток категории
original_categories_dir = files/categories/
resized_categories_dir = files/categories_resized/

;Изображения оригиналов и нарезок фоток доставки
original_deliveries_dir = files/deliveries/
resized_deliveries_dir = files/deliveries_resized/

;Изображения оригиналов и нарезок фоток способов оплаты
original_payments_dir = files/payments/
resized_payments_dir = files/payments_resized/

;Изображения баннеров
banners_images_dir = files/slides/
resized_banners_images_dir = files/slides_resized/

;Промо изображения
special_images_dir = files/special/

;Изображения пользователей
original_users_dir = files/user_img/

;Изображения вариантов товаров
original_variants_dir = files/originals/
resized_variants_dir = files/variants/

;Изображения типов товаров
original_types_dir = files/originals_products_types/
resized_types_dir = files/products_types/

[files]

;Директория хранения цифровых товаров
downloads_dir = files/downloads/
