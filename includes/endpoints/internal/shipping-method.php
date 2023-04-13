<?php

include_once SIMPL_PLUGIN_DIR . "/includes/simpl_integration/simpl_integration.php";
include_once SIMPL_PLUGIN_DIR . "/includes/helpers/cart_helper.php";
include_once SIMPL_PLUGIN_DIR . "/includes/helpers/wc_helper.php";


function set_shipping_method(WP_REST_Request $request)
{
    initCartCommon();
    WC()->cart->empty_cart();
    validate_shipping_method_request($request);
    $order_id = $request->get_params()["checkout_order_id"];
    $order = wc_get_order((int)$order_id);
    if ($order) {
        $cart = convert_wc_order_to_wc_cart($order);
        WC()->session->set('chosen_shipping_methods', array($request->get_params()["shipping_method_id"]));
        update_shipping_line($order_id);
        WC()->cart->calculate_shipping();
        WC()->cart->calculate_totals();
        $si = new SimplIntegration();
        return $si->cart_payload(WC()->cart, $order_id);
    }
    return "";
}
