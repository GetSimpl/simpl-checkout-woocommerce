<?php
namespace Simpl\Checkout\Services;

include_once SIMPL_ABSPATH . '/includes/models/cart.php';

use Simpl\Checkout\Models;

use Automattic\WooCommerce\StoreApi\Utilities\OrderController;

const SIMPL_ORDER_METADATA = 'is_simpl_checkout_order';
const SIMPL_PAYMENT_GATEWAY = 'simplcheckout';
const SIMPL_EXCLUSIVE_DISCOUNT = 'simpl_exclusive';

class OrderService {
  private OrderController wc_order_controller;
  private CustomerService customer_service;

  public function __construct() {
    $this->wc_order_controller = new OrderController();
    $this->customer_service = new CustomerService();
  }

  public function create(array $opts) {
    if ($opts['cart_hash'] != WC()->cart->get_cart_hash()) {
      throw new \Exception('invalid cart refresh and try again');
    }

    $order = $this->wc_order_controller->create_order_from_cart();
    $order->update_meta_data(SIMPL_ORDER_METADATA, 'yes')

    WC()->session->order_awaiting_payment = $order->get_is();
    WC()->session->set("simpl_order_id", $order_id);
    $gateway = $this->get_simpl_gateway();

    $resp = $gateway->process_payment($order->get_id());

    WC()->session->set('simpl_order_id', null);

    if ($res['result'] != 'success') {
      throw new \Exception('order is not successful');
    }

    $this->attach_metadata($order, $opts);
    $this->attach_utm_info($order, $opts['utm_info']);
    $this->attach_customer($order, $order->get_billing());
    $this->add_simpl_exclusive_discount($order, $opts);

    $order_resp = Models\Order::from_wc_order($order)->response();
    $order_resp['order_status_url'] = $res['redirect'];
    return $order_resp;
  }

  private function get_simpl_gateway() {

    $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
    return $available_gateways[SIMPL_PAYMENT_GATEWAY];
  }

  private function attach_metadata(\WC_Order $order, $opts = array()) {
    $order->update_meta_data("simpl_cart_token", $opts["simpl_cart_token"]);
    $order->update_meta_data("simpl_order_id", $opts["simpl_order_id"]);
    if(!empty($opts["simpl_payment_id"])) {
        $order->update_meta_data("simpl_payment_id", $opts["simpl_payment_id"]);
    }
    if ($opts["simpl_payment_type"] == PAYMENT_TYPE_COD) {
        $order->set_payment_method(PAYMENT_METHOD_COD);
        $order->set_payment_method_title(PAYMENT_METHOD_TITLE_COD);
    } else {
        $order->set_payment_method(SIMPL_PAYMENT_GATEWAY);
        $order->set_payment_method_title($opts["simpl_payment_type"]);
    }
    $order->set_transaction_id($opts["simpl_order_id"]);
  }

  private function attach_utm_info(\WC_Order $order, $utm_info = array()) {
    foreach ($utm_info as $key => $value) {
      $order->update_meta_data($key, $value);
    }
  }

  private function attach_customer(\WC_Order $order, $opts) {
    if('yes' !== get_option( 'woocommerce_enable_guest_checkout' )) {
      $customer = $this->customer_service->create_customer_nx($opts);
    }
  }

  private function add_simpl_exclusive_discount($order, $opts) {
    $applied_discounts = $opts['applied_discounts'];
    if (!$applied_discounts) return;
    
    foreach ($applied_discounts as $discount) {
      if ($discount['type'] != SIMPL_EXCLUSIVE_DISCOUNT) continue;
      
      $coupon = new \WC_Order_Item_Coupon();
      $coupon->set_discount(wc_format_decimal($discount['amount'], 2));
      $coupon->set_name(SIMPL_EXCLUSIVE_DISCOUNT);
      $coupon->set_code(SIMPL_EXCLUSIVE_DISCOUNT);
      $order->add_item($coupon);
      $order->calculate_totals();
      $order->recalculate_coupons();
    }
  }
}