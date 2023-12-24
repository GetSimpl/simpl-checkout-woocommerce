<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SimplUtil {

    static function state_code_for_state($state_name) {
        
        $supported_states = array();

        $states = WC()->countries->get_states("IN");
        foreach ($states as $s_code => $s_name) {
            $supported_states[$s_name] = $s_code;
        }
        return $supported_states[$state_name];
    }

    static function state_name_from_code($state_code)
    {
        $states = WC()->countries->get_states("IN");
        return $states[$state_code];
    }

    static function country_code_for_country($country_name)
    {
        $supported_countries = array("india" => "IN");
        return $supported_countries[strtolower($country_name)];
    }

    static function country_name_from_code($country_code)
    {
        $supported_countries = array("IN" => "India");
        return $supported_countries[$country_code];
    }

    static function get_data($obj) {
        $response = array();
        foreach( $obj as $obj_item ){
            array_push($response, $obj_item->get_data());
        }
    
        return $response;
    }
}
