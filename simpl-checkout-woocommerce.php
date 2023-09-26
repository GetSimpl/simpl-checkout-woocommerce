<?php
/**
 * Plugin Name: Simpl Checkout
 * Plugin URI: http://www.getsimpl.com
 * Description: Simpl checkout offers an optimised checkout process, higher order conversions and RTO reduction. We offer Simpl Pay Later, Pay-in-3, UPI, Cards, and COD for seamless transactions while you focus on growing your business.
 * Author:  One Sigma Technologies Pvt. Ltd.
 * Author URI: http://www.getsimpl.com
 * Version: 1.1.6
 */
add_action('plugins_loaded', 'simpl_checkout_int', 0);
add_filter( 'woocommerce_payment_gateways', 'simpl_add_gateway_class' );
define('SIMPL_SENTRY_DSN_KEY', 'simpl_sentry_dsn'); 

include_once "sentry/lib/Raven/Autoloader.php";
Raven_Autoloader::register();

function captureSimplPluginLastError($sentry_client) {
    if (null === $error = error_get_last()) {
        return null;
    }

    if(!str_contains($error['file'], 'simpl-checkout-woocommerce')) {
        return null;
    }

    $e = new ErrorException(
        @$error['message'], 0, @$error['type'],
        @$error['file'], @$error['line']
    );

    return $sentry_client->captureException($e);
}

$sentry_client = simpl_sentry_client();
if(isset($sentry_client)) {
    captureSimplPluginLastError($sentry_client);
}

function simpl_sentry_client() {
    $sentry_dsn = get_option(SIMPL_SENTRY_DSN_KEY);
    if($sentry_dsn == "") {
        return null;        
    }
    include_once 'includes/admin/load.php';
    if( ! function_exists('get_plugin_data') ){
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $plugin_data = get_plugin_data( __FILE__ );
    $plugin_version = $plugin_data['Version'];
    define('SIMPL_PLUGIN_VERSION', $plugin_version);
    $client = new Raven_Client($sentry_dsn, array('environment' => WC_Simpl_Settings::sentry_environment(), 'release' => $plugin_version));
    $client->install();
    return $client;
}

function simpl_set_sentry_client($dsn) {
    try {
        add_option(SIMPL_SENTRY_DSN_KEY, $dsn);
        $sentry_client = simpl_sentry_client();
        if(isset($sentry_client)) {
            captureSimplPluginLastError($sentry_client);
        }
    } catch (Exception $fe) {
        return;
    }
}

function simpl_sentry_exception($err) {
    $client = simpl_sentry_client();
    if(isset($client)) {
        $client->captureException($err);
    }
}

function simpl_checkout_int() {

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
    define('WIDGET_SCRIPT_LOCALHOST', 'http://localhost:4300/');
    define("SIMPL_ORDER_STATUS_CHECKOUT", "checkout-draft");
    define("SIMPL_ORDER_METADATA", "is_simpl_checkout_order");
    define('WIDGET_SCRIPT_STAGING_URL', 'https://s3.ap-southeast-1.amazonaws.com/staging-cdn.getsimpl.com/widget-script-v2/woocommerce/simpl-checkout-woocommerce-widget.iife.js');
    define('WIDGET_SCRIPT_PRODUCTION_URL', 'https://simpl-cdn.s3.amazonaws.com/widget-script-v2/woocommerce/simpl-checkout-woocommerce-widget.iife.js');

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

    add_filter( 'woocommerce_payment_gateways', 'simpl_add_gateway_class' );
    add_action( 'plugins_loaded', 'simpl_init_gateway_class' );
    register_activation_hook( __FILE__, 'my_plugin_activate' );
    register_deactivation_hook( __FILE__, 'my_plugin_deactivate' );

    add_action( 'woocommerce_order_refunded', 'order_hook', 10, 1 );
}

function order_hook($order_id)
{
    $order = wc_get_order($order_id);
    
    $simpl_host = WC_Simpl_Settings::simpl_host();
    $store_url = WC_Simpl_Settings::store_url_with_prefix();
    $client_credentials = WC_Simpl_Settings::merchant_credentials();

    $simplHttpResponse = wp_remote_post("https://" . $simpl_host . "/order_hook", array(
        "body" => json_encode($order->get_data()),
        "headers" => array(
            "content-type" => "application/json",
            "merchant_client_id" => $client_credentials["client_id"],
            "X-WC-Webhook-Source" => $store_url,
        ),
    ));
    
    if ( ! is_wp_error( $simplHttpResponse ) ) {
        $body = json_decode( wp_remote_retrieve_body( $simplHttpResponse ), true );
        // echo(json_encode($body));
        // error_log(json_encode($body));
        // if($body["success"]) {
        //     error_log(print_r($body, TRUE)); 
        // } else {
        //     throw new Exception( $body['message'] );            
        // }
    } else {
        $error_message = $simplHttpResponse->get_error_message();
        error_log(print_r($error_message, TRUE)); 
        throw new Exception( $error_message );
    }
}