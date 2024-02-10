<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include_once 'plugin-status-hook-handler.php';

// Simpl plugin activate/de-activate hooks
register_activation_hook( SIMPL_PLUGIN_FILE_URL, 'simpl_plugin_status_activate_hook' );
register_deactivation_hook( SIMPL_PLUGIN_FILE_URL, 'simpl_plugin_status_deactivate_hook' );
