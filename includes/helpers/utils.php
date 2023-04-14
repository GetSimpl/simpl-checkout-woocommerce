<?php

function get_unique_device_id() {
	simpl_cart_init_common();
	$unique_device_id = WC()->session->get("simpl:session:id") ?: "";
	return $unique_device_id;
}

function set_unique_device_id($unique_device_id){
	simpl_cart_init_common();
	WC()->session->set("simpl:session:id", $unique_device_id);
}

?>