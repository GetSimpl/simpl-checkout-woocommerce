<?php
namespace Simpl\Checkout\Services;

include_once SIMPL_ABSPATH . '/includes/clients/simpl_checkout.php';
include_once SIMPL_ABSPATH . '/includes/models/cart.php';

use Simpl\Checkout\Clients;
use Simpl\Checkout\Models;

class CartService {
  private $client;

  public function __construct() {
    $this->client = new Clients\SimplCheckoutClient();
  }

  public function create(string $unique_id, \WC_Cart $cart, $merchant_additional_details = [], array $client_information = []) {

    $merchant_additional_details['client_information'] = $client_information;
    $cart->apply_coupon('test10');

    // $payload = array();
    // $payload['cart'] = Models\Cart::from_wc_cart($cart)->response();
    // $payload['unique_id'] = $unique_id;
    // $payload['cart']['attributes'] = [];
    // $payload['cart']['merchant_additional_details'] = $merchant_additional_details;
    // $payload['source'] = 'cart';

    // return $this->client->create_cart($payload);

    return $this->client->create_cart(
      array(
        "source" => 'cart',
        "unique_id" => $unique_id,
        "merchant_additional_details" => $merchant_additional_details,
        "cart" => Models\Cart::from_wc_cart($cart)->response(),
      )
    );
  }
}