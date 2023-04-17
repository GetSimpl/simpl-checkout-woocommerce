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

//This function will clear all type of notice or success
function simpl_hide_error_messages()
{

	$_SESSION["simpl_session_message"] = [];
	WC()->session->set('wc_notices', null);
	add_filter( 'woocommerce_notice_types', '__return_empty_array' );
	add_filter( 'wc_add_to_cart_message_html', '__return_false' );
	add_filter( 'woocommerce_cart_item_removed_notice_type', '__return_false' );
	add_filter( 'woocommerce_coupon_message', '' );
	wc_clear_notices();
}

?>