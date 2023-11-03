<?php

class SimplCheckoutShippingController {
    function set_shipping_method(WP_REST_Request $request)
    {
        try {
            simpl_cart_init_common();
            WC()->cart->empty_cart();
            SimplRequestValidator::validate_shipping_method_request($request);
            $order = wc_get_order((int)$request->get_params()["checkout_order_id"]);
            SimplWcCartHelper::simpl_load_cart_from_order($order);

            WC()->session->set('chosen_shipping_methods', array($request->get_params()["shipping_method_id"]));
            WC()->cart->calculate_shipping();
            WC()->cart->calculate_totals();

            SimplWcCartHelper::simpl_update_shipping_line($order);
            $si = new SimplCartResponse();
            return $si->simpl_checkout_response_from_order($order);
        } catch (SimplCustomHttpBadRequest $fe) {
            simpl_sentry_exception($fe);
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            simpl_sentry_exception($fe);
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            simpl_sentry_exception($fe);
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in setting shipping method'), 500);
        }
    }
}
