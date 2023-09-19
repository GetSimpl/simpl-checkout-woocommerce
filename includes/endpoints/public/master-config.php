<?php

function scwp_fetch_master_config() {
	$scwp_simpl_host       = SCWP_Settings::scwp_simpl_host();
	$scwp_store_url        = SCWP_Settings::scwp_store_url();
	$simplTokenHeader = "simpl-widget-session-token";

	$unique_device_id = '';
	if ( function_exists( 'scwp_get_unique_device_id' ) ) {
		$unique_device_id = scwp_get_unique_device_id() ?: "";
	}
	$apiUrl            = "https://" . $scwp_simpl_host . "/api/v1/wc/widget/master-config?shop=" . $scwp_store_url;
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
			scwp_set_unique_device_id( $headers[ $simplTokenHeader ] );
		}
		$masterConfigData = isset( $body["success"] ) && isset( $body["data"] ) ? json_encode( $body["data"] ) : '{}';
		
		wp_enqueue_script( 'simpl-master-config' );
		wp_add_inline_script('simpl-master-config', 'var SimplMasterConfig = ' . $masterConfigData);
	} else {
		$error_message = $simplHttpResponse->get_error_message();
		scwp_console_log( $error_message );
	}
}