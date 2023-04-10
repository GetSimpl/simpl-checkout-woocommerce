<?php
function create_checkout( WP_REST_Request $request ) {
    try {
        console_log("Create Checkout Inititated with request ");
        console_log($request);
        $validation_errors = validate_line_items($request);
        if(isset($validation_errors)) {
            return $validation_errors;

        }
        $items = $request->get_params()["items"];
        initCartCommon();
        
        $error = add_to_cart($items);
        if(isset($error)) {
            return $error;
        }
        
        if(isset($request->get_params()["shipping_address"]) && isset($request->get_params()["billing_address"])) {
            set_address_in_cart($request->get_params()["shipping_address"], $request->get_params()["billing_address"]);
        }
        WC()->cart->calculate_totals();
        $order = create_order_from_cart();
        console_log("Fetching Order from cart ");
        console_log($order);
        $si = new SimplIntegration();
        $cart_payload =  $si->cart_payload(WC()->cart, $order->get_id());
        do_action("simpl_abandoned_cart", WC()->cart, $cart_payload);
        return $cart_payload;
    } catch (Exception $fe) {
        return new WP_Error("user_error", $fe->getMessage());
    } catch (Error $fe) {
        return new WP_Error("user_error", "error in creating checkout", array("error_mesage" => $fe->getMessage(), "backtrace" => $fe->getTraceAsString()));
    }    
}

function update_checkout( WP_REST_Request $request ) {
    try {
        print("Update Checkout Inititated with request ");
        $items = $request->get_params()["items"];
        initCartCommon();
        WC()->cart->empty_cart();
        $validation_errors = validate_shipping_address_or_items($request);
        if(isset($validation_errors)) {
            return $validation_errors;
        }

        print("Update Checkout Request :: Validating checkout order id ");
        $validation_errors = validate_checkout_order_id($request);
        if(isset($validation_errors)) {
            return $validation_errors;
        }

        if(isset($items) && count($items) > 0) {
            $validation_errors = validate_line_items($request);
            if(isset($validation_errors)) {
                return $validation_errors;
            }
            add_to_cart($items);
        } else {
            $order_id = $request->get_params()["checkout_order_id"];
            load_cart_from_order($order_id);
        }
        print("Update Checkout Request :: order id -> ");
        print($order_id);
        set_address_in_cart($request->get_params()["shipping_address"], $request->get_params()["billing_address"]);
        $order = update_order_from_cart($request->get_params()["checkout_order_id"]);
        print("Update Checkout Request :: Updating order from cart");
        print($order);
        $si = new SimplIntegration();
        $cart_payload = $si->cart_payload(WC()->cart, $order->id);
        do_action("simpl_abandoned_cart", WC()->cart, $cart_payload);
        return $cart_payload;
    } catch (Exception $fe) {
        return new WP_Error("user_error", $e->getMessage());
    } catch (Error $fe) {
        return new WP_Error("user_error", "error in creating checkout", array("error_mesage" => $fe->getMessage(), "backtrace" => $fe->getTraceAsString()));
    }    
}

function fetch_checkout(WP_REST_Request $request) {
    $validation_errors = validate_checkout_order_id($request);
    if(isset($validation_errors)) {
        return $validation_errors;
    }
    
    initCartCommon();
    WC()->cart->empty_cart();
    $order_id = $request->get_params()["checkout_order_id"];
    console_log("Fetching checkout :: -> order id ");
    console_log($order_id);
    $order = wc_get_order($order_id);
    console_log("Fetching checkout :: -> got order ");
    console_log($order);
    if($order) {
        console_log("Converting order to WC Cart");
        console_log($order);
        convert_wc_order_to_wc_cart($order);
        $si = new SimplIntegration();
        return $si->cart_payload(WC()->cart, $order_id);
    }
    return new WP_REST_Response(array("code"=> "not_found", "message"=> "invalid checkout_order_id"), 404);
}

function internal_authenticate() {        
    if(WC_Simpl_Settings::is_localhost()) {
        return true;
    }

    $api = new WC_REST_Authentication();
    $authenticated = $api->authenticate("");
    return $authenticated;
}
