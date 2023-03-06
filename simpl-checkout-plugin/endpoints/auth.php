<?php
function authenticate_simpl( WP_REST_Request $request ) {    
    //POST call to simpl with credentials
    //adds header related to shop domain
    echo(json_encode($request->get_params()));
    $simpl_host = WC_Simpl_Settings::simpl_host();
    $store_url = WC_Simpl_Settings::store_url();
    $simplHttpResponse = wp_remote_post( "https://".$simpl_host."/api/v1/app/install", array(
        "body" => json_encode($request->get_params()),
        "headers" => array("shop-domain" => $store_url, "content-type" => "application/json"),
    ));

    if ( ! is_wp_error( $simplHttpResponse ) ) {
        $body = json_decode( wp_remote_retrieve_body( $simplHttpResponse ), true );
        echo(json_encode($body));
        if($body["success"]) {
            add_option(WC_Simpl_Settings::simpl_authorized_flag_key(), "true");

        } else {
            throw new Exception( $body['message'] );            
        }
    } else {
        $error_message = $simplHttpResponse->get_error_message();
        throw new Exception( $error_message );
    }
}

function revert_authorization_flag( WP_REST_Request $request ) {
    update_option(WC_Simpl_Settings::simpl_authorized_flag_key(), "false");   
}
?>