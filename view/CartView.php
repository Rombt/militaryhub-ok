<?php
/*
 * Author: Andrii K (andrey.kovt@gmail.com)
 * Date: 08.02.2022
 * Time: 09:08:32
 */

require_once('View.php');

class CartView extends View {

    public function __construct() {
        parent::__construct();

        // Если передан id варианта, добавим его в корзину
        if ($variant_id = $this->request->get('variant', 'integer')) {
            $this->cart->add_item($variant_id, $this->request->get('amount', 'integer'));
            header('location: '.$this->config->root_url.'/'.$this->lang_link.'cart/');
        }

        // Удаление товара из корзины
        if ($delete_variant_id = intval($this->request->get('delete_variant'))) {
            $this->cart->delete_item($delete_variant_id);
            if (!isset($_POST['submit_order']) || $_POST['submit_order']!=1) {
                header('location: '.$this->config->root_url.'/'.$this->lang_link.'cart/');
            }
        }
        /*Оформление заказа*/
        if (isset($_POST['checkout'])) {
            $order = new stdClass;
            $order->payment_method_id = $this->request->post('payment_method_id', 'integer');
            $order->delivery_id = $this->request->post('delivery_id', 'integer');
            $order->name        = $this->request->post('name');
            $order->email       = $this->request->post('email');
            $order->address     = $this->request->post('address');
            $order->phone       = $this->request->post('phone');
            $order->comment     = $this->request->post('comment');
            $order->ip          = $_SERVER['REMOTE_ADDR'];
            $order->lang_id     = $this->languages->lang_id();

            $this->design->assign('delivery_id', $order->delivery_id);
            $this->design->assign('name', $order->name);
            $this->design->assign('email', $order->email);
            $this->design->assign('phone', $order->phone);
            $this->design->assign('address', $order->address);

            $delivery = $this->delivery->get_delivery($order->delivery_id);

            $payment_method = $this->payment->get_payment_method($order->payment_method_id);

            $captcha_code =  $this->request->post('captcha_code', 'string');

            // Скидка
            $cart = $this->cart->get_cart($_SESSION['shopping_cart']);
            $order->discount = $cart->discount;

            if ($cart->coupon) {
                $order->coupon_discount = round($cart->coupon_discount);
                $order->coupon_code = $cart->coupon->code;
            }

            if (!empty($this->user->id)) {
                $order->user_id = $this->user->id;
            }

            // NovaPoshta
            if ($delivery->method == 'novaposhta') {
                $novaposhta = new stdClass();
                $novaposhta->type = $this->request->post('novaposhta_type', 'string');
                $novaposhta->city = $this->request->post('novaposhta_city', 'string');

                switch ($novaposhta->type) {
                    case 'WarehouseDoors':
                        $novaposhta->street = $this->request->post('novaposhta_street');
                        $novaposhta->build = $this->request->post('novaposhta_build');
                        $novaposhta->apartment = $this->request->post('novaposhta_apartment');
                    break;
                    case 'WarehouseWarehouse':
                    default:
                        $novaposhta->ware = $this->request->post('novaposhta_ware', 'string');
                }

                $this->design->assign('novaposhta', $novaposhta->type);

                $address = $this->np->get_address($novaposhta->city, $novaposhta->ware);
                
                $this->design->assign('novaposhta_address', $address);
                if (!empty($address)) {
                    $order->address = $address->CityDescription;
                    switch ($novaposhta->type) {
                        case 'WarehouseDoors':
                            $order->address .= ', '. $novaposhta->street
                                . (!empty($novaposhta->build) ? (', ' . $novaposhta->build) : '')
                                . (!empty($novaposhta->apartment) ? (', ' . $novaposhta->apartment) : '')
                                ;
                            break;
                        case 'WarehouseWarehouse':
                        default:
                            $order->address .= ', ' . $address->Description;
                    }
                }
            }

            // JustIn
            if ($delivery->method == 'justin') {
                $justin = new stdClass();
                $justin->city = $this->request->post('justin_city', 'string');
                $justin->ware = $this->request->post('justin_ware', 'string');

                $this->design->assign('justin_city', $justin->city);
                $this->design->assign('justin_ware', $justin->ware);

                $address = $this->ji->get_address($justin->city, $justin->ware);
                $this->design->assign('justin_address', $address);
                if (!empty($address)) {
                    $order->address = $address->CityDescription . ', ' . $address->Description;
                }
            }

            // PickUp
            if ($delivery->method == 'pickup') {
                $order->store_id = $this->request->post('store_id', 'integer');

                $store = $this->stores->get_store(intval($order->store_id));
                if (!empty($store)) {
                    $order->address = $store->address;
                }
            }

            $bonuses = $this->request->post('bonuses', 'float');
            if (!empty($this->user) && !empty($bonuses)) {
                $paid_bonuses = min($cart->max_bonuses, $bonuses);
                $order->paid_bonuses = min($cart->total_price, (float)$paid_bonuses);
            }

            $this->db->query('SET AUTOCOMMIT = 0');
            $this->db->query('START TRANSACTION');

            /*Валидация данных клиента*/
            if (!$this->validate->is_name($order->name, true)) {
                $this->design->assign('error', 'empty_name');
            } elseif (!$this->validate->is_email($order->email)) {
                $this->design->assign('error', 'empty_email');
            } elseif (!$this->validate->is_phone($order->phone, true)) {
                $this->design->assign('error', 'empty_phone');
            } elseif (!$this->validate->is_address($order->address)) {
                $this->design->assign('error', 'empty_address');
            } elseif (!$this->validate->is_comment($order->comment)) {
                $this->design->assign('error', 'empty_comment');

            } elseif ($delivery->method == 'novaposhta' && ($novaposhta->type == 'WarehouseWarehouse' || !$novaposhta->type) && empty($novaposhta->ware) ) {
                $this->design->assign('error', 'empty_novaposhta_address');
            } elseif ($delivery->method == 'novaposhta' && $novaposhta->type == 'WarehouseDoors' && (empty($novaposhta->street) || empty($novaposhta->build))) {
                $this->design->assign('error', 'empty_novaposhta_address');

            } elseif ($delivery->method == 'justin' && (empty($justin->city) || empty($justin->ware))) {
                $this->design->assign('error', 'empty_justin_address');

            } elseif ($delivery->method == 'pickup' && (!$order->store_id || empty($store))) {
                $this->design->assign('error', 'empty_store_id');

            } elseif ((!empty($this->user)) && (float)$bonuses > (float)$this->user->bonuses) {
                $this->design->assign('error', 'empty_bonuses');

            } elseif ($payment_method && (float)$payment_method->max_discount_value > 0 && (float)$payment_method->max_discount_value < (float)$cart->total_max_discount) {
                $this->design->assign('error', 'empty_payment_method');

            } elseif ($this->settings->captcha_cart && !$this->validate->verify_captcha('captcha_cart', $captcha_code)) {
                $this->design->assign('error', 'captcha');
            } elseif ($order->id = $this->orders->add_order($order)) {
                // Добавляем заказ в базу
                $_SESSION['order_id'] = $order->id;

                // Если использовали купон, увеличим количество его использований
                if ($cart->coupon) {
                    $this->coupons->update_coupon($cart->coupon->id, array('usages'=>$cart->coupon->usages+1));
                }

                // Добавляем товары к заказу
                foreach ($this->request->post('amounts') as $variant_id => $amount) {
                    $this->orders->add_purchase(array('order_id' => $order->id, 'variant_id' => intval($variant_id), 'amount' => intval($amount)));
                }

                // NovaPoshta
                if (!empty($delivery) && !empty($novaposhta) && $delivery->method == 'novaposhta') {
                    $this->orders->update_order($order->id, array('novaposhta' => json_encode($novaposhta)));
                }

                // JustIn
                if (!empty($delivery) && !empty($justin) && $delivery->method == 'justin') {
                    $this->orders->update_order($order->id, array('justin' => json_encode($justin)));
                }
                
                // Стоимость доставки
                if (!empty($delivery) && $delivery->free_from > $order->total_price) {
                    $this->orders->update_order($order->id, array('delivery_price'=>$delivery->price, 'separate_delivery'=>$delivery->separate_payment));
                } elseif ($delivery->separate_payment) {
                    $this->orders->update_order($order->id, array('separate_delivery'=>$delivery->separate_payment));
                }

                if (!empty($order->user_id) && $order->paid_bonuses > 0) {
                    $this->users->remove_user_bonuses($order->user_id, $order->paid_bonuses * -1, 'order_removed', $order->id);
                }

                // Если использовали реферал, увеличим количество его использований
                if (!empty($_COOKIE['userRefererId'])) {
                    $referral_id = base64_decode($_COOKIE['userRefererId']);
                    if ($referral = $this->ref->get_item(intval($referral_id), array('valid' => 1))) {
                        $this->orders->update_order($order->id, array('referral_id' => intval($referral_id)));
                        if (($label_id = $this->settings->orders_labels['referral']) && $label = $this->orderlabels->get_label(intval($label_id))) {
                            $this->orderlabels->add_order_labels($order->id, (array)$label->id);
                        }
                        $this->ref->update_item($referral->id, array('usages' => ++$referral->usages));
                        if ($referral->single) {
                            setcookie('userRefererId', '', -1, '/', '', false, false);
                        }
                    } else {
                        setcookie('userRefererId', '', -1, '/', '', false, false);
                    }
                }

                $this->db->query('COMMIT');
                $this->db->query('SET AUTOCOMMIT = 1');

                $order = $this->orders->get_order($order->id);

                // Додаємо суму авансу, якщо він є
                if (!empty($order->payment_method_id)) {
                    $order_payment_method = $this->payment->get_payment_method($order->payment_method_id);
                    if (
                        $order_payment_method->advance_payment > PHP_FLOAT_EPSILON
                        || $order_payment_method->advance_payment_2 > PHP_FLOAT_EPSILON
                    ) {
                        $total_advance = ((float)$order->total_price > (float)$order_payment_method->advance_sum_delimiter) ? $order_payment_method->advance_payment_2 : $order_payment_method->advance_payment;
                        $this->orders->update_order(
                            $order->id,
                            [
                                'total_advance' => $total_advance,
                            ]);
                    }
                }

                // Отправляем письмо пользователю
                $this->notify->email_order_user($order->id);

                // Отправляем письмо администратору
                $this->notify->email_order_admin($order->id);

                // Отправляем sms пользователю
                $this->sms->order_user($order->id);

                // Очищаем корзину (сессию)
                $this->cart->empty_cart();

                // Перенаправляем на страницу заказа
                header('location: '.$this->config->root_url.'/'.$this->lang_link.'order/'.$order->url);
            } else {
                $this->design->assign('error', 'wrong');
            }
        } else {
            // Если нам запостили amounts, обновляем их
            if ($amounts = $this->request->post('amounts')) {
                foreach($amounts as $variant_id=>$amount) {
                    $this->cart->update_item($variant_id, $amount);
                }

                $coupon_code = trim($this->request->post('coupon_code', 'string'));
                if (empty($coupon_code)) {
                    $this->cart->apply_coupon('');
                    header('location: '.$this->config->root_url.'/'.$this->lang_link.'cart/');
                } else {
                    $coupon = $this->coupons->get_coupon((string)$coupon_code);
                    if (empty($coupon) || !$coupon->valid) {
                        $this->cart->apply_coupon($coupon_code);
                        $this->design->assign('coupon_error', 'invalid');
                    } else {
                        $this->cart->apply_coupon($coupon_code);
                        header('location: '.$this->config->root_url.'/'.$this->lang_link.'cart/');
                    }
                }
            }
        }
    }

