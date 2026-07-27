<?php
/*
 * Author: Andrii K (andrey.kovt@gmail.com)
 * Date: 15.04.2026
 * Time: 12:33:00
 */

require_once('Okay.php');

class Cart extends Okay {
    public $timeout = 30 * 24 * 2600;

    /*Выбираем содержимое корзины*/
    public function get_cart($shopping_cart = array()) {
        $cart = new stdClass();
        $cart->purchases = array();
        $cart->total_price = 0;
        $cart->total_products = 0;
        $cart->total_bonuses_price = 0;
        $cart->total_bonuses_products = 0;
        $cart->coupon = null;
        $cart->discount = 0;
        $cart->coupon_discount = 0;
        $cart->bonuses = 0.00;
        $cart->max_bonuses = 0.00;
        $cart->total_max_discount = 0;
        
        // Берем из сессии список variant_id=>amount
        if (!empty($shopping_cart)) {
            $session_items = $shopping_cart;
            
            $variants = $this->variants->get_variants(array('id'=>array_keys($session_items)));
            $colors_ids = [];
            if (!empty($variants)) {
                foreach ($variants as $variant) {
                    $items[$variant->id] = new stdClass();
                    $items[$variant->id]->variant = $variant;
                    $items[$variant->id]->amount = $session_items[$variant->id];
                    $products_ids[] = $variant->product_id;
                    $colors_ids[] = $variant->color;
                }
                
                $products = array();
                $brands_ids = array();
                $images_ids = array();
                foreach ($this->products->get_products(array('id'=>$products_ids, 'limit'=>count($products_ids))) as $product) {
                    $product->category = $this->categories->get_category(intval($product->main_category_id));
                    $products[$product->id] = $product;
                    $brands_ids[] = $product->brand_id;
                    $images_ids[] = $product->main_image_id;
                }
                
                if (!empty($images_ids)) {
                    $images = $this->products->get_images(array('id'=>$images_ids));
                    foreach ($images as $image) {
                        $products[$image->product_id]->image = $image;
                    }
                }

                $brands = [];
                if (!empty($brands_ids)) {
                    foreach ($this->brands->get_brands(array('id' => $brands_ids, 'visible_brand' => 1)) as $brand) {
                        $brands[$brand->id] = $brand;
                    }
                }

                if ($variants_images = $this->variants->get_images(array('product_id' => $products_ids, 'color' => $colors_ids, 'group_by' => 'variant'))) {
                    foreach ($variants_images as $image) {
                        foreach ($items as $item) {
                            if (($item->variant->color == $image->color) && empty($item->variant->image)) {
                                $item->variant->image = $image;
                                break;
                            }
                        }
                    }
                }

                $users_bonuses_on = (boolean)$this->settings->users_bonuses_on;
                $cart_bonuses_on = (boolean)$this->settings->cart_bonuses_on;
                $category_id = $this->settings->restricted_bonuses_category;
                $restricted_bonuses_category = $category_id ? $this->categories->get_category(intval($category_id)) : null;
                foreach ($items as $variant_id => $item) {
                    $purchase = null;
                    if (!empty($products[$item->variant->product_id])) {
                        $purchase = new stdClass();
                        $purchase->product = $products[$item->variant->product_id];
                        $purchase->variant = $item->variant;
                        $purchase->amount = $item->amount;
                        $purchase->discount = empty($item->variant->compare_price) ? 0 : (100 - round(($item->variant->price / $item->variant->compare_price) * 100));

                        if (isset($brands[$purchase->product->brand_id])) {
                            $purchase->product->brand = $brands[$purchase->product->brand_id];
                        }

                        $cart->purchases[] = $purchase;
                        $cart->total_price += ($item->variant->price * $item->amount);
                        $cart->total_products += $item->amount;

                        if (!$restricted_bonuses_category || !in_array($product->category->id, $restricted_bonuses_category->children)) {
                            if ($users_bonuses_on === true) {
                                $cart->bonuses += ($item->variant->bonuses * $item->amount);
                            }
                            if ($cart_bonuses_on === true) {
                                $cart->total_bonuses_price += ($item->variant->price * $item->amount);
                                $cart->total_bonuses_products += $item->amount;
                            }
                        }
                    }
                }
                
                // Пользовательская скидка
                $cart->discount = 0;
                if (isset($_SESSION['user_id']) && $user = $this->users->get_user(intval($_SESSION['user_id']))) {
                    $cart->discount = $user->discount;
                }
                
                $cart->total_price *= (100-$cart->discount)/100;
                $cart->total_max_discount = empty($cart->purchases) ? 0 : max(array_column($cart->purchases, 'discount'));

                // Скидка по купону
                if (isset($_SESSION['coupon_code'])) {
                    $cart->coupon = $this->coupons->get_coupon($_SESSION['coupon_code']);
                    if ($cart->coupon && $cart->coupon->valid && $cart->total_price>=$cart->coupon->min_order_price) {
                        if ($cart->coupon->type=='absolute') {
                            // Абсолютная скидка не более суммы заказа
                            $cart->coupon_discount = $cart->total_price>$cart->coupon->value?$cart->coupon->value:$cart->total_price;
                            $cart->total_price = max(0, $cart->total_price-$cart->coupon->value);
                            $cart->coupon->coupon_percent = round(100-($cart->total_price*100)/($cart->total_price+$cart->coupon->value),2);
                        } else {
                            $cart->coupon->coupon_percent = $cart->coupon->value;
                            $cart->coupon_discount = $cart->total_price * ($cart->coupon->value)/100;
                            $cart->total_price = $cart->total_price-$cart->coupon_discount;
                        }
                    } else {
                        unset($_SESSION['coupon_code']);
                    }
                }

                // Персональные бонусы
                if (!empty($user)) {
                    if ($users_bonuses_on === true && !($this->orders->count_orders(array('user_id' => $user->id)))) {
                        $bonuses = floatval($this->settings->orders_first_bonuses);
                        $cart->bonuses += $bonuses;
                    }

                    // Если использовали реферал, увеличим количество его использований
                    if (!empty($_COOKIE['userRefererId'])) {
                        $referral_id = base64_decode($_COOKIE['userRefererId']);
                        if ($referral = $this->ref->get_item(intval($referral_id), array('valid' => 1))) {
                            if ($referral->type == 'absolute') {
                                $cart->bonuses += $referral->value_referrer;
                            } else {
                                $cart->bonuses += round($cart->total_bonuses_price * ($referral->value_referrer / 100), 2);
                            }
                        } else {
                            setcookie('userRefererId', '', -1, '/', '', false, false);
                        }
                    }

                    $cart_bonuses_on = (boolean)$this->settings->cart_bonuses_on;
                    $cart_min_total_price = (int)$this->settings->orders_total_price_bonuses;
                    if ($cart_bonuses_on === true) {
                        $cart->max_bonuses = min($cart->total_bonuses_price * (intval($this->settings->max_bonuses_orders) / 100), $user->bonuses);
                        if ($cart->total_bonuses_price < $cart_min_total_price) {
                            $cart->bonuses = 0;
                        }
                    }
                }
            }
        }

        return $cart;
    }

