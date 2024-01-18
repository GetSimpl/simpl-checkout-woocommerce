<?php
namespace Simpl\Checkout\Services;

class CustomerService {
  public function __construct() {
  }

  public function create_customer_nx(array $opts) {
    $user = get_user_by('email', $opts['email']);
    if ($user) {
      return new \WC_Customer($user->ID);
    }

    $customer = WC()->customer;
    $customer->set_email($opts['email']);
    $customer->set_first_name($opts['first_name']);
    $customer->set_last_name($opts['last_name']);
    $customer->set_display_name($opts['first_name'] . ' ' . $opts['last_name']);
    $customer->set_username(
      wc_create_new_customer_username(
        $order->get_billing_email(),
        array(
          "first_name"=>$opts['first_name'],
          "last_name"=>$opts['first_name']
        )
      )
    );
    $customer->set_password(wp_generate_password());
    $this->update_address($customer, $opts);
    $customer->save();
    return $customer;
  }

  private function update_address(\WC_Customer $customer, $opts) {
    $billing_address = $opts('billing');
    $shipping_address = $opts('shipping');

    if(isset($shipping_address) && isset($billing_address)) {        
      foreach($shipping_address as $key => $value) {
        if(method_exists($customer, "set_shipping_".$key)) {
            $customer->{"set_shipping_".$key}($value);   
        }
      }
      foreach($billing_address as $key => $value) {
        if(method_exists($customer, "set_billing_".$key)) {
            $customer->{"set_billing_".$key}($value);    
        }
      }
    }
  }
}