<?php

class SimplCheckoutCartController {
    function create(WP_REST_Request $request) {
        if (isset($request->get_params()["is_pdp"]) && $request->get_params()["is_pdp"]) {
            $product_id = $request->get_params()["product_id"];
            $variant_id = $request->get_params()["variant_id"];
            $quantity = $request->get_params()["quantity"];
            $variation_attrs = array();
            if (isset($request->get_params()["attributes"])) {
                $variation_attrs = $request->get_params()["attributes"];
            }
            
            WC()->cart->empty_cart();
            WC()->cart->add_to_cart($product_id, $quantity, $variant_id, $variation_attrs);
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
            return new WP_REST_Response(array(
                "code" => SIMPL_HTTP_ERROR_CART_CREATE, 
                "message" => 'error in creating checkout'), 
                500);
        }
    }
}
