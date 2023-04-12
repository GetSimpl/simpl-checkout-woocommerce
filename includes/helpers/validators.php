<?php

function  validate_checkout_order_id($request) {
    $order_id = $request->get_params()["checkout_order_id"];    
    $order = wc_get_order($order_id);
    if(!$order || $order->get_meta(SIMPL_ORDER_METADATA) != "yes" || $order->get_status() != SIMPL_ORDER_STATUS_CHECKOUT) {
        return new WP_REST_Response(array("code"=> "bad_request", "message"=> "invalid checkout_order_id"), 400);   
    }   
    return null;
}

function  validate_coupon_request($request) {
    $coupon_code = $request->get_params()["coupon_code"];
    if(!isset($coupon_code) || $coupon_code == "") {
        return new WP_REST_Response(array("code"=> "bad_request", "message"=> "coupon_code is mandatory"), 400);
    }

    $errors = validate_checkout_order_id($request);
    if(isset($errors)) {
        return $errors;
    }
    
    return null;
}

function  validate_order_request($request) {
    $errors = validate_checkout_order_id($request);
    if(isset($errors)) {
        return $errors;
    }
    
    $simpl_cart_token = $request->get_params()["simpl_cart_token"];
    $simpl_payment_id = $request->get_params()["simpl_payment_id"];
    if(!isset($simpl_cart_token) || $simpl_cart_token == "") {
        return new WP_REST_Response(array("code"=> "bad_request", "message"=> "simpl_cart_token is mandatory"), 400);
    }
    if(!isset($simpl_payment_id) || $simpl_payment_id == "") {
        return new WP_REST_Response(array("code"=> "bad_request", "message"=> "simpl_payment_id is mandatory"), 400);
    }    
    return null;
}

function validate_line_items($request) {
    if(!isset($request->get_params()["items"]) || count($request->get_params()["items"]) == 0) {
        return new WP_REST_Response(array("code"=> "bad_request", "message"=> "items cannot be empty"), 400);
    }

    //product_id is mandatory

    foreach($request->get_params()["items"] as $item_id => $item) {
        if($item["quantity"] <= 0) {
            return new WP_REST_Response(array("code"=> "bad_request", "message"=> "quantity should be greater than 1"), 400);   
        }
    }
    return NULL;        
}

function validate_shipping_address_or_items($request_params) {
    $items = $request_params->get_params()["items"];
    $shipping_address = $request_params->get_params()["shipping_address"];
    
    if(!isset($items) && !isset($shipping_address)) {
        return new WP_REST_Response(array("code"=> "bad_request", "message"=> "update request requires 'items' or 'shipping_address'"), 400);   
    }
    return NULL;        
}

function convert_address_payload($address) {
    $supported_cc = SimplUtil::country_code_for_country($address["country"]);
    if(!isset($supported_cc)) {
        throw new Exception("country is not supported");
    }

    $supported_state = SimplUtil::state_code_for_state($address["state"]);
    // if(!isset($supported_state)) {
    //     throw new Exception("state is not supported");
    // }

    $address["country"] = $supported_cc;
    // $address["state"] = $supported_state;

    return  $address;
}
