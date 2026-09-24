<?php

require_once('api/Okay.php');

class PrivatPayParts extends Okay {

    private $_api_url = "https://payparts2.privatbank.ua/ipp/v2/payment/";
    private $_store_id;
    private $_private_password;
    protected $keysForSignature = array(
        'storeId',
        'orderId',
        'amount',
        'partsCount',
        'merchantType',
        'responseUrl',
        'redirectUrl',
        'products',
    );

    public function __construct() {
		parent::__construct();

		$lang_id = $this->languages->lang_id();
		$language = $this->languages->get_language(intval($lang_id));
		if (empty($language)) {
			$language = $this->languages->get_first_language();
			$this->languages->set_lang_id($language->id);
		}
		$lang_translations = (array)$this->translations->get_translations(array('lang' => $language->label));

		$file = __DIR__ . '/lang/' . $language->label . '.php';
        if (!file_exists($file)) {
            $f = reset(glob(__DIR__ . '/lang/??.php'));
            $file = __DIR__ . '/lang/' . pathinfo($f, PATHINFO_FILENAME) . '.php';
        }
        if (is_file($file) && is_readable($file)) {
            require_once($file);
            $lang_translations = array_replace((array)$lang_payment, (array)$lang_translations);
        }

        $this->design->assign('lang', (object)$lang_translations);
	}

    public function checkout_form($order_id) {
        $res = array();

        try {

            if (empty($order_id) || !($order = $this->orders->get_order((int)$order_id))) {
                throw new Exception('order not found');
            }

            if (!($purchases = $this->orders->get_purchases(array('order_id' => intval($order->id))))) {
                throw new Exception('purchases not found');
            }

            if (empty($order->payment_method_id) || !($payment_method = $this->payment->get_payment_method(intval($order->payment_method_id)))) {
                throw new Exception('empty payment method');
            }

            if (!($settings = $this->payment->get_payment_settings(intval($payment_method->id)))) {
                throw new Exception('empty settings');
            }

            if (empty($settings['privatbank_merchant_type']) || empty($settings['privatbank_store_id']) || empty($settings['privatbank_password'])) {
                throw new Exception('empty credentials');
            }

            $this->_store_id = $settings['privatbank_store_id'];
            $this->_private_password = $settings['privatbank_password'];

            if (empty($payment_method->currency_id) || !($payment_currency = $this->money->get_currency(intval($payment_method->currency_id)))) {
                throw new Exception('empty currency');
            }

            if ($order->paid) {
                throw new Exception('already paid');
            }

            $this->db->query('SELECT payment_details FROM __orders WHERE id = ? LIMIT 1', intval($order->id));
            $payment_details = $this->db->result('payment_details');
            $order->payment_details = empty($payment_details) ? array_fill_keys(['parts_count'], '') : json_decode(base64_decode($payment_details), true);
            $order->payment_details = is_null($order->payment_details) ? array_fill_keys(['parts_count'], '') : $order->payment_details;

            $curr_lang_id = $this->languages->lang_id();
            $payment_language = $this->languages->get_language(intval($order->lang_id));
            $this->languages->set_lang_id(intval($payment_language->id));

            $amount = (float)round($this->money->convert($order->total_price, $payment_method->currency_id, false), 2);
            if ($amount < 300) {
                throw new Exception('amount less then 300');
            }

            $res = array_replace(array_fill_keys(['parts_count'], ''), $order->payment_details);
            if (count(array_intersect($res, array('', 0, null))) > 0) {
                $res['ipn_method'] = 'post';
                $res['ipn_url'] = $this->languages->get_lang_link() . 'order/' . $order->url;
                $res['ipn_parts_count'] = [
                    'min' => empty($settings['privatbank_min_parts_count']) ? 1 : $settings['privatbank_min_parts_count'],
                    'max' => empty($settings['privatbank_max_parts_count']) ? 25 : $settings['privatbank_max_parts_count'],
                ];

                if ($this->request->method('post')) {
                    $payment_details = $this->request->post('payment_details');
                    if (!empty($payment_details)) {
                        foreach ($payment_details as $param => $value) {
                            switch ($param) {
                                case 'parts_count':
                                    $min = empty($settings['privatbank_min_parts_count']) ? 1 : $settings['privatbank_min_parts_count'];
                                    $max = empty($settings['privatbank_max_parts_count']) ? 25 : $settings['privatbank_max_parts_count'];
                                    if (!$this->validate->is_safe($value, true) || $value < $min || $value > $max) {
                                        throw new Exception('parts_count');
                                    } else {
                                        $res[$param] = $value;
                                    }
                                    break;
                            }
                        }
                        $_SESSION['message_success'] = $this->orders->update_order($order->id, array('payment_details' => base64_encode(json_encode($payment_details))));
                        header('Location: ' . $this->config->root_url . '/' . $this->languages->get_lang_link() . 'order/' . $order->url);
                        exit();
                    }
                }
            } else {
                $params = [
                    'storeId' => $this->_store_id,
                    'orderId' => $order->id . '#' . time(),
                    'amount' => $amount,
                    'partsCount' => intval($res['parts_count']),
                    'merchantType' => $settings['privatbank_merchant_type'],
                    'products' => array_map(function ($purchase, $cnt) use ($order, $payment_method) {
                        $purchase_price = $purchase->price * ((100 - $order->discount) / 100);
                        $purchase_price -= round($order->coupon_discount / $cnt / $purchase->amount, 2);
                        return (object)[
                            'name' => trim($purchase->product_name . ' ' . $purchase->variant_name),
                            'count' => (int)$purchase->amount,
                            'price' => (float)round($this->money->convert($purchase_price, $payment_method->currency_id, false), 2),
                        ];
                    }, $purchases, array_fill(0, count($purchases), count($purchases))),
                    'responseUrl' => $this->config->root_url . '/payment/' . basename(__DIR__) . '/callback.php',
                    'redirectUrl' => $this->config->root_url . '/' . $this->languages->get_lang_link() . 'order/' . $order->url,
                    'sendPhone' => $order->phone,
                ];

                if ($order->delivery_price > 0 && !$order->separate_delivery) {
                    $description = '';
                    if (empty($description)) {
                        $lang = $this->design->get_var('lang');
                        if (!empty($lang->order_delivery_description)) {
                            $description = $lang->order_delivery_description;
                        } else {
                            $description = 'Shipping price';
                        }
                    }
                    $params['products'][] = (object)[
                        'name' => $description,
                        'count' => 1,
                        'price' => (float)round($this->money->convert($order->delivery_price, $payment_method->currency_id, false), 2),
                    ];
                }

                $params['products'][count($params['products'])-1]->price += round($order->total_price - array_sum(array_map(function ($p) { return $p->price * $p->count; }, $params['products'])), 2);

                // Create order
                $result = $this->api($params);

                if (empty($result)) {
                    throw new Exception('wrong');
                }

                $res['ipn_method'] = 'get';
                $res['ipn_url'] = $this->_api_url;
                $res['ipn_params'] = [
                    'token' => $result['token'],
                ];
            }

            $this->languages->set_lang_id(intval($curr_lang_id));

        } catch (Exception $e) {
            $this->design->assign('error', 'empty_' . $e->getMessage());
        }

        return $res;
    }

