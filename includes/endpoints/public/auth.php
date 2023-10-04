<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
  
function simpl_authenticate( WP_REST_Request $request ) {    
    //POST call to simpl with credentials
    //adds header related to shop domain
    $simpl_host = Simpl_WC_Settings::simpl_host();
    $store_url = Simpl_WC_Settings::store_url();
    $client_credentials = Simpl_WC_Settings::merchant_credentials();
    $simpl_authorized_flag_key = Simpl_WC_Settings::simpl_authorized_flag_key();
    $simplHttpResponse = wp_remote_post( "https://".$simpl_host."/api/v1/wc/app/install", array(
        "body" => json_encode($request->get_params()),
        "headers" => array(
                "shop_domain" => $store_url,
                "merchant_client_id" => $client_credentials["client_id"],
                "merchant_client_secret" => $client_credentials["client_secret"],                
                "content-type" => "application/json"
            ),
    ));

    if ( ! is_wp_error( $simplHttpResponse ) ) {
        $body = json_decode( wp_remote_retrieve_body( $simplHttpResponse ), true );
        echo(wp_kses(json_encode($body)));
        if($body["success"]) {
            add_option( $simpl_authorized_flag_key, "true" );
        } else {
            throw new Exception( $body['message'] );            
        }
    } else {
        $error_message = $simplHttpResponse->get_error_message();
        throw new Exception( $error_message );
    }
}

function simpl_revert_authorization_flag( WP_REST_Request $request ) {
    update_option( $simpl_authorized_flag_key, "false" );   
}
?>