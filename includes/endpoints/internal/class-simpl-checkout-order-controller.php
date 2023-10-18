<?php

class SimplCheckoutOrderController
{
    function fetch(WP_REST_Request $request)
    {
        try {
            SimplRequestValidator::validate_fetch_order_request($request);
            $order = wc_get_order((int)$request->get_params()["order_id"]);
            $si = new SimplCartResponse();
            $order_payload = $si->order_payload($order);
            return $order_payload;
        } catch (SimplCustomHttpBadRequest $fe) {
            simpl_sentry_exception($fe);
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            simpl_sentry_exception($fe);
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            simpl_sentry_exception($fe);
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in fetching order'), 500);
        }
    }

    function create(WP_REST_Request $request)
    {
        try {
            simpl_cart_init_common();
            SimplRequestValidator::validate_order_request($request);

            $order = wc_get_order((int)$request->get_params()["checkout_order_id"]);
            self::simpl_update_order_details($request, $order);

            WC()->session->order_awaiting_payment = $order->get_id();

            $gateway = $this->simpl_gateway($order->get_id());
            if (!$gateway) {
                throw new SimplCustomHttpBadRequest("simpl payment is not configured");
            }
            $result = $gateway->process_payment($order->get_id());

            $this->reset_session();

            if ($result["result"] != "success") return new WP_Error(SIMPL_HTTP_ERROR_USER_NOTICE, "order is not successful");

            $si = new SimplCartResponse();
            $order_payload = $si->order_payload($order);
            $order_payload["order_status_url"] = $result["redirect"];
            return $order_payload;
        } catch (SimplCustomHttpBadRequest $fe) {
            simpl_sentry_exception($fe);
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            simpl_sentry_exception($fe);
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            simpl_sentry_exception($fe);
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in creating order'), 500);
        }
    }

    static function simpl_update_order_details($request, $order) {
        SimplWcCartHelper::simpl_update_order_metadata($request, $order);
        if('yes' !== get_option( 'woocommerce_enable_guest_checkout' )) {
            SimplWcCartHelper::simpl_set_customer_info_in_order($order);
        }

        // Check if there is In-house Shipping
        $shipping_method = $request['shipping_method'];
        if(!empty($shipping_method)) {
            SimplWcCartHelper::simpl_set_shipping_method_in_order($order, $shipping_method);
        }

        $order->save();
    }

    protected function reset_session()
    {
        WC()->session->set("simpl_order_id", null);
        WC()->session->set("simpl:session:id", null);
    }

    protected function simpl_gateway($order_id)
    {
        WC()->session->set("simpl_order_id", $order_id);
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        return $available_gateways[PAYMENT_GATEWAY_SIMPL];
    }
}
