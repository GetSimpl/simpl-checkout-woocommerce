<?php
function plugin_status_hook($plugin_status) {
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
        $logger->error(print_r($th, TRUE));
    }
}

function plugin_status_activate_hook() {
    plugin_status_hook("active");
}

function plugin_status_deactivate_hook() {
    plugin_status_hook("inactive");
}