<?php

include_once SIMPL_PLUGIN_DIR . "/includes/simpl_integration/simpl_integration.php";
include_once SIMPL_PLUGIN_DIR . "/includes/helpers/cart_helper.php";
include_once SIMPL_PLUGIN_DIR . "/includes/helpers/wc_helper.php";

function apply_coupon(WP_REST_Request $request)
{
    try {
        global $notice_message;
        simpl_cart_init_common();

        validate_coupon_request($request);

        $order_id = $request->get_params()["checkout_order_id"];
        $order = wc_get_order($order_id);
        $coupon_code = $request->get_params()["coupon_code"];
        $cart = convert_wc_order_to_wc_cart($order);
        $cart->apply_coupon($coupon_code);
        $notice_message = $_SESSION["simpl_session_message"];
        if ($notice_message["type"] == "error") {
            return new WP_Error("user_error", $notice_message["message"]);
        }
        $order->apply_coupon($coupon_code);
        $order->save();
        $si = new SimplIntegration();
        return $si->cart_payload(WC()->cart, $order_id);
    } catch (HttpBadRequest $fe) {
        return new WP_REST_Response(array("code" => "bad_request", "message" => $fe->getMessage()), 400);
    } catch (Exception $fe) {
        return new WP_Error("user_error", $fe->getMessage());
    } catch (Error $fe) {
        return new WP_Error("user_error", "error in creating checkout", array("error_mesage" => $fe->getMessage(), "backtrace" => $fe->getTraceAsString()));
    }
}

function remove_coupon(WP_REST_Request $request)
{
    try {
        global $notice_message;
        simpl_cart_init_common();

        validate_coupon_request($request);

        $order = wc_get_order((int)$request->get_params()["checkout_order_id"]);
        $coupon_code = $request->get_params()["coupon_code"];

        $cart = convert_wc_order_to_wc_cart($order);
        $cart->remove_coupon($coupon_code);
        $notice_message = $_SESSION["simpl_session_message"];
        if ($notice_message["type"] == "error") {
            return new WP_Error("user_error", $notice_message["message"]);
        }
        $order->remove_coupon($coupon_code);
        $order->save();
        $si = new SimplIntegration();
        return $si->cart_payload(WC()->cart, $order_id);
    } catch (HttpBadRequest $fe) {
        return new WP_REST_Response(array("code" => "bad_request", "message" => $fe->getMessage()), 400);
    } catch (Exception $fe) {
        return new WP_Error("user_error", $fe->getMessage());
    } catch (Error $fe) {
        return new WP_Error("user_error", "error in creating checkout", array("error_mesage" => $fe->getMessage(), "backtrace" => $fe->getTraceAsString()));
    }
}


function remove_coupons(WP_REST_Request $request)
{
    try {
        global $notice_message;
        simpl_cart_init_common();
        validate_checkout_order_id($request);
        $order = wc_get_order((int)$request->get_params()["checkout_order_id"]);

        $cart = convert_wc_order_to_wc_cart($order);
        $cart->remove_coupons();
        $notice_message = $_SESSION["simpl_session_message"];
        if ($notice_message["type"] == "error") {
            return new WP_Error("user_error", $notice_message["message"]);
        }
        $coupon_codes  = $order->get_coupon_codes();
        foreach ($coupon_codes as $index => $code) {
            $order->remove_coupon($code);
        }
        $order->save();
        $si = new SimplIntegration();
        return $si->cart_payload(WC()->cart, $order_id);
    } catch (HttpBadRequest $fe) {
        return new WP_REST_Response(array("code" => "bad_request", "message" => $fe->getMessage()), 400);
    } catch (Exception $fe) {
        return new WP_Error("user_error", $fe->getMessage());
    } catch (Error $fe) {
        return new WP_Error("user_error", "error in creating checkout", array("error_mesage" => $fe->getMessage(), "backtrace" => $fe->getTraceAsString()));
    }
}
