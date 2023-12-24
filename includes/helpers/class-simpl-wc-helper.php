<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Automattic\WooCommerce\StoreApi\Utilities\OrderController;
const SIMPL_EXCLUSIVE_DISCOUNT = 'simpl_exclusive';
const INTERNAL_COD_FEE = 'internal_cod_fee';
const SIMPL_SURE = 'simpl_sure';

class SimplWcCartHelper {

    static function simpl_create_order_from_cart($cart_session_token) {

        $oc = new OrderController();
        $order = $oc->create_order_from_cart();
        $order->update_meta_data(SIMPL_ORDER_METADATA, 'yes');
        $order->update_meta_data("simpl_cart_token", $cart_session_token);
        $order->save();

        return $order;
    }

    static function simpl_update_order_from_cart($order) {
        $oc = new OrderController();
		$order->set_cart_hash( '' );
// 		$order->update_meta_data( '_fees_hash', '' );
// 		$order->remove_order_items( 'line_item' );
// 		$order->remove_order_items( 'fee' );
		$order->remove_order_items( 'coupon' );
		$order->remove_order_items( 'shipping' );
		$order->update_meta_data( '_shipping_hash', '' );
		
        $oc->update_order_from_cart($order);

        self::simpl_set_address_in_order($order);

        return $order;
    }
    
    static protected function simpl_set_address_in_order($order) {
        $shipping_address = WC()->customer->get_shipping('edit');
        $billing_address = WC()->customer->get_billing('edit');
        WC()->cart->calculate_shipping();
        if($shipping_address != "" && $billing_address != "") {
            $order->set_address($shipping_address, 'shipping');
            $order->set_address($billing_address, 'billing');
        }
    }

    static function simpl_set_address_in_cart($shipping_address, $billing_address) {
		
        $shipping_address = self::simpl_convert_address_payload($shipping_address);
        $billing_address = self::simpl_convert_address_payload($billing_address);  
    
        if(isset($shipping_address) && isset($billing_address)) {        
            foreach($shipping_address as $key => $value) {
                if(method_exists(WC()->customer, "set_shipping_".$key)) {
                    WC()->customer->{"set_shipping_".$key}($value);
                }
            }
            foreach($billing_address as $key => $value) {
                if(method_exists(WC()->customer, "set_billing_".$key)) {
                    WC()->customer->{"set_billing_".$key}($value);    
                }
            }
			WC()->customer->save();

            WC()->cart->calculate_shipping();
            WC()->cart->calculate_totals();
        }
    }

    static function simpl_update_order_metadata($request, $order) {

        $order->update_meta_data("simpl_cart_token", $request->get_params()["simpl_cart_token"]);
        $order->update_meta_data("simpl_order_id", $request->get_params()["simpl_order_id"]);
        if(!empty($request->get_params()["simpl_payment_id"])) {
            $order->update_meta_data("simpl_payment_id", $request->get_params()["simpl_payment_id"]);
        }

        //To support Tera Wallet - If the order is getting paid by wallet entirely - wallet is the payment method
        //Bail for guest user
        if ( !SimplWcCartHelper::simpl_is_customer_guest(get_current_user_id()) && function_exists( 'is_full_payment_through_wallet' ) && is_full_payment_through_wallet() ) {
            $order->set_payment_method(SIMPL_PAYMENT_METHOD_WOO_WALLET); //TODO: Remove hardcoding
            $order->set_payment_method_title(SIMPL_PAYMENT_TITLE_WOO_WALLET); //TODO: Remove hardcoding
            //Transaction id has to be blank for the debit to happen. It is put by Tera Wallet
        } elseif ($request->get_params()["simpl_payment_type"] == SIMPL_PAYMENT_TYPE_COD) {
            $order->set_payment_method(SIMPL_PAYMENT_METHOD_COD);
            $order->set_payment_method_title(SIMPL_PAYMENT_METHOD_TITLE_COD);
            $order->set_transaction_id($request->get_params()["simpl_order_id"]);
        } else {
            $order->set_payment_method(SIMPL_PAYMENT_GATEWAY);
            $order->set_payment_method_title($request->get_params()["simpl_payment_type"]);
            $order->set_transaction_id($request->get_params()["simpl_order_id"]);
        }

        if (self::simpl_is_utm_info_present($request)) {
            self::simpl_set_utm_info_in_order($request, $order);
        }
    }

    static protected function simpl_is_utm_info_present($request) {
        return (isset($request->get_params()["utm_info"]) && count($request->get_params()["utm_info"]) > 0);
    }

    static protected function simpl_set_utm_info_in_order($request, $order) {
        if (isset($request['utm_info']["_landing_page"])) $order->update_meta_data("_landing_page", $request['utm_info']["_landing_page"]);
        if (isset($request['utm_info']["utm_source"])) $order->update_meta_data("utm_source", $request['utm_info']["utm_source"]);
        if (isset($request['utm_info']["utm_content"])) $order->update_meta_data("utm_content", $request['utm_info']["utm_content"]);
        if (isset($request['utm_info']["utm_campaign"])) $order->update_meta_data("utm_campaign", $request['utm_info']["utm_campaign"]);
        if (isset($request['utm_info']["utm_medium"])) $order->update_meta_data("utm_medium", $request['utm_info']["utm_medium"]);
        if (isset($request['utm_info']["utm_term"])) $order->update_meta_data("utm_term", $request['utm_info']["utm_term"]);
        if (isset($request['utm_info']["fbclid"])) $order->update_meta_data("fbclid", $request['utm_info']["fbclid"]);
        if (isset($request['utm_info']["gclid"])) $order->update_meta_data("gclid", $request['utm_info']["gclid"]);

        $order->save();
    }

