<?php

class SimplCheckoutShippingController {
    function set_shipping_method(WP_REST_Request $request)
    {
        try {
            SimplRequestValidator::validate_shipping_method_request($request);
            $order_id = $request->get_params()["checkout_order_id"];
            $order = wc_get_order($order_id);

            WC()->session->set('chosen_shipping_methods', array($request->get_params()["shipping_method_id"]));
			
			$order = SimplWcCartHelper::simpl_update_order_from_cart($order);
			
            $si = new SimplCartResponse();
            return $si->cart_payload(WC()->cart, $order);
        } catch (SimplCustomHttpBadRequest $fe) {
            get_simpl_logger()->error(print_r($fe, true));
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            get_simpl_logger()->error(print_r($fe, true));
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            get_simpl_logger()->error(print_r($fe, true));
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in setting shipping method'), 500);
        }
    }
}
