<?php
/**
 * Plugin Name: Simpl checkout
 * Plugin URI: http://www.getsimpl.com
 * Description: A plugin creates checkout app for woocommerce
 * Author:  Getsimpl pvt. 
 * Author URI: http://www.getsimpl.com
 * Version: 1.0
 */
define('SIMPL_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
define('SIMPL_CONFIG_STAGING_URL', 'checkout-3pp.stagingsimpl.com');
define('SIMPL_CONFIG_PRODUCTION_URL', 'checkout-3pp.getsimpl.com');
define('WIDGET_SCRIPT_STAGING_URL', 'http://localhost:4300/');
define('WIDGET_SCRIPT_PRODUCTION_URL', 'https://res.cloudinary.com/dlkxxfbi9/raw/upload/v1678800309/simpl-checkout-woocommerce-widget.iife_fu8z9g.js');
include_once 'includes/admin/index.php';
include_once 'includes/endpoints/index.php';
include_once 'includes/widget/buy-now-button.php';
?>