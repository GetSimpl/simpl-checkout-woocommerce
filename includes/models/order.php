<?php
namespace Simpl\Checkout\Models;

class Order {
  private \WC_Order $order;

  private function __construct(\WC_Order $order) {
    $this->order = $order;
  }

  public static function from_wc_order(\WC_Order $order) {
    return new Order($order);
  }

  public function response() {
    if ($this->order == null) {
      throw new \Exception("order is not given");
    }

    return array(
      "id" => $this->order->get_id(),
      "total_price" => wc_format_decimal($this->order->get_total(), 2),
      "items" => $this->items(),
      "taxes" => $this->get_tax_totals(),
      "shipping_address" => $this->shipping_address(),
      "billing_address" => $this->billing_address(),
      "applied_discounts" => $this->applied_discounts(),
      "total_discount" => wc_format_decimal($this->order->get_total_discount(false), 2),
      "item_subtotal_price" => wc_format_decimal($this->order->get_subtotal(), 2),
      "total_tax" => wc_format_decimal($this->order->get_total_tax(), 2),
      "total_shipping" => wc_format_decimal($this->order->get_shipping_total(), 2),
      "shipping_methods" => $this->shipping_methods(),
      "status" => $this->order->get_status(),
      "is_paid" => $this->order->is_paid(),
    )
  }

  private function items() {
    $items = array();

    foreach($this->order->get_items() as $id => $item) {
      $product = wc_get_product($item['product_id']);
      $price = $item['line_subtotal'] + $item['line_subtotal_tax'];

      array_push($items, array(
        "id" => $item->get_id(),
        "sku" => $product->get_sku(),
        "quantity" => $item['quantity'],
        "title" => mb_substr($product->get_title(), 0, 125, "UTF-8"),
        "description" => mb_substr($product->get_title(), 0, 250, "UTF-8"),
        "image" => wp_get_attachment_url($product->get_image_id),
        "url" => $product->get_permalink(),
        "price" => wc_format_decimal((empty($product->get_price()) === false) ? $price / $item['quantity'] : 0, 2),
        "variant_id" => $item['variation_id'],
        "product_id" => $item['product_id'],
        "product_category" => $this->get_product_category($item),
        "attributes" => $item['variation'],
        "offer_price" => (empty($productDetails['sale_price']) === false) ? wc_format_decimal((float)$productDetails['sale_price'], 2) : wc_format_decimal($price / $item['quantity'], 2),
      ));
    }

    return $items;
  }

  private function shipping_address() {
    return $this->order->get_address('shipping');
  }

  private function billing_address() {
    return $this->order->get_address('shipping');
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