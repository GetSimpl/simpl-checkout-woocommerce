<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly      
function scwp_authenticate_simpl( WP_REST_Request $request ) {    
    //POST call to simpl with credentials
    //adds header related to shop domain
    $scwp_simpl_host = SCWP_Settings::scwp_simpl_host();
    $scwp_store_url = SCWP_Settings::scwp_store_url();
    $client_credentials = SCWP_Settings::scwp_merchant_credentials();
    $simplHttpResponse = wp_remote_post( "https://".$scwp_simpl_host."/api/v1/wc/app/install", array(
        "body" => json_encode($request->get_params()),
        "headers" => array(
                "shop_domain" => $scwp_store_url,
                "merchant_client_id" => $client_credentials["client_id"],
                "merchant_client_secret" => $client_credentials["client_secret"],                
                "content-type" => "application/json"
            ),
    ));

    if ( ! is_wp_error( $simplHttpResponse ) ) {
        $body = json_decode( wp_remote_retrieve_body( $simplHttpResponse ), true );
        echo(wp_kses(json_encode($body)));
        if($body["success"]) {
            scwp_add_option(SCWP_Settings::scwp_authorized_flag_key(), "true"); //todo : doubt
        } else {
            throw new Exception( $body['message'] );            
        }
    } else {
        $error_message = $simplHttpResponse->get_error_message();
        throw new Exception( $error_message );
    }
}

function scwp_revert_authorization_flag( WP_REST_Request $request ) {
    update_option(SCWP_Settings::scwp_authorized_flag_key(), "false");   
}
?>