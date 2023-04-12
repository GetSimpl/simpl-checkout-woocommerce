<?php
function create_checkout(WP_REST_Request $request)
{
    try {
        validate_line_items($request);
        $items = $request->get_params()["items"];
        initCartCommon();
        add_to_cart($items);
        if (isset($request->get_params()["shipping_address"]) && isset($request->get_params()["billing_address"])) {
            set_address_in_cart($request->get_params()["shipping_address"], $request->get_params()["billing_address"]);
        }
        $order = create_order_from_cart();
        $si = new SimplIntegration();
        $cart_payload =  $si->cart_payload(WC()->cart, $order->get_id());
        do_action("simpl_abandoned_cart", WC()->cart, $cart_payload);
        return $cart_payload;
    } catch (HttpBadRequest $fe) {
        return new WP_REST_Response(array("code" => "bad_request", "message" => $fe->getMessage()), 400);
    } catch (Exception $fe) {
	    return new WP_REST_Response(array("code" => "user_error", "message" => $fe->getMessage()), $fe->getCode());

    } catch (Error $fe) {
	    return new WP_REST_Response(array("code" => "user_error", "message" => 'error in creating checkout'), $fe->getCode());
    }
}

function update_checkout(WP_REST_Request $request)
{
    try {
        $items = $request->get_params()["items"];
        initCartCommon();
        validate_shipping_address_or_items($request);
        validate_checkout_order_id($request);
        if (isset($items) && count($items) > 0) {
            validate_line_items($request);
            add_to_cart($items);
        } else {
            $order_id = $request->get_params()["checkout_order_id"];
            load_cart_from_order($order_id);
        }

        set_address_in_cart($request->get_params()["shipping_address"], $request->get_params()["billing_address"]);
        $order = update_order_from_cart($request->get_params()["checkout_order_id"]);
        $si = new SimplIntegration();
        $cart_payload = $si->cart_payload(WC()->cart, $order->id);
        do_action("simpl_abandoned_cart", WC()->cart, $cart_payload);
        return $cart_payload;
    } catch (HttpBadRequest $fe) {
        return new WP_REST_Response(array("code" => "bad_request", "message" => $fe->getMessage()), 400);
    } catch (Exception $fe) {
	    return new WP_REST_Response(array("code" => "user_error", "message" => $fe->getMessage()), $fe->getCode());
    } catch (Error $fe) {
	    return new WP_REST_Response(array("code" => "user_error", "message" => 'error in creating checkout'), $fe->getCode());
    }
}

function fetch_checkout(WP_REST_Request $request)
{
    try {
        validate_checkout_order_id($request);
        initCartCommon();
        WC()->cart->empty_cart();
        $order_id = $request->get_params()["checkout_order_id"];
        $order = wc_get_order($order_id);
        if ($order) {
            convert_wc_order_to_wc_cart($order);
            $si = new SimplIntegration();
            return $si->cart_payload(WC()->cart, $order_id);
        }
    } catch (HttpBadRequest $fe) {
        return new WP_REST_Response(array("code" => "bad_request", "message" => $fe->getMessage()), 400);
    } catch (Exception $fe) {
	    return new WP_REST_Response(array("code" => "user_error", "message" => $fe->getMessage()), $fe->getCode());
    } catch (Error $fe) {
	    return new WP_REST_Response(array("code" => "user_error", "message" => 'error in creating checkout'), $fe->getCode());
    }
}

function internal_authenticate()
{
    if (WC_Simpl_Settings::is_localhost()) {
        return true;
    }

    $api = new WC_REST_Authentication();
    $authenticated = $api->authenticate("");
    return $authenticated;
}

class HttpBadRequest extends Exception
{
    public function errorMessage()
    {
        //error message
        $errorMsg = 'Error on line ' . $this->getLine() . ' in ' . $this->getFile()
            . ':' . $this->getMessage();
        return $errorMsg;
    }
}
