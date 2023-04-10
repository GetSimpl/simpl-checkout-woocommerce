<?php        

function create_order_from_cart() {
    initCartCommon();
    $order = new WC_Order();  
    set_data_from_cart( $order);        
    $shipping_address = WC()->checkout->get_value('shipping')["address_1"];
    $billing_address = WC()->checkout->get_value('billing')["address_1"];  
    if($shipping_address != "" && $billing_address != "") {
        $order->set_address($shipping_address, 'shipping');
        $order->set_address($billing_address, 'billing');
    }
    $order->update_meta_data(SIMPL_ORDER_METADATA, 'yes');
    $order->save();
    updateToSimplDraft($order->get_id());
    return $order;
}    

function update_order_from_cart($order_id) {
    initCartCommon();
    $order = wc_get_order($order_id);        
    $order->remove_order_items("line_item");
    WC()->checkout->create_order_line_items( $order, WC()->cart );
    $shipping_address = WC()->checkout->get_value('shipping')["address_1"];
    $billing_address = WC()->checkout->get_value('billing')["address_1"];   
    print("Updating Order from cart :: Shipping Address :: ");
    print($shipping_address); 
    print("Updating Order from cart :: Billing Address :: ");
    print($billing_address);             
    WC()->cart->calculate_shipping();
    // var_dump(WC()->cart->get_shipping_packages());
    if($shipping_address != "" && $billing_address != "") {
        $order->set_address($shipping_address, 'shipping');
        $order->set_address($billing_address, 'billing');
    }
    $order->save();
    return $order;
}

//Created this method to sup
function set_data_from_cart( &$order ) {
    $order_vat_exempt = WC()->cart->get_customer()->get_is_vat_exempt() ? 'yes' : 'no';
    $order->add_meta_data( 'is_vat_exempt', $order_vat_exempt, true );
    $order->set_shipping_total( WC()->cart->get_shipping_total() );
    $order->set_discount_total( WC()->cart->get_discount_total() );
    $order->set_discount_tax( WC()->cart->get_discount_tax() );
    $order->set_cart_tax( WC()->cart->get_cart_contents_tax() + WC()->cart->get_fee_tax() );
    $order->set_shipping_tax( WC()->cart->get_shipping_tax() );
    $order->set_total( WC()->cart->get_total( 'edit' ) );
    WC()->checkout->create_order_line_items( $order, WC()->cart );
    WC()->checkout->create_order_fee_lines( $order, WC()->cart );
    WC()->checkout->create_order_shipping_lines( $order, WC()->session->get( 'chosen_shipping_methods' ), WC()->shipping()->get_packages() );
    WC()->checkout->create_order_tax_lines( $order, WC()->cart );
    WC()->checkout->create_order_coupon_lines( $order, WC()->cart );
}

function update_shipping_line($order_id) {
    $order = wc_get_order($order_id);        
    $order->remove_order_items("shipping");
    $shipping_methods = WC()->cart->calculate_shipping();
    if(count($shipping_methods) > 0) {
        $item = new WC_Order_Item_Shipping();
        $item->set_method_id($shipping_methods[0]->get_id());
        $item->set_method_title($shipping_methods[0]->get_label());
        $item->set_total($shipping_methods[0]->get_cost());
        $item->calculate_taxes($shipping_methods[0]->get_taxes());
        $order->add_item($item);        
    }
    $order->save();
    return $order;
}

function set_address_in_cart($shipping_address, $billing_address) {
    print("Updating Address in the cart ::  SHipping Adress -> ");
    print($shipping_address);
    print("Updating Address in the cart ::  Billing Adress -> ");
    print($billing_address);
    if(isset($shipping_address) && isset($billing_address)) {
        WC()->customer->set_shipping_address($shipping_address);
        WC()->customer->set_billing_address($billing_address);           
    }
}

function load_cart_from_order($order_id) {
    $order = wc_get_order((int)$order_id);
    convert_wc_order_to_wc_cart($order);
}

function convert_wc_order_to_wc_cart($order) {
    initCartCommon();
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
        set_address_in_cart($order->get_address('shipping'), $order->get_address('billing'));
    }
    
    return WC()->cart;
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

function add_to_cart($items) {
    WC()->cart->empty_cart();
    foreach($items as $item_id => $item) {
        WC()->cart->add_to_cart($item["product_id"], $item["quantity"], $item["variant_id"]);
    }
    if(WC()->cart->is_empty()) {
        return new WP_REST_Response(array("code"=> "bad_request", "message"=> "invalid line items"), 400);
    }
}
