<?php

class SimplCheckoutCartController {
    function create(WP_REST_Request $request)
    {
        if (isset($request->get_params()["is_pdp"]) && $request->get_params()["is_pdp"]) {
            $productID = $request->get_params()["product_id"];
            $variantID = $request->get_params()["variant_id"];
            $quantity = $request->get_params()["quantity"];
            //TODO: we needs to add validation
            WC()->cart->empty_cart();
            WC()->cart->add_to_cart($productID, $quantity, $variantID);
        }
    
    
        try {
            $si = new SimplCartResponse();
            return $si->cart_redirection_url(WC()->cart);
        } catch (Exception $fe) {
            return new WP_Error("cart_creation_error", "error in creating checkout", array("error_mesage" => $fe->getMessage(), "backtrace" => $fe->getTraceAsString()));
        }
    }
}

