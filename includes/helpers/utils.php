<?php

    initCartCommon();

    function get_unique_device_id() {
        $unique_device_id = WC()->session->get("simpl:session:id");
        //TODO: remove random uid from this 
        if($unique_device_id) {
            $unique_device_id = WC()->session->get("simpl:session:id");
        } else {
            $unique_device_id = md5(uniqid(wp_rand(), true));
            WC()->session->set("simpl:session:id", $unique_device_id);
        }

        return $unique_device_id;
    }

    function set_unique_device_id($unique_device_id){
        WC()->session->set("simpl:session:id", $unique_device_id);
    }

?>