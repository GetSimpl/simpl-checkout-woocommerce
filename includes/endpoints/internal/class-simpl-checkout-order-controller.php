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
            //TODO: Logger
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            //TODO: Logger
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            //TODO: Logger
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in fetching order'), 500);
        }
    }

    function create(WP_REST_Request $request)
    {
        try {
            SimplRequestValidator::validate_order_request($request);

            $order = wc_get_order((int)$request->get_params()["checkout_order_id"]);
            SimplWcCartHelper::simpl_load_cart_from_order($order);
            self::simpl_update_order_details($request, $order);

            WC()->session->order_awaiting_payment = $order->get_id();
			
            $gateway = $this->simpl_gateway($order->get_id());
            if( 'wallet' == $order->get_payment_method("edit") ) {
                //TODO: Need to put additional check to ensure wallet payment method exists
                $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
                $gateway = $available_gateways['wallet'];
            }
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
            //TODO: Logger
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => $fe->getMessage()), 400);
        } catch (Exception $fe) {
            //TODO: Logger
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
            //TODO: Logger
            return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in creating order'), 500);
        }
    }

    static function simpl_update_order_details($request, $order) {

        SimplWcCartHelper::simpl_update_order_metadata($request, $order);

        if(is_user_logged_in()) {
            //Map the order to current logged_in user. Required for store credit usage
            $order->set_customer_id(get_current_user_id());            
        } elseif('yes' !== get_option( 'woocommerce_enable_guest_checkout' )) {
            SimplWcCartHelper::simpl_set_customer_info_in_order($order);
        }

        // Add simpl exclusive discount if exists
        SimplWcCartHelper::simpl_set_simpl_exclusive_discount($request, $order);

        // Add fees if exists
        SimplWcCartHelper::simpl_set_fee_to_order($request, $order);
        
        $order->save();
    }

    protected function reset_session() {
        WC()->session->set("simpl_order_id", null);
        WC()->session->set("simpl:session:id", null);
    }

    protected function simpl_gateway($order_id) {
        WC()->session->set("simpl_order_id", $order_id);
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        return $available_gateways[SIMPL_PAYMENT_GATEWAY];
    }
}
