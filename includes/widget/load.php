<?php 

	include_once SIMPL_PLUGIN_DIR . '/includes/helpers/simpl_logger.php';
	include_once SIMPL_PLUGIN_DIR . '/includes/helpers/debug_helper.php';
	include_once SIMPL_PLUGIN_DIR . '/includes/helpers/utils.php';

	if ( ! is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
		include_once 'buy-now-button.php';
		include_once 'set-plugin-config.php';
	}

?>
