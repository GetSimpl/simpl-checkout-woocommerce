<?php

/**
 * Plugin Name: Simpl woocommerce checkout
 * Plugin URI: http://www.getsimpl.com
 * Description: A plugin creates checkout app for woocommerce
 * Author:  Getsimpl pvt. 
 * Author URI: http://www.getsimpl.com
 * Version: 1.0
 */
define('SIMPL_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
define('SIMPL_CONFIG_STAGING_URL', 'checkout-3pp.stagingsimpl.com');
define('SIMPL_CONFIG_PRODUCTION_URL', 'checkout-3pp.getsimpl.com');
include_once 'simpl-settings.php';
include_once 'simpl-payments.php';
include_once 'endpoints/api.php';
include_once 'widget/buy-now-button.php';
?>