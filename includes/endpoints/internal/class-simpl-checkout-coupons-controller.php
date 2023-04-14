<?php

class SimplCheckoutCouponController {
    function apply(WP_REST_Request $request)
    {
        try {
            global $notice_message;
            simpl_cart_init_common();
    
            SimplRequestValidator::validate_coupon_request($request);
    
            $order_id = $request->get_params()["checkout_order_id"];
            $order = wc_get_order($order_id);
            $coupon_code = $request->get_params()["coupon_code"];
            $cart = SimplWcCartHelper::load_cart_from_order($order_id);
            $cart->apply_coupon($coupon_code);
            $notice_message = $_SESSION["simpl_session_message"];
            if ($notice_message["type"] == "error") {
                return new WP_Error("user_error", $notice_message["message"]);
            }
            $order->apply_coupon($coupon_code);
            $order->save();
            $si = new SimplCartResponse();
            return $si->cart_payload(WC()->cart, $order_id);
        } catch (HttpBadRequest $fe) {
            return new WP_REST_Response(array("code" => "bad_request", "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            return new WP_Error("user_error", $fe->getMessage());
        } catch (Error $fe) {
            return new WP_Error("user_error", "error in creating checkout", array("error_mesage" => $fe->getMessage(), "backtrace" => $fe->getTraceAsString()));
        }
    }
    
    function remove(WP_REST_Request $request)
    {
        try {
            global $notice_message;
            simpl_cart_init_common();
    
            SimplRequestValidator::validate_coupon_request($request);
    
            $order = wc_get_order((int)$request->get_params()["checkout_order_id"]);
            $coupon_code = $request->get_params()["coupon_code"];
    
            $cart = SimplWcCartHelper::load_cart_from_order($order->get_id());
            $cart->remove_coupon($coupon_code);
            $notice_message = $_SESSION["simpl_session_message"];
            if ($notice_message["type"] == "error") {
                return new WP_Error("user_error", $notice_message["message"]);
            }
            $order->remove_coupon($coupon_code);
            $order->save();
            $si = new SimplCartResponse();
            return $si->cart_payload(WC()->cart, $order->get_id());
        } catch (HttpBadRequest $fe) {
            return new WP_REST_Response(array("code" => "bad_request", "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            return new WP_Error("user_error", $fe->getMessage());
        } catch (Error $fe) {
            return new WP_Error("user_error", "error in creating checkout", array("error_mesage" => $fe->getMessage(), "backtrace" => $fe->getTraceAsString()));
        }
    }
    
    
    function remove_all(WP_REST_Request $request)
    {
        try {
            global $notice_message;
            simpl_cart_init_common();
            SimplRequestValidator::validate_checkout_order_id($request);
            $order = wc_get_order((int)$request->get_params()["checkout_order_id"]);
    
            $cart = SimplWcCartHelper::load_cart_from_order($order->get_id());
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
            $si = new SimplCartResponse();
            return $si->cart_payload(WC()->cart, $order->get_id());
        } catch (HttpBadRequest $fe) {
            return new WP_REST_Response(array("code" => "bad_request", "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            return new WP_Error("user_error", $fe->getMessage());
        } catch (Error $fe) {
            return new WP_Error("user_error", "error in creating checkout", array("error_mesage" => $fe->getMessage(), "backtrace" => $fe->getTraceAsString()));
        }
    }    
}