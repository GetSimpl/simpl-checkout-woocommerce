<?php        

class SimplWcCartHelper {
    static function create_order_from_cart() {
        $order = new WC_Order();  
        self::set_data_from_cart( $order);        
        self::set_address_in_order($order);
        $order->update_meta_data(SIMPL_ORDER_METADATA, 'yes');
        $order->save();
        updateToSimplDraft($order->get_id());
        return $order;
    }     

    static function add_to_cart($items) {
        WC()->cart->empty_cart();
        foreach($items as $item_id => $item) {
            WC()->cart->add_to_cart($item["product_id"], $item["quantity"], $item["variant_id"], $item["attributes"]);
        }
        if(WC()->cart->is_empty()) {
            throw new SimplCustomHttpBadRequest("invalid cart items");
        }
    }

    static function update_order_from_cart($order_id) {
        $order = wc_get_order($order_id);        
        $order->remove_order_items("line_item");
        WC()->checkout->create_order_line_items( $order, WC()->cart );
        self::set_address_in_order($order);
        $order->save();
        return $order;
    }
    
    static protected function set_address_in_order($order) {
        $shipping_address = WC()->customer->get_shipping('edit');
        $billing_address = WC()->customer->get_billing('edit');
        WC()->cart->calculate_shipping();
        if($shipping_address != "" && $billing_address != "") {
            $order->set_address($shipping_address, 'shipping');
            $order->set_address($billing_address, 'billing');
        }
    }

    //Created this method to support older version
    static protected function set_data_from_cart( &$order ) {
        $order->set_shipping_total( WC()->cart->get_shipping_total() );
        $order->set_discount_total( WC()->cart->get_discount_total() );
        $order->set_discount_tax( WC()->cart->get_discount_tax() );
        $order->set_cart_tax( WC()->cart->get_cart_contents_tax() + WC()->cart->get_fee_tax() );
        $order->set_shipping_tax( WC()->cart->get_shipping_tax() );
        $order->set_total( WC()->cart->get_total( 'edit' ) );
        $order->set_prices_include_tax(wc_prices_include_tax());
        WC()->checkout->create_order_line_items( $order, WC()->cart );
        WC()->checkout->create_order_fee_lines( $order, WC()->cart );
        WC()->checkout->create_order_shipping_lines( $order, WC()->session->get( 'chosen_shipping_methods' ), WC()->shipping()->get_packages() );
        WC()->checkout->create_order_tax_lines( $order, WC()->cart );
        WC()->checkout->create_order_coupon_lines( $order, WC()->cart );
    }

    static function set_address_in_cart($shipping_address, $billing_address) {
        $shipping_address = self::convert_address_payload($shipping_address);
        $billing_address = self::convert_address_payload($billing_address);  
    
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
        if ($request->get_params()["simpl_payment_type"] == PAYMENT_TYPE_COD) {
            $order->set_payment_method(PAYMENT_METHOD_COD);
            $order->set_payment_method_title(PAYMENT_METHOD_TITLE_COD);
        } else {
            $order->set_payment_method(SIMPL_PAYMENT_GATEWAY);
            $order->set_payment_method_title($request->get_params()["simpl_payment_type"]);
        }
        $order->set_transaction_id($request->get_params()["simpl_order_id"]);

        if (self::simpl_is_utm_info_present($request)) {
            self::simpl_set_utm_info_in_order($request, $order);
        }
    }

    static protected function simpl_is_utm_info_present($request)
    {
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

    static protected function convert_address_payload($address) {
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

    static function load_cart_from_order($order_id) {
        $order = wc_get_order((int)$order_id);
        return self::convert_wc_order_to_wc_cart($order);
    }
    

    static function update_shipping_line($order_id) {
        $order = wc_get_order($order_id);
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

    static protected function convert_wc_order_to_wc_cart($order) {
        $variationAttributes = [];
        WC()->cart->empty_cart();
        if ($order && $order->get_item_count() > 0) {
            foreach ($order->get_items() as $item_id => $item) {
                $productId   = $item->get_product_id();
                $variationId = $item->get_variation_id();
                $quantity    = $item->get_quantity();
                
                $customData['item_id'] = $item_id;
                $product               = $item->get_product();
                if ($product->is_type('variation')) {
                    $variation_attributes = $product->get_variation_attributes();
                    foreach ($variation_attributes as $attribute_taxonomy => $term_slug) {
                        $taxonomy                                 = str_replace('attribute_', '', $attribute_taxonomy);
                        $value                                    = wc_get_order_item_meta($item_id, $taxonomy, true);
                        $variationAttributes[$attribute_taxonomy] = $value;
                    }
                }
                
                WC()->cart->add_to_cart($productId, $quantity, $variationId, $variationAttributes, $customData);                
            }
            $order_coupons = get_order_coupon_codes($order);
            if(count($order_coupons) > 0) {
                foreach ($order_coupons as $item_id => $coupon_code) {
                    WC()->cart->add_discount($coupon_code);
                }
            }
            set_order_address_in_cart($order->get_address('shipping'), $order->get_address('billing'));
            set_order_shipping_method_in_cart($order);
        }
        return WC()->cart;
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

    static function simpl_set_shipping_method_in_order($order, $shipping_method) {
        $method = new WC_Order_Item_Shipping();

        $method->set_method_id($shipping_method['slug']);
        $method->set_name($shipping_method['name']);
        $method->set_total($shipping_method['amount']);

        // Add Shipping item to the order.
        $order->add_item( $method );
        $order->calculate_totals();
    }
}


function set_order_address_in_cart($shipping_address, $billing_address) {
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

        WC()->cart->calculate_shipping();
        WC()->cart->calculate_totals(); 
    }
}


function set_order_shipping_method_in_cart($order) {
    $order_shipping_methods = $order->get_shipping_methods();
    foreach ($order_shipping_methods as $key => $method) {
        $id = $method->get_method_id() . ':' . $method->get_instance_id();
        WC()->session->set('chosen_shipping_methods', array($id));
        break;
    }

    WC()->cart->calculate_shipping();
    WC()->cart->calculate_totals();
}

function get_order_coupon_codes($order) {
	$coupon_codes = array();
	$coupons      = $order->get_items( 'coupon' );

	if ( $coupons ) {
		foreach ( $coupons as $coupon ) {
			$coupon_codes[] = $coupon->get_code();
		}
	}
	return $coupon_codes;
}

function updateToSimplDraft($orderId) {
    wp_update_post(array(
        'ID'          => $orderId,
        'post_status' => 'checkout-draft',
    ));
}


class SimplWcEventHelper {
    static function publish_event($event_name, $event_data, $entity, $flow) {
        $simpl_host = WC_Simpl_Settings::simpl_host();
        $event_payload = array(
            "trigger_timestamp" => time(),
            "event_name" => $event_name,
            "event_data" => $event_data,
            "entity" =>  $entity,
            "flow" => $flow
        );
        $simplHttpResponse = wp_remote_post("https://".$simpl_host."/api/v1/wc/publish/events", array(
            "body" => json_encode($event_payload),
            "headers" => array(            
                    "content-type" => "application/json"
                ),
        )); 
        return $simplHttpResponse;
    }
}

function simpl_is_success_response($simplHTTPResponse) {
    if (isset($simplHTTPResponse) && $simplHTTPResponse["response"]["code"] == 200) {
        return true;
    }
    return false;
}
