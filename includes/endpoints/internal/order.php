<?php

include_once SIMPL_PLUGIN_DIR . "/includes/simpl_integration/simpl_integration.php";
include_once SIMPL_PLUGIN_DIR . "/includes/helpers/cart_helper.php";
include_once SIMPL_PLUGIN_DIR . "/includes/helpers/wc_helper.php";


function create_order( WP_REST_Request $request ) {
    initCartCommon();
    WC()->cart->empty_cart();
    $order_id = $request->get_params()["checkout_order_id"];
    $order = wc_get_order((int)$order_id);
    WC()->session->order_awaiting_payment = $order->get_id();
    WC()->session->set("simpl_order_id", $order_id);
    $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
    if($available_gateways["simpl"]) {
        $result = $available_gateways["simpl"]->process_payment($order_id);
        WC()->session->set("simpl_order_id", "");
        if($result["result"] == "success") {
            $si = new SimplIntegration();
            $order_payload = $si->order_payload($order);
            $order_payload["order_status_url"] = $result["redirect"];
            return $order_payload;
        }
    } else {
        return new WP_REST_Response(array("code"=> "bad_request", "message"=> "order already confirmed"), 400);
    }

    return "";
}
