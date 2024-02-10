<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SimplCheckoutController {

    function create(WP_REST_Request $request) {

        try {

            $cart_session_token = $request->get_params()["cart_token"];
            simpl_get_logger()->debug("includes->endpoints->internal->SimplCheckoutController->create token: ". $cart_session_token);

            if ($this->is_address_present($request)) {
                SimplWcCartHelper::simpl_set_address_in_cart($request->get_params()["shipping_address"], $request->get_params()["billing_address"]);
            }

            $order = SimplWcCartHelper::simpl_create_order_from_cart($cart_session_token);
            SimplWcCartHelper::simpl_add_order_token_to_cache($order->id, $cart_session_token);

            $si = new SimplCartResponse();
            $cart_payload = $si->cart_payload(WC()->cart, $order);

            do_action("simpl_abandoned_cart", WC()->cart, $cart_payload);

            return $cart_payload;

        } catch (SimplCustomHttpBadRequest $fe) {
            simpl_get_logger()->error(wc_print_r($fe, true));
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            simpl_get_logger()->error(wc_print_r($fe, true));
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            simpl_get_logger()->error(wc_print_r($fe, true));
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        }
    }

    function update(WP_REST_Request $request) {

        try {

            SimplRequestValidator::validate_shipping_address_or_items($request);
            SimplRequestValidator::validate_checkout_order_id($request);
            
            $order_id = $request->get_params()["checkout_order_id"];
            simpl_get_logger()->debug("includes->endpoints->internal->SimplCheckoutController->update checkout_order_id: ". $order_id);
            $order = wc_get_order($order_id);            

            if ($this->is_address_present($request)) {
			    SimplWcCartHelper::simpl_set_address_in_cart($request->get_params()["shipping_address"], $request->get_params()["billing_address"]);
            }
            
            $order = SimplWcCartHelper::simpl_update_order_from_cart($order);
            
            $si = new SimplCartResponse();
            $cart_payload = $si->cart_payload(WC()->cart, $order);

            do_action("simpl_abandoned_cart", WC()->cart, $cart_payload);

            return $cart_payload;

        } catch (SimplCustomHttpBadRequest $fe) {
            simpl_get_logger()->error(wc_print_r($fe, true));
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            simpl_get_logger()->error(wc_print_r($fe, true));
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            simpl_get_logger()->error(wc_print_r($fe, true));
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in updating checkout'), 500);
        }
    }

    //Session and cart would not exist
    function fetch(WP_REST_Request $request) {

        try {

            SimplRequestValidator::validate_checkout_order_id($request);

            $order_id = $request->get_params()["checkout_order_id"];
            simpl_get_logger()->debug("includes->endpoints->internal->SimplCheckoutController->fetch checkout_order_id: ". $order_id);
            $order = wc_get_order($order_id);

            $si = new SimplCartResponse();
            return $si->order_payload($order);

        } catch (SimplCustomHttpBadRequest $fe) {
            simpl_get_logger()->error(wc_print_r($fe, true));
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            simpl_get_logger()->error(wc_print_r($fe, true));
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            simpl_get_logger()->error(wc_print_r($fe, true));
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in fetching checkout'), 500);
        }
    }

    protected function is_address_present($request) {
        return (isset($request->get_params()["shipping_address"]) && isset($request->get_params()["billing_address"]) && count($request->get_params()["shipping_address"]) > 0 && $request->get_params()["billing_address"] > 0);
    }
}


function simpl_internal_authenticate() {

    // if (Simpl_WC_Settings::is_localhost()) {
    //     return true;
    // }

    $api = new WC_REST_Authentication();
    $authenticated = $api->authenticate("");
    
    //This is not needed anymore since we are setting user cookies. If enabled, this would override the logged-in user.
    // wp_set_current_user(0);

    return $authenticated;
}

class SimplCustomHttpBadRequest extends Exception {

    public function errorMessage() {
        //error message
        $errorMsg = 'Error on line ' . $this->getLine() . ' in ' . $this->getFile()
            . ':' . $this->getMessage();
        return $errorMsg;
    }
}
