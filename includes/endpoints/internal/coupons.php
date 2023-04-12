<?php

include_once SIMPL_PLUGIN_DIR . "/includes/simpl_integration/simpl_integration.php";
include_once SIMPL_PLUGIN_DIR . "/includes/helpers/cart_helper.php";
include_once SIMPL_PLUGIN_DIR . "/includes/helpers/wc_helper.php";

function apply_coupon(WP_REST_Request $request)
{
    try {
        global $notice_message;
        initCartCommon();

        validate_coupon_request($request);

        $order_id = $request->get_params()["checkout_order_id"];
        $order = wc_get_order($order_id);
        $coupon_code = $request->get_params()["coupon_code"];
        $cart = convert_wc_order_to_wc_cart($order);
        $cart->apply_coupon($coupon_code);
        $notice_message = $_SESSION["simpl_session_message"];
        if ($notice_message["type"] == "error") {
	        return new WP_REST_Response(array("code" => "user_error", "message" => $notice_message["message"]), 400);
        }
        $order->apply_coupon($coupon_code);
        $order->save();
        $si = new SimplIntegration();
        return $si->cart_payload(WC()->cart, $order_id);
    } catch (HttpBadRequest $fe) {
        return new WP_REST_Response(array("code" => "bad_request", "message" => $fe->getMessage()), 400);
    } catch (Exception $fe) {
	    return new WP_REST_Response(array("code" => "user_error", "message" => $fe->getMessage()), 400);
    } catch (Error $fe) {
	    return new WP_REST_Response(array("code" => "user_error", "message" => 'error in creating checkout'), 400);
    }
}

function remove_coupon(WP_REST_Request $request)
{
    try {
        global $notice_message;
        initCartCommon();

        validate_coupon_request($request);

        $order = wc_get_order((int)$request->get_params()["checkout_order_id"]);
        $coupon_code = $request->get_params()["coupon_code"];

        $cart = convert_wc_order_to_wc_cart($order);
        $cart->remove_coupon($coupon_code);
        $notice_message = $_SESSION["simpl_session_message"];
        if ($notice_message["type"] == "error") {
	        return new WP_REST_Response(array("code" => "user_error", "message" => $notice_message["message"]), 400);
        }
        $order->remove_coupon($coupon_code);
        $order->save();
        $si = new SimplIntegration();
        return $si->cart_payload(WC()->cart, $order->get_id());
    } catch (HttpBadRequest $fe) {
        return new WP_REST_Response(array("code" => "bad_request", "message" => $fe->getMessage()), 400);
    } catch (Exception $fe) {
	    return new WP_REST_Response(array("code" => "user_error", "message" => $fe->getMessage()), $fe->getCode());
    } catch (Error $fe) {
	    return new WP_REST_Response(array("code" => "user_error", "message" => 'error in creating checkout'), $fe->getCode());
    }
}


function remove_coupons(WP_REST_Request $request)
{
    try {
        global $notice_message;
        initCartCommon();
        validate_checkout_order_id($request);
        $order = wc_get_order((int)$request->get_params()["checkout_order_id"]);

        $cart = convert_wc_order_to_wc_cart($order);
        $cart->remove_coupons();
        $notice_message = $_SESSION["simpl_session_message"];
        if ($notice_message["type"] == "error") {
	        return new WP_REST_Response(array("code" => "user_error", "message" => $notice_message["message"]), 400);
        }
        $coupon_codes  = $order->get_coupon_codes();
        foreach ($coupon_codes as $index => $code) {
            $order->remove_coupon($code);
        }
        $order->save();
        $si = new SimplIntegration();
        return $si->cart_payload(WC()->cart, $order->get_id());
    } catch (HttpBadRequest $fe) {
        return new WP_REST_Response(array("code" => "bad_request", "message" => $fe->getMessage()), 400);
    } catch (Exception $fe) {
	    return new WP_REST_Response(array("code" => "user_error", "message" => $fe->getMessage()), $fe->getCode());
    } catch (Error $fe) {
	    return new WP_REST_Response(array("code" => "bad_request", "message" => 'error in creating checkout'), $fe->getCode());
    }
}
