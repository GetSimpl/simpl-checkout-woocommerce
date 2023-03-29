<?php

add_action("simpl_abandoned_cart", "save_abandoned_cart_support", 10, 2);

function save_abandoned_cart_support($cart, $simpl_checkout_data)
{
    global $wpdb;
    $currentTime = current_time('Y-m-d H:i:s');

    $otherFields = array(
        'wcf_billing_company'     => "",
        'wcf_billing_address_1'   => $simpl_checkout_data['billing_address']['line1'] ?? '',
        'wcf_billing_address_2'   => $simpl_checkout_data['billing_address']['line2'] ?? '',
        'wcf_billing_state'       => $simpl_checkout_data['billing_address']['state'] ?? '',
        'wcf_billing_postcode'    => $simpl_checkout_data['billing_address']['postcode'] ?? '',
        'wcf_shipping_first_name' => $simpl_checkout_data['shipping_address']['first_name'] ?? '',
        'wcf_shipping_last_name'  => $simpl_checkout_data['shipping_address']['last_name'] ?? '',
        'wcf_shipping_company'    => "",
        'wcf_shipping_country'    => $simpl_checkout_data['shipping_address']['country'] ?? '',
        'wcf_shipping_address_1'  => $simpl_checkout_data['shipping_address']['line1'] ?? '',
        'wcf_shipping_address_2'  => $simpl_checkout_data['shipping_address']['line2'] ?? '',
        'wcf_shipping_city'       => $simpl_checkout_data['shipping_address']['city'] ?? '',
        'wcf_shipping_state'      => $simpl_checkout_data['shipping_address']['state'] ?? '',
        'wcf_shipping_postcode'   => $simpl_checkout_data['shipping_address']['postcode'] ?? '',
        'wcf_order_comments'      => "",
        'wcf_first_name'          => $simpl_checkout_data['shipping_address']['first_name'] ?? '',
        'wcf_last_name'           => "",
        'wcf_phone_number'        => $simpl_checkout_data['shipping_address']['phone'] ?? '',
    );
    $cart_content  = $cart->get_cart();
    $checkoutDetails = array(
        'email'         => $simpl_checkout_data['shipping_address']['email'] ?? '',
        'cart_contents' => serialize($cart_content),
        'cart_total'    => sanitize_text_field($simpl_checkout_data['total_price']),
        'time'          => $currentTime,
        'other_fields'  => serialize($otherFields),
        'checkout_id'   => wc_get_page_id('cart'),
    );

    $sessionId = WC()->session->get('wcf_session_id');

    $cartAbandonmentTable = $wpdb->prefix . "cartflows_ca_cart_abandonment";
    if (empty($checkoutDetails) == false) {
        $result = $wpdb->get_row(
            $wpdb->prepare('SELECT * FROM `' . $cartAbandonmentTable . '` WHERE session_id = %s and order_status IN (%s, %s)', $sessionId, 'normal', 'abandoned')
        );

        var_dump($result);

        if (isset($result)) {
            $wpdb->update(
                $cartAbandonmentTable,
                $checkoutDetails,
                array('session_id' => $sessionId)
            );
            if ($wpdb->last_error) {
                $response['status']  = false;
                $response['message'] = $wpdb->last_error;
                $statusCode          = 400;
            } else {
                $response['status']  = true;
                $response['message'] = 'Data successfully updated for wooCommerce cart abandonment recovery';
                $statusCode          = 200;
            }

        } else {
            $sessionId                     = md5(uniqid(wp_rand(), true));  // nosemgrep: php.lang.security.weak-crypto.weak-crypto

            $checkoutDetails['session_id'] = sanitize_text_field($sessionId);

            // Inserting row into Database.
            $wpdb->insert(
                $cartAbandonmentTable,
                $checkoutDetails
            );

            if ($wpdb->last_error) {
                $response['status']  = false;
                $response['message'] = $wpdb->last_error;
                $statusCode          = 400;
            } else {
                // Storing session_id in WooCommerce session.
                WC()->session->set('wcf_session_id', $sessionId);
                $response['status']  = true;
                $response['message'] = 'Data successfully inserted for wooCommerce cart abandonment recovery';
                $statusCode          = 200;
            }
        }
    }

    return $logObj;
}


?>