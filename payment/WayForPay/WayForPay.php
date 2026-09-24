<?php

require_once('api/Okay.php');

class WayForPay extends Okay
{
    protected $keysForSignature = array(
        'merchantAccount',
        'merchantDomainName',
        'orderReference',
        'orderDate',
        'amount',
        'currency',
        'productName',
        'productCount',
        'productPrice'
    );

    protected $apiURL = 'https://api.wayforpay.com/api';

    public function checkout_form($order_id)
    {
        $order            = $this->orders->get_order((int)$order_id);
        $purchases        = $this->orders->get_purchases(array('order_id' => intval($order->id)));
        $payment_method   = $this->payment->get_payment_method($order->payment_method_id);
        $payment_currency = $this->money->get_currency(intval($payment_method->currency_id));
        $settings         = $this->payment->get_payment_settings($payment_method->id);
        $amount           = round($this->money->convert($order->total_price, $payment_method->currency_id, false), 2);
        $delivery         = $this->delivery->get_delivery($order->delivery_id);

        $currency = $payment_currency->code;

        $productNames  = array();
        $productQty    = array();
        $productPrices = array();
        foreach ($purchases as $purchase) {
            $productNames[]  = trim($purchase->product_name . ' ' . $purchase->variant_name);
            $productPrices[] = $this->money->convert($purchase->price, $payment_method->currency_id, false);
            $productQty[]    = $purchase->amount;
        }

        $option                                  = array();
        $option['merchantAccount']               = $settings['wayforpay_merchant'];
        $option['orderReference']                = $order->id . '#' . time();
        $option['orderDate']                     = strtotime($order->date);
        $option['merchantAuthType']              = 'simpleSignature';
        $option['merchantDomainName']            = $_SERVER['HTTP_HOST'];
        $option['merchantTransactionSecureType'] = 'AUTO';
        $option['currency']                      = $currency;
        if ($order->total_advance > PHP_FLOAT_EPSILON && !$order->advance_paid) {
            $option['amount'] = $order->total_advance;
        } elseif ($order->advance_paid) {
            $option['amount'] = $amount - $order->total_advance;
        } else {
            $option['amount'] = $amount;
        }


        $option['productName']  = $productNames;
        $option['productPrice'] = $productPrices;
        $option['productCount'] = $productQty;

        $option['returnUrl']  = $this->config->root_url . '/order/' . $order->url;
        $option['serviceUrl'] = $this->config->root_url . '/payment/WayForPay/callback.php';

        $hash = array();
        foreach ($this->keysForSignature as $dataKey) {
            if (!isset($option[$dataKey])) {
                continue;
            }
            if (is_array($option[$dataKey])) {
                foreach ($option[$dataKey] as $v) {
                    $hash[] = $v;
                }
            } else {
                $hash [] = $option[$dataKey];

            }
        }
        $hash = implode(';', $hash);

        $option['merchantSignature'] = hash_hmac('md5', $hash, $settings['wayforpay_secretkey']);

        /**
         * Check phone
         */
        $phone = str_replace(array('+', ' ', '(', ')'), array('', '', '', ''), $order->phone);
        if (strlen($phone) == 10) {
            $phone = '38' . $phone;
        } elseif (strlen($phone) == 11) {
            $phone = '3' . $phone;
        }


        $name                        = explode(' ', $order->name);
        $option['deliveryFirstName'] = $option['clientFirstName'] = isset($name[0]) ? $name[0] : '';
        $option['deliveryLastName']  = $option['clientLastName'] = isset($name[1]) ? $name[1] : '';
        $option['deliveryEmail']     = $option['clientEmail'] = $order->email;
        $option['deliveryPhone']     = $option['clientPhone'] = $phone;
        $option['clientCity']        = $order->location;
        $option['deliveryAddress']   = $option['clientAddress'] = $order->address;
        $option['language']          = $settings['wayforpay_language'];

        //Додамо спосіб оплати
        if (!empty($delivery)) {
            switch ($delivery->method) {
                case 'novaposhta':
                {
                    $option['deliveryList'] = 'nova';
                    if (!empty($order->novaposhta)) {
                        $order->novaposhta      = json_decode($order->novaposhta);
                        $novaposhta_address     = $this->np->get_address($order->novaposhta->city, $order->novaposhta->ware, true);
                        $option['deliveryCity'] = $novaposhta_address->CityDescription;

                        if ($order->novaposhta->type != 'WarehouseDoors') {
                            $option['deliveryAddress'] = $novaposhta_address->Description;
                        }
                    }

                    break;
                }
                default:
                {
                    $option['deliveryList'] = 'other';

                }
            }
        }

        return $option;
    }

