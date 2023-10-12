<?php
function order_refunded_hook($order_id)
{
    $order = wc_get_order($order_id);

    $order_data = fetch_order_data($order);

    $request["topic"] = "order.updated";
    $request["resource"] = "order";
    $request["event"] = "updated";
    $request["data"] = $order_data;

    $checkout_3pp_client = new SimplCheckout3ppClient();
    try {
        $simplHttpResponse = $checkout_3pp_client->post_hook_request($request);
    } catch (\Throwable $th) { 
        error_log(print_r($th, TRUE)); 
    }
}

function woocommerce_checkout_order_created_hook($order_id, $posted_data, $order)
{
    $checkout_id = WC()->session->get('checkout_id');

    $request["topic"] = "order.created";
    $request["checkout_id"] = $checkout_id;
    $request["data"] = fetch_order_data($order);;

    $checkout_3pp_client = new SimplCheckout3ppClient();
    try {
        $simplHttpResponse = $checkout_3pp_client->post_hook_request($request);
    } catch (\Throwable $th) {
        error_log(print_r($th, TRUE)); 
    }

    // unset checkout_id when order is created
    WC()->session->_unset();
}

function woocommerce_checkout_update_order_hook($posted_data)
{
    // set checkout_id when we receive this hook first time
    $checkout_id = WC()->session->get('checkout_id');
    if ($checkout_id == null) {
        $checkout_id = get_uuid4();
        WC()->session->set('checkout_id', $checkout_id);
    }

    $request["topic"] = "checkout.updated";
    $request["checkout_id"] = $checkout_id;
    $request["data"] = fetch_checkout_data($posted_data);

    $checkout_3pp_client = new SimplCheckout3ppClient();
    try {
        $simplHttpResponse = $checkout_3pp_client->post_hook_request($request);
    } catch (\Throwable $th) {
        error_log(print_r($th, TRUE)); 
    }
}

function fetch_order_data($order) {
    $order_data = $order->get_data();
    $order_data["line_items"] = SimplUtil::get_data($order->get_items());
    $order_data["tax_lines"] = SimplUtil::get_data($order->get_taxes());
    $order_data["shipping_lines"] = SimplUtil::get_data($order->get_shipping_methods());
    $order_data["refunds"] = SimplUtil::get_data($order->get_refunds());
    return $order_data;
}

function fetch_checkout_data($posted_data) {
    // doing this cause getting data as encoded query params
    $posted_data = urldecode($posted_data);
    parse_str($posted_data, $params);
    return json_encode($params);
}