    /*Добавление товара в корзину*/
    public function add_item($variant_id, $amount = 1) {
        // Выберем товар из базы, заодно убедившись в его существовании
        $variant = $this->variants->get_variant($variant_id);
        // Если товар существует, добавим его в корзину
        if (!empty($variant) && ($variant->stock>0 || $this->settings->is_preorder)) {
            $amount = max(1, $amount);
            if (isset($_SESSION['shopping_cart'][$variant_id])) {
                $amount = max(1, $amount+$_SESSION['shopping_cart'][$variant_id]);
            }
            // Не дадим больше чем на складе
            $amount = min($amount, ($variant->stock ? $variant->stock : min($this->settings->max_order_amount, $amount)));
            $_SESSION['shopping_cart'][$variant_id] = intval($amount);

            if (isset($_SESSION['user_id'])) {
                $this->cart_item_user($_SESSION['user_id'], $variant_id, $amount);
            }
        }
    }

    /*Обновление товара в корзине*/
    public function update_item($variant_id, $amount = 1) {
        // Выберем товар из базы, заодно убедившись в его существовании
        $variant = $this->variants->get_variant($variant_id);
        // Если товар существует, добавим его в корзину
        if (!empty($variant) && ($variant->stock>0 || $this->settings->is_preorder)) {
            $amount = max(1, $amount);
            // Не дадим больше чем на складе
            $amount = min($amount, ($variant->stock ? $variant->stock : min($this->settings->max_order_amount, $amount)));
            $_SESSION['shopping_cart'][$variant_id] = intval($amount);

            if (isset($_SESSION['user_id'])) {
                $this->cart_item_user($_SESSION['user_id'], $variant_id, $amount);
            }
        }
    }

