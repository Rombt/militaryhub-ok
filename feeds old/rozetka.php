<?php

ini_set('max_execution_time', 120);
set_time_limit(120);

$debug_key = 'rz123dbg';
$isDebug = !empty($_GET['debug']) && $_GET['debug'] === $debug_key;
$debug_rejects = $isDebug ? [
    'invisible'               => [],
    'stock_zero'              => [],
    'price_zero'              => [],
    'category_blocked'        => [],
    'brand_blocked'           => [],
    'no_category_match'       => [],
    'no_direct_category_match'     => [],
    'no_final_category_match'     => [],
    'skipped_sql_other'       => [],
    'no_product_data'         => [],
    'no_images'         => [],
    'exported'                => [],
] : null;

chdir(__DIR__);
require_once 'api/Okay.php';
$okay = new Okay();

$lang_id  = $okay->languages->lang_id();
$language = $okay->languages->get_language($lang_id);
$lang_link = '';
if (!empty($language)) {
    $lang_link = $okay->languages->get_lang_link();
}
$main_url = $okay->config->root_url . '/' . $lang_link;

$currencies = $okay->money->get_currencies(array('enabled' => 1));
$currency = reset($currencies);

$featureIdRozetka = $okay->settings->feature_id['category_rozetka'];
$fid_size   = $okay->settings->feature_id['size'] ?? null;
$fid_color  = $okay->settings->feature_id['color'] ?? null;
$fid_rz_cat = $okay->settings->feature_id['category_rozetka'] ?? null;


//!!  rmbt FeedManager замена сокращений
require_once $_SERVER['DOCUMENT_ROOT'] . '/ModulesCore/modules/FeedManager/controllers/FeedRozetka.php';


header("Content-type: text/xml; charset=UTF-8");
print (pack('CCC', 0xef, 0xbb, 0xbf));
print "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
//print "<!DOCTYPE yml_catalog SYSTEM \"shops.dtd\">\n";
print "<yml_catalog date=\"" . date('Y-m-d H:i') . "\">\n";
print "<shop>\n";
print "\t<name>" . $okay->settings->site_name ."</name>\n";
print "\t<company>" . $okay->settings->company_name ."</company>\n";
print "\t<url>" . $okay->config->root_url . "</url>\n";

print "\t<currencies>\n";
foreach ($currencies as $c) {
    print "\t\t<currency id=\"" . $c->code . "\" rate=\"" . $c->rate_to/$c->rate_from*$currency->rate_from/$currency->rate_to . "\" />\n";
}
print "\t</currencies>\n";
print "\t<categories>\n";
if (empty($featureIdRozetka)) {
    print "\t</categories>\n";
    print "\t<offers>\n";
    print "\t</offers>\n";
    print "</shop>\n";
    print "</yml_catalog>\n";
    exit();
}
$categories_values = $okay->features_values->get_features_values(
    [
    'feature_id' => $featureIdRozetka,
    'rozetka_exclude' => 0,    
    ]
);
foreach ($categories_values as $category) {
    
    $categoryName = !empty($category->rozetka_name) ? $category->rozetka_name : $category->value;
    print "\t\t<category id=\"". $category->id . "\">" . htmlspecialchars($categoryName) . "</category>\n";
}
print "\t</categories>\n";
print "\t<offers>\n";
$whereRozetka = ' AND v.feed_rozetka = 1 ';
$settings_feed_rozetka_prom = $okay->settings->feed_prom_rozetka_relations;
if (empty($settings_feed_rozetka_prom)) {
    $whereRozetka = ' AND v.feed_rozetka ';
} else {
    switch ($settings_feed_rozetka_prom) {
    case 'from':
        $whereRozetka = ' AND v.feed_prom = 1 ';
        break;
    case 'equal':
        $whereRozetka = ' AND (v.feed_rozetka = 1 OR v.feed_prom = 1) ';
        break;
    case 'to':
    default:
        $whereRozetka = ' AND v.feed_rozetka = 1 ';
    }
}

