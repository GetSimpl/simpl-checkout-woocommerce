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
}
