<?php

class SimplCheckoutCouponController {
    function apply(WP_REST_Request $request)
    {
        try {
			global $notice_message;
    
            SimplRequestValidator::validate_coupon_request($request);
    
            $order_id = $request->get_params()["checkout_order_id"];
            $order = wc_get_order($order_id);
            $coupon_code = $request->get_params()["coupon_code"];

            $cart = SimplWcCartHelper::simpl_load_cart_from_order($order);
            // We first need to apply coupon on cart - to ensure coupon applicability
            $status = $cart->apply_coupon($coupon_code);

            $order = SimplWcCartHelper::simpl_update_order_from_cart($order, false);

            $si = new SimplCartResponse();
            return $si->cart_payload($cart, $order);
        } catch (SimplCustomHttpBadRequest $fe) {
            //TODO: Logger
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            //TODO: Logger
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            //TODO: Logger
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in applying coupon'), 500);
        }
    }
    
    function remove(WP_REST_Request $request)
    {
        try {
            global $notice_message;
    
            SimplRequestValidator::validate_coupon_request($request);
            
            $order_id = $request->get_params()["checkout_order_id"];
            $order = wc_get_order($order_id);
            $coupon_code = $request->get_params()["coupon_code"];
            
            $cart = SimplWcCartHelper::simpl_load_cart_from_order($order);
            $cart->remove_coupon($coupon_code);
			
            $order = SimplWcCartHelper::simpl_update_order_from_cart($order, false);

            $si = new SimplCartResponse();
            return $si->cart_payload($cart, $order);
        } catch (SimplCustomHttpBadRequest $fe) {
            //TODO: Logger
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            //TODO: Logger
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            //TODO: Logger
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in removing coupon'), 500);
        }
    }
    
    
    function remove_all(WP_REST_Request $request)
    {
        try {
            global $notice_message;
            SimplRequestValidator::validate_checkout_order_id($request);
            $order_id = $request->get_params()["checkout_order_id"];
            $order = wc_get_order($order_id);
            $cart = SimplWcCartHelper::simpl_load_cart_from_order($order);
            $cart->remove_coupons();
            //Remove automatic coupon meta data. Would be updated again if applicable
            $order->delete_meta_data('_simpl_auto_applied_coupons'); //TODO: Remove hardcoding

            SimplWcCartHelper::simpl_update_order_from_cart($order, false);
            SimplWcCartHelper::simpl_add_automatic_discounts_to_order($order);
            $order->save();
            $si = new SimplCartResponse();
            return $si->cart_payload(WC()->cart, $order);
        } catch (SimplCustomHttpBadRequest $fe) {
            //TODO: Logger
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            //TODO: Logger
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            //TODO: Logger
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in removing coupons'), 500);
        }
    }
}