// Фильтр по значению коллекции
// $feature_id = array_filter(
//     (array)$okay->settings->feature_id, function ($url) {
//         return $url === 'collection'; 
//     }, ARRAY_FILTER_USE_KEY
// );
// $features = empty($feature_id) ? [] : [ reset($feature_id) => '2025' ];
// if (!empty($features)) {
//     $features = array_map(
//         function ($value, $feature_id) use ($okay) {
//             return $okay->db->placehold("(lfv.feature_id = ? AND CAST(REGEXP_REPLACE(LEFT(lfv.value, 4), '[^[:digit:]]', '') as UNSIGNED) >= ?)", $feature_id, (int)$value);
//         }, $features, array_keys($features)
//     );
//     $feature_filter = implode(' AND ', $features);
//     $whereRozetka .= $okay->db->placehold(
//         " AND v.product_id in (SELECT pf.product_id FROM __products_features_values pf
//         LEFT JOIN __lang_features_values lfv ON lfv.feature_value_id = pf.value_id AND lfv.lang_id = ?
//         WHERE $feature_filter
//         GROUP BY pf.product_id HAVING COUNT(*) >= ?
//     )", $lang_id, count($features)
//     );
// }

// $okay->db->query(
//     "
//     SELECT 
//         v.id AS variant_id,
//         v.product_id
//     FROM __variants v
//     INNER JOIN __products p ON p.id = v.product_id
//     LEFT JOIN __brands b ON b.id = p.brand_id
//     INNER JOIN __products_categories pc ON pc.product_id = p.id
//     INNER JOIN __categories c ON c.id = pc.category_id
//     WHERE 
//         1
//         $whereRozetka
//         AND p.visible
//         AND (v.stock > 0 OR v.stock is NULL)
//         AND v.price > 0
//         AND c.rozetka_exclude != 1
//         AND (p.brand_id IS NULL OR p.brand_id = 0 OR (b.visible = 1 AND b.rozetka_exclude != 1))
//     GROUP BY v.id
// "
// );

$okay->db->query(
    "
    SELECT 
        v.id AS variant_id,
        v.product_id
    FROM __variants v
    INNER JOIN __products p ON p.id = v.product_id
    LEFT JOIN __brands b ON b.id = p.brand_id
    INNER JOIN __products_categories pc ON pc.product_id = p.id
    INNER JOIN __categories c ON c.id = pc.category_id
    WHERE 
        1
        $whereRozetka
        AND p.visible
        AND (v.stock > 0 OR v.stock IS NULL)
        AND v.price > 0
        AND c.rozetka_exclude != 1
        AND (
            p.brand_id IS NULL
            OR p.brand_id = 0
            OR (
                b.visible = 1
                AND (
                    b.rozetka_exclude IS NULL
                    OR b.rozetka_exclude != 1
                )
            )
        )
    GROUP BY v.id
"
);
$rows = $okay->db->results();
$productIds = [];
$variantIds = [];

foreach ($rows as $row) {
    $variantIds[] = $row->variant_id;
    $productIds[$row->product_id][] = $row->variant_id;
}

