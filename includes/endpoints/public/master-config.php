<?php

function fetch_master_config() {
	$simpl_host       = WC_Simpl_Settings::simpl_host();
	$store_url        = WC_Simpl_Settings::store_url();
	$simplTokenHeader = "simpl-widget-session-token";

	$unique_device_id = '';
	if ( function_exists( 'get_unique_device_id' ) ) {
		$unique_device_id = get_unique_device_id() ?: "";
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
			set_unique_device_id( $headers[ $simplTokenHeader ] );
		}
		$masterConfigData = isset( $body["success"] ) && isset( $body["data"] ) ? json_encode( $body["data"] ) : '{}';
		
		wp_enqueue_script( 'simpl-master-config' );
		wp_add_inline_script('simpl-master-config', 'var SimplMasterConfig = ' . $masterConfigData);
	} else {
		$error_message = $simplHttpResponse->get_error_message();
		scw_console_log( $error_message );
	}
}