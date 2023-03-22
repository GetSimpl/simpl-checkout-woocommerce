<?php
    function create_events(WP_REST_Request $request) {
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

        return new WP_REST_Response($request->get_params("event_payload"));
    }
?>