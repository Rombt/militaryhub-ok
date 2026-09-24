<?php

/*
 * Author: Andrii K (andrey.kovt@gmail.com)
 * Date: 12.07.2019
 * Time: 20:23
 */

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

$categories = $okay->categories->get_categories();

//!! rmbt FeedManager
$feed_prom_discount = $okay->settings->feed_prom_discount ?? null;
$feed_prom_min_discount = $okay->settings->feed_prom_min_discount ?? null;
require_once $_SERVER['DOCUMENT_ROOT'] . '/ModulesCore/modules/FeedManager/controllers/FeedProm.php';


$px = ($lang_id ? 'l' : 'p');
$bx = ($lang_id ? 'lb' : 'b');
$vx = ($lang_id ? 'lv' : 'v');

$px = 'p';
$bx = 'b';
$vx = 'v';

// $okay->db->query("SET SQL_BIG_TABLES = 1");
// $okay->db->query("SET SQL_BIG_SELECTS = 0");
// $okay->db->query("SET SQL_BUFFER_RESULT = 1");
$okay->db->query(
    "SELECT
    v.id as variant_id,
    MAX($vx.name) as variant_name,
    v.currency_id,
    IFNULL(v.stock, ?) as stock,
    (v.stock IS NULL) as infinity,
    v.compare_price,
    v.sku,
    v.price,
    v.color,
    v.size,
    v.position as variant_position,
    p.id as product_id,
    MAX($px.name) as product_name,
    MAX($px.annotation) as annotation,
    MAX($px.meta_keywords) as keywords,
    p.url,
    MAX(pc.category_id) as category_id,
    MAX(i.filename) as image,
    MAX($bx.name) as vendor,
    MAX(c.name) as category_name,
    MAX(c.prom_category) as prom_category
FROM __variants v
INNER JOIN __products p ON v.product_id=p.id
INNER JOIN __brands b ON p.brand_id=b.id AND b.visible
LEFT JOIN __images i ON p.id=i.product_id
INNER JOIN __products_categories pc ON (
    p.id = pc.product_id
    AND pc.position=(
        SELECT MIN(position)
        FROM __products_categories
        WHERE product_id=p.id
        LIMIT 1
    )
)
INNER JOIN __categories c ON c.id=pc.category_id
WHERE 1
    AND p.visible
    AND (v.stock > 0 OR v.stock IS NULL)
    AND v.feed_prom
    AND v.price > 0
GROUP BY v.id
ORDER BY p.id DESC",
    $okay->settings->max_order_amount/*, intval($lang_id)*/
);
// -- LEFT JOIN __lang_products lp ON lp.product_id=p.id AND lp.lang_id = ?
// -- LEFT JOIN __lang_variants lv ON lv.variant_id=v.id AND lv.lang_id = ?
// -- LEFT JOIN __lang_brands lb ON lb.brand_id=b.id AND lb.lang_id = ?
$products = $okay->db->results();

$p_ids = array();
$p_colors = array();
foreach ($products as $p) {
    if (!in_array($p->product_id, $p_ids)) {
        $p_ids[] = $p->product_id;
    }
    if (!in_array($p->color, $p_colors)) {
        $p_colors[] = $p->color;
    }
}

$p_images = array();
foreach ($okay->products->get_images(array('product_id' => $p_ids)) as $image) {
    $p_images[$image->product_id][] = $image->filename;
}

$p_v_images = array();
if ($variants_images = $okay->variants->get_images(array('product_id' => $p_ids, 'color' => $p_colors, 'group_by' => 'variant'))) {
    foreach ($variants_images as $image) {
        if (!isset($p_v_images[$image->product_id])) {
            $p_v_images[$image->product_id] = array();
        }
        if (!isset($p_v_images[$image->product_id][$image->color])) {
            $p_v_images[$image->product_id][$image->color] = array();
        }
        $p_v_images[$image->product_id][$image->color][] = $image->filename;
    }
}

