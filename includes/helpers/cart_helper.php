<?php   

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
}

function initCartCommon()
{ 
    if (defined('WC_ABSPATH')) {
        // WC 3.6+ - Cart and other frontend functions are not included for REST requests.
        include_once WC_ABSPATH . 'includes/wc-cart-functions.php'; // nosemgrep: file-inclusion
        include_once WC_ABSPATH . 'includes/wc-notice-functions.php'; // nosemgrep: file-inclusion
        include_once WC_ABSPATH . 'includes/wc-template-hooks.php'; // nosemgrep: file-inclusion
        // include_once SIMPL_PLUGIN_DIR . "/includes/helpers/notice_helper.php";
    }

    if (null === WC()->session) {
        $session_class = apply_filters('woocommerce_session_handler', 'WC_Session_Handler');
        WC()->session  = new $session_class();
        WC()->session->init();        
    }

    if ( is_null( WC()->customer ) ) {
        WC()->customer = new WC_Customer( get_current_user_id() );
    }

    // Load Cart.
    if ( null === WC()->cart ) {
        WC()->cart = new WC_Cart();
        WC()->cart->empty_cart();
    }
}

function getCartLineItem($cart) {
    $i = 0;

    foreach($cart as $item_id => $item) { 
        $product =  wc_get_product( $item['product_id']); 
        $price = round($item['line_subtotal']*100) + round($item['line_subtotal_tax']*100);

       $data[$i]['sku'] = $product->get_sku();
       $data[$i]['quantity'] = (int)$item['quantity'];
       $data[$i]['title'] = mb_substr($product->get_title(), 0, 125, "UTF-8");
       $data[$i]['description'] = mb_substr($product->get_title(), 0, 250,"UTF-8");
       $productImage = $product->get_image_id()?? null;
       $data[$i]['image'] = $productImage? wp_get_attachment_url( $productImage ) : null;
       $data[$i]['url'] = $product->get_permalink();
       $data[$i]['price'] = (empty($product->get_price())=== false) ? $price/$item['quantity'] : 0;
       $data[$i]['variant_id'] = $item['variation_id'];
       $data[$i]['product_id'] = $item['product_id'];
       $data[$i]['offer_price'] = (empty($productDetails['sale_price'])=== false) ? (int) $productDetails['sale_price']*100 : $price/$item['quantity'];
       $i++;
    } 

    return $data;
}
?>