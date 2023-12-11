<?php
namespace Simpl\Checkout\Services;

include_once SIMPL_ABSPATH . '/includes/models/cart.php';

use Simpl\Checkout\Clients;
use Simpl\Checkout\Models;

class ShippingService {

  public function __construct() {
  }

  public function get_shipping() {
    return Models\Cart::from_wc_cart(WC()->cart)->shipping_methods();
  }

  public function apply_shipping(string $shipping_method_id) {
    WC()->session->set('chosen_shipping_methods', array($shipping_method_id));
    WC()->cart->calculate_shipping();
    WC()->cart->calculate_totals();

    return array(
      "source" => 'cart',
      "cart" => Models\Cart::from_wc_cart(WC()->cart)->response(),
    );
  }
}