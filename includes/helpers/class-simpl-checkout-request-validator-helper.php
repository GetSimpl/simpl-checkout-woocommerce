<?php

class SimplRequestValidator {
    static function validate_checkout_order_id($request) {
        $order_id = $request->get_params()["checkout_order_id"];    
        $order = wc_get_order($order_id);
        if(!$order || $order->get_meta(SIMPL_ORDER_METADATA) != "yes" || $order->get_status() != SIMPL_ORDER_STATUS_CHECKOUT) {
            throw new HttpBadRequest("invalid checkout_order_id");
        }   
        return null;
    }
    
    static function  validate_coupon_request($request) {
        $coupon_code = $request->get_params()["coupon_code"];
        if(!isset($coupon_code) || $coupon_code == "") {
            throw new HttpBadRequest("coupon_code is mandatory");
        }
    
        SimplRequestValidator::validate_checkout_order_id($request);
        
        return null;
    }
    
    static function  validate_shipping_method_request($request) {
        SimplRequestValidator::validate_checkout_order_id($request);
    
        $shipping_method = $request->get_params()["shipping_method_id"];
        
        if(!isset($shipping_method) || $shipping_method == "") {
            throw new HttpBadRequest("shipping_method is mandatory");
        }    
        return null;
    }
    
    static function  validate_order_request($request) {
        SimplRequestValidator::validate_checkout_order_id($request);
        
        $simpl_cart_token = $request->get_params()["simpl_cart_token"];
        $simpl_payment_id = $request->get_params()["simpl_payment_id"];
        if(!isset($simpl_cart_token) || $simpl_cart_token == "") {
            throw new HttpBadRequest("simpl_cart_token is mandatory");
        }
        if(!isset($simpl_payment_id) || $simpl_payment_id == "") {
            throw new HttpBadRequest("simpl_payment_id is mandatory");
        }    
        return null;
    }
    
    static function validate_line_items($request) {
        if(!isset($request->get_params()["items"]) || count($request->get_params()["items"]) == 0) {
            throw new HttpBadRequest("items cannot be empty");
        }
    
        //product_id is mandatory
    
        foreach($request->get_params()["items"] as $item_id => $item) {
            if($item["quantity"] <= 0) {
                throw new HttpBadRequest("quantity should be greater than 1");
            }
        }
        return NULL;        
    }
    
    static function validate_shipping_address_or_items($request_params) {
        $items = $request_params->get_params()["items"];
        $shipping_address = $request_params->get_params()["shipping_address"];
        
        if(!isset($items) && !isset($shipping_address)) {
            throw new HttpBadRequest("update request requires 'items' or 'shipping_address'");
        }
        return NULL;        
    }
    
    static function convert_address_payload($address) {
        $supported_cc = SimplUtil::country_code_for_country($address["country"]);
        if(!isset($supported_cc)) {
            throw new HttpBadRequest("country is not supported");
        }
    
        $supported_state = SimplUtil::state_code_for_state($address["state"]);
        // if(!isset($supported_state)) {
        //     throw new Exception("state is not supported");
        // }
    
        $address["country"] = $supported_cc;
        // $address["state"] = $supported_state;
    
        return  $address;
    }    
}
