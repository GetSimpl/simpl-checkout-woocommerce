<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SimplCheckoutCartControllerV2 {
    
    function create(WP_REST_Request $request) {
        //Errors cleared in cart_helper as part of woocommerce_init. We need the errors generated post that to be rendered on PDP
        // wc_clear_notices();

        // We can't add this check. When add-to-cart fails because of mandatory field, cart would be empty
        // and in that case, error alert would not render if we return from here.
        // if ( WC()->cart->is_empty() ) {
		// 	throw new Exception('Cannot proceed with an empty cart');
		// }

        $err = wc_get_notices('error');
        // Added this condition because there may be error if default add-to-cart failed because of error like required field is empty
        if( !isset($err) || count($err) == 0 ) {
            // Now since we are loading frontend, add_to_cart is invoked automatically for PDP
            //class-wc-form-handler.php -> add_to_cart_action
            // WC()->cart->empty_cart(); This is not required here. Doing this in cart_helper before default add_to_cart is triggered
            if ( isset($request->get_params()['add-to-cart']) && WC()->cart->is_empty() ) {
                // There are times when default add_to_cart_action is overriden and is handled by ajax instead.
                // In that case, we specifically have to invoke add to cart.
                WC_Form_Handler::add_to_cart_action();
            }
        }

        // Fetching this again as the above statement may have added errors.
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
            //WC()->session->set( 'chosen_shipping_methods', '' ); To check if this would remove default shipping to support in-house shipping ?
            
            $si = new SimplCartResponse();
            $redirection_url = $si->simpl_cart_redirection_url(WC()->cart, $request);

            // parse cart_session_token from received redirection_url
            $query = parse_url($redirection_url, PHP_URL_QUERY);
            $cart_session_token = explode("=", $query)[1];

            // fetch woocommerce session_cookies
            $wc_session_cookie_key = apply_filters( 'woocommerce_cookie', 'wp_woocommerce_session_' . COOKIEHASH );
            $wc_session_cookie = $_COOKIE[$wc_session_cookie_key];

            // Cookie is not set until add_to_cart call is complete.
			// Hence, we extract the cookie from headers
			if(!$wc_session_cookie) {				
				foreach(headers_list() as $header) {
					if (stripos($header, $wc_session_cookie_key) !== false) {
						$parsed_cookie = explode("=", $header)[1];
						$parsed_cookie = explode(";", $parsed_cookie)[0];
						$wc_session_cookie = urldecode($parsed_cookie);
						break;
					}
				}
			}

            // now set these session_cookies to cache against our cart_session_token
            if($wc_session_cookie) {
            	set_transient($cart_session_token, $wc_session_cookie, 1 * HOUR_IN_SECONDS);
			} else {
				throw new Exception('Unable to fetch wc_session_cookie');
			}

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
