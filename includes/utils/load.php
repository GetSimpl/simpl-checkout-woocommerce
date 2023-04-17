<?php

class SimplUtil
{
    static function state_code_for_state($state_name)
    {
        $supported_states = array();

        $states = WC()->countries->get_states("IN");
        foreach ($states as $s_code => $s_name) {
            $supported_states[$s_name] = $s_code;
        }
        return $supported_states[$state_name];
    }

    static function country_code_for_country($country_name)
    {
        $supported_countries = array("india" => "IN");
        return $supported_countries[strtolower($country_name)];
    }

    static function get_ctr_unique_id($req_body) {
        if(NULL == $req_body["trigger_timestamp"]) {
            return new WP_REST_Response(array("code"=> SIMPL_HTTP_ERROR_BAD_REQUEST, "message"=> "trigger_timestamp is required"), 400);
        }
        if(NULL == $req_body["event_data"]) {
            return new WP_REST_Response(array("code"=> SIMPL_HTTP_ERROR_EVENT_PAYLOAD, "message"=> "event_data is required"), 400);
        }
        if(NULL == $req_body["event_data"]["merchant_id"]) {
            return new WP_REST_Response(array("code"=> SIMPL_HTTP_ERROR_EVENT_PAYLOAD, "message"=> "merchant_id is required"), 500);
        }
        if(NULL == $req_body["event_data"]["Simpl-Widget-Session-Token"]) {
            return new WP_REST_Response(array("code"=> SIMPL_HTTP_ERROR_EVENT_PAYLOAD, "message"=> "Simpl-Widget-Session-Token is required"), 500);
        }
        
        return $req_body["event_data"]["merchant_id"]."-".$req_body["event_data"]["Simpl-Widget-Session-Token"]."-".$req_body["trigger_timestamp"];
    }
}