    static protected function simpl_convert_address_payload($address) {
        $supported_cc = SimplUtil::country_code_for_country($address["country"]);
        if(!isset($supported_cc)) {
            throw new SimplCustomHttpBadRequest("country is not supported");
        }
        $address["country"] = $supported_cc;

        if(!empty($address["state"])) {
            $supported_sc = SimplUtil::state_code_for_state($address["state"]);
            $address["state"] = $supported_sc;
        }
    
        return  $address;
    }

    static function simpl_is_customer_guest($customer_id) {
        $customer_id = strval( $customer_id );

        if ( empty( $customer_id ) ) {
            return true;
        }

        if ( 't_' === substr( $customer_id, 0, 2 ) ) {
            return true;
        }
        return false;
    }
    
    static function simpl_update_shipping_line($order) {
        $order->remove_order_items("shipping");
        $shipping_methods = WC()->cart->calculate_shipping();

        if(count($shipping_methods) > 0) {
            $item = new WC_Order_Item_Shipping();

            $item->set_method_id($shipping_methods[0]->get_id());
            $item->set_method_title($shipping_methods[0]->get_label());
            $item->set_total($shipping_methods[0]->get_cost());
 
            $order->add_item($item);
            $order->calculate_totals();
        }
        $order->save();
        return $order;
    }

    static function simpl_set_customer_info_in_order($order) {
        if(!empty($order->get_billing_email())){
            $customer = null;
            $user = get_user_by('email', $order->get_billing_email());
            if( $user ) {
                $customer = new WC_Customer($user->ID);
            } else {
                $customer = self::simpl_create_new_customer($order);
            }
            $order->set_customer_id($customer->get_id());
        }
    }

    static function simpl_set_simpl_exclusive_discount($request, $order) {
        $applied_discounts = $request['applied_discounts'];
        if (!$applied_discounts) return;
        
        foreach ($applied_discounts as $discount) {
            if ($discount['type'] != SIMPL_EXCLUSIVE_DISCOUNT) continue;
            
			$sed = wc_format_decimal($discount['amount'], 2);
            $coupon = new WC_Order_Item_Coupon();
            $coupon->set_discount($sed);
            $coupon->set_name(SIMPL_EXCLUSIVE_DISCOUNT);
            $coupon->set_code(SIMPL_EXCLUSIVE_DISCOUNT);
            $order->add_item($coupon);
			
            //$order->calculate_totals(); Calculating totals on order adds tax on store credits
            //Hence we update the total manually
            $order->set_total($order->get_total('edit') - $sed);            
        }
    }

    //For COD Charges & Simpl Sure. Rest may have already been applied to order via cart
    static function simpl_set_fee_to_order($request, $order) {
        $fees = $request['fees'];
        if (!$fees) return;

        foreach ($fees as $fee) {
            if ( $fee['type'] != INTERNAL_COD_FEE && $fee['type'] != SIMPL_SURE) continue;
            
            $additional_fee = wc_format_decimal($fee['amount'], 2);
            $item_fee = new WC_Order_Item_Fee();
            $item_fee->set_name($fee['name']);
            $item_fee->set_amount( $additional_fee );
            $item_fee->set_total( $additional_fee );
            $item_fee->set_tax_status( 'none' ); // since not taxable
            $order->add_item($item_fee);
            
            //$order->calculate_totals(); Calculating totals on order adds tax on store credits
            //Hence we update the total manually
            $order->set_total($order->get_total('edit') + $additional_fee);
        }
    }

    static protected function simpl_create_new_customer($order) {
        $customer = WC()->customer;
        $customer->set_email($order->get_billing_email());
        $customer->set_first_name($order->get_shipping_first_name());
        $customer->set_last_name($order->get_shipping_last_name());
        $customer->set_display_name($order->get_shipping_first_name() . " " . $order->get_shipping_last_name());
        self::simpl_set_customer_address_from_order($customer, $order);
        $customer->set_username(
            wc_create_new_customer_username(
                $order->get_billing_email(),
                array(
                    "first_name"=>$order->get_shipping_first_name(),
                    "last_name"=>$order->get_shipping_last_name()
                )
            )
        );
        $customer->set_password(wp_generate_password());
        $customer->save();

        return $customer;
    }

    static function simpl_set_customer_address_from_order($customer, $order) {
        $billing_address = $order->get_address('billing');
        $shipping_address = $order->get_address('shipping');

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

    static function simpl_add_automatic_discounts_to_order($order) {
        $coupons = WC()->cart->get_coupons();
        $auto_applied_coupons = array();
        foreach($coupons as $coupon) {
            $code = $coupon->get_code();
            $order->apply_coupon($code);
            array_push($auto_applied_coupons, $code);
        }
        
        $order->update_meta_data('_simpl_auto_applied_coupons', $auto_applied_coupons);
        $order->save();
    }

    static function simpl_add_order_token_to_cache($order_id, $cart_session_token) {
        // now set these session_cookies to cache against our cart_session_token
        set_transient($order_id, $cart_session_token, 1 * HOUR_IN_SECONDS);
    }
}

function simpl_is_auto_applied_coupon($order, $coupon) {
    // In the first call i.e. redirection url, order would be null
    if ($order && $order->meta_exists('_simpl_auto_applied_coupons')) {
        $auto_applied_coupons = $order->get_meta('_simpl_auto_applied_coupons');
        return in_array($coupon->get_code(), $auto_applied_coupons);
    }
    return false;
}
