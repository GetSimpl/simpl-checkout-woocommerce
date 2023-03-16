<?php

class SimplIntegration {
    public function cart_redirection_url($cart) {
        $cart_request = self::cart_payload($cart);
        $simpl_host = WC_Simpl_Settings::simpl_host();    
    
        $simplHttpResponse = wp_remote_post( "https://".$simpl_host."/api/v1/wc/cart", array(
            "body" => json_encode($cart_request),
            "headers" => array("Shopify-Shop-Domain" => "checkout-staging-v2.myshopify.com", "content-type" => "application/json"),
        ));

        if ( ! is_wp_error( $simplHttpResponse ) ) {
            $body = json_decode( wp_remote_retrieve_body( $simplHttpResponse ), true );
    
            return $body["redirection_url"];
        } else {
            $error_message = $simplHttpResponse->get_error_message();
            throw new Exception( $error_message );
        }
        
        return "";
    }


    public function cart_payload($cart, $order_id=NULL) {
        $cart_content = $cart->get_cart();
        $response = array("source" => "cart");
        $checkout = $cart->checkout;
        foreach($cart_content as $item_id => $item) {
            $unique_device_id = WC()->session->get("simpl:session:id");
            if($unique_device_id) {
                $unique_device_id = WC()->session->get("simpl:session:id");
            } else {
                $unique_device_id = uniqid();
                WC()->session->set("simpl:session:id", $unique_device_id);
            }
            $response["unique_id"] = $unique_device_id;
            $price = round($item['line_subtotal']*100) + round($item['line_subtotal_tax']*100);
            $cart_payload = array();
            $cart_payload["total_price"] = (int)$cart->get_total('float') * 100;
            $shipping_address = $cart->get_customer()->get_shipping_address();
            $billing_address = $cart->get_customer()->get_billing_address();
            $cart_payload["shipping_address"] = ($shipping_address != "" ? $shipping_address : null);
            $cart_payload["billing_address"] = ($billing_address != "" ? $billing_address : null);
            $cart_payload["applied_discounts"] = $this->formatted_coupons($cart->get_coupons());
            $discount_amount = 0;
            if($cart->get_discount_total()) {
                $discount_amount = $cart->get_discount_total();
            }
            $cart_payload["total_discount"] = $discount_amount;
            $cart_payload["item_subtotal_price"] = $price; 
            $cart_payload["total_tax"] = $cart->get_total_tax(); 
            $cart_payload["total_shipping"] = $cart->get_shipping_total();
            $cart_payload["checkout_url"] = wc_get_checkout_url();
            $cart_payload["shipping_methods"] = $this->get_shipping_methods($cart);
            $cart_payload["applied_shipping_method"] = $this->get_applied_shipping_method($cart);
            $cart_payload["items"] = getCartLineItem($cart_content);
            $cart_payload['attributes'] = array('a' => '2');
            if(isset($order_id)) {
                $cart_payload['checkout_order_id'] = $order_id;
            }
            $response["cart"] = $cart_payload;
        }
        return $response;
    }


    public function order_payload($order) {
        $response = array();
        $response["id"] = $order->get_id();
        $response["total_price"] = $order->get_total();
        $response["items"] = $this->getOrderLineItem($order);        
        $response["shipping_address"] = $order->get_address('shipping');
        $response["billing_address"] = $order->get_address('billing');
        $response["applied_discounts"] = $this->formatted_coupons($order->get_coupons());
        $discount_amount = 0;
        if($order->get_discount_total()) {
            $discount_amount = $order->get_discount_total();
        }
        $response["total_discount"] = $discount_amount;
        $response["item_subtotal_price"] = $order->get_subtotal(); 
        $response["total_tax"] = $order->get_total_tax(); 
        $response["total_shipping"] = $order->get_shipping_total();
        $response["shipping_methods"] = $this->formatted_shipping_methods($order->get_shipping_methods());
        return $response;
    }

    protected function get_applied_shipping_method($cart) {
        $chosen_shipping_method = $cart->calculate_shipping();
        if(count($chosen_shipping_method) > 0) {
            return $chosen_shipping_method[0]->get_id();
        }
        return "";
    }

    protected function formatted_coupons($coupons) {
        $applied_discounts = array();
        $applied_discount_count = 0;
        foreach($coupons as $coupon_code => $coupon) {
            $applied_discounts[$applied_discount_count] = array("code" => $coupon_code, "amount" => $coupon->get_amount(), "free_shipping" => $coupon->enable_free_shipping());
            $applied_discount_count += 1;
        }
        return $applied_discounts;
    }

    protected function formatted_shipping_methods($shipping_methods) {
        $shipping_methods_array = array();
        foreach($shipping_methods as $item_id => $item) {
            $shipping_methods_array["id"] = $item->get_id();
            $shipping_methods_array["method_id"] = $item->get_method_id();
            $shipping_methods_array["amount"] = $item->get_total();
            $shipping_methods_array["total_tax"] = $item->get_total_tax();
        }
        return $shipping_methods_array;
    }

    function get_shipping_methods($cart) {
    
        $shipping_methods_count = 0;
            $shipping_methods_array = array();
            foreach ( $cart->get_shipping_packages() as $package_id => $package ) {
                // Check if a shipping for the current package exist
                if ( WC()->session->__isset( 'shipping_for_package_'.$package_id ) ) {
                    // Loop through shipping rates for the current package
                    foreach ( WC()->session->get( 'shipping_for_package_'.$package_id )['rates'] as $shipping_rate_id => $shipping_rate ) {
                        $rate_id     = $shipping_rate->get_id(); // same thing that $shipping_rate_id variable (combination of the shipping method and instance ID)
                        $method_id   = $shipping_rate->get_method_id(); // The shipping method slug
                        $label_name  = $shipping_rate->get_label(); // The label name of the method
                        $cost        = $shipping_rate->get_cost(); // The cost without tax
                        $tax_cost    = $shipping_rate->get_shipping_tax(); // The tax cost
                        $taxes       = $shipping_rate->get_taxes(); // The taxes details (array)
                        $shipping_methods_array[$shipping_methods_count] = array("id" => $rate_id, "slug" => $method_id, "name" => $label_name, "amount" => $cost, "total_tax" => $tax_cost, "taxes" => $taxes);
                        $shipping_methods_count += 1;
                    }
                }
            }
            return $shipping_methods_array;
    }

    function getOrderLineItem($order) {
        $i = 0;
    
        foreach($order->get_items() as $item_id => $item) { 
            $product =  wc_get_product( $item['product_id']); 
            $price = round($item['line_subtotal']*100) + round($item['line_subtotal_tax']*100);
    
           $data[$i]['sku'] = $product->get_sku();
           $data[$i]['quantity'] = (int)$item['quantity'];
           $data[$i]['title'] = mb_substr($product->get_title(), 0, 125, "UTF-8");
           $data[$i]['description'] = mb_substr($product->get_title(), 0, 250,"UTF-8");
           $productImage = $product->get_image_id()?? null;
           $data[$i]['image'] = $productImage? wp_get_attachment_url( $productImage ) : null;
           $data[$i]['url'] = $product->get_permalink();
           $data[$i]['price'] = (empty($product->get_price())=== false) ? $price/$item['quantity'] : 0;
           $data[$i]['variant_id'] = $item['variation_id'];
           $data[$i]['product_id'] = $item['product_id'];
           $data[$i]['offer_price'] = (empty($productDetails['sale_price'])=== false) ? (int) $productDetails['sale_price']*100 : $price/$item['quantity'];
           $i++;
        } 
    
        return $data;
    }
    
}

?>