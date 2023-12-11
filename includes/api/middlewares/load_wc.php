<?php
namespace Simpl\Checkout\Api\Middlewares;

function load_wc_core() {
  return function(\WP_REST_Request $request) {
    require_once WC_ABSPATH . 'includes/wc-cart-functions.php';
    require_once WC_ABSPATH . 'includes/wc-notice-functions.php';

    WC()->initialize_session();
    WC()->initialize_cart();
  };
}