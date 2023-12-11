<?php
namespace Simpl\Checkout\Services;

include_once SIMPL_ABSPATH . '/includes/clients/simpl_checkout.php';
include_once SIMPL_ABSPATH . '/includes/models/cart.php';

use Simpl\Checkout\Clients;
use Simpl\Checkout\Models;

class CouponService {
  public function __construct() {
  }

  public function apply(string $coupon_code) {
    $status = WC()->cart->apply_coupon($coupon_code);
    wc_clear_notices();

    if ($status) {
      return array(
        "source" => 'cart',
        "cart" => Models\Cart::from_wc_cart(WC()->cart)->response(),

      );
    }

    throw new \Exception("coupon can't be applied on cart");
  }

  public function remove_all() {
    WC()->cart->remove_coupons();
    WC()->cart->calculate_totals();
    wc_clear_notices();

    return array(
      "source" => 'cart',
      "cart" => Models\Cart::from_wc_cart(WC()->cart)->response(),
    );
  }
}