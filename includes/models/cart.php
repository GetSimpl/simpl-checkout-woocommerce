<?php
namespace Simpl\Checkout\Models;

class Cart {
  private \WC_Cart $cart;

  private function __construct(\WC_Cart $cart) {
    $this->cart = $cart;
  }

  public static function from_wc_cart(\WC_Cart $cart) {
    return new Cart($cart);
  }

  public function response() {
    if ($this->cart == null) {
      throw new \Exception("cart is not given");
    }

    $totals = $this->cart->get_totals();
    return array(
      "total_price" => wc_format_decimal($this->cart->get_total('float'), 2),
      "applied_discounts" => $this->applied_discounts(),
      "total_discount" => wc_format_decimal($totals['discount_total'] + $totals['discount_tax'], 2),
      "tax_included" => wc_prices_include_tax(),
      "item_subtotal_price" => wc_format_decimal($this->cart->get_subtotal() + $this->cart->get_subtotal_tax(), 2),
      "total_tax" => wc_format_decimal($this->cart->get_total_tax(), 2),
      "checkout_url" => wc_get_checkout_url(),
      "shipping_methods" => $this->shipping_methods(),
      "applied_shipping_method" => $this->applied_shipping_method(),
      "items" => $this->items(),
      "fees" => $this->cart->get_fees(),
      "cart_hash" => $this->cart->get_cart_hash(),
    );
  }

  private function applied_discounts() {
    $coupons = array();

    foreach ($this->cart->get_coupons() as $code => $coupon) {
      array_push($coupons, array(
        "code" => $code,
        "amount" => wc_format_decimal($this->cart->get_coupon_discount_amount($code, false), 2),
        "free_shipping" => $coupon->get_free_shipping(),
        "type" => "",
      ));
    }

    return $coupons;
  }

  private function shipping_methods() {
    $this->cart->calculate_shipping();
    $shipping_methods = array();

    foreach ($this->cart->get_shipping_packages() as $id => $package) {
      if (WC()->session->__isset('shipping_for_package_' . $id)) {
        foreach (WC()->session->get('shipping_for_package_' . $id)['rates'] as $rate_id => $shipping_rate) {
          array_push($shipping_methods, array(
            "id" => $shipping_rate->get_id(),
            "slug" => $shipping_rate->get_method_id(),
            "name" => $shipping_rate->get_label(),
            "amount" => wc_format_decimal($shipping_rate->get_cost(), 2),
            "total_tax" => wc_format_decimal($shipping_rate->get_shipping_tax(), 2),
            "taxes" => $shipping_rate->get_taxes(),
          ));
        }
      }
    }
    return $shipping_methods;
  }

  private function applied_shipping_method() {
    $shipping_methods = $this->cart->calculate_shipping();
    if(count($shipping_methods) > 0) {
      return $shipping_methods[0]->get_id();
    }
    return "";
  }

  private function items() {
    $items = array();

    foreach($this->cart->get_cart() as $id => $item) {
      $product = wc_get_product($item['product_id']);
      $price = wc_format_decimal($item['line_subtotal'] + $item['line_subtotal_tax']);

      array_push($items, array(
        "id" => (string)$item['product_id'] . (string)$item['variation_id'],
        "sku" => $product->get_sku(),
        "quantity" => $item['quantity'],
        "title" => mb_substr($product->get_title(), 0, 125, "UTF-8"),
        "description" => mb_substr($product->get_title(), 0, 250, "UTF-8"),
        "image" => wp_get_attachment_url($product->get_image_id()),
        "url" => $product->get_permalink(),
        "price" => wc_format_decimal((empty($product->get_price()) === false) ? $price / $item['quantity'] : 0, 2),
        "variant_id" => $item['variation_id'],
        "product_id" => $item['product_id'],
        "product_category" => $this->get_product_category($product),
        "attributes" => $item["variation"],
        "offer_price" => wc_format_decimal((empty($productDetails['sale_price']) === false) ? (float) $productDetails['sale_price'] : $price / $item['quantity'], 2),
      ));
    }
    return $items;
  }

  private function get_product_category(\WC_Product $product) {
    $product_categories = wp_get_post_terms($product->get_id(),'product_cat',array('fields'=>'names'));
    if(isset($product_categories) && count($product_categories) > 0) {
      $product_category = htmlspecialchars_decode($product_categories[0]);
      return ($product_category == 'Uncategorized') ? "" : $product_category;
    }
    return "";
  }
}