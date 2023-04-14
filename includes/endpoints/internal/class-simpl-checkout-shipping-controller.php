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
            SimplWcCartHelper::update_shipping_line($order_id);
            WC()->cart->calculate_shipping();
            WC()->cart->calculate_totals();
            $si = new SimplCartResponse();
            return $si->cart_payload(WC()->cart, $order_id);
        } catch (HttpBadRequest $fe) {
            return new WP_REST_Response(array("code" => "bad_request", "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            return new WP_Error("user_error", $fe->getMessage());
        } catch (Error $fe) {
            return new WP_Error("user_error", "error in setting shipping method", array("error_mesage" => $fe->getMessage(), "backtrace" => $fe->getTraceAsString()));
        }
    }
}


