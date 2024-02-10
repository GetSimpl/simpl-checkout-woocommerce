<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function simpl_plugin_status_hook($plugin_status) {
    $request["topic"] = "plugin.status_updated";
    $request["resource"] = "plugin";
    $request["event"] = "status_updated";
    $data = array();
    $data["status"] = $plugin_status;
    $request["data"] = $data;

    $checkout_3pp_client = new SimplCheckout3ppClient();
    try {
        $simplHttpResponse = $checkout_3pp_client->post_hook_request($request);
    } catch (\Throwable $th) { 
        $logger->error(wc_print_r($th, TRUE));
    }
}

function simpl_plugin_status_activate_hook() {
    simpl_plugin_status_hook("active");
}

function simpl_plugin_status_deactivate_hook() {
    simpl_plugin_status_hook("inactive");
}
