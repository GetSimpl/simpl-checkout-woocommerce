<?php

class Simpl_Checkout_3pp_Client {
    public static function init() {
        define('WEBHOOK_SOURCE', 'X-WC-Webhook-Source');
        define('WEBHOOK_TOPIC', 'X-WC-Webhook-Topic');
        define('WEBHOOK_RESOURCE', 'X-WC-Webhook-Resource');
        define('WEBHOOK_EVENT', 'X-WC-Webhook-Event');
    }

    function simpl_post_hook_request($request) {
        $simpl_host = WC_Simpl_Settings::simpl_host();
        
        $client_credentials = WC_Simpl_Settings::merchant_credentials();
        $request["merchant_client_id"] = $client_credentials["client_id"];
        $request["store_url"] = WC_Simpl_Settings::store_url();
        
        $simplHttpResponse = wp_remote_post("https://" . $simpl_host . "/hook", array(
            "body" => json_encode($request),
            "headers" => array(
                "content-type" => "application/json",
            ),
        ));

        if ( ! is_wp_error( $simplHttpResponse ) ) {
            $body = json_decode( wp_remote_retrieve_body( $simplHttpResponse ), true );
        } else {
            $error_message = $simplHttpResponse->get_error_message();
            error_log(print_r($error_message, TRUE)); 
            throw new Exception( $error_message );
        }

        return $simplHttpResponse;
    }
}

Simpl_Checkout_3pp_Client::init();