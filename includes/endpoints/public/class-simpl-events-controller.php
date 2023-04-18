<?php 
class SimplEventsController {
    function publish_events(WP_REST_Request $request) {
        try {
            SimplRequestValidator::validate_events_payload($request);
            $simpl_host = WC_Simpl_Settings::simpl_host();
            $req_body = $request->get_params()["event_payload"];
            $req_body["event_data"]["merchant_id"] = $simpl_host;
            $req_body["event_data"]["Simpl-Widget-Session-Token"] = get_unique_device_id();
            $req_body["event_data"]["Simpl-CTR-Unique-ID"] = $req_body["event_data"]["merchant_id"]."-".$req_body["event_data"]["Simpl-Widget-Session-Token"]."-".$req_body["trigger_timestamp"];
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
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in publishing event'), 500);
        }
    }

}
?>