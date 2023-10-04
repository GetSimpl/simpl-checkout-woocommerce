<?php

class SimplCheckout3ppClient {
    private $clientId;
    private $simplHost;
    private $storeUrl;

    function __construct($storeUrl, $simplHost, $clientId) {
        $this->storeUrl = $storeUrl;
        $this->simplHost = $simplHost;
        $this->clientId = $clientId;
    }

    function simpl_post_hook_request($request) {
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
            error_log(print_r($error_message, TRUE)); 
            throw new Exception( $error_message );
        }

        return $simplHttpResponse;
    }
}