$px = ($lang_id ? 'l' : 'p');
$bx = $lang_id ? 'lb' : 'b';
$vx = $lang_id ? 'lv' : 'v';
foreach (array_chunk(array_keys($productIds), 500) as $chunk) {
    $okay->db->query(
        "
        WITH RECURSIVE cat_tree AS (
            SELECT 
                c.id,
                c.parent_id,
                c.rozetka_category_value_id,
                c.id AS root_category_id
            FROM __categories c
            INNER JOIN __products_categories pc ON pc.category_id = c.id
            WHERE pc.product_id IN (?@)
        
            UNION ALL
        
            SELECT 
                c2.id,
                c2.parent_id,
                c2.rozetka_category_value_id,
                ct.root_category_id
            FROM __categories c2
            JOIN cat_tree ct ON ct.parent_id = c2.id
        )

        SELECT 
            p.id,
            p.url,
            p.visible,
        
            COALESCE(lp.name, p.name) AS name,
            COALESCE(lp.annotation, p.annotation) AS annotation,
            COALESCE(lp.description, p.description) AS description,
            COALESCE(lp.meta_keywords, p.meta_keywords) AS meta_keywords,
      
            p.brand_id,
            COALESCE(lb.name, b.name) AS vendor,
    
            pc.category_id,
            c.rozetka_name AS category_name,
            c.rozetka_category_value_id AS direct_rozetka_category_value_id,
            (
                SELECT rozetka_category_value_id
                FROM cat_tree
                WHERE cat_tree.root_category_id = c.id
                  AND rozetka_category_value_id IS NOT NULL
                ORDER BY id
                LIMIT 1
            ) AS final_rozetka_category_value_id
        FROM __products p
        
        LEFT JOIN __lang_products lp 
            ON lp.product_id = p.id AND lp.lang_id = ?
        
        LEFT JOIN __brands b
            ON b.id = p.brand_id
        
        LEFT JOIN __lang_brands lb
            ON lb.brand_id = b.id AND lb.lang_id = ?
        
        LEFT JOIN (
            SELECT product_id, MIN(position) AS pos
            FROM __products_categories
            GROUP BY product_id
        ) pc2 ON pc2.product_id = p.id
        
        LEFT JOIN __products_categories pc
            ON pc.product_id = p.id AND pc.position = pc2.pos
        
        LEFT JOIN __categories c
            ON c.id = pc.category_id
        
        WHERE p.id IN (?@)
    ", $chunk, $lang_id, $lang_id, $chunk
    );

    $products = $okay->db->results();

    foreach ($products as $p) {
        $productData[$p->id] = $p;
    }

    $features_values = [];
    $products_values = [];
    $productsWithRozetkaCategory = [];
    $imagesProduct = [];
    $imagesVariant = [];

    if ($lang_id) {
        $okay->db->query(
            "
            SELECT 
                fv.id,
                fv.feature_id,
                fv.position,
                fv.rozetka_name,
                
                COALESCE(lfv.value, fv.value) AS value,
                COALESCE(lfv.translit, fv.translit) AS translit,

                COALESCE(lf.name, f.name) AS feature_name

            FROM __features_values fv

            LEFT JOIN __features f
                ON f.id = fv.feature_id

            LEFT JOIN __lang_features_values lfv
                ON lfv.feature_value_id = fv.id AND lfv.lang_id = ?

            LEFT JOIN __lang_features lf
                ON lf.feature_id = f.id AND lf.lang_id = ?

            INNER JOIN __products_features_values pfv
                ON pfv.value_id = fv.id AND pfv.product_id IN (?@)

            ORDER BY f.position, fv.position
        ", $lang_id, $lang_id, $chunk
        );
    } else {
        $okay->db->query(
            "
            SELECT 
                fv.id,
                fv.feature_id,
                fv.position,
                fv.rozetka_name,
                fv.value,
                fv.translit,
                f.name AS feature_name

            FROM __features_values fv

            LEFT JOIN __features f
                ON f.id = fv.feature_id

            INNER JOIN __products_features_values pfv
                ON pfv.value_id = fv.id AND pfv.product_id IN (?@)

            ORDER BY f.position, fv.position
        ", $chunk
        );
    }
    $values = $okay->db->results();
    foreach ($values as $val) {
        $features_values[$val->id] = $val;
    }

    $okay->db->query(
        "
        SELECT product_id, value_id, fv.feature_id
        FROM __products_features_values pfv
        INNER JOIN __features_values fv
            ON fv.id = pfv.value_id
        WHERE pfv.product_id IN (?@)
    ", $chunk
    );

    $rows = $okay->db->results();

    foreach ($rows as $r) {

        if (!isset($products_values[$r->product_id])) {
            $products_values[$r->product_id] = [];
        }

        if (!isset($products_values[$r->product_id][$r->feature_id])) {
            $products_values[$r->product_id][$r->feature_id] = [];
        }

        $products_values[$r->product_id][$r->feature_id][] = $r->value_id;

        if ($r->feature_id == $featureIdRozetka) {
            $productsWithRozetkaCategory[$r->product_id] = true;
        }
    }

    $variantsData = [];

    $okay->db->query(
        "
        SELECT 
            v.id,
            v.product_id,
            v.currency_id,
            v.stock,
            (v.stock IS NULL) AS infinity,
            v.compare_price,
            v.promo_price,
            v.sku,
            v.price,
            v.color,
            v.size,
            v.position,
    
            COALESCE(lv.name, v.name) AS variant_name
    
        FROM __variants v
    
        LEFT JOIN __lang_variants lv 
            ON lv.variant_id = v.id AND lv.lang_id = ?
    
        WHERE v.product_id IN (?@)
    ", $lang_id, $chunk
    );

    $vars = $okay->db->results();

    $chunkVariantIds = [];
    foreach ($chunk as $pid) {
        if (!empty($productIds[$pid])) {
            foreach ($productIds[$pid] as $vid) {
                $chunkVariantIds[$vid] = true;
            }
        }
    }

    foreach ($vars as $v) {
        if (empty($chunkVariantIds[$v->id])) {
            continue;
        }
        $variantsData[$v->id] = $v;
    }

    $part = $okay->products->get_images(
        [
        'product_id' => $chunk
        ]
    );

    foreach ($part as $img) {
        $imagesProduct[$img->product_id][] = $img->filename;
    }

    unset($part);

    $part = $okay->variants->get_images(
        [
        'product_id' => $chunk,
        'group_by'   => 'variant'
        ]
    );

    if ($part) {
        foreach ($part as $img) {

            $prod = $img->product_id;
            $color = $img->color;

            if (!isset($imagesVariant[$prod])) {
                $imagesVariant[$prod] = [];
            }
            if (!isset($imagesVariant[$prod][$color])) {
                $imagesVariant[$prod][$color] = [];
            }

            $imagesVariant[$prod][$color][] = $img->filename;
        }
    }

    unset($part);

    $seen = [];
    foreach ($variantsData as $variant_id => $v) {
        $pid = $v->product_id;
        $categoryIdRozetka = null;

        if (empty($productData[$pid])) {
            if ($isDebug) {
                $debug_rejects['no_product_data'][] = [
                    'pid' => $pid,
                    'vid' => $variant_id
                ];
            }
            continue;
        }

        if (!empty($productsWithRozetkaCategory[$pid])) {
            $categoryIdRozetka = reset($products_values[$pid][$fid_rz_cat]);
        } elseif (!empty($productData[$pid]->direct_rozetka_category_value_id)) {
            $categoryIdRozetka = $productData[$pid]->direct_rozetka_category_value_id;
        } elseif (!empty($productData[$pid]->final_rozetka_category_value_id)) {
            $categoryIdRozetka = $productData[$pid]->final_rozetka_category_value_id;
        } else {
            if ($isDebug) {
                $debug_rejects['no_category_match'][] = [
                    'pid' => $pid,
                    'vid' => $variant_id
                ];
                $debug_rejects['category_without_rozetka'][$productData[$pid]->category_id] = true;
            }
            continue;
        }

        $product = $productData[$pid];

        if (empty($product->visible)) {
            $v->stock = 0;
        }

        $variant_url = $okay->config->root_url . '/products/' . $product->url;

        if (count($productIds[$pid]) > 1) {
            $variant_url .= '?variant=' . $variant_id;
        }

        if (!empty($currencies[$v->currency_id])) {
            $curr = $currencies[$v->currency_id];
        } else {
            $curr = $currency;
        }

        $price = $okay->money->convert($v->price, $curr->id, false);
        $price = round($price, 2);

        //!! rmbt 
        // добавил && $v->stock >= 2 && $v->promo_price < $v->price в соответствии с условиями Розетки
        if (!empty($v->promo_price) && $v->stock >= 2 && $v->promo_price < $v->price) {
            $promo_price = $okay->money->convert($v->promo_price, $curr->id, false);
            $promo_price = round($promo_price, 2);
        } else {
            $promo_price = null;
        }

        //!! rmbt
        $price_old = 0;
        if (!empty($v->compare_price) && $v->compare_price > $v->price) {
            $price_old = $okay->money->convert($v->compare_price, $curr->id, false);
            $price_old = round($price_old, 2);
        }

        $pictures = [];

        if (!empty($imagesVariant[$pid]) && !empty($imagesVariant[$pid][$v->color])) {
            $pictures = $imagesVariant[$pid][$v->color];
            $img_dir  = $okay->config->resized_variants_dir;
        } elseif (!empty($imagesProduct[$pid])) {
            $pictures = $imagesProduct[$pid];
            $img_dir  = $okay->config->resized_images_dir;
        } else {
            $img_dir  = $okay->config->resized_images_dir;
        }

        if (empty($pictures)) {
            if ($isDebug) {
                $debug_rejects['no_images'][] = [
                    'pid' => $pid,
                    'vid' => $variant_id
                ];
            }
            continue;
        }

        $product_variant_name = [];

        $color_translit = $okay->translit_alpha($v->color);
        $size_translit  = $okay->translit_alpha($v->size);

        $productParams = '';

        if (!empty($products_values[$pid])) {
            foreach ($products_values[$pid] as $feature_id => $value_ids) {
                if ($feature_id == $fid_rz_cat) {
                    continue;
                }
                foreach ($value_ids as $value_id) {
                    if (empty($features_values[$value_id])) {
                        continue;
                    }

                    $fv = $features_values[$value_id];

                    if ($feature_id == $fid_color || $feature_id == $fid_size) {
                        if ($fv->translit == $color_translit
                            || $fv->translit == $size_translit
                        ) {

                            //!! rmbt FeedManager замена сокращений 
                            // $product_variant_name[] = htmlspecialchars($fv->value);
                            if (class_exists(FeedRozetka::class)) {
                                $product_variant_name[] = FeedRozetka::normalizeFeatureValue($fv->feature_name, $fv->value, $okay->db);
                            } else {
                                $product_variant_name[] = htmlspecialchars($fv->value);
                            }

                            
                        
                        } else {
                            continue;
                        }
                    }

                    $paramName  = htmlspecialchars($fv->feature_name);
                    
                    // !! rmbt FeedManager замена сокращений
                    // $paramValue = htmlspecialchars($fv->value);

                    if (class_exists(FeedRozetka::class)) {
                        $paramValue = FeedRozetka::normalizeFeatureValue($fv->feature_name, $fv->value, $okay->db);
                    } else {
                        $paramValue = htmlspecialchars($fv->value);
                    }

                    $productParams .= "\t\t\t<param name=\"{$paramName}\">{$paramValue}</param>\n";
                }
            }
        }

        //!! rmbt 
        $product_name_normalized = preg_replace('/\s+/u', ' ', $product->name);
        
        $name = trim(
            $product_name_normalized . (!empty($product_variant_name)
            ? (' ' . implode(', ', $product_variant_name))
            : '')
        );

        $pid = $v->product_id;

        $colorKey = $okay->translit_alpha(trim((string)$v->color));
        $sizeKey  = $okay->translit_alpha(trim((string)$v->size));
        $key = $colorKey . '|' . $sizeKey;

        if ($key === '|') {
            $key = 'vid:' . (int)$variant_id;
        }

        if (!isset($seen[$pid])) {
            $seen[$pid] = [];
        }
        if (isset($seen[$pid][$key])) {
            if ($isDebug) {
                $debug_rejects['duplicates'][] = ['pid' => $pid, 'vid' => $variant_id, 'key' => $key];
            }
            continue;
        }
        $seen[$pid][$key] = true;

        $available = ($v->stock > 0 || $v->infinity) ? 'true' : 'false';

        print "\t\t<offer id=\"" . $variant_id . "\" available=\"" . $available . "\">\n";
        print "\t\t\t<url>" . htmlspecialchars($variant_url) . "</url>\n";

        //!! rmbt 01.09.26 принято решение отключить все возможные скидки на Розетке и продавать по максимально возможной цене
        print "\t\t\t<price>" . number_format(max((float)$price, (float)$price_old, (float)$promo_price), 2, '.', '') . "</price>\n";

        // print "\t\t\t<price>" . number_format($price, 2, '.', '') . "</price>\n";
        // if (!empty($promo_price)) {
        //     print "\t\t\t<promo_price>" . number_format($promo_price, 2, '.', '') . "</promo_price>\n";
        // }       

        // //!! rmbt
        // if (!empty($price_old)) {
        //     print "\t\t\t<price_old>" . number_format($price_old, 2, '.', '') . "</price_old>\n";
        // }

        print "\t\t\t<currencyId>" . $curr->code . "</currencyId>\n";
        print "\t\t\t<categoryId>" . (int)$categoryIdRozetka . "</categoryId>\n";
        print "\t\t\t<vendor>" . htmlspecialchars($product->vendor) . "</vendor>\n";
       
        //!! rmbt
        if (!empty($v->sku)) {
            print "\t\t\t<article>" . htmlspecialchars($v->sku) . "</article>\n";
        }

        print "\t\t\t<model>" . htmlspecialchars($product->name) . "</model>\n";
        print "\t\t\t<stock_quantity>" . (int)$v->stock . "</stock_quantity>\n";
        print $productParams;

        //!! rmbt
        $pictures = array_slice($pictures, 0, 15);
        foreach ($pictures as $img) {
            $url = $okay->design->resize_modifier($img, 800, 800, false, $img_dir);
            print "\t\t\t<picture>" . htmlspecialchars($url) . "</picture>\n";
        }

        if (!empty($product->annotation)) {
            print "\t\t\t<description><![CDATA[" . $product->annotation . "]]></description>\n";
            
            //!! rmbt
            print "\t\t\t<description_ua><![CDATA[" . $product->annotation . "]]></description_ua>\n";
        } elseif(!empty($product->description)) {
            print "\t\t\t<description><![CDATA[" . $product->description . "]]></description>\n";
            print "\t\t\t<description_ua><![CDATA[" . $product->description . "]]></description_ua>\n";
            
        }else{
            print "\t\t\t<description></description>\n";
            
            //!! rmbt
            print "\t\t\t<description_ua></description_ua>\n";        
        }

        print "\t\t\t<name>" . htmlspecialchars($name) . "</name>\n";

        //!! rmbt
        print "\t\t\t<name_ua>" . htmlspecialchars($name) . "</name_ua>\n";

        print "\t\t</offer>\n";
        if ($isDebug) {
            $debug_rejects['exported'][] = [
                'pid' => $pid,
                'vid' => $variant_id
            ];
        }
    }

    unset($features_values, $products_values, $productsWithRozetkaCategory, $variantsData, $imagesProduct, $imagesVariant);
}

