<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SimplWcEventHelper {
    
    static function publish_event($event_name, $event_data, $entity, $flow) {
        $simpl_host = WC_Simpl_Settings::simpl_host();
        $event_payload = array(
            "trigger_timestamp" => time(),
            "event_name" => $event_name,
            "event_data" => $event_data,
            "entity" =>  $entity,
            "flow" => $flow
        );
        $simplHttpResponse = wp_remote_post("https://".$simpl_host."/api/v1/wc/publish/events", array(
            "body" => json_encode($event_payload),
            "headers" => array(            
                    "content-type" => "application/json"
                ),
        )); 
        return $simplHttpResponse;
    }
}

// TODO: add a wrapper instead of a helper function
function simpl_is_success_response($simplHTTPResponse) {
    if (isset($simplHTTPResponse) && $simplHTTPResponse["response"]["code"] == 200) {
        return true;
    }
    return false;
}
