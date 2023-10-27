<?php

define('CHECKOUT_TOKEN_EXPIRY', 3 * 24 * 60 * 60);
define('SYNC_ORDER_ACTION_TEXT', 'Sync Order - Simpl Checkout');

function order_updated_hook($order_id)
{
    $order = wc_get_order($order_id);

    if ($order->meta_exists('simpl_order_id')) {
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


/**
 * Add a custom action to order actions select box on edit order page
 * Only added for Simpl orders
 *
 * @param array $actions order actions array to display
 * @return array - updated actions
 */
function simpl_add_sync_order_action( $actions ) {
	global $theorder;

    // Render the option for simpl orders only
    if('yes' == get_post_meta( $theorder->id, SIMPL_ORDER_METADATA, true ) ) {
        $actions['wc_custom_order_action'] = SYNC_ORDER_ACTION_TEXT; // Remove hardcoding - create a constant    
    }
    return $actions;
}


function simpl_process_sync_order_action( $order ) {
    if ($order->meta_exists('simpl_order_id')) {
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

        // add the sync timestamp to order note
        if (isset($simplHttpResponse) && $simplHttpResponse["response"]["code"] == 200) {
            $message = sprintf( 'Order synced with Simpl Checkout', wp_get_current_user()->display_name );
            $order->add_order_note( $message );
        } else {
            $message = sprintf( 'Order sync with Simpl Checkout failed!', wp_get_current_user()->display_name );
            $order->add_order_note( $message );
        }
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
