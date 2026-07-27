<?php

/**
 * IPN Script for PrivatPayParts
 */

 // Working in root dir
chdir(dirname(dirname(__DIR__)));
require_once('api/Okay.php');

class PrivatPayPartsCallback extends Okay {

    protected $keysForSignature = array(
        'storeId',
        'orderId',
        'paymentState',
        'message',
    );

    public function fetch() {
        try {

            $input = file_get_contents('php://input');
            $params = json_decode($input, true);
            file_put_contents('payment/' . basename(__DIR__) . '/log.txt', date("m.d.Y H:i:s") . ' ' . json_encode($params, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

            if (!in_array($params['paymentState'], ['SUCCESS'])) {
                throw new Exception('bad status');
            }

            if (empty($params['orderId'])) {
                throw new Exception('empty order id');
            }

            list($order_id) = explode('#', $params['orderId']);
            if (empty($order_id) || !($order = $this->orders->get_order(intval($order_id)))) {
                throw new Exception('empty order');
            }

            if (empty($order->payment_method_id) || !($payment_method = $this->payment->get_payment_method(intval($order->payment_method_id)))) {
                throw new Exception('empty payment method');
            }

            if (!($settings = $this->payment->get_payment_settings(intval($payment_method->id)))) {
                throw new Exception('empty settings');
            }

            if (empty($settings['privatbank_store_id']) || empty($settings['privatbank_password'])) {
                throw new Exception('empty credentials');
            }

            if ($settings['privatbank_store_id'] != $params['storeId']) {
                throw new Exception('empty store id');
            }

            // Проверяем контрольную подпись
            $sign = array();
            foreach ($this->keysForSignature as $dataKey) {
                if (array_key_exists($dataKey, $params)) {
                    $sign[] = $params[$dataKey];
                }
            }
            $mysignature = base64_encode(hex2bin(sha1($settings['privatbank_password'] . implode('', $sign) . $settings['privatbank_password'])));
            if ($mysignature !== $params['signature']) {
                throw new Exception("bad sign {$params['signature']}");
            }

            if ($order->paid) {
                throw new Exception('already paid');
            }

            // Установим статус оплачен
            $this->orders->update_order(intval($order->id), array('payment_date' => date('Y-m-d H:i:s'), 'paid' => 1));

            // Спишем товары  
            $this->orders->close(intval($order->id));

            // Отправим уведомление на email
            $this->notify->email_order_user(intval($order->id));
            $this->notify->email_order_admin(intval($order->id));

        } catch (Exception $e) {
            file_put_contents('payment/' . basename(__DIR__) . '/log.txt', date("m.d.Y H:i:s") . ' ' . $e->getMessage() . "\n", FILE_APPEND);
            return $e->getMessage();
        }

        return 'success';
    }

}

$results = new PrivatPayPartsCallback();
if ($result = $results->fetch()) {
    header("Content-Type: application/json; charset=UTF-8");
    header("Cache-Control: must-revalidate");
    header("Pragma: no-cache");
    header("Expires: -1");
    print json_encode($result, JSON_UNESCAPED_UNICODE);
}
exit();
