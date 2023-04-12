<?php

function publish_events( WP_REST_Request $request ) {
    $simpl_host = WC_Simpl_Settings::simpl_host();
    $simplHttpResponse = wp_remote_post("https://".$simpl_host."/api/v1/wc/publish/events", array(
        "body" => json_encode($request->get_params()),
        "headers" => array(            
                "content-type" => "application/json"
            ),
    )); 
    if ( ! is_wp_error( $simplHttpResponse ) ) {
        $body = json_decode( wp_remote_retrieve_body( $simplHttpResponse ), true );
    } else {
        $error_message = $simplHttpResponse->get_error_message();
        throw new Exception( $error_message );
    }
}
?>