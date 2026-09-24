<?php

// Работаем в корневой директории
chdir ('../../');
require_once('api/Okay.php');
$okay = new Okay();

$data = json_decode(file_get_contents("php://input"), true);
file_put_contents('log/WayForPay.log', json_encode($data, JSON_UNESCAPED_UNICODE) . "\n");

$keysForSignature = array(
    'merchantAccount',
    'orderReference',
    'amount',
    'currency',
    'authCode',
    'cardPan',
    'transactionStatus',
    'reasonCode'
);

$order_parse = !empty($data['orderReference']) ? explode('#', $data['orderReference']) : null;
if (is_array($order_parse)) {
    $order_id = $order_parse[0];
} else {
    $order_id = $order_parse;
}
$order = $okay->orders->get_order(intval($order_id));
if (empty($order)) {
    die('Заказ не найден');
}

$method = $okay->payment->get_payment_method(intval($order->payment_method_id));
if (empty($method)) {
    die("Неизвестный метод оплаты");
} else {
    $method_advance_payment = $order->total_advance > PHP_FLOAT_EPSILON;
}

$amount = !empty($data['amount']) ? $data['amount'] : null;
$w4pAmount = round($amount, 2);
if ($method_advance_payment) {
    $orderAmount = $order->total_advance;
} else {
    $orderAmount = round($okay->money->convert($order->total_price, $method->currency_id, false), 2);
}

if ($orderAmount != $w4pAmount) {
    die("Неверная сумма оплаты");
}

$settings = unserialize($method->settings);

$merchant = $settings['wayforpay_merchant'];

$sign = array();
foreach ($keysForSignature as $dataKey) {
    if (array_key_exists($dataKey, $data)) {
        $sign [] = $data[$dataKey];
    }
}
$sign = implode(';', $sign);
$sign = hash_hmac('md5', $sign, $settings['wayforpay_secretkey']);
if (!empty($data["merchantSignature"]) && $data["merchantSignature"] != $sign) {
    die("Контрольная подпись не верна");
}

$time = time();

$responseToGateway = array(
//    'orderReference' => $order->id,
    'orderReference' => $data['orderReference'],
    'status' => 'accept',
    'time' => $time
);
$sign = array();
foreach ($responseToGateway as $dataKey => $dataValue) {
    $sign [] = $dataValue;
}
$sign = implode(';', $sign);
$sign = hash_hmac('md5', $sign, $settings['wayforpay_secretkey']);
$responseToGateway['signature'] = $sign;

$paid_check = $method_advance_payment ? $order->advance_paid : $order->paid;

if (!empty($data['transactionStatus']) &&  $data['transactionStatus'] == 'Approved' && !$paid_check) {
    if ($method_advance_payment) {
        $okay->orders->update_order(intval($order->id), array('advance_paid' => 1));
        $okay->orderlabels->add_order_labels($order->id,$okay->settings->orders_labels['advance']);
    } else {
        $okay->orders->update_order(intval($order->id), array('paid' => 1));
    }

    $okay->orders->close(intval($order->id));
    $okay->notify->email_order_user(intval($order->id));
    $okay->notify->email_order_admin(intval($order->id));
}
echo json_encode($responseToGateway);
exit();
