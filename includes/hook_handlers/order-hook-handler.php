<?php
function order_hook($order_id, $refund_id)
{
    $order = wc_get_order($order_id);

    $order_data = $order->get_data();
    $order_data["line_items"] = SimplUtil::get_data($order->get_items());
    $order_data["tax_lines"] = SimplUtil::get_data($order->get_taxes());
    $order_data["shipping_lines"] = SimplUtil::get_data($order->get_shipping_methods());
    $order_data["refunds"] = SimplUtil::get_data($order->get_refunds());

    $topic = "order.updated";
    $resource = "order";
    $event = "updated";
    $request["topic"] = $topic;
    $request["resource"] = $resource;
    $request["event"] = $event;
    $request["data"] = $order_data;

    $checkout_3pp_client = new SimplCheckout3ppClient();
    try {
        $simplHttpResponse = $checkout_3pp_client->post_hook_request($request);
    } catch (\Throwable $th) {
        log_hook_status($order_id, $refund_id, $topic, $resource, $event, current_filter(), $th->getMessage(), "failed");
    }
}


function log_hook_status($order_id, $refund_id, $topic, $resource, $event, $hook, $data, $status) {
    global $wpdb;
    $hook_status_table = $wpdb->prefix . "hook_status";

    $sql = $wpdb->prepare('SELECT * FROM `' . $hook_status_table . '` WHERE order_id = %s and refund_id = %s and topic = %s and hook = %s', $order_id, $refund_id, $topic, $hook);

    $result = $wpdb->get_row($sql);
    
    if (isset($result)) {
        $now = new DateTime(null, new DateTimeZone('Asia/Kolkata'));
        $retry_count = $result->retry_count + 1;
        $checkout_details = array(
            'data'          => $data,
            'status'        => $status,
            'retry_count'   => $retry_count,
            'updated_at'    => $now->format('Y-m-d H:i:s'),
        );

        $wpdb->update(
            $hook_status_table,
            $checkout_details,
            array(
                'order_id'  => $order_id,
                'refund_id' => $refund_id,
                'topic'     => $topic,
                'hook'      => $hook,
            )
        );
    } else {
        $now = new DateTime(null, new DateTimeZone('Asia/Kolkata'));
        $checkout_details = array(
            'order_id'      => $order_id,
            'refund_id'     => $refund_id,
            'topic'         => $topic,
            'resource'      => $resource,
            'event'         => $event,
            'hook'          => $hook,
            'data'          => $data,
            'status'        => $status,
            'retry_count'   => 1,
            'created_at'    => $now->format('Y-m-d H:i:s'),
        );

        $wpdb->insert( 
            $hook_status_table,
            $checkout_details
        );

        error_log(print_r($response, TRUE));
    }

    if ($wpdb->last_error) {
        $response['status']  = false;
        $response['message'] = $wpdb->last_error;
        $statusCode          = 400;
    } else {
        $response['status']  = true;
        $response['message'] = 'Data successfully updated for wooCommerce hook log';
    }
}