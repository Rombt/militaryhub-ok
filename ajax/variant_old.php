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

        $variant_id = $this->request->post('variant_id', 'integer');
        $product_id = $this->request->post('product_id', 'integer');

        $variants = [];
        foreach ($this->variants->get_variants(array('product_id' => $product_id, 'sort' => 'position')) as $v) {
            $variants[$v->id] = $v;
        }

        $first_variant_id = min(array_keys($variants));
        
        $first_current_variant = reset($variants);
        if ((int)$first_current_variant->id === (int)$first_variant_id) {
            // Картинки товара
            $product_images = $this->products->get_images(array('product_id' => $product_id));
            $images = [];
            foreach ($product_images as &$image) {
                if (!isset($variants[$first_variant_id])) {
                    $variants[$first_variant_id]->images = [];
                }
                $variants[$first_variant_id]->images[] = $this->design->resize_modifier($image->filename, 870, 870, true);
            }
        }
        $current_variant = $variants[$variant_id];

        // Изображения вариантов товара
        if ($variants_images = $this->products->get_images(array('product_id' => $product_id, 'variant_id' => array_keys($variants)))) {
            $images = [];
            foreach ($variants_images as &$image) {
                if (!isset($variants[$image->variant_id])) {
                    $variants[$image->variant_id]->images = [];
                }
                $variants[$image->variant_id]->images[] = $this->design->resize_modifier($image->filename, 870, 870, true, $this->config->resized_variants_dir);
            }
        }

        $results = [];
        foreach ($variants as $variant) {
            if ($variant->color === $current_variant->color) {
                $results[] = [
                    'id' => (int)$variant->id,
                    'sku' => (string)$variant->sku,
                    'size' => (string)$variant->size,
                    'color' => (string)$variant->color,
                    'price' => (float)$variant->price,
                    'compare_price' => (float)$variant->compare_price,
                    'stock' => (int)$variant->stock,
                    'name' => (string)$variant->name,
                    'units' => (string)$variant->units,
                    'images' => (array)$variant->images,
                ];
            }
        }

        return [
            'success' => 1,
            'data' => $results,
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