    /*Отображение заказа*/
    public function fetch() {
        // Способы доставки
        $deliveries = $this->delivery->get_deliveries(array('enabled'=>1));
        foreach($deliveries as $delivery) {
            $delivery->payment_methods = $this->payment->get_payment_methods(array('delivery_id' => $delivery->id, 'enabled' => 1));
            $delivery->payment_methods = array_filter($delivery->payment_methods, function ($payment_method) {
                return empty((float)$payment_method->max_discount_value) || (float)$this->checkout->total_max_discount < (float)$payment_method->max_discount_value;
            });
        }
        $this->design->assign('all_currencies', $this->money->get_currencies());
        $this->design->assign('deliveries', $deliveries);
        
        // Данные пользователя
        if ($this->user) {
            $last_order = $this->orders->get_orders(array('user_id'=>$this->user->id, 'limit'=>1));
            $last_order = reset($last_order);
            if ($last_order) {
                $this->design->assign('name', $last_order->name);
                $this->design->assign('email', $last_order->email);
                $this->design->assign('phone', $last_order->phone);
                $this->design->assign('address', $last_order->address);
            } else {
                $this->design->assign('name', $this->user->name);
                $this->design->assign('email', $this->user->email);
                $this->design->assign('phone', $this->user->phone);
                $this->design->assign('address', $this->user->address);
            }
        }

        // Если существуют валидные купоны, нужно вывести инпут для купона
        if ($this->coupons->count_coupons(array('valid'=>1))>0) {
            $this->design->assign('coupon_request', true);
        }

        // Выводим корзину
        return $this->design->fetch('cart.tpl');
    }
    
}
