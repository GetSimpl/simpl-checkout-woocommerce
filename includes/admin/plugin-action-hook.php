<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly   

function simpl_checkout_plugin_activate() {    
    //wp_remote_get("https://webhook.site/15d3a3ef-58bc-41bc-9633-0e9f19593c69?activated=true");
}

function simpl_checkout_plugin_deactivate() {
    $simpl_host = Simpl_WC_Settings::simpl_host();
    $current_user = wp_get_current_user();
    $email = $current_user->user_email;
    $admin = $current_user->user_login;
    $event_name = "Update Plugin";
    $entity = "Manage Plugin";
    $flow = "woocommerce-admin plugin page";
    $event_data = array(
        "merchant_id" => $simpl_host,
        "action" => "plugin deactivate",
        "user" => $admin,
        "email" => $email
    );
    $simplHttpResponse = SimplWcEventHelper::publish_event($event_name, $event_data, $entity, $flow);
}
?>