<?php
/**
 * Plugin Name: Simpl checkout
 * Plugin URI: http://www.getsimpl.com
 * Description: A plugin creates checkout app for woocommerce
 * Author:  Getsimpl pvt. 
 * Author URI: http://www.getsimpl.com
 * Version: 1.0
 */
add_action('plugins_loaded', 'simpl_checkout_int', 0);
add_filter( 'woocommerce_payment_gateways', 'simpl_add_gateway_class' );
function simpl_checkout_int() {

    if (!class_exists('WC_Payment_Gateway') || class_exists('WC_Razorpay'))
    {
        return;
    }
    define('SIMPL_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
    define('SIMPL_CONFIG_STAGING_URL', 'checkout-3pp.stagingsimpl.com');
    define('SIMPL_CONFIG_PRODUCTION_URL', 'checkout-3pp.getsimpl.com');
    define("SIMPL_ENV", getenv("SIMPL_ENV"));
    define('WIDGET_SCRIPT_LOCALHOST', 'http://localhost:4300/');
    define("SIMPL_ORDER_STATUS_CHECKOUT", "checkout-draft");
    define("SIMPL_ORDER_METADATA", "is_simpl_checkout_order");
    define('WIDGET_SCRIPT_STAGING_URL', 'https://res.cloudinary.com/dlkxxfbi9/raw/upload/v1680081376/latest/simpl-checkout-woocommerce-widget.iife_ccd59l.js');
    define('WIDGET_SCRIPT_PRODUCTION_URL', 'https://res.cloudinary.com/dlkxxfbi9/raw/upload/v1680081376/latest/simpl-checkout-woocommerce-widget.iife_ccd59l.js');
    include_once 'includes/admin/index.php';
    include_once 'includes/endpoints/index.php';
    include_once 'includes/widget/index.php';
    add_filter( 'woocommerce_payment_gateways', 'simpl_add_gateway_class' );
    add_action( 'plugins_loaded', 'simpl_init_gateway_class' );
    register_activation_hook( __FILE__, 'my_plugin_activate' );
    register_deactivation_hook( __FILE__, 'my_plugin_deactivate' );
}

?>
