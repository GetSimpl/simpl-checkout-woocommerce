<?php

class SimplCheckoutShippingController {
    function set_shipping_method(WP_REST_Request $request)
    {
        try {
            simpl_cart_init_common();
            WC()->cart->empty_cart();
            SimplRequestValidator::validate_shipping_method_request($request);
            $order_id = $request->get_params()["checkout_order_id"];
            SimplWcCartHelper::load_cart_from_order($order_id);

            WC()->session->set('chosen_shipping_methods', array($request->get_params()["shipping_method_id"]));
            WC()->cart->calculate_shipping();
            WC()->cart->calculate_totals();

            SimplWcCartHelper::update_shipping_line($order_id);
            $si = new SimplCartResponse();
            return $si->cart_payload(WC()->cart, $order_id);
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


