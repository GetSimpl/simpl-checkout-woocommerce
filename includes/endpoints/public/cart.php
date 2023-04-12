<?php

include_once SIMPL_PLUGIN_DIR . "/includes/simpl_integration/simpl_integration.php";
include_once SIMPL_PLUGIN_DIR . "/includes/helpers/cart_helper.php";

function create_cart(WP_REST_Request $request)
{
    if (isset($request->get_params()["is_pdp"]) && $request->get_params()["is_pdp"]) {
        $productID = $request->get_params()["product_id"];
        $variantID = $request->get_params()["variant_id"];
        $quantity = $request->get_params()["quantity"];
        WC()->cart->empty_cart();
        WC()->cart->add_to_cart($productID, $quantity, $variantID);
    }

    try {
        $si = new SimplIntegration();
        return $si->cart_redirection_url(WC()->cart);
    } catch (Exception $fe) {
	    return new WP_REST_Response(array("code"=> "cart_creation_error", "message"=> 'error in creating checkout'), $fe->getCode());
    }
}

function getCart()
{
    initCartCommon();
}
