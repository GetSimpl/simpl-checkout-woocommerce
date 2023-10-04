<?php

class SimplCheckoutCartController {
    function create(WP_REST_Request $request)
    {
        if (isset($request->get_params()["is_pdp"]) && $request->get_params()["is_pdp"]) {
            $productID = $request->get_params()["product_id"];
            $variantID = $request->get_params()["variant_id"];
            $quantity = $request->get_params()["quantity"];
            $variation = array();
            if (isset($request->get_params()["attributes"])) {
                $variation = $request->get_params()["attributes"];
            }
            
            WC()->cart->empty_cart();
            WC()->cart->add_to_cart($productID, $quantity, $variantID, $variation);
        }
    
    
        try {
            $si = new SimplCartResponse();
            return array('redirection_url'=>$si->cart_redirection_url(WC()->cart, $request));
        } catch (Exception $fe) {
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_CART_CREATE, "message" => 'error in creating checkout'), 500);
        }
    }
}

