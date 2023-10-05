<?php

class SimplCheckout3ppClient {
    private $clientId;
    private $simplHost;
    private $storeUrl;

    function __construct() {
        $client_credentials = WC_Simpl_Settings::merchant_credentials();
        $this->clientId = $client_credentials["client_id"];
        $this->storeUrl = WC_Simpl_Settings::store_url();;
        $this->simplHost = WC_Simpl_Settings::simpl_host();
    }

    function post_hook_request($request) {
        $request["merchant_client_id"] = $this->clientId;
        $request["store_url"] = $this->storeUrl;
        
        $simplHttpResponse = wp_remote_post("https://" . $this->simplHost . "/hook", array(
            "body" => json_encode($request),
            "headers" => array(
                "content-type" => "application/json",
            ),
        ));

        if ( ! is_wp_error( $simplHttpResponse ) ) {
            $body = json_decode( wp_remote_retrieve_body( $simplHttpResponse ), true );
        } else {
            $error_message = $simplHttpResponse->get_error_message();
            throw new Exception( $error_message );
        }

        return $simplHttpResponse;
    }
}