print "\t</offers>\n";
print "</shop>\n";

if ($isDebug && false) {

    if (false) {

        $okay->db->query(
            "
        SELECT v.id
        FROM __variants v
        WHERE 
            1
            $whereRozetka
            AND v.id NOT IN (?@) 
    ", $variantIds
        );

        $debugVariantIds = $okay->db->results('id');

        foreach (array_chunk($debugVariantIds, 300) as $chunk) {
            $okay->db->query(
                "
            WITH RECURSIVE cat_tree AS (
                SELECT 
                    c.id,
                    c.parent_id,
                    c.rozetka_category_value_id,
                    c.id AS root_category_id
                FROM __categories c
            
                UNION ALL
            
                SELECT 
                    c2.id,
                    c2.parent_id,
                    c2.rozetka_category_value_id,
                    ct.root_category_id
                FROM __categories c2
                JOIN cat_tree ct ON ct.parent_id = c2.id
            )
            SELECT
                v.id AS variant_id,
                v.product_id,
        
                p.visible,
                v.stock,
                v.price,
                v.promo_price,
        
                IFNULL(b.rozetka_exclude, 0) AS brand_blocked,
                IFNULL(c.rozetka_exclude, 0) AS category_blocked,
        
                (
                    SELECT COUNT(*)
                    FROM __products_features_values pfv
                    WHERE pfv.product_id = p.id
                      AND pfv.feature_id = ?
                ) AS has_rozetka_feature,
        
                c.rozetka_category_value_id AS direct_rozetka_category_value_id,
                (
                    SELECT rozetka_category_value_id
                    FROM cat_tree
                    WHERE cat_tree.root_category_id = c.id
                      AND rozetka_category_value_id IS NOT NULL
                    ORDER BY cat_tree.id
                    LIMIT 1
                ) AS final_rozetka_category_value_id
        
            FROM __variants v
        
            INNER JOIN __products p ON p.id = v.product_id
            LEFT JOIN __brands b ON b.id = p.brand_id
            
            LEFT JOIN (
                SELECT product_id, MIN(position) AS pos
                FROM __products_categories
                GROUP BY product_id
            ) pc2 ON pc2.product_id = p.id
        
            LEFT JOIN __products_categories pc ON pc.product_id = p.id AND pc.position = pc2.pos
            LEFT JOIN __categories c ON c.id = pc.category_id
        
            WHERE v.id IN (?@)
        
            GROUP BY v.id
        ", $fid_rz_cat, $chunk
            );

            $debugRows = $okay->db->results();

            foreach ($debugRows as $row) {

                if (!$row->visible) {
                    $debug_rejects['invisible'][] = $row;
                    continue;
                }

                if ($row->brand_blocked) {
                    $debug_rejects['brand_blocked'][] = $row;
                    continue;
                }

                if ($row->category_blocked) {
                    $debug_rejects['category_blocked'][] = $row;
                    continue;
                }

                if ($row->stock <= 0) {
                    $debug_rejects['stock_zero'][] = $row;
                    continue;
                }

                if ($row->price <= 0) {
                    $debug_rejects['price_zero'][] = $row;
                    continue;
                }

                if (!$row->has_rozetka_feature 
                    && !$row->direct_rozetka_category_value_id 
                    && !$row->final_rozetka_category_value_id
                ) {
                    $debug_rejects['no_category_match'][] = $row;
                    continue;
                }

                if (!$row->direct_rozetka_category_value_id) {
                    $debug_rejects['no_direct_category_match'][] = $row;
                }

                if (!$row->final_rozetka_category_value_id 
                    && $row->direct_rozetka_category_value_id
                ) {
                    $debug_rejects['no_final_category_match'][] = $row;
                }

                $debug_rejects['skipped_sql_other'][] = $row;
            }
        }
    }


    echo "<pre>==== FEED DEBUG SUMMARY ====\n\n";
    foreach ($debug_rejects as $reason => $rows) {
        echo strtoupper($reason) . ": " . count($rows) . "\n";
    }
    echo "\nSaved: rozetka_feed_debug.json\n";
    echo "</pre>";
    file_put_contents(
        $okay->config->root_dir . '/log/rozetka/rozetka_feed_debug.json',
        json_encode($debug_rejects, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

}

print "</yml_catalog>\n";

exit();
