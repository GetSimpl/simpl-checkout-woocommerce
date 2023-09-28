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
        $store_url = WC_Simpl_Settings::store_url_with_prefix();
        $client_credentials = WC_Simpl_Settings::merchant_credentials();

        $data = $request["data"];
        $topic = $request["topic"];
        $resource = $request["resource"];
        $event = $request["event"];

        $simplHttpResponse = wp_remote_post("https://" . $simpl_host . "/order_hook", array(
            "body" => json_encode($data),
            "headers" => array(
                "content-type" => "application/json",
                "merchant_client_id" => $client_credentials["client_id"],
                WEBHOOK_SOURCE => $store_url,
                WEBHOOK_TOPIC => $topic,
                WEBHOOK_RESOURCE => $resource,
                WEBHOOK_EVENT => $event,
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