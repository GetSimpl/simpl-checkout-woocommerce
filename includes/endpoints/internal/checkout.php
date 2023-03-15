<?php

include_once SIMPL_PLUGIN_DIR . "/includes/simpl_integration/simpl_integration.php";
include_once SIMPL_PLUGIN_DIR . "/includes/helpers/cart_helper.php";
include_once SIMPL_PLUGIN_DIR . "/includes/helpers/wc_helper.php";


function create_checkout( WP_REST_Request $request ) {
    try {
        $items = $request->get_params()["items"];
        $validation_errors = line_items_validation_errors($items);
        if(isset($validation_errors)) {
            return $validation_errors;

        }
        initCartCommon();
        add_to_cart($items);
        $shipping_address = $request->get_params()["shipping_address"];
        $billing_address = $request->get_params()["billing_address"];    
        if(isset($shipping_address) && isset($billing_address)) {
            WC()->customer->set_shipping_address($shipping_address);
            WC()->customer->set_billing_address($billing_address);   
        }
        WC()->cart->calculate_totals();
        $order = create_order_from_cart();
        $si = new SimplIntegration();
        $cart_payload =  $si->cart_payload(WC()->cart, $order->id);
        return $cart_payload;
    } catch (Exception $fe) {
        return new WP_Error("user_error", $e->getMessage());
    } catch (Error $fe) {
        return new WP_Error("user_error", "error in creating checkout", array("error_mesage" => $fe->getMessage(), "backtrace" => $fe->getTraceAsString()));
    }    
}

function update_checkout( WP_REST_Request $request ) {
    try {
        $items = $request->get_params()["items"];
        initCartCommon();
        WC()->cart->empty_cart();
        $validation_errors = validating_update_checkout($request);
        if(isset($validation_errors)) {
            return $validation_errors;
        }

        if(isset($items) && count($items) > 0) {
            $validation_errors = line_items_validation_errors($items);
            if(isset($validation_errors)) {
                return $validation_errors;
            }
            add_to_cart($items);
        } else {
            $order_id = $request->get_params()["checkout_order_id"];
            load_cart_from_order($order_id);
        }
        set_address_in_cart($request->get_params()["shipping_address"], $request->get_params()["billing_address"]);
        $order = update_order_from_cart($request->get_params()["checkout_order_id"]);
        $si = new SimplIntegration();
        $cart_payload = $si->cart_payload(WC()->cart, $order->id);
        return $cart_payload;
    } catch (Exception $fe) {
        return new WP_Error("user_error", $e->getMessage());
    } catch (Error $fe) {
        return new WP_Error("user_error", "error in creating checkout", array("error_mesage" => $fe->getMessage(), "backtrace" => $fe->getTraceAsString()));
    }    
}

function fetch_checkout(WP_REST_Request $request) {
    initCartCommon();
    WC()->cart->empty_cart();
    $order_id = $request->get_params()["checkout_order_id"];
    $order = wc_get_order((int)$order_id);
    if($order) {
        convert_wc_order_to_wc_cart($order);
        $si = new SimplIntegration();
        return $si->cart_payload(WC()->cart, $order_id);
    }
    return new WP_REST_Response(array("code"=> "not_found", "message"=> "invalid checkout_order_id"), 404);
}

function internal_authenticate() {    
    $api = new WC_REST_Authentication();
    $authenticated = $api->authenticate("");
    return $authenticated;
}

function add_to_cart($items) {
    WC()->cart->empty_cart();
    foreach($items as $item_id => $item) {
        WC()->cart->add_to_cart($item["product_id"], $item["quantity"], $item["variant_id"]);
    }
    if(WC()->cart->is_empty()) {
        return new WP_REST_Response(array("code"=> "bad_request", "message"=> "error in creating checkout for given params"), 400);
    }
}

function line_items_validation_errors($items) {
    if(!isset($items) || count($items) == 0) {
        return new WP_REST_Response(array("code"=> "bad_request", "message"=> "items cannot be empty"), 400);
    }

    foreach($items as $item_id => $item) {
        if($item["quantity"] <= 0) {
            return new WP_REST_Response(array("code"=> "bad_request", "message"=> "quantity should be greater than 1"), 400);   
        }
    }
    return NULL;        
}

function validating_update_checkout($request_params) {
    $items = $request_params->get_params()["items"];
    $shipping_address = $request_params->get_params()["shipping_address"];
    if(!isset($items) && !isset($shipping_address)) {
        return new WP_REST_Response(array("code"=> "bad_request", "message"=> "update request requires 'items' or 'shipping_address'"), 400);   
    }
    return NULL;        
}

function load_cart_from_order($order_id) {
    $order = wc_get_order((int)$order_id);
    convert_wc_order_to_wc_cart($order);
}
?>