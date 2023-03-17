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
define("SIMPL_ENV", getenv("SIMPL_ENV"));
define('WIDGET_SCRIPT_LOCALHOST', 'http://localhost:4300/');
define('WIDGET_SCRIPT_STAGING_URL', 'https://res.cloudinary.com/dlkxxfbi9/raw/upload/v1678951701/script/simpl-checkout-woocommerce-widget.iife_rxwoq9.js');
define('WIDGET_SCRIPT_PRODUCTION_URL', 'https://res.cloudinary.com/dlkxxfbi9/raw/upload/v1678951701/script/simpl-checkout-woocommerce-widget.iife_rxwoq9.js');
define("SIMPL_ORDER_STATUS_CHECKOUT", "checkout-draft");
define("SIMPL_ORDER_METADATA", "is_simpl_checkout_order");
include_once 'includes/admin/index.php';
include_once 'includes/endpoints/index.php';
include_once 'includes/widget/buy-now-button.php';
?>