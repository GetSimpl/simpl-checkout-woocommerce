<?php

class SimplIntegration {
    public static function cart_redirection_url($cart) {
        $cart_request = cart_payload(1);
        $simpl_host = WC_Simpl_Settings::simpl_host();    
    
        $simplHttpResponse = wp_remote_post( "https://".$simpl_host."/wc/v1/cart", array(
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


    public static function cart_payload($cart, $order_id=NULL) {
        $cart_content = $cart->get_cart();
        $response = array("source" => "cart");
        foreach($cart_content as $item_id => $item) {
            $price = round($item['line_subtotal']*100) + round($item['line_subtotal_tax']*100);
            $response["unique_id"] = $item['key'];
            $cart_payload = array("total_price" => $price);
            $cart_payload["total_price"] = $price;
            $discount_amount = 0;
            if(WC()->cart->get_discount_total()) {
                $discount_amount = WC()->cart->get_discount_total();
            }
            $shipping_address = WC()->checkout->get_value('shipping')["address_1"];
            $billing_address = WC()->checkout->get_value('billing')["address_1"];
            $cart_payload["shipping_address"] = ($shipping_address != "" ? $shipping_address : null);
            $cart_payload["billing_address"] = ($billing_address != "" ? $billing_address : null);
            $cart_payload["applied_discounts"] = get_applied_discounts_from_cart();
            $cart_payload["total_discount"] = $discount_amount;
            $cart_payload["item_subtotal_price"] = $price; 
            $cart_payload["total_tax"] = WC()->cart->get_total_tax(); 
            $cart_payload["total_shipping"] = WC()->cart->get_shipping_total();
            $cart_payload["checkout_url"] = WC()->cart->get_checkout_url();
            $cart_payload["shipping_methods"] = get_shipping_methods();
            $cart_payload["items"] = getCartLineItem($cart_content);
            $cart_payload['attributes'] = array('a' => '2');
            if(isset($order_id)) {
                $cart_payload['checkout_order_id'] = $order_id;
            }
            $response["cart"] = $cart_payload;
        }
        return $response;
    }

}

?>