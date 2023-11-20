<?php
class SimplCheckoutController
{
    function create(WP_REST_Request $request)
    {
        try {
            SimplRequestValidator::validate_line_items($request);
            $items = $request->get_params()["items"];
            simpl_cart_init_common();
            SimplWcCartHelper::add_to_cart($items);
            if ($this->is_address_present($request)) {
                SimplWcCartHelper::set_address_in_cart($request->get_params()["shipping_address"], $request->get_params()["billing_address"]);
            }

            $order = SimplWcCartHelper::create_order_from_cart();
            $si = new SimplCartResponse();
            $cart_payload = $si->cart_payload(WC()->cart, $order);
            do_action("simpl_abandoned_cart", WC()->cart, $cart_payload);
            return $cart_payload;
        } catch (SimplCustomHttpBadRequest $fe) {
            simpl_sentry_exception($fe);
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            simpl_sentry_exception($fe);
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            simpl_sentry_exception($fe);
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

            if (isset($items) && count($items) > 0) {
                SimplRequestValidator::validate_line_items($request);
                SimplWcCartHelper::add_to_cart($items);
                $is_line_items_updated = true;
            } else {
                SimplWcCartHelper::simpl_load_cart_from_order($order);
            }

            if ($this->is_address_present($request)) {
                SimplWcCartHelper::set_address_in_cart($request->get_params()["shipping_address"], $request->get_params()["billing_address"]);
            }

            $order = SimplWcCartHelper::simpl_update_order_from_cart($order, $is_line_items_updated);
            
            $si = new SimplCartResponse();
            $cart_payload = $si->cart_payload(WC()->cart, $order);
            do_action("simpl_abandoned_cart", WC()->cart, $cart_payload);
            return $cart_payload;
        } catch (SimplCustomHttpBadRequest $fe) {
            simpl_sentry_exception($fe);
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            simpl_sentry_exception($fe);
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            simpl_sentry_exception($fe);
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
            simpl_sentry_exception($fe);
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            simpl_sentry_exception($fe);
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            simpl_sentry_exception($fe);
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
