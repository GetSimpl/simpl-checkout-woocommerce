<?php   

add_action( 'wp_loaded', 'maybe_load_cart', 5 );

function maybe_load_cart() {
	// WC 3.6+ - Cart and other frontend functions are not included for REST requests.
	if ( version_compare( WC_VERSION, '3.6.0', '>=' ) && WC()->is_rest_api_request() ) {

		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}

		$rest_prefix = 'simpl/v';
		$req_uri     = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		$is_my_endpoint = ( false !== strpos( $req_uri, $rest_prefix ) );
		if ( ! $is_my_endpoint ) {
			return;
		}


		if (defined('WC_ABSPATH')) {
			include_once WC_ABSPATH . 'includes/wc-notice-functions.php'; // nosemgrep: file-inclusion
			include_once WC_ABSPATH . 'includes/wc-template-hooks.php'; // nosemgrep: file-inclusion
			include_once WC_ABSPATH . 'includes/wc-cart-functions.php'; // nosemgrep: file-inclusion
		}
	
		if ( null === WC()->session ) {
			$session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			WC()->session = new $session_class();
			WC()->session->init();
		}

		// Set payment method into session - this also clears the payment method level discount applicable for competitor from cart
		// Avoid discount not applicable for Simpl
		WC()->session->set('chosen_payment_method', SIMPL_PAYMENT_GATEWAY);
	
		if (null === WC()->customer) {
			WC()->customer = new WC_Customer( get_current_user_id(), true );
		}
	
		if ( null === WC()->cart ) {
			WC()->cart = new WC_Cart();
		}

		// We set the chosen_payment_method above. In case of a change, everything needs to be recalculated to be safe
		WC()->cart->calculate_fees();
		// WC()->cart->calculate_shipping();
		WC()->cart->calculate_totals();
	}
}

function simpl_cart_init_common()
{ 
    if (defined('WC_ABSPATH')) {
        // WC 3.6+ - Cart and other frontend functions are not included for REST requests.
        include_once WC_ABSPATH . 'includes/wc-cart-functions.php'; // nosemgrep: file-inclusion
        include_once WC_ABSPATH . 'includes/wc-notice-functions.php'; // nosemgrep: file-inclusion
        include_once WC_ABSPATH . 'includes/wc-template-hooks.php'; // nosemgrep: file-inclusion
        // include_once SIMPL_PLUGIN_DIR . "/includes/helpers/notice_helper.php";
    }
    
	$session_class = apply_filters('woocommerce_session_handler', 'WC_Session_Handler');
	WC()->session  = new $session_class();
    WC()->session->init();
	WC()->session->set('chosen_payment_method', SIMPL_PAYMENT_GATEWAY);
	WC()->customer = new WC_Customer();        
	WC()->cart = new WC_Cart();
	WC()->cart->empty_cart();

	// If we pass the session_id, we can set the customer here
	//$user_id = $order->get_customer_id();
	//wc_set_customer_auth_cookie($user_id);
}
