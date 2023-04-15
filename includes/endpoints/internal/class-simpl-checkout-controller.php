<?php
class SimplCheckoutController {
    function create(WP_REST_Request $request)
    {
        try {
            SimplRequestValidator::validate_line_items($request);
            $items = $request->get_params()["items"];
            simpl_cart_init_common();
            SimplWcCartHelper::add_to_cart($items);
            if (isset($request->get_params()["shipping_address"]) && isset($request->get_params()["billing_address"])) {
               SimplWcCartHelper::set_address_in_cart($request->get_params()["shipping_address"], $request->get_params()["billing_address"]);
            }
            $order = SimplWcCartHelper::create_order_from_cart();
            $si = new SimplCartResponse();
            $cart_payload =  $si->cart_payload(WC()->cart, $order->get_id());
            do_action("simpl_abandoned_cart", WC()->cart, $cart_payload);
            return $cart_payload;
        } catch (HttpBadRequest $fe) {
            return new WP_REST_Response(array("code" => "bad_request", "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in creating checkout'), 400);
        }
    }
    
    function update(WP_REST_Request $request)
    {
        try {
            $items = $request->get_params()["items"];
            simpl_cart_init_common();
            SimplRequestValidator::validate_shipping_address_or_items($request);
            SimplRequestValidator::validate_checkout_order_id($request);
            if (isset($items) && count($items) > 0) {
                SimplRequestValidator::validate_line_items($request);
                SimplWcCartHelper::add_to_cart($items);
            } else {
                $order_id = $request->get_params()["checkout_order_id"];
                SimplWcCartHelper::load_cart_from_order($order_id);
            }
    
            SimplWcCartHelper::set_address_in_cart($request->get_params()["shipping_address"], $request->get_params()["billing_address"]);
            $order = SimplWcCartHelper::update_order_from_cart($request->get_params()["checkout_order_id"]);
            $si = new SimplCartResponse();
            $cart_payload = $si->cart_payload(WC()->cart, $order->get_id());
            do_action("simpl_abandoned_cart", WC()->cart, $cart_payload);
            return $cart_payload;
        } catch (HttpBadRequest $fe) {
            return new WP_REST_Response(array("code" => "bad_request", "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in creating checkout'), 400);
        }
    }
    
    function fetch(WP_REST_Request $request)
    {
        try {
            SimplRequestValidator::validate_checkout_order_id($request);
            simpl_cart_init_common();
            WC()->cart->empty_cart();
            $order_id = $request->get_params()["checkout_order_id"];
            SimplWcCartHelper::load_cart_from_order($order_id);
            $si = new SimplCartResponse();
            return $si->cart_payload(WC()->cart, $order_id);
        } catch (HttpBadRequest $fe) {
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in creating checkout'), 400);
        }
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
