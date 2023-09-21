<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly      
function scwp_get_unique_device_id() {
	$unique_device_id = WC()->session->get("simpl:session:id") ?: "";
	return $unique_device_id;
}

function scwp_set_unique_device_id($unique_device_id){
	WC()->session->set("simpl:session:id", $unique_device_id);
}

?>