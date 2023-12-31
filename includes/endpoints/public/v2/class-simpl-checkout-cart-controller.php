<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SimplCheckoutCartControllerV2 {
    
    function create(WP_REST_Request $request) {
        //Errors cleared in cart_helper as part of woocommerce_init. We need the errors generated post that to be rendered on PDP
        // wc_clear_notices();

        foreach ( $request->get_params() as $key => $value ) {
            $_REQUEST[$key] = $value;
            $_POST[$key] = $value;
        }

        //Now since we are loading frontend, add-to-cart is invoked automatically for PDP
        //class-wc-form-handler.php -> add_to_cart_action
        // if (isset($request->get_params()['add-to-cart'])) {
        //     WC()->cart->empty_cart();
        //     WC_Form_Handler::add_to_cart_action();
        // }

        $err = wc_get_notices('error');
        if (isset($err) && count($err) > 0) {
            $err_messages = array();
            foreach ($err as $err_message) {
                array_push($err_messages, strip_tags($err_message["notice"]));
            }

            //Error messages to be handled by Simpl. Cleanup native message
            wc_clear_notices();
            
            return new WP_REST_Response(array(
                "success"=> false, 
                "code" => SIMPL_HTTP_ERROR_CART_CREATE, 
                "errors" => $err_messages
            ), 400);
        }

        try {
            // Remove discount not applicable on Simpl
			WC()->session->set('chosen_payment_method', SIMPL_PAYMENT_GATEWAY);

            $si = new SimplCartResponse();
            $redirection_url = $si->simpl_cart_redirection_url(WC()->cart, $request);

            // parse cart_session_token from received redirection_url
            $query = parse_url($redirection_url, PHP_URL_QUERY);
            $cart_session_token = explode("=", $query)[1];

            // fetch woocommerce session_cookies
            $wc_session_cookie_key = apply_filters( 'woocommerce_cookie', 'wp_woocommerce_session_' . COOKIEHASH );
            $wc_session_cookie = $_COOKIE[$wc_session_cookie_key];

            // now set these session_cookies to cache against our cart_session_token
            set_transient($cart_session_token, $wc_session_cookie, 1 * HOUR_IN_SECONDS);

            return array('redirection_url'=>$redirection_url);
        } catch (Exception $fe) {

            simpl_get_logger()->error(wc_print_r($fe, true));
            
            return new WP_REST_Response(array(
                "code" => SIMPL_HTTP_ERROR_CART_CREATE, 
                "message" => 'error in creating checkout'), 
                500);
        }
    }
}
