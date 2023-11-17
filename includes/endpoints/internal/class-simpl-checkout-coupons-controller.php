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
			
			$cart = SimplWcCartHelper::simpl_load_cart_from_order($order);
			// We first need to apply coupon on cart - to ensure coupon applicability
            $cart->apply_coupon($coupon_code);
			$order = SimplWcCartHelper::update_order_coupons_from_cart($order_id);

            $si = new SimplCartResponse();
            return $si->cart_payload($cart, $order);
        } catch (SimplCustomHttpBadRequest $fe) {
            simpl_sentry_exception($fe);
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            simpl_sentry_exception($fe);
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            simpl_sentry_exception($fe);
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in applying coupon'), 500);
        }
    }
    
    function remove(WP_REST_Request $request)
    {
        try {
            global $notice_message;
            simpl_cart_init_common();
    
            SimplRequestValidator::validate_coupon_request($request);
    
            $order_id = $request->get_params()["checkout_order_id"];
            $order = wc_get_order($order_id);
            $coupon_code = $request->get_params()["coupon_code"];
			
			$cart = SimplWcCartHelper::simpl_load_cart_from_order($order);
			$cart->remove_coupon($coupon_code);
			$order = SimplWcCartHelper::update_order_coupons_from_cart($order_id);

            $si = new SimplCartResponse();
            return $si->cart_payload($cart, $order);
        } catch (SimplCustomHttpBadRequest $fe) {
            simpl_sentry_exception($fe);
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            simpl_sentry_exception($fe);
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            simpl_sentry_exception($fe);
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in removing coupon'), 500);
        }
    }
    
    
    function remove_all(WP_REST_Request $request)
    {
        try {
            global $notice_message;
            simpl_cart_init_common();
            SimplRequestValidator::validate_checkout_order_id($request);
            $order_id = $request->get_params()["checkout_order_id"];
            $order = wc_get_order($order_id);

            $coupon_codes  = $order->get_coupon_codes();
            foreach ($coupon_codes as $index => $code) {
                $order->remove_coupon($code);
            }

            SimplWcCartHelper::simpl_load_cart_from_order($order);
            SimplWcCartHelper::simpl_add_automatic_discounts_to_order($order);
            $order->save();
            $si = new SimplCartResponse();
            return $si->cart_payload(WC()->cart, $order);
        } catch (SimplCustomHttpBadRequest $fe) {
            simpl_sentry_exception($fe);
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            simpl_sentry_exception($fe);
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            simpl_sentry_exception($fe);
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in removing coupons'), 500);
        }
    }
}