$features_values = array();
// foreach ($okay->features_values->get_features_values(array('product_id' => $p_ids)) as $fv) {
//     if (!isset($features_values[$fv->feature_id])) {
//         $features_values[$fv->feature_id] = array();
//     }
//     $features_values[$fv->feature_id][$fv->id] = $fv;
// }

$products_values = array();
// foreach ($okay->features_values->get_product_value_id($p_ids) as $pv) {
//     $products_values[$pv->product_id][] = $pv->value_id;
// }

if (!empty($p_ids)) {
    foreach ($okay->features_values->get_features_values(array('product_id' => $p_ids, 'feed' => 'prom')) as $fv) {
        $features_values[$fv->id] = $fv;
    }

    foreach ($okay->features_values->get_products_values_id($p_ids) as $pv) {
        $products_values[$pv->product_id][] = $pv->value_id;
    }
}

header("Content-type: text/xml; charset=UTF-8");
print(pack('CCC', 0xef, 0xbb, 0xbf));
print "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
print "<!DOCTYPE yml_catalog SYSTEM \"shops.dtd\">\n";
print "<yml_catalog date=\"" . date('Y-m-d H:i') . "\">\n";
print "<shop>\n";

print "\t<currencies>\n";
foreach ($currencies as $c) {
    print "\t\t<currency id=\"" . $c->code . "\" rate=\"" . $c->rate_to / $c->rate_from * $currency->rate_from / $currency->rate_to . "\" />\n";
}
print "\t</currencies>\n";

print "\t<categories>\n";
foreach ($categories as $category) {
    $portal = unserialize($category->prom_category);
    $attributes = $category->parent_id ? (" parentId=\"" . $category->parent_id . "\"") : '';
    $attributes .= isset($portal['id']) ? (" portal_id=\"" . $portal['id'] . "\"") : '';
    $attributes .= isset($portal['url']) ? (" portal_url=\"" . $portal['url'] . "\"") : '';
    print "\t\t<category id=\"". $category->id . "\"{$attributes}>" . $category->name . "</category>\n";
}
print "\t</categories>\n";

