<?php
function simpl_cart_payload_conversion($order_id=NULL) {
    $cart = WC()->cart;
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


function get_shipping_methods() {
    $shipping_methods_count = 0;
        $shipping_methods_array = array();
        foreach ( WC()->cart->get_shipping_packages() as $package_id => $package ) {
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

function get_applied_discounts_from_cart() {
    $applied_discounts = array();
    $applied_discount_count = 0;
    foreach(WC()->cart->get_coupons() as $coupon_code => $coupon) {
        $applied_discounts[$applied_discount_count] = array("code" => $coupon_code, "amount" => $coupon->get_amount(), "free_shipping" => $coupon->enable_free_shipping());
        $applied_discount_count += 1;
    }
}

function getCartLineItem($cart) {
    $i = 0;

    foreach($cart as $item_id => $item) { 
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

function get_simpl_redirection_session_url() {
    $cart_request = simpl_cart_payload_conversion(1);
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

?>