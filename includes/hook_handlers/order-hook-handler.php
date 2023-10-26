<?php

define('CHECKOUT_TOKEN_EXPIRY', 3 * 24 * 60 * 60);

function order_updated_hook($order_id)
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

function order_created_hook($order_id, $posted_data, $order)
{
    $checkout_token = WC()->session->get('checkout_token');

    $request["topic"] = "order.created";
    $request["data"] = fetch_order_data($order);
    $request["data"]["checkout_token"] = $checkout_token;
    $request["data"]["merchant_session_token"] = $checkout_token;

    $checkout_3pp_client = new SimplCheckout3ppClient();
    try {
        $simplHttpResponse = $checkout_3pp_client->post_hook_request($request);
    } catch (\Throwable $th) {
        error_log(print_r($th, TRUE)); 
    }

    // unset checkout_token when order is created
    WC()->session->__unset('checkout_token');
}

function checkout_update_order_hook($posted_data)
{
    // unsetting checkout_token if it is expired
    unset_checkout_token_if_expired();

    $request["topic"] = "checkout.updated";

    // set checkout_token when we receive this hook first time
    $checkout_token = WC()->session->get('checkout_token');
    if ($checkout_token == null) {
        set_checkout_token();
        $request["topic"] = "checkout.created";
    }

    $request["data"] = fetch_checkout_data($posted_data);
    $request["data"]["checkout_token"] = $checkout_token;
    $request["data"]["merchant_session_token"] = $checkout_token;

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
    $posted_data = urldecode($posted_data);
    parse_str($posted_data, $params);
    $params['cart'] = WC()->cart->get_cart();
    return $params;
}

function set_checkout_token() {
    $checkout_token = get_uuid4();
    WC()->session->set('checkout_token', $checkout_token);
    WC()->session->set('checkout_token_timestamp', current_time('timestamp'));
}

// Define the custom function to set an expiry for a field in the session.
function unset_checkout_token_if_expired() {
    // Get the WooCommerce session data.
    $checkout_token = WC()->session->get('checkout_token');
    $checkout_token_timestamp = WC()->session->get('checkout_token_timestamp');

    // Check if the session field exists and if it's expired.
    if (!empty($checkout_token) && $checkout_token_timestamp < (current_time('timestamp') - CHECKOUT_TOKEN_EXPIRY)) {
        // Field has expired, so remove it.
        WC()->session->__unset('checkout_token');
    }
}