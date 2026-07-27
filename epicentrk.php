<?php
/*
 * Author: Andrii K (andrey.kovt@gmail.com)
 * Date: 12.04.2021
 * Time: 17:20:21
 */
//ini_set('display_errors', 'on');

chdir(dirname(dirname(__DIR__)));
require_once('api/Okay.php');
$okay = new Okay();

$lang_id = false;
//$lang_id = $okay->languages->lang_id();
//$language = $okay->languages->get_language($lang_id);
//$lang_link = '';
//if (!empty($language)) {
//    $lang_link = $okay->languages->get_lang_link();
//}
$main_url = $okay->config->root_url;// . '/' . $lang_link;

$currencies = $okay->money->get_currencies(array('enabled' => 1));
$currency = reset($currencies);

// $categories = $okay->categories->get_categories();

// $categories_values = array();
// if ($okay->settings->feature_id['category_epicentrk']) {
//     $categories_values = $okay->features_values->get_features_values(array('feature_id' => $okay->settings->feature_id['category_epicentrk']));
// }

$px = ($lang_id ? 'l' : 'p');
$bx = ($lang_id ? 'lb' : 'b');
$vx = ($lang_id ? 'lv' : 'v');

$px = 'p';
$bx = 'b';
$vx = 'v';
$query = $okay->db->placehold("SELECT
    v.id as variant_id,
    v.currency_id,
    IFNULL(v.stock, ?) as stock, 
    (v.stock IS NULL) as infinity, 
    v.compare_price,
    v.sku,
    v.price,
    v.color,
    v.size,
    p.id as product_id,
    p.url,
    pc.category_id,
    MAX($bx.name) as vendor,
    IF(LENGTH(c.epicentrk_name) > 0, c.epicentrk_name, c.name) as category_name
    -- (SELECT fv.value FROM __features_values fv WHERE fv.id = pfv.value_id LIMIT 1) as category_name
FROM __variants v
INNER JOIN __products p ON v.product_id=p.id
INNER JOIN __brands b ON p.brand_id=b.id AND b.visible
INNER JOIN __images i ON i.product_id=p.id 
INNER JOIN __products_categories pc ON (p.id = pc.product_id AND pc.position=(SELECT MIN(position) FROM __products_categories WHERE product_id=p.id LIMIT 1))
INNER JOIN __categories c ON c.id=pc.category_id
LEFT JOIN __products_features_values pfv ON pfv.product_id = v.product_id AND pfv.value_id in (SELECT id FROM __features_values WHERE feature_id = ?)

WHERE 1
    AND p.visible
    AND (v.stock > 0 OR v.stock is NULL)
    AND v.feed_epicentr
    AND v.price > 0
GROUP BY v.id
ORDER BY v.position", $okay->settings->max_order_amount, $okay->settings->feature_id['category_epicentrk']);
$okay->db->query($query);

//var_dump($query);
$products = $okay->db->results();
$p_ids = array();
$variantIds = [];
$p_colors = array();
$categoryIds = [];
foreach ($products as $p) {
    if (!in_array($p->product_id, $p_ids)) {
        $p_ids[] = $p->product_id;
    }
    if (!in_array($p->variant_id, $variantIds)) {
        $variantIds[] = $p->variant_id;
    }
    if (!in_array($p->color, $p_colors)) {
        $p_colors[] = $p->color;
    }
    if (!in_array($p->category_id, $categoryIds)) {
        $categoryIds[] = $p->category_id;
    }
}

$p_images = array();
foreach ($okay->products->get_images(array('product_id' => $p_ids)) as $image) {
    $p_images[$image->product_id][] = $image->filename;
}

// $features_values = array();
// $products_values = array();

// if (!empty($p_ids)) {
//     foreach ($okay->features_values->get_features_values(array('product_id' => $p_ids, 'feed' => 'epicentrk')) as $fv) {
//         $features_values[$fv->id] = $fv;
//     }

//     foreach ($okay->features_values->get_products_values_id($p_ids) as $pv) {
//         if (!isset($products_values[$pv->product_id])) {
//             $products_values[$pv->product_id] = [];
//         }
//         if (!isset($products_values[$pv->product_id][$pv->feature_id])) {
//             $products_values[$pv->product_id][$pv->feature_id] = [];
//         }
//         $products_values[$pv->product_id][$pv->feature_id][] = $pv->value_id;
//     }
// }

$lang_ua = $okay->languages->get_language('ua');
$lang_ru = $okay->languages->get_language('ru');
if ($lang_ua && $lang_ru && !empty($p_ids) && !empty($variantIds)) {

    /*получим переводы для продуктов */
    $query = $okay->db->placehold("SELECT lang_id, product_id, name, annotation, automatic_translation_done FROM `__lang_products` WHERE `lang_id` in (?@) and `product_id` in (?@)",
        [$lang_ua->id, $lang_ru->id], $p_ids);
    $okay->db->query($query);
    $translatedProducts = [];

    foreach ($okay->db->results() as $result) {
        $translatedProducts[intval($result->product_id)][intval($result->lang_id)] = $result;
    };


    /*получим переводи для вариантов*/
    $query = $okay->db->placehold("SELECT lang_id, variant_id, name FROM `__lang_variants` WHERE `lang_id` in (?@) and `variant_id` in (?@)",
        [$lang_ua->id, $lang_ru->id], $variantIds);

    $okay->db->query($query);
    $translatedVariants = [];
    foreach ($okay->db->results() as $result) {
        $translatedVariants[intval($result->variant_id)][intval($result->lang_id)] = $result;
    };

}
header("Content-type: text/xml; charset=UTF-8");
echo pack('CCC', 0xef, 0xbb, 0xbf);
// Заголовок
echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n
       <!DOCTYPE yml_catalog SYSTEM \"shops.dtd\">\n
       <yml_catalog date=\"" . date('Y-m-d H:i') . "\">\n";
if ($products) {
    echo '<offers>';
    foreach ($products as $p) {
        if (
            !isset($translatedProducts[intval($p->product_id)][intval($lang_ru->id)]) ||
            empty($translatedProducts[intval($p->product_id)][intval($lang_ru->id)]->automatic_translation_done)
        ) {
            continue;
        }
        $print = '<offer id="' . $p->variant_id . '" available="' . ($p->stock > 0 || $p->stock === null ? "true" : "false") . '">';

        if (isset($currencies[$p->currency_id])) {
            $curr = $currencies[$p->currency_id];
        } else {
            $curr = $currency;
        }
        $p->price = round($okay->money->convert($p->price, $curr->id, false), 2);
        $p->price = $p->price * 100 / 80;

        $print .= '<price>' . number_format($p->price, 2, '.', '') . '</price>';

        if ($p->compare_price) {
            $p->compare_price = $p->compare_price * 100 / 80;
            $p->compare_price = round($okay->money->convert($p->compare_price, $curr->id, false), 2);
            $print .= '<price_old>' . number_format($p->compare_price, 2, '.', '') . '</price_old>';
        }

        /*Категория*/
        $print .= '<category>' . trim(htmlspecialchars($p->category_name)) . '</category>';


        if (isset($p_images[intval($p->product_id)])) {
            foreach ($p_images[intval($p->product_id)] as $image) {
                $print .= '<picture>' . $okay->design->resize_modifier($image, 800, 800, false, $okay->config->resized_images_dir) . '</picture>';
            }
        }
        /*Бренд*/
        $print .= '<vendor>' . trim(htmlspecialchars($p->vendor)) . '</vendor>';

        /*Название*/
        $print .= '<name lang="ru">' . trim(htmlspecialchars($translatedProducts[intval($p->product_id)][intval($lang_ru->id)]->name)) . '</name>';
        $print .= '<name lang="ua">' . trim(htmlspecialchars($translatedProducts[intval($p->product_id)][intval($lang_ua->id)]->name)) . '</name>';

        /*Описание*/
        $print .= '<description lang="ru">' . htmlspecialchars(strip_tags($translatedProducts[intval($p->product_id)][intval($lang_ru->id)]->annotation)) . '</description>';
        $print .= '<description lang="ua">' . htmlspecialchars(strip_tags($translatedProducts[intval($p->product_id)][intval($lang_ua->id)]->annotation)) . '</description>';


        // if (!empty($products_values[$product->product_id])) {
        //     if (is_array($products_values[$product->product_id])) {
        //         foreach ($products_values[$product->product_id] as $values_ids) {
        //             foreach ($values_ids as $i => $value_id) {
        //                 if (isset($features_values[$value_id])) {
        //                     $feature = $features_values[$value_id];
        //                     if ($feature->feature_id == $okay->settings->feature_id['category_epicentrk']) {
        //                         $print .= "\t\t\t<category>" . trim($value_id) . "</category>\n";
        //                     } elseif ( (in_array($feature->feature_id, $okay->settings->feature_id) && in_array($feature->translit, [$okay->translit_alpha($product->color), $okay->translit_alpha($product->size)]))
        //                         || (!in_array($feature->feature_id, $okay->settings->feature_id) && $i == count($values_ids)-1)
        //                     ) {
        //                         if (in_array($feature->feature_id, $okay->settings->feature_id)) {
        //                             $product_variant_name[] = htmlspecialchars($feature->value);
        //                         }
        //                         // print "\t\t\t<param name=\"" . htmlspecialchars($feature->name) . "\">" . htmlspecialchars($feature->value) . "</param>\n";
        //                     }
        //                 }
        //             }
        //         }
        //     } elseif (isset($features_values[$products_values[$product->product_id]])) {
        //         $feature = $features_values[$products_values[$product->product_id]];
        //         if ($feature->feature_id == $okay->settings->feature_id['category_epicentrk']) {
        //             $print .= "\t\t\t<category>" . trim($value_id) . "</category>\n";
        //         } else {
        //             // print "\t\t\t<param name=\"" . htmlspecialchars($feature->name) . "\">" . htmlspecialchars($feature->value) . "</param>\n";
        //         }
        //     }
        // }

        $print .= '</offer>';
        echo $print;
    }
    echo '</offers>';
}
echo "</yml_catalog>";
exit();
