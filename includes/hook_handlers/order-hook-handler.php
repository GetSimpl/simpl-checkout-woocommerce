<?php

$CHECKOUT_ID_EXPIRY = 24 * 3 * 60 * 60;

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

function woocommerce_order_created_hook($order_id, $posted_data, $order)
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
    WC()->session->_unset('checkout_id');
}

function woocommerce_checkout_update_order_hook($posted_data)
{
    // unsetting checkout_id if it is expired
    unset_checkout_id_if_expired();

    $request["topic"] = "checkout.updated";

    // set checkout_id when we receive this hook first time
    $checkout_id = WC()->session->get('checkout_id');
    if ($checkout_id == null) {
        $checkout_id = get_uuid4();
        WC()->session->set('checkout_id', $checkout_id);
        WC()->session->set('checkout_id_timestamp', current_time('timestamp'));
        $request["topic"] = "checkout.created";
    }

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

// Define the custom function to set an expiry for a field in the session.
function unset_checkout_id_if_expired() {
    // Get the WooCommerce session data.
    $checkout_id = WC()->session->get('checkout_id');
    $checkout_id_timestamp = WC()->session->get('checkout_id_timestamp');

    // Check if the session field exists and if it's expired.
    if (!empty($checkout_id) && $checkout_id_timestamp < (current_time('timestamp') - $CHECKOUT_ID_EXPIRY)) {
        // Field has expired, so remove it.
        WC()->session->_unset('checkout_id');
    }
}