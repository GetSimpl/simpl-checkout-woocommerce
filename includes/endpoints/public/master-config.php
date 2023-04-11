<?php

function fetch_master_config( ) {
    $simpl_host = WC_Simpl_Settings::simpl_host();
    $store_url = WC_Simpl_Settings::store_url();
    $unique_device_id = get_unique_device_id();
    $simplHttpResponse = wp_remote_get( "https://".$simpl_host."/api/v1/wc/widget/master-config?shop=".$store_url, array(
        "headers" => array(
                "simpl-widget-session-token" => $unique_device_id,
                "content-type" => "application/json"
            ),
    ));

    if ( ! is_wp_error( $simplHttpResponse ) ) {
        $body = json_decode( wp_remote_retrieve_body( $simplHttpResponse ), true );
        $headers = wp_remote_retrieve_headers($simplHttpResponse);
        if(isset($headers["simpl-widget-session-token"])){
            set_unique_device_id($headers["simpl-widget-session-token"]);
        }
        if(isset($body["success"]) && isset($body["data"])) {
            echo("<script>var SimplMasterConfig = ". json_encode($body["data"]) ."</script>");
        } else {
            throw new Exception( $body['message'] );            
        }
    } else {
        $error_message = $simplHttpResponse->get_error_message();
        console_log($error_message);
        //Add airbrake alert here
        throw new Exception( $error_message );
    }
}