print "\t<offers>\n";
$images = array();
$now = strtotime(date("Y-m-d H:i:s"));
$prev_product_id = null;
foreach ($products as $product) {
    // $variant_url = '';
    // if ($prev_product_id === $product->product_id) {
    //     $variant_url = '?variant=' . $product->variant_id;
    // }
    // $prev_product_id = $product->product_id;

    print "\t\t<offer id=\"" . $product->variant_id . "\" available=\"" . (($product->stock > 0 || $product->stock === null) ? "true" : "false") . "\" type=\"model\" selling_type=\"r\" group_id=\"" . $product->product_id . "\">\n";
    print "\t\t\t<name>" . trim(htmlspecialchars($product->product_name)) . "</name>\n";
    print "\t\t\t<categoryId>" . trim(htmlspecialchars($product->category_id)) . "</categoryId>\n";

    $category = unserialize($product->prom_category);
    print "\t\t\t<portal_category_id>" . $category['id'] . "</portal_category_id>\n";
    print "\t\t\t<portal_category_url>" . $category['url'] . "</portal_category_url>\n";

    if (isset($currencies[$product->currency_id])) {
        $curr = $currencies[$product->currency_id];
    } else {
        $curr = $currency;
    }
    $price = $okay->money->convert($product->price, $curr->id, false);
    $compare_price = $okay->money->convert($product->compare_price, $curr->id, false);
    $compare_price = ($compare_price > $price) ? $compare_price : $price;

        //!! rmbt 
    if ($feed_prom_discount > 0) {
        $price = FeedProm::reduceDiscount($compare_price, $price, $feed_prom_discount, $feed_prom_min_discount);
    };


    print "\t\t\t<price>" . number_format($price, 2, '.', '') . "</price>\n";

     //!! rmbt 
    if ($compare_price > $price) {
        print "\t\t\t<oldprice>" . number_format($compare_price, 2, '.', '') . "</oldprice>\n";
    }


    print "\t\t\t<quantity_in_stock>" . round($product->stock) . "</quantity_in_stock>\n";
    print "\t\t\t<currencyId>" . $curr->code . "</currencyId>\n";

    if ($prev_product_id !== $product->product_id) {
        $images = array();
    }
    if (isset($p_v_images[$product->product_id]) && isset($p_v_images[$product->product_id][$product->color])) {
        $images[$product->color] = [
            'urls' => $p_v_images[$product->product_id][$product->color],
            'dir' => $okay->config->resized_variants_dir,
        ];
    } elseif (isset($p_v_images[$product->product_id])) {
        $images[$product->color] = [
            'urls' => $p_images[$product->product_id],
            'dir' => $okay->config->resized_images_dir,
        ];
    } elseif ($prev_product_id !== $product->product_id && isset($p_images[$product->product_id])) {
        $images[$product->color] = [
            'urls' => $p_images[$product->product_id],
            'dir' => $okay->config->resized_images_dir,
        ];
    }

    if (isset($images[$product->color])) {
        $dir = $images[$product->color]['dir'];
        foreach ($images[$product->color]['urls'] as $image) {
            print "\t\t\t<picture>" . $okay->design->resize_modifier($image, 800, 800, false, $dir) . "</picture>\n";
        }
    }

    $prev_product_id = $product->product_id;

    print "\t\t\t<vendor>" . trim(htmlspecialchars($product->vendor)) . "</vendor>\n";
    print "\t\t\t<model>" . trim(htmlspecialchars($product->product_name)) . "</model>\n";
    print "\t\t\t<vendorCode>" . htmlspecialchars(strip_tags($product->sku)) . "</vendorCode>\n";


    if (!empty($products_values[$product->product_id])) {
        $products_feature_count = 0;
        $products_feature_ids = [];
        $settings_feature_ids = $okay->settings->feature_id;
        foreach ($products_values[$product->product_id] as $value_id) {
            if (isset($features_values[$value_id])) {
                $feature = $features_values[$value_id];
                $key = array_search($feature->feature_id, $settings_feature_ids);
                $product->color = $okay->translit_alpha($product->color);
                $product->size = $okay->translit_alpha($product->size);


                if ($key && in_array($feature->translit, array($product->color, $product->size))) {
                    unset($settings_feature_ids[$key]);
                    $products_feature_ids[] = $feature->feature_id;

                    // if ($product->product_id == 43932) {
                    //     error_log('00.' . print_r([
                    //         '$settings_feature_ids' => $settings_feature_ids,
                    //         '$products_feature_ids' => $products_feature_ids,
                    //         '$key' => $key,
                    //         '$feature' => $feature,
                    //     ], true));
                    // }

                    print "\t\t\t<param name=\"" . htmlspecialchars($feature->name) . "\">" . htmlspecialchars($feature->value) . "</param>\n";
                    if (++$products_feature_count > 2) {
                        break;
                    }
                } elseif (!$key && !in_array($feature->feature_id, $products_feature_ids)) {
                    print "\t\t\t<param name=\"" . htmlspecialchars($feature->name) . "\">" . htmlspecialchars($feature->value) . "</param>\n";
                    if (++$products_feature_count > 2) {
                        break;
                    }
                }
            }
        }
    }

    print "\t\t\t<description>" . (!empty($product->annotation) ? ('<![CDATA[' . $product->annotation . ']]>') : '') . "</description>\n";
    // if ($product->stock > 0 || $product->stock === null) {
    //     $availability = "true";
    // } else {
    //     $availability = "false";
    // }
    // print "\t\t\t<available>" . $availability . "</available>\n";
    print "\t\t\t<keywords>" . trim(htmlspecialchars($product->product_name)) . "</keywords>\n";
    print "\t\t</offer>\n";
}
print "\t</offers>\n";

print "</shop>\n";
print "</yml_catalog>\n";
exit();
