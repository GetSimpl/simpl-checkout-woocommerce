<?php

include_once SIMPL_PLUGIN_DIR . "/includes/simpl_integration/simpl_integration.php";
include_once SIMPL_PLUGIN_DIR . "/includes/helpers/cart_helper.php";
include_once SIMPL_PLUGIN_DIR . "/includes/helpers/wc_helper.php";

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

?>