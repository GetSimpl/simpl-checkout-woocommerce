<?php

class SimplCheckoutCartControllerV2 {
    function create(WP_REST_Request $request) {
        foreach ( $request->get_params() as $key => $value ) {
            $_REQUEST[$key] = $value;
            $_POST[$key] = $value;
        }

        WC()->cart->empty_cart();
        WC_Form_Handler::add_to_cart_action();
        try {
            $si = new SimplCartResponse();
            return array('redirection_url'=>$si->cart_redirection_url(WC()->cart, $request));
        } catch (Exception $fe) {
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_CART_CREATE, "message" => 'error in creating checkout'), 500);
        }
	}
}