    public function create_invoice($invoice_id)
    {
        $invoice          = $this->invoices->get_order_invoice(['id' => $invoice_id]);
        $order            = $this->orders->get_order((int)$invoice->order_id);
        $payment_method   = $this->payment->get_payment_method($invoice->payment_method_id);
        $payment_currency = $this->money->get_currency(intval($payment_method->currency_id));
        $purchases        = $this->orders->get_purchases(array('order_id' => intval($invoice->order_id)));
        $settings         = $this->payment->get_payment_settings($invoice->payment_method_id);
        $currency         = $payment_currency->code;

        $productNames  = array();
        $productQty    = array();
        $productPrices = array();
        foreach ($purchases as $purchase) {
            $productNames[]  = trim($purchase->product_name . ' ' . $purchase->variant_name);
            $productPrices[] = $this->money->convert($purchase->price, $payment_method->currency_id, false);
            $productQty[]    = $purchase->amount;
        }

        $clientPhone = str_replace(array('+', ' ', '(', ')'), array('', '', '', ''), $order->phone);
        if (strlen($clientPhone) == 10) {
            $clientPhone = '38' . $clientPhone;
        } elseif (strlen($clientPhone) == 11) {
            $clientPhone = '3' . $clientPhone;
        }
        $clientName = $name = explode(' ', $order->name);

        $params     = [
            'transactionType'    => "CREATE_INVOICE",
            'merchantAccount'    => $settings['wayforpay_merchant'],
            'merchantDomainName' => $_SERVER['HTTP_HOST'],
            'orderReference'     => $invoice->order_invoice_id,
            'orderDate'          => strtotime($order->date),
            'orderTimeout'       => 259200,
            'apiVersion'         => 1,
            'notifyMethod'       => 'all',
            'serviceUrl'         => $this->config->root_url . '/payment/WayForPay/invoice_callback.php',
            'amount'             => $invoice->payment,
            'currency'           => $currency,
            'productName'        => $productNames,
            'productPrice'       => $productPrices,
            'productCount'       => $productQty,
            'clientEmail'        => $order->email,
            'clientPhone'        => $clientPhone,
        ];
        if (!empty($clientName[0])) {
            $params['clientFirstName'] = $clientName[0];
        }
        if (!empty($clientName[1])) {
            $params['clientLastName'] = $clientName[1];
        }

        $signFields                  = [
            $params['merchantAccount'],
            $params['merchantDomainName'],
            $params['orderReference'],
            $params['orderDate'],
            $params['amount'],
            $params['currency'],
            implode(';', $params['productName']),
            implode(';', $params['productCount']),
            implode(';', $params['productPrice'])
        ];
        $hash                        = implode(';', $signFields);
        $params['merchantSignature'] = hash_hmac('md5', $hash, $settings['wayforpay_secretkey']);
        $json                        = json_encode($params);

        $ch = curl_init($this->apiURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        $response = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($response, true);
        if ($response['reasonCode'] == 1100) {
            $response_data = [
                'invoiceUrl' => $response['invoiceUrl'],
            ];
            $this->invoices->update_order_invoice($invoice_id, 'id', ['data' => json_encode($response_data)]);
            return true;

        }
        return false;
    }

    public function cancel_invoice($invoice_id)
    {
        $invoice          = $this->invoices->get_order_invoice(['id' => $invoice_id]);
        $payment_method   = $this->payment->get_payment_method($invoice->payment_method_id);
        $payment_currency = $this->money->get_currency(intval($payment_method->currency_id));
        $settings         = $this->payment->get_payment_settings($payment_method->id);

        // === Prepare Request Data ===
        $params = [
            'transactionType' => 'REMOVE_INVOICE',
            'apiVersion'      => 1,
            'merchantAccount' => $settings['wayforpay_merchant'],
            'orderReference'  => $invoice->order_invoice_id,
        ];

        // === Create Signature ===
        $signatureBase               = $params['merchantAccount'] . ';' . $params['orderReference'];
        $params['merchantSignature'] = hash_hmac('md5', $signatureBase, $settings['wayforpay_secretkey']);

        // === Send Request ===
        $ch = curl_init($this->apiURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        $response = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($response, true);
        if ($response['reasonCode'] == 1100 || $response['reasonCode'] == 1127) {
            $this->invoices->update_order_invoice($invoice_id, 'id', ['status' => 'cancelled']);
            return true;
        }
        return false;

    }
}
