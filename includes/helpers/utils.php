<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

function simpl_get_unique_device_id() {
	$unique_device_id = WC()->session->get("simpl:session:id") ?: "";
	return $unique_device_id;
}

function simpl_set_unique_device_id($unique_device_id){
	WC()->session->set("simpl:session:id", $unique_device_id);
}

?>