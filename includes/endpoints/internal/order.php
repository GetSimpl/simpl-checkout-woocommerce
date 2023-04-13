<?php

include_once SIMPL_PLUGIN_DIR . "/includes/simpl_integration/simpl_integration.php";
include_once SIMPL_PLUGIN_DIR . "/includes/helpers/cart_helper.php";
include_once SIMPL_PLUGIN_DIR . "/includes/helpers/wc_helper.php";


function create_order(WP_REST_Request $request)
{
    try {
        initCartCommon();
        validate_order_request($request);

        $order = wc_get_order((int)$request->get_params()["checkout_order_id"]);
        WC()->session->order_awaiting_payment = $order->get_id();
        $order_id = $order->get_id();
        WC()->session->set("simpl_order_id", $order_id);
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        if (!$available_gateways["simpl"]) {
            return new WP_REST_Response(array("code" => "bad_request", "message" => "order already confirmed"), 400);
        }

        $order->update_meta_data("simpl_cart_token", $request->get_params()["simpl_cart_token"]);
        $order->update_meta_data("simpl_payment_id", $request->get_params()["simpl_payment_id"]);
        $order->save();
        $result = $available_gateways["simpl"]->process_payment($order_id);
        WC()->session->set("simpl_order_id", null);
        WC()->session->set("simpl:session:id", null);

        if ($result["result"] != "success") return new WP_Error("user_error", "order is not successful");

        $si = new SimplIntegration();
        $order_payload = $si->order_payload($order);
        $order_payload["order_status_url"] = $result["redirect"];
        return $order_payload;
    } catch (Exception $fe) {
        return new WP_Error("user_error", $fe->getMessage());
    } catch (Error $fe) {
        return new WP_Error("user_error", "error in creating order", array("error_mesage" => $fe->getMessage(), "backtrace" => $fe->getTraceAsString()));
    }
}
