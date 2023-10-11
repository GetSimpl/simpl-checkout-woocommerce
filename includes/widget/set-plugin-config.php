<?php
add_action( 'wp_head', 'simpl_set_plugin_config');

function simpl_is_cta_enabled() {
	$queries = array();
  parse_str($_SERVER['QUERY_STRING'], $queries);
  $is_simpl_pre_qa_env = (isset($queries[SIMPL_PRE_QA_QUERY_PARAM_KEY]) && $queries[SIMPL_PRE_QA_QUERY_PARAM_KEY] == SIMPL_PRE_QA_QUERY_PARAM_VALUE);
  $is_simpl_enabled_for_admin = WC_Simpl_Settings::is_simpl_enabled_for_admins() && current_user_can('manage_woocommerce');  

  $is_simpl_cta_enabled = WC_Simpl_Settings::is_simpl_button_enabled() || $is_simpl_enabled_for_admin || $is_simpl_pre_qa_env;

	return $is_simpl_cta_enabled;
}

function simpl_set_plugin_config() {
	
	if(simpl_is_cta_enabled()) {
		$simpl_plugin_settings = WC_Simpl_Settings::simpl_get_all_latest_settings();

		if(array_key_exists('merchant_client_secret', $simpl_plugin_settings)) {
			unset($simpl_plugin_settings['merchant_client_secret']);
		}

		if (!wp_script_is( 'simpl-plugin-helper', 'enqueued' )) {
			wp_register_script( 'simpl-plugin-helper', '' );
			wp_enqueue_script( 'simpl-plugin-helper' );
		}
	
		wp_add_inline_script('simpl-plugin-helper', 'var SimplPluginConfig = ' . json_encode($simpl_plugin_settings) . ';', false);

	}
}
