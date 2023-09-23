<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Simpl_Checkout_Coupon_Controller {
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
            $notice_message = sanitize_term($_SESSION["simpl_session_message"], 'category');
            if ($notice_message["type"] == "error") {
                return new WP_Error(SIMPL_HTTP_ERROR_USER_NOTICE, $notice_message["message"]);
            }
            $order->apply_coupon($coupon_code);
            $order->save();
            $si = new SimplCartResponse();
            return $si->cart_payload(WC()->cart, $order_id);
        } catch (SimplCustomHttpBadRequest $fe) {
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in creating checkout'), 500);
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
            $notice_message = sanitize_term($_SESSION["simpl_session_message"], 'category');
            if ($notice_message["type"] == "error") {
                return new WP_Error(SIMPL_HTTP_ERROR_USER_NOTICE, $notice_message["message"]);
            }
            $order->remove_coupon($coupon_code);
            $order->save();
            $si = new SimplCartResponse();
            return $si->cart_payload(WC()->cart, $order->get_id());
        } catch (SimplCustomHttpBadRequest $fe) {
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in creating checkout'), 500);
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
            $notice_message = sanitize_term($_SESSION["simpl_session_message"], 'category');
            if ($notice_message["type"] == "error") {
                return new WP_Error(SIMPL_HTTP_ERROR_USER_NOTICE, $notice_message["message"]);
            }
            $coupon_codes  = $order->get_coupon_codes();
            foreach ($coupon_codes as $index => $code) {
                $order->remove_coupon($code);
            }
            $order->save();
            $si = new SimplCartResponse();
            return $si->cart_payload(WC()->cart, $order->get_id());
        } catch (SimplCustomHttpBadRequest $fe) {
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in creating checkout'), 500);
        }
    }    
}
