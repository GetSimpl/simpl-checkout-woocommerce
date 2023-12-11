<?php
namespace Simpl\Checkout\Api\Middlewares;

const HEADER_MERCHANT_PLATFORM_SESSION_TOKEN = 'merchant-partner-session-token';

function init_wc_session() {
  return function(\WP_REST_Request $request) {
    $wc_session = $request->get_header(HEADER_MERCHANT_PLATFORM_SESSION_TOKEN);

    $wc_session_cookie_key = apply_filters( 'woocommerce_cookie', 'wp_woocommerce_session_' . COOKIEHASH );

    if ($wc_session != '') {
      $_COOKIE[$wc_session_cookie_key] = $wc_session;
      $customer_id = WC()->session->get_session_cookie()[0];

      if (!is_customer_guest($customer_id)) {
        wp_set_current_user($customer_id);
      }

      WC()->session->init();
      WC()->customer = new \WC_Customer( get_current_user_id(), true );
      WC()->cart->init();
    }
  };
}

function is_customer_guest($customer_id) {
  $customer_id = strval( $customer_id );

  if ( empty( $customer_id ) ) {
    return true;
  }

  if ( 't_' === substr( $customer_id, 0, 2 ) ) {
    return true;
  }
  return false;
}