    /*Удаление товара из корзины*/
    public function delete_item($variant_id) {
        unset($_SESSION['shopping_cart'][$variant_id]);

        if (isset($_SESSION['user_id'])) {
            $this->delete_item_api($_SESSION['user_id'], $variant_id);
        }
    }

    /*Очистка корзины*/
    public function empty_cart() {
        unset($_SESSION['shopping_cart']);
        unset($_SESSION['coupon_code']);

        if (isset($_SESSION['user_id'])) {
            $this->empty_cart_api(intval($_SESSION['user_id']));
        }
    }

    /*Применение купона в корзине*/
    public function apply_coupon($coupon_code) {
        $coupon = $this->coupons->get_coupon((string)$coupon_code);
        if ($coupon && $coupon->valid) {
            $_SESSION['coupon_code'] = $coupon->code;
        } else {
            unset($_SESSION['coupon_code']);
        }
    }

    public function update_items_api($user_id, $shopping_cart = array()) {
        if (!$user_id) return false;

        $shopping_cart = !is_array($shopping_cart) ? array() : $shopping_cart;

        $this->empty_cart_api($user_id);
        foreach ($shopping_cart as $variant_id => $amount) {
            $this->cart_item_user($user_id, $variant_id, $amount);
        }

        return $shopping_cart;
    }

    public function delete_item_api($user_id, $variant_id) {
        if (!$user_id || !$variant_id) return false;

        $this->db->query("DELETE FROM __cart WHERE user_id=? AND variant_id=?", $user_id, $variant_id);
        
        return $variant_id;
    }

    public function empty_cart_api($user_id) {
        if (!$user_id) return false;

        $this->db->query("DELETE FROM __cart WHERE user_id=?", intval($user_id));
    }

    public function sync_cart($user_id, $shopping_cart = array()) {
        if (!$user_id) return false;

        $cart = array();
        $time = time();
        $last_modify = $time;
        
        $query = $this->db->placehold("SELECT 
            c.variant_id,
            c.amount
        FROM __cart c
        LEFT JOIN __users u ON c.user_id=u.id
        WHERE c.user_id=?
        ORDER BY c.last_modify DESC", intval($user_id));
        $this->db->query($query);
        if ($results = $this->db->results()) {
            foreach ($results as $c) {
                $cart[$c->variant_id] = $c;
                $last_modify = $c->last_modify;
                
                $amount = 0;
                if (isset($shopping_cart[$c->variant_id])) {
                    $amount += $shopping_cart[$c->variant_id]->amount;
                }
                $shopping_cart[$c->variant_id] = $c->amount + $amount;
            }
        }

        if (!empty($cart) && strtotime($last_modify) >= $_COOKIE['shopping_last_modify']) {
            $shopping_cart_keys = array_keys($shopping_cart);
            foreach ($shopping_cart_keys as $key) {
                if (!in_array($key, array_keys($cart))) {
                    unset($shopping_cart[$key]);
                }
            }
        } elseif (!isset($_COOKIE['shopping_last_modify'])) {
            $shopping_cart = $this->update_items_api($user_id, $shopping_cart);
        }
        setcookie('shopping_last_modify', $time, $time + $this->timeout, '/', $this->config->root_host, false, true);

        return $shopping_cart;
    }

    private function cart_item_user($user_id, $variant_id, $amount = 1) {
        if (!$user_id) return false;

        $this->db->query("SELECT id FROM __cart WHERE user_id=? AND variant_id=?", intval($user_id), $variant_id);
        if (!$cart_id = $this->db->result('id')) {
            $query = $this->db->placehold("INSERT INTO __cart SET user_id=?, variant_id=?, amount=?", intval($user_id), $variant_id, $amount);
        } else {
            $query = $this->db->placehold("UPDATE __cart SET amount=?, last_modify=? WHERE id=?", $amount, date("Y-m-d H:i:s"), $cart_id);
        }
        $this->db->query($query);
    }
    
}
