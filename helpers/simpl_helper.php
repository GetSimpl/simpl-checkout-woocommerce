<?php
function simpl_cart_payload_conversion($order_id=NULL) {
    $cart = WC()->cart;
    $cart_content = $cart->get_cart();
    $response = array("source" => "cart");
    foreach($cart_content as $item_id => $item) {
        $price = round($item['line_subtotal']*100) + round($item['line_subtotal_tax']*100);
        $response["unique_id"] = $item['key'];
        $cartObj = array("total_price" => $price);
        $cartObj["total_price"] = $price;
        $discount_amount = 0;
        if(WC()->cart->get_discount_total()) {
            $discount_amount = WC()->cart->get_discount_total();
        }
        $shipping_address = WC()->checkout->get_value('shipping')["address_1"];
        $billing_address = WC()->checkout->get_value('billing')["address_1"];
        $cartObj["shipping_address"] = ($shipping_address != "" ? $shipping_address : null);
        $cartObj["billing_address"] = ($billing_address != "" ? $billing_address : null);
        $cartObj["applied_discounts"] = WC()->cart->get_coupons();
        $cartObj["total_discount"] = $discount_amount;
        $cartObj["item_subtotal_price"] = $price; 
        $shipping_methods = WC()->cart->calculate_shipping();
        $shipping_methods_array = array();
        foreach($shipping_methods as $item_id => $item) {
            $shipping_methods_array[$item->get_id()] = array("price" => $item->get_cost(), "name" => $item->get_label());
        }
        $cartObj["shipping_methods"] = $shipping_methods_array;
        $cartObj["items"] = getCartLineItem($cart_content);
        $cartObj['attributes'] = array('a' => '2');
        if(isset($order_id)) {
            $cartObj['checkout_order_id'] = $order_id;
        }
        $response["cart"] = $cartObj;
    }
    return $response;
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
       $data[$i]['offer_price'] = (empty($productDetails['sale_price'])=== false) ? (int) $productDetails['sale_price']*100 : $price/$item['quantity'];
       $i++;
    } 

    return $data;
}

function get_simpl_redirection_session_url() {
    $cart_request = simplCartPayload();
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