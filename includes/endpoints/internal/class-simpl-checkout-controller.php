<?php
class SimplCheckoutController
{
    function create(WP_REST_Request $request)
    {
        try {
            $cart_session_token = $request->get_params()["cart_token"];

            $success = SimplWcCartHelper::init_woocommerce_session_with_cart_session_token($cart_session_token);

            if (!$success) {
                $items = $request->get_params()["items"];
                simpl_cart_init_common();
                SimplWcCartHelper::add_to_cart($items);
            }

            if ($this->is_address_present($request)) {
                SimplWcCartHelper::set_address_in_cart($request->get_params()["shipping_address"], $request->get_params()["billing_address"]);
            }
			
			SimplWcCartHelper::simpl_wallet_payment_gateway();

            $order = SimplWcCartHelper::create_order_from_cart($cart_session_token);
            SimplWcCartHelper::store_woocommerce_session_cookies_to_order($order, $cart_session_token);
            $si = new SimplCartResponse();
            $cart_payload = $si->cart_payload(WC()->cart, $order);
            do_action("simpl_abandoned_cart", WC()->cart, $cart_payload);
            return $cart_payload;
        } catch (SimplCustomHttpBadRequest $fe) {
            //TODO: Logger
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            //TODO: Logger
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            //TODO: Logger
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        }
    }

    function update(WP_REST_Request $request)
    {
        try {
            $items = $request->get_params()["items"];
            $is_line_items_updated = false;
            simpl_cart_init_common();
            SimplRequestValidator::validate_shipping_address_or_items($request);
            SimplRequestValidator::validate_checkout_order_id($request);
            
            $order_id = $request->get_params()["checkout_order_id"];
            $order = wc_get_order($order_id);

            $cart_session_token = $order->get_meta("simpl_cart_token");
            $success = SimplWcCartHelper::init_woocommerce_session_with_cart_session_token($cart_session_token);
            if ($success) {
                SimplWcCartHelper::store_woocommerce_session_cookies_to_order($order, $cart_session_token);
            } else {
                SimplWcCartHelper::simpl_load_cart_from_order($order);
            }

            if ($this->is_address_present($request)) {
			    SimplWcCartHelper::set_address_in_cart($request->get_params()["shipping_address"], $request->get_params()["billing_address"]);
            }
			
			SimplWcCartHelper::simpl_wallet_payment_gateway();

            $order = SimplWcCartHelper::simpl_update_order_from_cart($order, $is_line_items_updated);
            
            $si = new SimplCartResponse();
            $cart_payload = $si->cart_payload(WC()->cart, $order);
            do_action("simpl_abandoned_cart", WC()->cart, $cart_payload);
            return $cart_payload;
        } catch (SimplCustomHttpBadRequest $fe) {
            //TODO: Logger
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            //TODO: Logger
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            //TODO: Logger
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in updating checkout'), 500);
        }
    }

    function fetch(WP_REST_Request $request)
    {
        try {
            SimplRequestValidator::validate_checkout_order_id($request);
            simpl_cart_init_common();
            WC()->cart->empty_cart();
            $order_id = $request->get_params()["checkout_order_id"];
            $order = wc_get_order($order_id);
            SimplWcCartHelper::simpl_load_cart_from_order($order);

            $si = new SimplCartResponse();
            return $si->cart_payload(WC()->cart, $order);
        } catch (SimplCustomHttpBadRequest $fe) {
            //TODO: Logger
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            //TODO: Logger
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            //TODO: Logger
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in fetching checkout'), 500);
        }
    }

    protected function is_address_present($request)
    {
        return (isset($request->get_params()["shipping_address"]) && isset($request->get_params()["billing_address"]) && count($request->get_params()["shipping_address"]) > 0 && $request->get_params()["billing_address"] > 0);
    }
}


function internal_authenticate()
{
    if (WC_Simpl_Settings::is_localhost()) {
        return true;
    }

    $api = new WC_REST_Authentication();
    $authenticated = $api->authenticate("");
    wp_set_current_user("");
    return $authenticated;
}

class SimplCustomHttpBadRequest extends Exception
{
    public function errorMessage()
    {
        //error message
        $errorMsg = 'Error on line ' . $this->getLine() . ' in ' . $this->getFile()
            . ':' . $this->getMessage();
        return $errorMsg;
    }
}
