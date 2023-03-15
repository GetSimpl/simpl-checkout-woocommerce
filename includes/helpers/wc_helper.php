<?php        

function create_order_from_cart() {
    initCartCommon();
    $order = new WC_Order();  
    WC()->checkout->set_data_from_cart( $order);        
    $shipping_address = WC()->checkout->get_value('shipping')["address_1"];
    $billing_address = WC()->checkout->get_value('billing')["address_1"];  
    if($shipping_address != "" && $billing_address != "") {
        $order->set_address($shipping_address, 'shipping');
        $order->set_address($billing_address, 'billing');
    }
    $order->update_meta_data('is_simpl_checkout_order', 'yes');
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
    WC()->cart->calculate_shipping();
    // var_dump(WC()->cart->get_shipping_packages());
    if($shipping_address != "" && $billing_address != "") {
        $order->set_address($shipping_address, 'shipping');
        $order->set_address($billing_address, 'billing');
    }
    $order->save();
    return $order;
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
    if(isset($shipping_address) && isset($billing_address)) {
        WC()->customer->set_shipping_address($shipping_address);
        WC()->customer->set_billing_address($billing_address);           
    }
}

function convert_wc_order_to_wc_cart($order) {
    initCartCommon();
    $variationAttributes = [];
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
        if(count($order->get_coupon_codes()) > 0) {
            foreach ($order->get_coupon_codes() as $item_id => $coupon_code) {
                WC()->cart->add_discount($coupon_code);
            }
        }
        set_address_in_cart($order->get_address('shipping'), $order->get_address('billing'));
    } else {
        return false;
    }
    
    return true;
}

function updateToSimplDraft($orderId) {
    wp_update_post(array(
        'ID'          => $orderId,
        'post_status' => 'draft',
    ));
}
?>