<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'template_redirect', 'simpl_force_login' );

//Login the customer immediately after registeration
function simpl_force_login () {

	// Force login only when redirecting to Thankyou page
	if( is_wc_endpoint_url( 'order-received' ) ) {

		// Force login only when guest checkout is not allowd and we create the customer
		if('yes' !== get_option( 'woocommerce_enable_guest_checkout' )) {

			// If user is already logged-in, do nothing
			if ( is_user_logged_in() ) return;

			$order_id = wc_get_order_id_by_order_key( esc_attr( $_GET['key'] ) );
			$order = wc_get_order( $order_id );

			// Force login only for Simpl checkout
			if('yes' == $order->get_meta(SIMPL_ORDER_METADATA) ) {			
				
				$user_id = $order->get_customer_id();

				// Link past orders to this newly created customer. Below function is not tested yet.
				//wc_update_new_customer_past_orders( $user_id );
				
				wc_set_customer_auth_cookie($user_id);
			}
		}
	}
}
