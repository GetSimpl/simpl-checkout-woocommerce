<?php

class SimplRequestValidator {
    static function validate_checkout_order_id($request) {
        $order_id = $request->get_params()["checkout_order_id"];    
        $order = wc_get_order($order_id);
        if(!$order || $order->get_meta(SIMPL_ORDER_METADATA) != "yes" || $order->get_status() != SIMPL_ORDER_STATUS_CHECKOUT) {
            throw new SimplCustomHttpBadRequest("invalid checkout_order_id");
        }   
        return null;
    }
    
    static function  validate_coupon_request($request) {
        $coupon_code = $request->get_params()["coupon_code"];
        if(!isset($coupon_code) || $coupon_code == "") {
            throw new SimplCustomHttpBadRequest("coupon_code is mandatory");
        }
    
        SimplRequestValidator::validate_checkout_order_id($request);
        
        return null;
    }
    
    static function  validate_shipping_method_request($request) {
        SimplRequestValidator::validate_checkout_order_id($request);
    
        $shipping_method = $request->get_params()["shipping_method_id"];
        
        if(!isset($shipping_method) || $shipping_method == "") {
            throw new SimplCustomHttpBadRequest("shipping_method is mandatory");
        }    
        return null;
    }
    
    static function  validate_order_request($request) {
        SimplRequestValidator::validate_checkout_order_id($request);

        foreach(array("simpl_cart_token", "simpl_payment_id", "simpl_order_id") as $key => $value) {
            $simpl_order_request_param = $request->get_params()[$value];
            if(!isset($simpl_order_request_param) || $simpl_order_request_param == "") {
                throw new SimplCustomHttpBadRequest($value . " is mandatory");
            }
        }
        return null;
    }
    
    static function validate_line_items($request) {
        if(!isset($request->get_params()["items"]) || count($request->get_params()["items"]) == 0) {
            throw new SimplCustomHttpBadRequest("items cannot be empty");
        }
    
        //product_id is mandatory
    
        foreach($request->get_params()["items"] as $item_id => $item) {
            if($item["quantity"] <= 0) {
                throw new SimplCustomHttpBadRequest("quantity should be greater than 1");
            }
        }
        return NULL;        
    }
    
    static function validate_shipping_address_or_items($request_params) {
        $items = $request_params->get_params()["items"];
        $shipping_address = $request_params->get_params()["shipping_address"];
        
        if(!isset($items) && !isset($shipping_address)) {
            throw new SimplCustomHttpBadRequest("update request requires 'items' or 'shipping_address'");
        }
        return NULL;        
    }
    
    static function convert_address_payload($address) {
        $supported_cc = SimplUtil::country_code_for_country($address["country"]);
        if(!isset($supported_cc)) {
            throw new SimplCustomHttpBadRequest("country is not supported");
        }
    
        return  $address;
    }
    
    static function validate_events_payload($request) {
        $event_payload = $request->get_params()["event_payload"] ?? null;
        
        if (NULL == $event_payload) {
            throw new HttpBadRequest("event_payload is required");
        }
    
        $required_fields = ["entity", "event_name", "flow", "event_data", "trigger_timestamp"];
    
        foreach ($required_fields as $field) {
            if (!isset($event_payload[$field]) || NULL == $event_payload[$field]) {
                throw new HttpBadRequest("$field is required");
            }
        }
    }
}

