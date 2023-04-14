<?php 
class SimplEventsController {
    function publish_events(WP_REST_Request $request) {
        if(NULL == $request->get_params()["event_payload"]) {
            return new WP_REST_Response(array("code"=> "bad_request", "message"=> "event_payload is required"), 400);   
        }

        if(NULL == $request->get_params()["event_payload"]["entity"]) {
            return new WP_REST_Response(array("code"=> "bad_request", "message"=> "entity is required"), 400);   
        }

        if(NULL == $request->get_params()["event_payload"]["event_name"]) {
            return new WP_REST_Response(array("code"=> "bad_request", "message"=> "event_name is required"), 400);   
        }

        if(NULL == $request->get_params()["event_payload"]["flow"]) {
            return new WP_REST_Response(array("code"=> "bad_request", "message"=> "flow is required"), 400);   
        }

        if(NULL == $request->get_params()["event_payload"]["flow"]) {
            return new WP_REST_Response(array("code"=> "bad_request", "message"=> "flow is required"), 400);   
        }

        if(NULL == $request->get_params()["event_payload"]["event_data"]) {
            return new WP_REST_Response(array("code"=> "bad_request", "message"=> "event_data is required"), 400);   
        }

        $simpl_host = WC_Simpl_Settings::simpl_host();
        $req_body1 = $request->get_params()["event_payload"];
        $req_body["event_data"]["Simpl-Widget-Session-Token"] = "1234567890"; //use the function once other pr gets merged
        $req_body["event_data"]["Simpl-CTR-Unique-ID"] = get_ctr_unique_id($req_body);
        $req_body["event_data"]["merchant_id"] = $simpl_host;
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
    }

    function get_ctr_unique_id($req_body) {
        if(NULL == $req_body["trigger_timestamp"]) {
            return new WP_REST_Response(array("code"=> "bad_request", "message"=> "trigger_timestamp is required"), 400);
        }
        
        return $req_bodyy["event_data"]["merchant_id"]."-".$req_body["event_data"]["Simpl-Widget-Session-Token"]."-".$req_body["trigger_timestamp"];
    }
}
?>