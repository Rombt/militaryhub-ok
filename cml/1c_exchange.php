<?php

$start_time = microtime(true);

if (!empty($_SERVER['HTTP_USER_AGENT'])) {
    session_name(md5($_SERVER['HTTP_USER_AGENT']));
}
session_start();

require_once dirname(__DIR__).'/api/Okay.php';
require_once 'vendor/autoload.php';
$okay = new \Okay();
$integration_1c = new \Integration1C\Integration1C($okay, $start_time);
$response = new \Integration1C\Response();

// Аутентификация (лигинимся под менеджером из админки)
if ($integration_1c->check_auth() === false) {
    $response->add_header("WWW-Authenticate: Basic realm=\"1C integration {$okay->config->version} {$okay->config->version_type}\"");
    $response->add_header("HTTP/1.0 401 Unauthorized");
    $response->send();
    exit;
}

if ($okay->request->get('mode') == 'checkauth') {
    $response->set_content("success\n");
    $response->set_content(session_name()."\n");
    $response->set_content(session_id()."\n");
}

// Инициализация обмена
if ($okay->request->get('mode') == 'init') {
    
    $integration_1c->rrmdir($integration_1c->get_tmp_dir());
    
    // Очищаем все временнные данные
    $integration_1c->clear_storage();
    
    // Если нужно, очищаем базу
    if ($integration_1c->delete_all === true) {
        $integration_1c->flush_database();
    }
    
    $response->set_content("zip=no\n");
    $response->set_content("file_limit=1000000\n");
}

if ($okay->request->get('mode') == 'file' && in_array($okay->request->get('type'), array('catalog', 'sale'))) {
    
    $filename = $okay->request->get('filename');
    $xml_file = $integration_1c->get_full_path($filename);

    // Загружаем файл
    // $integration_1c->upload_file($xml_file);
    //!! rmbt интересуют все ошибки которые возникаю в процессе
    upload_file_with_logging($integration_1c, $xml_file);

    //!! rmbt для поиска проблемы смены статусов заказов сохраняем ВСЕ входящие файлы заказов до валидации 
    copy(
        $xml_file,
        __DIR__ . '/rmbt_order_backup/' . basename($xml_file)
    );

    // Если файл не валидный, прекращаем всё
    if ($integration_1c->validate_file($xml_file) === false) {
        $response->set_content("error import file\n");
        $response->send();
        exit;
    }

    copy($xml_file, __DIR__ . '/backup/' . basename($xml_file));
    
    // Здесь "success" отвечаем только когда импортирууется каталог, в случае с заказами, ответ отдаст клас импорта заказов
    if ($okay->request->get('type') == 'catalog') {
        $response->set_content("success\n");
    }
}

if ($okay->request->get('type') == 'sale') {
    if ($okay->request->get('mode') == 'success') {

        $okay->settings->last_1c_orders_export_date = date("Y-m-d H:i:s");
        
        $response->set_content("success\n");
        $response->send();
        exit;
    } elseif ($okay->request->get('mode') == 'query') {
        
        $export_factory = new \Integration1C\Export\ExportFactory\ExportOrdersFactory();
        $export = $export_factory->create_export($okay, $integration_1c);

        if ($xml = $export->export()) {

            //!! rmbt для поиска проблемы смены статусов заказов сохраняем ВСЕ исходящие файлы заказов 
            file_put_contents(
                __DIR__ . '/rmbt_order_export_backup/order_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.xml', $xml 
            );

            $okay->settings->last_1c_orders_export_date = date("Y-m-d H:i:s");
            $response->set_content("\xEF\xBB\xBF"); // Добавим BOM
            $response->set_content($xml);
            $response->add_header("Content-type: text/xml; charset=utf-8");
        }
        // } elseif ($okay->request->get('mode') == 'import') {
        //     $filename = $okay->request->get('filename');
        //     $import_factory = new \Integration1C\Import\ImportFactory\ImportOrdersFactory();
    }
    
    if ($okay->request->get('mode') == 'file') {
        $import_factory = new \Integration1C\Import\ImportFactory\ImportOrdersFactory();
        $okay->settings->last_1c_orders_export_date = date("Y-m-d H:i:s");
    }
    
    if ($okay->request->get('mode') == 'success') {
        $okay->settings->last_1c_orders_export_date = date("Y-m-d H:i:s");
    }
    
} elseif ($okay->request->get('type') == 'catalog') {
    
    if ($okay->request->get('mode') == 'import') {
        $filename = $okay->request->get('filename');
        // Определяем какую фабрику импорта создать, импорта товаров или предложений
        if (preg_match('~^.*import.*\.xml$~', $filename)) {
            $import_factory = new \Integration1C\Import\ImportFactory\ImportProductsFactory();
        } elseif (preg_match('~^.*(offers|prices|rests).*\.xml$~', $filename)) {
            $import_factory = new \Integration1C\Import\ImportFactory\ImportOffersFactory();
        }
    }
}

// Если определили фабрику импорта, тогда запустим импорт
if (!empty($import_factory) && $import_factory instanceof \Integration1C\Import\ImportFactory\ImportFactoryInterface) {
    $import = $import_factory->create_import($okay, $integration_1c);

    $filename = $okay->request->get('filename');
    $xml_file = $integration_1c->get_full_path($filename);

    //$response->set_content("\xEF\xBB\xBF"); // Добавим BOM
    //$response->add_header("Content-type: text/xml; charset=utf-8");

    // Запускаем импорт, и утанавливаем результат как контент ответа
    $result = $import->import($xml_file);
    $response->set_content($result);
}

$response->send();


//!! rmbt 
function upload_file_with_logging($integration_1c, $xml_file)
{
    $log_file = __DIR__ . '/rmbt_order_backup/rmbt_1c_upload.log';
    $start_time = microtime(true);

    $log = static function ($message) use ($log_file) {
        file_put_contents(
            $log_file,
            '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL,
            FILE_APPEND
        );
    };

    $log('===== START upload_file =====');
    $log('XML file: ' . $xml_file);

    $size_before = file_exists($xml_file) ? filesize($xml_file) : 0;
    $log('File size before: ' . $size_before . ' bytes');

    try {
        $integration_1c->upload_file($xml_file);

        $size_after = file_exists($xml_file) ? filesize($xml_file) : 0;

        $log('upload_file(): SUCCESS');
        $log('File size after: ' . $size_after . ' bytes');
        $log('Bytes added: ' . ($size_after - $size_before));
    } catch (\Throwable $e) {
        $log('upload_file(): ERROR');
        $log('Exception: ' . get_class($e));
        $log('Message: ' . $e->getMessage());
        $log('File: ' . $e->getFile() . ':' . $e->getLine());
        $log('Trace: ' . $e->getTraceAsString());

        throw $e;
    } finally {
        $log(
            'Duration: ' .
            round(microtime(true) - $start_time, 3) .
            ' sec'
        );
        $log('===== END upload_file =====');
    }
};