    private function api($params = array()) {
        $hash = array();
        foreach ($this->keysForSignature as $dataKey) {
            if (!isset($params[$dataKey])) {
                continue;
            }
            if (is_array($params[$dataKey]) && $dataKey == 'products') {
                $products = array_fill(0, count($params[$dataKey]), array());
                foreach ($params[$dataKey] as $i => $param) {
                    $products[$i] = array_replace(array_fill_keys(['name', 'count', 'price'], null), (array)$param);
                    $products[$i]['price'] *= 100;
                }
                $hash[] = implode('', array_map(function ($p) { return implode('', $p) ;}, $products));
            } elseif ($dataKey == 'amount') {
                $hash[] = $params[$dataKey] * 100;
            } else {
                $hash[] = $params[$dataKey];
            }
        }

        $params['signature'] = base64_encode(hex2bin(sha1($this->_private_password . implode('', $hash) . $this->_private_password)));

        if (!empty($params['sendPhone'])) {
            /**
             * Check phone
             */
            $phone = preg_replace('~[^\+\d]~', '', $order->phone);
            if (strlen($phone) == 10) {
                $phone = '38' . $phone;
            } elseif(strlen($phone) == 11) {
                $phone = '3' . $phone;
            }
            $params['sendPhone'] = '+' . $phone;
        }

        $params = array_diff($params, array('', 0, null));

        $headers = [
            'Accept: application/json',
            'Accept-Encoding: UTF-8',
            'Content-Type: application/json; charset=UTF-8',
        ];

        $response = file_get_contents($this->_api_url . 'create', false, stream_context_create(array(
            'http' => array(
                'header' => implode("\r\n", $headers),
                'method' => 'POST',
                'content' => json_encode($params, JSON_UNESCAPED_UNICODE),
                'max_redirects' => 0, 'timeout' => 3, 'ignore_errors' => true
            ),
        )));

        if (!empty($response)) {
            $response = json_decode($response, true);
            if (!in_array($response['state'], ['SUCCESS']) || empty($response['token'])) {
                throw new Exception($response['message']);
            }
        }

        return $response;
    }

}