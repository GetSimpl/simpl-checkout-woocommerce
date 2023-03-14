<?php

include_once SIMPL_PLUGIN_DIR . "/includes/simpl_integration/simpl_integration.php";
include_once SIMPL_PLUGIN_DIR . "/includes/helpers/cart_helper.php";

function create_cart( WP_REST_Request $request ) {
    $productID = $request->get_params()["product_id"];
    $variantID = $request->get_params()["variant_id"];
    $quantity = $request->get_params()["quantity"];
    initCartCommon();
    WC()->cart->add_to_cart($productID, $quantity, $variantID);
    return SimplIntegration::cart_redirection_url(WC()->cart);
}
?>