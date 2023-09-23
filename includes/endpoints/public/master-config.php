<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly      
function simpl_fetch_master_config() {
	$simpl_host       = Simpl_WC_Settings::simpl_host();
	$store_url        = Simpl_WC_Settings::store_url();
	$simplTokenHeader = "simpl-widget-session-token";

	$unique_device_id = '';
	if ( function_exists( 'simpl_get_unique_device_id' ) ) {
		$unique_device_id = simpl_get_unique_device_id() ?: "";
	}
	$apiUrl            = "https://" . $simpl_host . "/api/v1/wc/widget/master-config?shop=" . $store_url;
	$simplHttpResponse = wp_remote_get( $apiUrl,
		array(
			"headers" => array(
				$simplTokenHeader => $unique_device_id,
				"content-type"    => "application/json"
			),
		)
	);
	$body              = json_decode( wp_remote_retrieve_body( $simplHttpResponse ), true );

	if ( ! is_wp_error( $simplHttpResponse ) ) {
		$headers = wp_remote_retrieve_headers( $simplHttpResponse );
		if ( isset( $headers[ $simplTokenHeader ] ) ) {
			simpl_set_unique_device_id( $headers[ $simplTokenHeader ] );

			wp_register_script( 'simpl-widget-session-token', '' );
			wp_enqueue_script( 'simpl-widget-session-token' );
			wp_add_inline_script('simpl-widget-session-token', 'localStorage.setItem("'. $simplTokenHeader. '", "'. $headers[ $simplTokenHeader ].'")');
		}
		$masterConfigData = isset( $body["success"] ) && isset( $body["data"] ) ? json_encode( $body["data"] ) : '{}';
		
		wp_register_script( 'simpl-master-config', '' );
		wp_enqueue_script( 'simpl-master-config' );
		wp_add_inline_script('simpl-master-config', 'var SimplMasterConfig = ' . $masterConfigData, false);
	} else {
		$error_message = $simplHttpResponse->get_error_message();
		simpl_console_log( $error_message );
	}
}