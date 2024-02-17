<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define('SIMPL_CHECKOUT_TOKEN_EXPIRY', 3 * 24 * 60 * 60);
define('SIMPL_SYNC_ORDER_ACTION_TEXT', 'Sync Order - Simpl Checkout');

function simpl_init_wc_session() {
    $session_class = apply_filters('woocommerce_session_handler', 'WC_Session_Handler');
    WC()->session  = new $session_class();
    WC()->session->init();
}

function simpl_order_refunded_hook($order_id) {
	simpl_get_logger()->debug("order-hook-handler->simpl_order_refunded_hook order_id: ". $order_id);
	
    $order = wc_get_order($order_id);

    if (!$order->meta_exists('simpl_order_id')) {
        return;
    }

    $order_data = simpl_fetch_order_data($order);

    $request["topic"] = "order.refunded";
    $request["resource"] = "order";
    $request["event"] = "refunded";
    $request["data"] = $order_data;

    $checkout_3pp_client = new SimplCheckout3ppClient();
    try {
        $simplHttpResponse = $checkout_3pp_client->post_hook_request($request);
    } catch (\Throwable $th) { 
        simpl_get_logger()->error("error in simpl_order_refunded_hook ". wc_print_r($th, TRUE));
    }

    if (!simpl_is_success_response($simplHttpResponse)) {
		simpl_get_logger()->error(wc_print_r($simplHttpResponse, true));
        throw new Exception('Failed refunding order with Simpl Checkout');
    }
}

function simpl_order_cancelled_hook($order_id) {
	simpl_get_logger()->debug("order-hook-handler->simpl_order_cancelled_hook order_id: ". $order_id);	
    $order = wc_get_order($order_id);

    if (!$order->meta_exists('simpl_order_id')) {
        return;
    }

    $order_data = simpl_fetch_order_data($order);

    $request["topic"] = "order.cancelled";
    $request["resource"] = "order";
    $request["event"] = "cancelled";
    $request["data"] = $order_data;

    $checkout_3pp_client = new SimplCheckout3ppClient();
    try {
        $simplHttpResponse = $checkout_3pp_client->post_hook_request($request);
    } catch (\Throwable $th) {
        simpl_get_logger()->error("error in simpl_order_cancelled_hook ". wc_print_r($th, TRUE));
    }

    if (!simpl_is_success_response($simplHttpResponse)) {
		simpl_get_logger()->error(wc_print_r($simplHttpResponse, true));
        throw new Exception('Failed cancelling order with Simpl Checkout');
    }
}

function simpl_order_created_hook($order_id, $order) {
	simpl_get_logger()->debug("order-hook-handler->simpl_order_created_hook order_id: ". $order_id);		
    if (WC()->session == null) {
        simpl_init_wc_session();
    }

    $checkout_token = WC()->session->get('checkout_token');

    $request["topic"] = "order.created";
    $request["data"] = simpl_fetch_order_data($order);
    $request["data"]["checkout_token"] = $checkout_token;
    $request["data"]["merchant_session_token"] = $checkout_token;

    $checkout_3pp_client = new SimplCheckout3ppClient();
    try {
        $simplHttpResponse = $checkout_3pp_client->post_hook_request($request);
    } catch (\Throwable $th) {
        simpl_get_logger()->error("error while processing simpl_order_created_hook ". wc_print_r($th, TRUE));
    }

    // unset checkout_token when order is created
    WC()->session->__unset('checkout_token');
}

