<?php

require_once('../api/Okay.php');

$okay = new Okay();

$object = $_GET['object'] ?? '';
$file   = $_GET['file'] ?? '';

if ($object === '' || $file === '') {
    http_response_code(400);
    exit('Bad request');
}

/**
 * Мінімальна санітизація:
 * - object: тільки [a-z0-9_-]
 * - file: прибрати path traversal
 */
$object = preg_replace('~[^a-z0-9_\-]~i', '', $object);
$file   = str_replace(["\0", "\\", "../", "..\\"], '', $file); // базовий захист
$file   = ltrim($file, '/'); // щоб не було абсолютних шляхів

// Мапінг директорій
$map = [
    'blog_resized'       => [$okay->config->original_blog_dir,       $okay->config->resized_blog_dir],
    'brands_resized'     => [$okay->config->original_brands_dir,     $okay->config->resized_brands_dir],
    'categories_resized' => [$okay->config->original_categories_dir, $okay->config->resized_categories_dir],
    'deliveries_resized' => [$okay->config->original_deliveries_dir, $okay->config->resized_deliveries_dir],
    'payments_resized'   => [$okay->config->original_payments_dir,   $okay->config->resized_payments_dir],
    'slides_resized'     => [$okay->config->banners_images_dir,      $okay->config->resized_banners_images_dir],
    'variants'           => [$okay->config->original_variants_dir,   $okay->config->resized_variants_dir],
    'products_types'     => [$okay->config->original_types_dir,      $okay->config->resized_types_dir],
];

if (!isset($map[$object]) && $object !== 'products') {
    http_response_code(404);
    exit('Not found');
}

[$original_img_dir, $resized_img_dir] = $map[$object] ?? [null, null];

$resized_filename = $okay->image->resize($file, $original_img_dir, $resized_img_dir);

if (!$resized_filename) {
    http_response_code(404);
    exit('Image not found');
}

/**
 * Retry: файл може бути 0 байт / ще дописується
 */
$tries = 5;
for ($i = 0; $i < $tries; $i++) {
    clearstatcache(true, $resized_filename);

    if (is_file($resized_filename) && is_readable($resized_filename) && filesize($resized_filename) > 0) {
        break;
    }

    usleep(150000); // 150ms
}

clearstatcache(true, $resized_filename);

if (!is_file($resized_filename) || !is_readable($resized_filename) || filesize($resized_filename) <= 0) {
    // якщо згенерувався “порожняк” — краще прибрати, щоб не кешувався
    if (is_file($resized_filename) && filesize($resized_filename) === 0) {
        @unlink($resized_filename);
    }
    http_response_code(503);
    exit('Image is generating, try again');
}

// MIME
$mime_type = 'application/octet-stream';
if (function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo) {
        $mime_type = finfo_file($finfo, $resized_filename) ?: $mime_type;
        finfo_close($finfo);
    }
}
if (strncmp($mime_type, 'image/', 6) !== 0) {
    http_response_code(415);
    exit('Unsupported Media Type');
}

header('Content-Type: ' . $mime_type);
header('Content-Length: ' . filesize($resized_filename));
// (опціонально) кеш
header('Cache-Control: public, max-age=31536000, immutable');

readfile($resized_filename);
exit;
