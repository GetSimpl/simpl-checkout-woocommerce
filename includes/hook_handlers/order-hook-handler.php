<?php
function order_hook($order_id)
{
    $order = wc_get_order($order_id);

    $order_data = $order->get_data();
    $order_data["line_items"] = get_data($order->get_items());
    $order_data["tax_lines"] = get_data($order->get_taxes());
    $order_data["shipping_lines"] = get_data($order->get_shipping_methods());
    $order_data["refunds"] = get_data($order->get_refunds());

    $request["topic"] = "order.updated";
    $request["resource"] = "order";
    $request["event"] = "updated";
    $request["data"] = $order_data;

    $client_credentials = WC_Simpl_Settings::merchant_credentials();
    $store_url = WC_Simpl_Settings::store_url();
    $simpl_host = WC_Simpl_Settings::simpl_host();

    $checkout_3pp_client = new SimplCheckout3ppClient($store_url, $simpl_host, $client_credentials["client_id"]);
    try {
        $simplHttpResponse = $checkout_3pp_client->simpl_post_hook_request($request);
    } catch (\Throwable $th) { 
        error_log(print_r($th, TRUE)); 
    }
}

function get_data($obj) {
    $response = array();
    foreach( $obj as $obj_item ){
        array_push($response, $obj_item->get_data());
    }

    return $response;
}