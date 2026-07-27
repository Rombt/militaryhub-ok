<?php
if (!empty($_SERVER['HTTP_USER_AGENT'])){
    session_name(md5($_SERVER['HTTP_USER_AGENT']));
}
session_start();
require_once('../api/Okay.php');
define('IS_CLIENT', true);



class SelectColor extends Okay {
    public function fetch() {
        if (isset($_POST['variant']) && isset($_POST['product'])) {
            $variant_id = $_POST['variant'];
            $product_id = $_POST['product'];
        }

        // Выбираем товар из базы
        $product = $this->products->get_product((int) $product_id);
        if (empty($product) || (!$product->visible && empty($_SESSION['admin']))) {
            return false;
        }

        /*if ($product_values = $this->features_values->get_features_values(array('product_id'=>$product_id))) {
            foreach ($product_values as $pv) {
                if (!isset($product->features[$pv->feature_id])) {
                    $product->features[$pv->feature_id] = $pv;
                }
                $product->features[$pv->feature_id]->values[] = $pv;

                switch ($pv->feature_id) {
                    case $this->settings->feature_id['size']:
                        $option_name = 'size';
                        break;
                    case $this->settings->feature_id['color']:
                        $option_name = 'color';
                        break;
                    default:
                        $option_name = '';
                }

                if (!empty($option_name)) {
                    if (!isset($product->{$option_name})) {
                        $product->{$option_name} = array();
                    }
                    $product->$option_name[] = $pv;
                }
            }
        }*/

        $product->images = $this->products->get_images(array('product_id' => $product->id));
        $product->image = reset($product->images);

        foreach ($product->images as $image) {
            if (preg_match("~^https?://~", $image->filename)) {
                if ($filename = $this->image->download_image($image->filename)) {
                    $image->filename = $filename;
                }
            }
        }

        $variants = array();
        if ($variant_id != '0') {
            foreach ($this->variants->get_variants(array('id' => $variant_id)) as $v) {
                $variants[$v->id] = $v;
            }
        } else {
            foreach ($this->variants->get_variants(array('product_id' => $product_id)) as $v) {
                $variants[$v->id] = $v;
            }
        }

        // Изображения вариантов товара
        if ($variants_images = $this->products->get_images(array('product_id' => $product->id, 'variant_id' => array_keys($variants)))) {
            foreach ($variants_images as $image) {
                if (!isset($variants[$image->variant_id])) {
                    $variants[$image->variant_id]->images = [];
                }
                $variants[$image->variant_id]->images[] = $image;
            }
        }

        if (!$product->variant->compare_price) {
            $hidden = ' hidden';
        } else {
            $hidden = '';
        }

        $price = '
        <div class="col-xs-12 col-sm-12 product_price">
            <div class="price">
                <span class="fn_price" itemprop="price" content="'. $this->money->convert($variants[$variant_id]->price, '', false)  . '">'. $this->money->convert($variants[$variant_id]->price) . '</span>
                <span itemprop="priceCurrency" content="UAH">грн</span>
            </div>
            <div class="old_price' . $hidden . '">
            <span class="fn_old_price">' . $this->money->convert($variants[$variant_id]->compare_price) . '</span> грн
            </div>
        </div>
        ';



        if ($variant_id != '0') {
            $imgfancybox = $this->design->resize_modifier($variants[$variant_id]->images[0]->filename, 870, 870, false, $this->config->resized_variants_dir);
            $imgmain = $this->design->resize_modifier($variants[$variant_id]->images[0]->filename, 340, 340, false, $this->config->resized_variants_dir);
            $imgzoom = $this->design->resize_modifier($variants[$variant_id]->images[0]->filename, 650, 650, false, $this->config->resized_variants_dir);
        } else {
            $imgfancybox = $this->design->resize_modifier($product->image->filename, 870, 870);
            $imgmain = $this->design->resize_modifier($product->image->filename, 340, 340);
            $imgzoom = $this->design->resize_modifier($product->image->filename, 650, 650);
        }

        $data = '
<div class="fn_product_images col-sm-12 col-md-7 col-lg-7 col-xl-8">
    <div class="masonry">
        <div class="product_image item_1">
            <div class="inside">
                <a href="' . $imgfancybox . '" data-fancybox="group" data-caption="' . $product->name . '">
                                      <img class="fn_img product_img xzoom" itemprop="image"
                                      src="' . $imgmain . '" alt="' . $product->name . '"
                                      xoriginal="' . $imgzoom . '"/>
                </a>
                <div class="hidden">
                    <img class="product_img xzoom-gallery" itemprop="image"
                                         src="' . $imgmain . '"
                                         alt="' . $product->name . '"
                                         xpreview="' . $imgzoom . '"
                    />
                </div>
            </div>
        </div>';

        ?>
        <?php
        $isFirst = true;
        foreach ($variants[$variant_id]->images as $i => $image) {
            if ($isFirst)
            {
                $isFirst = false;
                continue;
            }

            $data .= '
                    <div class="product_image item_' . ($i + 1) . '">
                                    <div class="inside">
                                        <a class="images_link" href="' . $this->design->resize_modifier($image->filename, 870, 870, false, $this->config->resized_variants_dir) . '"
                                           data-fancybox="group"
                                           data-caption="' . $product->name . '">
                                            <img class="xzoom' . ($i + 1) . '" src="' . $this->design->resize_modifier($image->filename, 340, 340, false, $this->config->resized_variants_dir) . '"
                                                 alt="' . $product->name . '"
                                                 xoriginal="' . $this->design->resize_modifier($image->filename, 650, 650, false, $this->config->resized_variants_dir) . '"/>
                                        </a>
                                        <div class="hidden">
                                            <img class="xzoom-gallery' . ($i + 1) . '" src="' . $this->design->resize_modifier($image->filename, 340, 340, false, $this->config->resized_variants_dir) . '"
                                                 alt="' . $product->name . '"
                                                 xpreview="' . $this->design->resize_modifier($image->filename, 650, 650, false, $this->config->resized_variants_dir) . '"/>
                                        </div>
                                    </div>
                                </div>
            ';
        }
        ?>
        <?php

        $data .= '</div>
</div>
';

        $result['price'] = $price;
        $result['product'] = $product;
        $result['variants'] = $variants;
        $result['data'] = $data;

        return $result;
    }
}

$results = new SelectColor();
if ($result = $results->fetch()) {
    header("Content-Type: application/json; charset=UTF-8");
    header("Cache-Control: must-revalidate");
    header("Pragma: no-cache");
    header("Expires: -1");
    print json_encode($result);
}
exit();