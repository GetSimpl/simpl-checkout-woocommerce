<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly      
class SimplCheckoutShippingController {
    function scwp_set_shipping_method(WP_REST_Request $request)
    {
        try {
            simpl_cart_init_common();
            WC()->cart->empty_cart();
            SimplRequestValidator::validate_shipping_method_request($request);
            $order_id = $request->get_params()["checkout_order_id"];
            SimplWcCartHelper::scwp_load_cart_from_order($order_id);

            WC()->session->set('chosen_shipping_methods', array($request->get_params()["shipping_method_id"]));
            WC()->cart->calculate_shipping();
            WC()->cart->calculate_totals();

            SimplWcCartHelper::scwp_update_shipping_line($order_id);
            $si = new SimplCartResponse();
            return $si->scwp_cart_payload(WC()->cart, $order_id);
        } catch (SimplCustomHttpBadRequest $fe) {
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in setting shipping method'), 500);
        }
    }
}


