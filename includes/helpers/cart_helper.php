<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'before_woocommerce_init', 'simpl_before_woocommerce_init', 5 );
add_action( 'woocommerce_init', 'simpl_woocommerce_init', 5 );
add_filter( 'woocommerce_is_rest_api_request', 'simpl_is_woocommerce_rest_api_request');
add_filter( 'woocommerce_rest_is_request_to_rest_api', 'simpl_is_woocommerce_rest_api_request');

function simpl_woocommerce_init() {
	
	//Only for simpl request, bail otherwise
	if ( !simpl_is_woocommerce_rest_api_request(false, true) ) {
		return;
	}
	
	//Add to cart action is hooked to wp_loaded and hence invoked automatically.
	//Since this actions is triggered from PDP, we need to clear the cart. woocommerce_init precedes wp_loaded and hence this is the best hook for the same.
	if ( isset( $_REQUEST['add-to-cart'] ) && is_numeric( wp_unslash( $_REQUEST['add-to-cart'] ) ) ) {
		//Clear any existing error messages to start afresh
		wc_clear_notices();
		WC()->cart->empty_cart();
	}
}

//Load cookie for Simpl APIs
function simpl_before_woocommerce_init() {
	
	//Only for simpl request, bail otherwise
	if ( !simpl_is_woocommerce_rest_api_request(false, true) ) {
		return;
	}
	
	$request_body = file_get_contents('php://input');
	$data = json_decode($request_body);
	$wc_session_cookie = null;
	
	//For checkout create/update calls, 3PP would send cart token. Here, we'd create order and store order <> cart token map	
	if(isset($data->cart_token)) {
		$cart_session_token = $data->cart_token;
		$wc_session_cookie = get_transient($cart_session_token);
	} elseif (isset($data->checkout_order_id)) {
	//For remaining, we'll pull cart token from order and subsequently cookie from token.
		$order_id = $data->checkout_order_id;
		$cart_session_token = get_transient('simpl_'.$order_id);
		$wc_session_cookie = get_transient($cart_session_token);
	} elseif ( isset( $_REQUEST['checkout_order_id'] ) ) {
	//We get the params as part of REQUEST object for HTTP DELETE method - remove coupons
		$order_id = $_REQUEST['checkout_order_id'];
		$cart_session_token = get_transient('simpl_'.$order_id);
		$wc_session_cookie = get_transient($cart_session_token);
	}

	if($wc_session_cookie) {
		simpl_set_cookie($wc_session_cookie);
	}
}

//WooCommerce checks if the API request is REST or Frontend.
//Respond false to treat Simpl API requests as Frontend to load session, cart, etc.
function simpl_is_woocommerce_rest_api_request($is_rest_api_request, $simpl_response = false) {
	
	if ( version_compare( WC_VERSION, '3.6.0', '>=' ) ) {
		if ( empty( sanitize_url( $_SERVER['REQUEST_URI'] ) ) ) {
			return $is_rest_api_request;
		}

		$rest_prefix = 'simpl/';
		$req_uri     = esc_url_raw( wp_unslash( sanitize_url( $_SERVER['REQUEST_URI'] ) ) );

		$is_my_endpoint = ( false !== strpos( $req_uri, $rest_prefix ) );

		if ( ! $is_my_endpoint ) {
			return $is_rest_api_request;
		}

		//Do not want to initialise session, cart, etc for the following
		if ( false !== strpos( $req_uri, '/events' ) || false !== strpos( $req_uri, '/master-config' ) || 
			false !== strpos( $req_uri, '/authenticate_simpl' ) || false !== strpos( $req_uri, '/revert_authenticate_simpl' ) ) {
				return $is_rest_api_request;
		}
		
		//Return false for Simpl Checkout APIs to load everything
		return $simpl_response;
	}
	
	return $is_rest_api_request;
}

function simpl_set_cookie($wc_session_cookie) {

	if($wc_session_cookie) {	
		
		$customer_id = explode("||", $wc_session_cookie)[0];		

		if( !SimplWcCartHelper::simpl_is_customer_guest( $customer_id ) ) {
			//Login to Wordpress for WooCommerce login user.
			$user = get_user_by( 'id', $customer_id );
			if($user) {
				// Following 2 lines doesn't work for some reason.
				// wp_clear_auth_cookie();
				// wp_set_auth_cookie( $user_id );

				do_action( 'wp_login', $user->user_email, $user );
				wp_set_current_user ( $customer_id );
			}
		}
		
		$wc_session_cookie_key = apply_filters( 'woocommerce_cookie', 'wp_woocommerce_session_' . COOKIEHASH );
		$_COOKIE[$wc_session_cookie_key] = $wc_session_cookie;		
	}
}
