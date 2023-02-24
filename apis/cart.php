<?php

include dirname(__DIR__) . "/helpers/cart_helper.php";

function create_cart( WP_REST_Request $request ) {
    $productID = $request->get_params()["product_id"];
    $variantID = $request->get_params()["variant_id"];
    $quantity = $request->get_params()["quantity"];
    initCartCommon();
    $cart = WC()->cart->add_to_cart($productID, $quantity, $variantID);

    return getRedirectionUrl();
}

function simplCartRequest() {
    $cart = WC()->cart->get_cart();
    $response = array("source" => "cart");
    foreach($cart as $item_id => $item) {
        $price = round($item['line_subtotal']*100) + round($item['line_subtotal_tax']*100);
        $response["unique_id"] = $item['key'];
        $cartObj = array("total_price" => $price);
        $cartObj["total_price"] = $price;
        $cartObj["total_discount"] = 0;
        $cartObj["item_subtotal_price"] = $price; 
        $cartObj["items"] = getCartLineItem($cart);
        $cartObj['attributes'] = array('a' => '2');
        $response["cart"] = $cartObj;
    }
    return $response;
}

function getRedirectionUrl() {
    $cart_request = simplCartRequest();
    
    $simplHttpResponse = wp_remote_post( "https://checkout-pi3-backend.stagingsimpl.com/api/v2/cart/initiate", array(
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


function getCartLineItem($cart)
{
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


?>