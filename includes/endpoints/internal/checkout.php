<?php

include_once SIMPL_PLUGIN_DIR . "/includes/simpl_integration/simpl_integration.php";
include_once SIMPL_PLUGIN_DIR . "/includes/helpers/cart_helper.php";
include_once SIMPL_PLUGIN_DIR . "/includes/helpers/wc_helper.php";


function create_checkout( WP_REST_Request $request ) {
    try {
        $items = $request->get_params()["items"];
        if(!isset($items) || count($items) == 0) {
            return new WP_REST_Response(array("code"=> "bad_request", "message"=> "items cannot be empty"), 400);
        }
        initCartCommon();
        WC()->cart->empty_cart();
        foreach($items as $item_id => $item) {
            WC()->cart->add_to_cart($item["product_id"], $item["quantity"], $item["variant_id"]);
        }
        if(WC()->cart->is_empty()) {
            return new WP_REST_Response(array("code"=> "bad_request", "message"=> "error in creating checkout for given params"), 400);
        }
        WC()->checkout();
        $shipping_address = $request->get_params()["shipping_address"];
        $billing_address = $request->get_params()["billing_address"];    
        if(isset($shipping_address) && isset($billing_address)) {
            WC()->customer->set_shipping_address($shipping_address);
            WC()->customer->set_billing_address($billing_address);   
        }
        WC()->cart->calculate_totals();
        $order = create_order_from_cart();
        $cart_payload = SimplIntegration::cart_payload(WC()->cart, $order->id);
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
        if(isset($items) && count($items) > 0) {
            foreach($items as $item_id => $item) {
                WC()->cart->add_to_cart($item["product_id"], $item["quantity"], $item["variant_id"]);
            }
            if(WC()->cart->is_empty()) {
                return new WP_REST_Response(array("code"=> "bad_request", "message"=> "error in creating checkout for given params"), 400);
            }
        } else {
            $order_id = $request->get_params()["checkout_order_id"];
            $order = wc_get_order((int)$order_id);
            convert_wc_order_to_wc_cart($order);
        }
        WC()->checkout();  
        set_address_in_cart($request->get_params()["shipping_address"], $request->get_params()["billing_address"]);
        $order = update_order_from_cart($request->get_params()["checkout_order_id"]);
        $cart_payload = SimplIntegration::cart_payload(WC()->cart, $order->id);
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
        return SimplIntegration::cart_payload(WC()->cart, $order_id);
    }
    return array("not_found");
}

function apply_coupon(WP_REST_Request $request) {
    global $notice_message;
    initCartCommon();
    WC()->cart->empty_cart();
    $order_id = $request->get_params()["checkout_order_id"];
    $coupon_code = $request->get_params()["coupon_code"];
    $order = wc_get_order((int)$order_id);
    if($order) {
        $cart = convert_wc_order_to_wc_cart($order);
        WC()->cart->apply_coupon($coupon_code);
        $notice_message = $_SESSION["simpl_session_message"];
        if($notice_message["type"] == "error") {
            return new WP_Error("user_error", $notice_message["message"]);
        }
        $order->apply_coupon($coupon_code);
        $order->save();
        return SimplIntegration::cart_payload(WC()->cart, $order_id);
    }    
    return array("not_found");
}

function remove_coupon(WP_REST_Request $request) {
    global $notice_message;
    initCartCommon();
    WC()->cart->empty_cart();
    $order_id = $request->get_params()["checkout_order_id"];
    $coupon_code = $request->get_params()["coupon_code"];
    $order = wc_get_order((int)$order_id);
    if($order) {
        $cart = convert_wc_order_to_wc_cart($order);
        WC()->cart->remove_coupon($coupon_code);
        $notice_message = $_SESSION["simpl_session_message"];
        if($notice_message["type"] == "error") {
            return new WP_Error("user_error", $notice_message["message"]);
        }
        $order->remove_coupon($coupon_code);
        $order->save();
        return SimplIntegration::cart_payload(WC()->cart, $order_id);
    }    
    return array("not_found");
}

function create_cart_api($variantID, $quantity) {    
    $simplHttpResponse = wp_remote_get( WC_Simpl_Settings::store_url_with_prefix().'/wp-json/wc/store/v1/cart/add-item?id='.$variantID.'&quantity='.$quantity, array(
        "headers" => array("content-type" => "application/json"),
    ));

    if ( ! is_wp_error( $simplHttpResponse ) ) {
        return wp_remote_retrieve_body( $simplHttpResponse );
    } else {
        $error_message = $simplHttpResponse->get_error_message();
        throw new Exception( $error_message );
    }
}

function updateOrderStatus($order_id, $orderStatus)
{
    wp_update_post(array(
        'ID'          => $order_id,
        'post_status' => $orderStatus,
    ));
}

function internal_authenticate() {    
    $api = new WC_REST_Authentication();
    $authenticated = $api->authenticate("");
    return $authenticated;
}
?>