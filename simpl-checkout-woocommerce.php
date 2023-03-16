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
include_once 'includes/admin/index.php';
include_once 'includes/endpoints/index.php';
include_once 'includes/widget/buy-now-button.php';


add_action( 'wp_loaded', 'maybe_load_cart', 5 );

function maybe_load_cart() {
	if ( version_compare( WC_VERSION, '3.6.0', '>=' ) && WC()->is_rest_api_request() ) {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}

		$rest_prefix = 'simpl/v1';
		$req_uri     = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );

		$is_my_endpoint = ( false !== strpos( $req_uri, $rest_prefix ) );

		if ( ! $is_my_endpoint ) {
			return;
		}

		require_once WC_ABSPATH . 'includes/wc-cart-functions.php';
		require_once WC_ABSPATH . 'includes/wc-notice-functions.php';

		if ( null === WC()->session ) {
			$session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

			// Prefix session class with global namespace if not already namespaced
			if ( false === strpos( $session_class, '\\' ) ) {
				$session_class = '\\' . $session_class;
			}

			WC()->session = new $session_class();
			WC()->session->init();
		}

		/**
		 * For logged in customers, pull data from their account rather than the
		 * session which may contain incomplete data.
		 */
		if ( is_null( WC()->customer ) ) {
			if ( is_user_logged_in() ) {
				WC()->customer = new WC_Customer( get_current_user_id() );
			} else {
				WC()->customer = new WC_Customer( get_current_user_id(), true );
			}

			// Customer should be saved during shutdown.
			add_action( 'shutdown', array( WC()->customer, 'save' ), 10 );
		}

		// Load Cart.
		if ( null === WC()->cart ) {
			WC()->cart = new WC_Cart();
		}
	}
} // END maybe_load_cart()



?>