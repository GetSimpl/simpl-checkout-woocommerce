<?php

class SimplCheckoutOrderController {
    function create(WP_REST_Request $request)
    {
        try {
            simpl_cart_init_common();
            SimplRequestValidator::validate_order_request($request);
    
            $order = wc_get_order((int)$request->get_params()["checkout_order_id"]);
            WC()->session->order_awaiting_payment = $order->get_id();
            $order_id = $order->get_id();
            WC()->session->set("simpl_order_id", $order_id);
            $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
            if (!$available_gateways["simpl"]) {
                return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_BAD_REQUEST, "message" => "order already confirmed"), 400);
            }
    
            $order->update_meta_data("simpl_cart_token", $request->get_params()["simpl_cart_token"]);
            $order->update_meta_data("simpl_payment_id", $request->get_params()["simpl_payment_id"]);
            $order->save();
            $result = $available_gateways["simpl"]->process_payment($order_id);

	        WC()->mailer()->customer_invoice( $order ); // Sending Invoice to Customer

            WC()->session->set("simpl_order_id", null);
            WC()->session->set("simpl:session:id", null);
    
            if ($result["result"] != "success") return new WP_Error(SIMPL_HTTP_ERROR_USER_NOTICE, "order is not successful");
    
            $si = new SimplCartResponse();
            $order_payload = $si->order_payload($order);
            $order_payload["order_status_url"] = $result["redirect"];
            return $order_payload;
        } catch (Exception $fe) {
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => $fe->getMessage()), 500);
        } catch (Error $fe) {
	        return new WP_REST_Response(array("code" => SIMPL_HTTP_ERROR_USER_NOTICE, "message" => 'error in creating order'), 500);
        }
    }
}


