<?php
/**
 * Plugin Name: Simpl Checkout for WooCommerce
 * Plugin URI: http://www.getsimpl.com
 * 
 * Description: Simpl checkout offers an optimised checkout process, higher order conversions and RTO reduction.
 * We offer Simpl Pay Later, Pay-in-3, UPI, Cards, and COD for seamless transactions while you focus on growing your business.
 * 
 * Author:  Bill Sigma Technologies Pvt. Ltd.
 * Author URI: http://www.getsimpl.com
 * 
 * Version: 1.1.6
 */

 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action('plugins_loaded', 'scwp_checkout_int', 0);
add_filter( 'woocommerce_payment_gateways', 'scwp_add_gateway_class' );

function scwp_checkout_int() {

    if (!class_exists('WC_Payment_Gateway'))
    {
        return;
    }
    define('SIMPL_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
    define('SIMPL_CONFIG_STAGING_URL', 'checkout-3pp.stagingsimpl.com');
    define('SIMPL_CONFIG_PRODUCTION_URL', 'checkout-3pp.getsimpl.com');
    define("SIMPL_ENV", getenv("SIMPL_ENV"));
    define("SIMPL_PRE_QA_QUERY_PARAM_KEY", "simpl-qa");
    define("SIMPL_PRE_QA_QUERY_PARAM_VALUE", "ce50e3b0-641b-4b26-8bbb-8a240f03811b");
    // define('WIDGET_SCRIPT_LOCALHOST', 'http://localhost:4300/');
    // this is used only to run locally
    define("SIMPL_ORDER_STATUS_CHECKOUT", "checkout-draft");
    define("SIMPL_ORDER_METADATA", "is_simpl_checkout_order");
    $https = 'https://';
    $staging_base_url = 's3.ap-southeast-1.amazonaws.com/';
    $staging_cdn_base_url = 'staging-cdn.getsimpl.com/';
    $widget_script = 'widget-script-v2/woocommerce/simpl-checkout-woocommerce-widget.iife';
    $js = '.js';
    $widget_script_staging_url = $https.$staging_base_url.$staging_cdn_base_url.$widget_script.$js;
    define('WIDGET_SCRIPT_STAGING_URL', $widget_script_staging_url);
    $production_base_url = 'cdn.getsimpl.com/';
    $widget_script_production_url = $https.$production_base_url.$widget_script.$js;
    define('WIDGET_SCRIPT_PRODUCTION_URL', $widget_script_production_url);

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

    add_filter( 'woocommerce_payment_gateways', 'scwp_add_gateway_class' );
    add_action( 'plugins_loaded', 'scwp_init_gateway_class' );
    register_activation_hook( __FILE__, 'scwp_activate' );
    register_deactivation_hook( __FILE__, 'scwp_deactivate' );
}
