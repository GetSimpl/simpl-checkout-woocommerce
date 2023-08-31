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
            $gateway = $this->simpl_gateway($order->get_id());
            if (!$gateway) {
                throw new SimplCustomHttpBadRequest("simpl payment is not configured");
            }

            WC()->session->order_awaiting_payment = $order->get_id();
            $this->update_order_metadata($request, $order);
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

    function fetch_refund_orders(WP_REST_Request $request) {
        try {
            $refund_order_ids = $this->sv_get_wc_orders_with_refunds();
            $response_payload = array();
            foreach ($refund_order_ids as $refund_order_id) {
                $order = wc_get_order((int)$refund_order_id);
                $si = new SimplCartResponse();
                $order_payload = $si->order_refund_payload($order);
                array_push($response_payload, $order_payload);
            }
            return $response_payload;
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

    protected function sv_get_wc_orders_with_refunds() {
        $args = array(
            'fields'         => 'id=>parent',
            'post_type'      => 'shop_order_refund',
            'post_status'    => 'any',
			'date_query' => array(
				'after'     => date('Y-m-d H:i:s', strtotime('-1 day')), // since we only want orders from last 24 hours
				'before'    => date('Y-m-d H:i:s'),
				'inclusive' => true,
			),
			'posts_per_page' => -1,
        );

        $refunds = get_posts( $args );
        return array_values( array_unique( $refunds ) );
    }
    
    protected function update_order_metadata($request, $order)
    {
        $order->update_meta_data("simpl_cart_token", $request->get_params()["simpl_cart_token"]);
        $order->update_meta_data("simpl_payment_id", $request->get_params()["simpl_payment_id"]);
        $order->update_meta_data("simpl_order_id", $request->get_params()["simpl_order_id"]);
        if ($request->get_params()["simpl_payment_type"] == 'Cash on Delivery (COD)') {
            $order->set_payment_method('cod');
            $order->set_payment_method_title('Cash on delivery');
        } else {
            $order->set_payment_method('simpl_checkout_payment');
            $order->set_payment_method_title($request->get_params()["simpl_payment_type"]);
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
        return $available_gateways["simpl_checkout_payment"];
    }
}
