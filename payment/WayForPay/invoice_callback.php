<?php

chdir ('../../');
require_once('api/Okay.php');
$okay = new Okay();

$data = json_decode(file_get_contents("php://input"), true);
file_put_contents($okay->config->root_dir.'log/WayForPay_invoice.log', json_encode($data, JSON_UNESCAPED_UNICODE) . "\n");

$invoice = $okay->invoices->get_order_invoice(['order_invoice_id'=>$data['orderReference']]);
if (empty($invoice)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'reason' => 'Invoice not found']);
    exit;
}
$payment_method = $okay->payment->get_payment_method(intval($invoice->payment_method_id));
$payment_method_settings = unserialize($payment_method->settings);

if ($invoice->payment != round($data['amount'], 2)) {
    die("Невірна сума оплати");
}

// === Verify Signature ===
$signatureFields = [
    $data['merchantAccount'] ?? '',
    $data['orderReference'],
    $data['amount'],
    $data['currency'],
    $data['authCode'] ?? '',
    $data['cardPan'] ?? '',
    $data['transactionStatus'],
    $data['reasonCode'],
];

$signatureString = implode(';', $signatureFields);
$expectedSignature = hash_hmac('md5',$signatureString ,  $payment_method_settings['wayforpay_secretkey']);

if (!empty($data["merchantSignature"]) && $expectedSignature !== $data['merchantSignature']) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'reason' => 'Invalid signature']);
    exit;
}

if ($data['transactionStatus'] == 'Approved' && $invoice->status == 'pending') {
    $order = $okay->orders->get_order(intval($invoice->order_id));
    $order_update = [];
    if (
        $invoice->payment_type == 'full'
        || abs($order->total_price - $invoice->payment) < PHP_FLOAT_EPSILON
    ) {
        $order_update['paid'] = 1;
    } else {
        $order_update['advance_paid'] = 1;
        if (abs($order->total_advance - $invoice->payment) < PHP_FLOAT_EPSILON) {
            $order_update['total_advance'] = $invoice->payment;
        }
        $okay->orderlabels->add_order_labels($order->id,$okay->settings->orders_labels['advance']);
    }
    $okay->orders->update_order($order->id, $order_update);
    $okay->invoices->update_order_invoice($invoice->id,'id', ['status'=>'paid']);
    $okay->notify->email_order_user(intval($order->id));
    $okay->notify->email_order_admin(intval($order->id));
}


// === Respond back ===
$response = [
    'orderReference' => $data['orderReference'],
    'status'         => 'accept',
    'time'           => time()
];

$signature = implode(';', [$response['orderReference'], $response['status'], $response['time']]);
$response['signature'] = base64_encode(sha1($signature . $payment_method_settings['wayforpay_secretkey'], true));
//$response['signature'] = base64_encode(sha1($signature . 'flk3409refn54t54t*FNJRET', true));

echo json_encode($response);