<?php

const SIMPL_SESSION_HEADER_KEY = 'simpl-widget-session-token';

const GET_MASTER_CONFIG_PATH = 'https://%s/api/v1/wc/widget/master-config?shop=%s';
const POST_HOOK_PATH = 'https://%s/wc/hook';

//TODO: need to implement this proper client -> service -> controller way
class SimplCheckout3ppClient {
    private $client_id;
    private $simpl_host;
    private $store_url;

    function __construct() {
        $client_credentials = WC_Simpl_Settings::merchant_credentials();
        $this->client_id = $client_credentials["client_id"];
        $this->store_url = WC_Simpl_Settings::store_url();;
        $this->simpl_host = WC_Simpl_Settings::simpl_host();
    }

    function post_hook_request($request) {
        $request["merchant_client_id"] = $this->client_id;
        $request["store_url"] = $this->store_url;

        $request_url = sprintf(POST_HOOK_PATH, $this->simpl_host);
        
        $simplHttpResponse = wp_remote_post($request_url, array(
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

    function get_master_config(WP_REST_Request $request) {
        // TODO: do this cache flushing through middleware
        // wp_cache_flush();
        $request_url = sprintf(GET_MASTER_CONFIG_PATH, $this->simpl_host, $this->store_url);
        $unique_device_id = '';
        if ($request->get_header(SIMPL_SESSION_HEADER_KEY)) {
            $unique_device_id = $request->get_header(SIMPL_SESSION_HEADER_KEY);
        }

        $resp = wp_remote_get( $request_url,
            array(
                "headers" => array(
                    SIMPL_SESSION_HEADER_KEY => $unique_device_id,
                    "content-type"    => "application/json"
                ),
            )
        );

        $body = json_decode( wp_remote_retrieve_body( $resp )) ;
        if ( is_wp_error( $resp ) ) {
            $error_message = $resp->get_error_message();
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $error_message), 500);
        }

        $response = new WP_REST_Response($body, 200);
        $headers = wp_remote_retrieve_headers( $resp );
        if ( isset( $headers[ SIMPL_SESSION_HEADER_KEY ] ) ) {
            $response->header(SIMPL_SESSION_HEADER_KEY, $headers[ SIMPL_SESSION_HEADER_KEY ]);
        }
        if ( isset( $body->success ) && isset( $body->data ) ) {
            return $response;
        }
        return $response;
    }
}
