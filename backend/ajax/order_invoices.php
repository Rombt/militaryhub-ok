<?php
    if(!$okay->managers->access('orders')) {
        exit();
    }

    $result = [
        'status' => 'error',
        'message' => ''
    ];
if ($okay->request->method("post")) {
    $action = $okay->request->post("action", "string");
    $payment_method_module = $okay->request->post("payment_method_module", "string");
    if (empty($action)) {
        $result['message'] = 'Action is required';
    } else if (empty($payment_method_module)) {
        $result['message'] = 'Payment method module is required';
    } else {
        $module_name = preg_replace("/[^A-Za-z0-9]+/", "", $payment_method_module);
        include_once("payment/$module_name/$module_name.php");
        $module = new $module_name();
        switch ($action) {
            case "cancel":
                $invoice_id = $okay->request->post("id", "integer");
                if(empty($invoice_id)){
                    $result['message'] = 'Invoice ID is required';
                    break;
                }
                if ($okay->invoices->cancel_invoice($invoice_id)) {
                    $result['status'] = 'success';
                    $result['message'] = 'Invoice added successfully';
                } else {
                    $result['message'] = 'Something went wrong';
                }
                break;
            case "add":
                $invoice_data = [
                    'order_id' => $okay->request->post("id", "integer"),
                    'payment_method' => $module_name,
                    'payment_method_id' => $okay->request->post("payment_method_id", "integer"),
                    'payment_type' => $okay->request->post("type", "string"),
                ];
                if(empty($invoice_data['order_id'])){
                    $result['message'] = 'Order ID is required';
                    break;
                }
                if ($okay->invoices->create_invoice($invoice_data)) {
                    $result['status'] = 'success';
                    $result['message'] = 'Invoice added successfully';
                } else {
                    $result['message'] = 'Something went wrong';
                }
                break;
            default:
                $result['message'] = 'Unknown action';
                break;
        }
    }
    if ($result['status'] == 'error') {
        http_response_code(400);
    }
    header("Content-type: application/json; charset=UTF-8");
    header("Cache-Control: must-revalidate");
    header("Pragma: no-cache");
    header("Expires: -1");
    print json_encode($result);
}
