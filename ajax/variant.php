<?php

if (!empty($_SERVER['HTTP_USER_AGENT'])){
    session_name(md5($_SERVER['HTTP_USER_AGENT']));
}
session_start();
require_once('../api/Okay.php');
define('IS_CLIENT', true);

class VariantAjax extends Okay {
    public function fetch() {
        if (!$this->request->method('post')) {
            return false;
        }

        $isDebug = $this->request->post('debug', 'integer', 0);

        $language = $this->languages->get_language($this->languages->lang_id());
        $this->translations->debug = (bool)$this->config->debug_translation;
        $translations = $this->translations->get_translations(array('lang' => $language->label));

        $product_id = $this->request->post('product_id', 'integer');
        $variant_id = $this->request->post('variant_id', 'integer');

        $variants = [];
        $variants_positions = [];
        $values_aliases = [];
        $colors_ids = [];
        $sizes_ids = [];

        foreach ($this->variants->get_variants(array('product_id' => $product_id, 'sort' => 'image')) as $v) {
            $variants[$v->id] = $v;
            $variants_positions[$v->position] = $v->id;

            $color_translit = $this->translit_alpha($v->color);
            $colors_ids[$color_translit] = $v->color;
            $values_aliases[] = $color_translit;

            $size_translit = $this->translit_alpha($v->size);
            $sizes_ids[$size_translit] = $v->size;
            $values_aliases[] = $size_translit;
        }

        $first_variant_id = $variants_positions[min(array_keys($variants_positions))];
        $first_current_variant = $variants[$first_variant_id];
        $current_variant = $variants[$variant_id];

        // Картинки товара
        $product_images = $this->products->get_images(array('product_id' => $product_id));

        foreach ($product_images as &$image) {
            if (!isset($first_current_variant->images)) {
                $first_current_variant->images = [];
            }
            $first_current_variant->images[] = $this->design->resize_modifier($image->filename, 870, 870);
        }

        // Изображения вариантов товара
        $resizedImages = [
            'images' => [],
            'images_sm' => [],
            'images_lg' => [],
        ];
        if ($variants_images = $this->variants->get_images(array('product_id' => $product_id, 'color' => $current_variant->color, 'group_by' => 'variant'))) {
            foreach ($variants_images as $image) {
                $resizedImages['images'][] = $this->design->resize_modifier($image->filename, 870, 870, false, $this->config->resized_variants_dir);
                $resizedImages['images_sm'][] = $this->design->resize_modifier($image->filename, 100, 100, false, $this->config->resized_variants_dir);
                $resizedImages['images_lg'][] = $this->design->resize_modifier($image->filename, 1800, 1800, false, $this->config->resized_variants_dir);
            }
            foreach ($variants as &$variant) {
                if (($variant->color == $current_variant->color)) {
                    $variant->images = $resizedImages['images'];
                    $variant->images_sm = $resizedImages['images_sm'];
                    $variant->images_lg = $resizedImages['images_lg'];
                    $variant->image = reset($variant->images);
                }
            }
        }

        $product_values_aliases = [];
        if ($features_values = $this->features_values->get_features_values(array('translit' => $values_aliases))) {
            foreach ($features_values as $feature_value) {
                if (in_array($feature_value->feature_id, $this->settings->feature_id)) {
                    $product_values_aliases[$feature_value->translit] = $feature_value->value;
                }
            }
        }

        $temp_variants = [];
        $colors_sizes = [];
        foreach ($variants as &$variant) {
            $color_translit = $this->translit_alpha($variant->color);
            $size_translit = $this->translit_alpha($variant->size);

            $bonuses = $this->money->convert($variant->bonuses);
            $temp_variant = (object)[
                'id'            => (int)$variant->id,
                'product_id'    => (int)$variant->product_id,
                'sku'           => (string)$variant->sku,
                'size'          => isset($product_values_aliases[$size_translit]) ? (string)$product_values_aliases[$size_translit] : (string)$v->size,
                'color'         => isset($product_values_aliases[$color_translit]) ? (string)$product_values_aliases[$color_translit] : (string)$v->color,
                'price'         => (float)$variant->price,
                'compare_price' => (float)$variant->compare_price,
                'stock'         => (int)$variant->stock,
                'name'          => (string)$variant->name,
                'units'         => (string)$variant->units,
                'image'         => !empty($variant->images) ? reset($variant->images) : '',
                'images'        => (array)$variant->images,
                'images_sm'     => (array)$variant->images_sm,
                'images_lg'     => (array)$variant->images_lg,
                'enabled'       => (boolean)($variant->color == $current_variant->color),
                'bonuses'       => $this->money->convert($bonuses, null, false) . ' ' . $this->design->plural_modifier($bonuses, $translations->product_bonus, $translations->product_bonuses, $translations->product_of_bonuses),
            ];

            if (($temp_variant->enabled === true) || ($variant->color == $current_variant->color)) {
                $colors_sizes[] = $temp_variant->size;
            }

            $temp_variants[$temp_variant->id] = $temp_variant;
        }

        $variants = [];
        foreach ($temp_variants as &$variant) {
            if ((in_array($variant->size, $colors_sizes) && $variant->enabled) || ($variant->id == $current_variant->id)) {
                $variants[] = $variant;
            }
        }

        foreach ($temp_variants as &$variant) {
            if (!in_array($variant->size, $colors_sizes)) {
                $size_translit = $this->translit_alpha($variant->size);
                $variants[] = [
                    'size' => isset($product_values_aliases[$size_translit]) ? (string)$product_values_aliases[$size_translit] : (string)$v->size,
                    'enabled' => false,
                ];
            }
        }

        return [
            'success' => 1,
            'data' => $variants,
        ];
    }
}

$results = new VariantAjax();
if ($result = $results->fetch()) {
    header("Content-Type: application/json; charset=UTF-8");
    header("Cache-Control: must-revalidate");
    header("Pragma: no-cache");
    header("Expires: -1");
    print json_encode($result);
}
exit();
