<?php

function get_unique_device_id() {
	if(WC()->session) {
		return WC()->session->get("simpl:session:id");
	}
	return "";
}

function set_unique_device_id($unique_device_id){
	if(WC()->session) {
		WC()->session->set("simpl:session:id", $unique_device_id);
	}
}

?>