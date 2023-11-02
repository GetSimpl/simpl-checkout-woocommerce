<?php
/**
 * Plugin Name: Simpl Checkout
 * Plugin URI: http://www.getsimpl.com
 * Description: Simpl checkout offers an optimised checkout process, higher order conversions and RTO reduction. We offer Simpl Pay Later, Pay-in-3, UPI, Cards, and COD for seamless transactions while you focus on growing your business.
 * Author:  One Sigma Technologies Pvt. Ltd.
 * Author URI: http://www.getsimpl.com
 * Version: 1.2.8
 */
add_action('plugins_loaded', 'simpl_checkout_int', 0);
add_filter( 'woocommerce_payment_gateways', 'simpl_add_gateway_class' );

function simpl_checkout_int() {

    if (!class_exists('WC_Payment_Gateway'))
    {
        return;
    }
    define('SIMPL_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
    define("SIMPL_ENV", getenv("SIMPL_ENV"));
    define("SIMPL_PRE_QA_QUERY_PARAM_KEY", "simpl-qa");
    define("SIMPL_PRE_QA_QUERY_PARAM_VALUE", "ce50e3b0-641b-4b26-8bbb-8a240f03811b");
    define('WIDGET_SCRIPT_LOCALHOST', 'http://localhost:4300/');
    define("SIMPL_ORDER_STATUS_CHECKOUT", "checkout-draft");
    define("SIMPL_ORDER_METADATA", "is_simpl_checkout_order");
    define('WIDGET_SCRIPT_STAGING_URL', 'https://s3.ap-southeast-1.amazonaws.com/staging-cdn.getsimpl.com/widget-script-v2/woocommerce/simpl-checkout-woocommerce-widget.iife.js');
    define('WIDGET_SCRIPT_SANDBOX_URL', 'https://s3.ap-southeast-1.amazonaws.com/sandbox-cdn.getsimpl.com/widget-script-v2/woocommerce/simpl-checkout-woocommerce-widget.iife.js');
    define('WIDGET_SCRIPT_QA_URL', 'https://s3.ap-south-1.amazonaws.com/qa-cdn.stagingsimpl.com/widget-script-v2/woocommerce/simpl-checkout-woocommerce-widget.iife.js');
    define('WIDGET_SCRIPT_PRODUCTION_URL', 'https://cdn.getsimpl.com/widget-script-v2/woocommerce/simpl-checkout-woocommerce-widget.iife.js');

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
    add_filter( 'woocommerce_shipping_chosen_method', '__return_false', 99); // this disables the application of default shipping method
    add_action( 'plugins_loaded', 'simpl_init_gateway_class' );
    
    // initiating logger instance
    $logger = get_simpl_logger();
}

// registering hooks as soon as the plugin starts so that we are able listen to the data as soon as plugin installed
// no matter it's activated or not
define('SIMPL_SANDBOX_STORE_URL', 'sandbox.1bill.in');
define('SIMPL_QA_STORE_URL', 'qa.1bill.in');
define('SIMPL_CONFIG_STAGING_URL', 'checkout-3pp.stagingsimpl.com');
define('SIMPL_CONFIG_SANDBOX_URL', 'sandbox-checkout-3pp.getsimpl.com');
define('SIMPL_CONFIG_QA_URL', 'qa-checkout-3pp.stagingsimpl.com');
define('SIMPL_CONFIG_PRODUCTION_URL', 'checkout-3pp.getsimpl.com');
define('SIMPL_PLUGIN_FILE_URL',  __FILE__ );
include_once 'includes/admin/class-simpl-checkout-settings.php';
include_once 'includes/clients/load.php';
include_once 'includes/hook_handlers/load.php';
