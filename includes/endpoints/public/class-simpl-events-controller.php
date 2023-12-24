<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SimplEventsController {
    
    function publish_events(WP_REST_Request $request) {
        try {
            SimplRequestValidator::validate_events_payload($request);
            $simpl_host = WC_Simpl_Settings::simpl_host();
            $shop_domain = WC_Simpl_Settings::store_url();
            $req_body = $request->get_params()["event_payload"];
            $req_body["event_data"]["merchant_id"] = $shop_domain;
            $req_body["event_data"]["plugin_version"] = SIMPL_PLUGIN_VERSION;
            $unique_id = $request->get_header(SIMPL_WIDGET_SESSION_HEADER);
            if($unique_id == "") {
                return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_UNAUTHORIZED, "message" => "session id can not be empty"), 401);
            }
            $req_body["event_data"]["Simpl-Widget-Session-Token"] = $unique_id;
            $req_body["event_data"]["Simpl-CTR-Unique-ID"] = $shop_domain."-".$req_body["event_data"]["Simpl-Widget-Session-Token"]."-".$req_body["trigger_timestamp"];
            $simplHttpResponse = wp_remote_post("https://".$simpl_host."/api/v1/wc/publish/events", array(
                "body" => json_encode($req_body),
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
        } catch (HttpBadRequest $fe) {
            simpl_get_logger()->error(wc_print_r($fe, true));
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            simpl_get_logger()->error(wc_print_r($fe, true));
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            simpl_get_logger()->error(wc_print_r($fe, true));
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        }
    }

}
