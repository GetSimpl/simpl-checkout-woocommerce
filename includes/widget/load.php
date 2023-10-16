<?php 

    include_once SIMPL_PLUGIN_DIR . '/includes/helpers/debug_helper.php';
	include_once SIMPL_PLUGIN_DIR . '/includes/helpers/utils.php';

	if ( ! is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
		include_once 'fetch-master-config.php'; // This will load the simpl-master-config on Frontend, On every page it will be get call
		include_once 'buy-now-button.php';
		include_once 'set-plugin-config.php';
	}

?>
