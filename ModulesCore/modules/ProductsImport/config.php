<?php

return [
    'source' => [
        'host'     => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'local_sportfly',
    ],

    // Prefixes of physical SQL tables in the two independent databases.
    'tables' => [
        'source_prefix' => 'sfly_',
        'target_prefix' => 'mh_',
    ],

    'import' => [
        'source_category_id' => 250,
        'include_subcategories' => true,
        // 0 = все товары; иначе максимум указанное количество.
        'product_limit' => 100,
        // Пропустить указанное количество товаров после сортировки.
        'product_offset' => 0,
        // true = сначала новые по created, false = сначала старые.
        'newest_first' => true,
        // Только для локальной разработки: удаляет ВЕСЬ каталог.
        'allow_full_reset' => true,
        'rollback_max_rows_per_table' => 100000,
    ],

    // Development only: disable SSL verification locally; enable it in production.
    'download' => [
        'ssl_verify' => false,
        'ca_bundle' => '',
    ],

    'filesystem' => [
        'source_files_url'  => 'https://sportfly.com.ua',
        'target_files_root' => 'F:\\web\\local.militaryhub-ok',
    ],
];