function simpl_checkout_update_order_hook($posted_data) {
	simpl_get_logger()->debug("order-hook-handler->simpl_checkout_update_order_hook");			
    // unsetting checkout_token if it is expired
    simpl_unset_checkout_token_if_expired();

    $request["topic"] = "checkout.updated";

    // set checkout_token when we receive this hook first time
    $checkout_token = WC()->session->get('checkout_token');
    if ($checkout_token == null) {
        simpl_set_checkout_token();
        $request["topic"] = "checkout.created";
    }

    $request["data"] = simpl_fetch_checkout_data($posted_data);
    $request["data"]["checkout_token"] = $checkout_token;
    $request["data"]["merchant_session_token"] = $checkout_token;

    $checkout_3pp_client = new SimplCheckout3ppClient();
    try {
        $simplHttpResponse = $checkout_3pp_client->post_hook_request($request);
    } catch (\Throwable $th) {
        simpl_get_logger()->error("error in simpl_checkout_update_order_hook ". wc_print_r($th, TRUE));
    }
}

/**
 * Add a custom action to order actions select box on edit order page
 * Only added for Simpl orders
 *
 * @param array $actions order actions array to display
 * @return array - updated actions
 */
function simpl_add_sync_order_action( $actions, $order ) {

    // Only show the sync order action for Simpl orders
    if('yes' == get_post_meta( $order->get_id(), SIMPL_ORDER_METADATA, true ) ) {
        $actions['simpl_sync_order'] = SIMPL_SYNC_ORDER_ACTION_TEXT;   
    }
    return $actions;
}

function simpl_sync_order_hook( $order ) {
	simpl_get_logger()->debug("order-hook-handler->simpl_sync_order_hook order_id: ". $order->get_id());	
    if (!$order->meta_exists('simpl_order_id')) {
        return;
    }

    $order_data = simpl_fetch_order_data($order);

    $request["topic"] = "order.sync";
    $request["resource"] = "order";
    $request["event"] = "sync";
    $request["data"] = $order_data;

    $checkout_3pp_client = new SimplCheckout3ppClient();
    try {
        $simplHttpResponse = $checkout_3pp_client->post_hook_request($request);
    } catch (\Throwable $th) { 
        simpl_get_logger()->error("error in simpl_sync_order_hook ". wc_print_r($th, TRUE));
    }

    // add the order sync note to order
    if (!simpl_is_success_response($simplHttpResponse)) {
        simpl_get_logger()->error(wc_print_r($simplHttpResponse, true));
        $message = sprintf( 'Order sync with Simpl Checkout failed!', wp_get_current_user()->display_name );
        $order->add_order_note( $message );
        return;
    }

    $message = sprintf( 'Order synced with Simpl Checkout', wp_get_current_user()->display_name );
    $order->add_order_note( $message );
}

function simpl_fetch_order_data($order) {
    $order_data = $order->get_data();
    $order_data["line_items"] = SimplUtil::get_data($order->get_items());
    $order_data["tax_lines"] = SimplUtil::get_data($order->get_taxes());
    $order_data["shipping_lines"] = SimplUtil::get_data($order->get_shipping_methods());
    $order_data["refunds"] = SimplUtil::get_data($order->get_refunds());
    return $order_data;
}

function simpl_fetch_checkout_data($posted_data) {
	    $posted_data = urldecode($posted_data);
    parse_str($posted_data, $params);
    $params['cart'] = WC()->cart->get_cart();
    return $params;
}

function simpl_set_checkout_token() {
	    if (WC()->session == null) {
        simpl_init_wc_session();
    }
    
    $checkout_token = simpl_get_uuid4();
    WC()->session->set('checkout_token', $checkout_token);
    WC()->session->set('checkout_token_timestamp', current_time('timestamp'));
}

// Define the custom function to set an expiry for a field in the session.
function simpl_unset_checkout_token_if_expired() {
    if (WC()->session == null) {
        simpl_init_wc_session();
    }
    // Get the WooCommerce session data.
    $checkout_token = WC()->session->get('checkout_token');
    $checkout_token_timestamp = WC()->session->get('checkout_token_timestamp');

    // Check if the session field exists and if it's expired.
    if (!empty($checkout_token) && $checkout_token_timestamp < (current_time('timestamp') - SIMPL_CHECKOUT_TOKEN_EXPIRY)) {
        // Field has expired, so remove it.
        WC()->session->__unset('checkout_token');
    }
}
