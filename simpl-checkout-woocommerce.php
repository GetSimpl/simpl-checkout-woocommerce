<?php
/**
 * Plugin Name: Simpl Checkout
 * Plugin URI: http://www.getsimpl.com
 * 
 * Description: Simpl checkout offers an optimised checkout process, higher order conversions and RTO reduction.
 * We offer Simpl Pay Later, Pay-in-3, UPI, Cards, and COD for seamless transactions while you focus on growing your business.
 * 
 * Author:  Bill Sigma Technologies Pvt. Ltd.
 * Author URI: http://www.getsimpl.com
 * 
 * Version: 2.0.1
 */
add_action('plugins_loaded', 'simpl_checkout_int', 0);

function simpl_checkout_int() {

    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    include_once 'includes/admin/load.php';
    if( ! function_exists('get_plugin_data') ){
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $plugin_data = get_plugin_data( __FILE__ );
    $plugin_version = $plugin_data['Version'];
    if (!defined('SIMPL_PLUGIN_VERSION')) {
        define('SIMPL_PLUGIN_VERSION', $plugin_version);    
    }

    define('SIMPL_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
    define("SIMPL_ENV", getenv("SIMPL_ENV"));
    define("SIMPL_PRE_QA_QUERY_PARAM_KEY", "simpl-qa");
    define("SIMPL_PRE_QA_QUERY_PARAM_VALUE", "ce50e3b0-641b-4b26-8bbb-8a240f03811b");
    // define('SIMPL_WIDGET_SCRIPT_LOCALHOST', 'http://localhost:4300/');
    define("SIMPL_ORDER_STATUS_CHECKOUT", "checkout-draft");
    define("SIMPL_ORDER_METADATA", "is_simpl_checkout_order");    

    $https = 'https://';
    $widget_script = 'widget-script-v2/woocommerce/simpl-checkout-woocommerce-widget.iife';
    $js = '.js';
    $staging_base_url = 's3.ap-southeast-1.amazonaws.com/staging-cdn.getsimpl.com/';
    $sandbox_base_url = 's3.ap-southeast-1.amazonaws.com/sandbox-cdn.getsimpl.com/';
    $qa_base_url = 's3.ap-south-1.amazonaws.com/qa-cdn.stagingsimpl.com/';
    $production_base_url = 'cdn.getsimpl.com/';
    
    $widget_script_staging_url = $https.$staging_base_url.$widget_script.$js;
    define('SIMPL_WIDGET_SCRIPT_STAGING_URL', $widget_script_staging_url);

    $widget_script_sandbox_url = $https.$sandbox_base_url.$widget_script.$js;
    define('SIMPL_WIDGET_SCRIPT_SANDBOX_URL', $widget_script_sandbox_url);

    $widget_script_qa_url = $https.$qa_base_url.$widget_script.$js;
    define('SIMPL_WIDGET_SCRIPT_QA_URL', $widget_script_qa_url);

    $widget_script_production_url = $https.$production_base_url.$widget_script.$js;
    define('SIMPL_WIDGET_SCRIPT_PRODUCTION_URL', $widget_script_production_url);
    
    define('SIMPL_WIDGET_SESSION_HEADER', 'simpl-widget-session-token');

    // Defined error CODE for API
    define('SIMPL_HTTP_ERROR_USER_NOTICE','user_error');
    define('SIMPL_HTTP_ERROR_BAD_REQUEST','bad_request');
    define('SIMPL_HTTP_ERROR_CART_CREATE','cart_creation_error');
    define('SIMPL_HTTP_ERROR_UNAUTHORIZED','unauthorized_request');

    include_once 'includes/utils/load.php';
    include_once 'includes/admin/load.php';

    include_once "includes/helpers/load.php";

    include_once 'includes/simpl_integration/load.php';
    include_once 'includes/endpoints/load.php';
    include_once 'includes/widget/load.php';
    include_once 'includes/plugin_support/load.php';
    include_once 'includes/clients/load.php';
    include_once 'includes/hook_handlers/load.php';

    add_filter( 'woocommerce_payment_gateways', 'simpl_add_gateway_class' );
    // The following was added to support in-house shipping. Commenting as we current do not support in-house shipping
    //add_filter( 'woocommerce_shipping_chosen_method', '__return_false', 99); // this disables the application of default shipping method
    add_action( 'plugins_loaded', 'simpl_init_gateway_class' );
    
    // initiating logger instance
    $logger = get_simpl_logger();
}

// registering hooks as soon as the plugin starts so that we are able listen to the data as soon as plugin installed
// no matter it's activated or not
define('SIMPL_STAGING_STORE_URL', 'shop.1bill.in');
define('SIMPL_SANDBOX_STORE_URL', 'sandbox.1bill.in');
define('SIMPL_QA_STORE_URL', 'qa.1bill.in');
define('SIMPL_CONFIG_STAGING_URL', 'checkout-3pp.stagingsimpl.com');
define('SIMPL_CONFIG_SANDBOX_URL', 'sandbox-checkout-3pp.getsimpl.com');
define('SIMPL_CONFIG_QA_URL', 'qa-checkout-3pp.stagingsimpl.com');
define('SIMPL_CONFIG_PRODUCTION_URL', 'checkout-3pp.getsimpl.com');
define('SIMPL_PLUGIN_FILE_URL',  __FILE__ );
include_once 'includes/admin/class-simpl-checkout-settings.php';
include_once 'includes/clients/load.php';
include_once 'includes/hook_handlers/setup-plugin-status-hooks.php';
