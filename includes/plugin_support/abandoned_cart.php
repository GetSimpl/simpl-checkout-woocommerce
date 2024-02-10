<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//cart flow is third party plugin
add_action("simpl_abandoned_cart", "simpl_cart_flows_abandoned_cart", 10, 2);
function simpl_cart_flows_abandoned_cart($cart, $simpl_checkout_data)
{
    if (!is_plugin_active('woo-cart-abandonment-recovery/woo-cart-abandonment-recovery.php')) {
        return false;
    }
 
    global $wpdb;
    $current_time = current_time('Y-m-d H:i:s');
    $simpl_checkout_data = $simpl_checkout_data['cart'];

    $provider_other_data = array(
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
    $checkout_details = array(
        'email'         => $simpl_checkout_data['billing_address']['email'] ?? '',
        'cart_contents' => serialize($cart_content),
        'cart_total'    => sanitize_text_field($simpl_checkout_data['total_price']),
        'time'          => $current_time,
        'order_status' => 'normal',
        'other_fields'  => serialize($provider_other_data),
        'checkout_id'   => wc_get_page_id('cart'),
    );
    
    $sessionId = WC()->session->get('wcf_session_id');

    $cart_abandonment_table = $wpdb->prefix . "cartflows_ca_cart_abandonment";
    if (empty($checkout_details) == false) {
        $result = $wpdb->get_row(
            $wpdb->prepare('SELECT * FROM `' . $cart_abandonment_table . '` WHERE session_id = %s and order_status IN (%s, %s)', $sessionId, 'normal', 'abandoned')
        );


        if (isset($result)) {
            $wpdb->update(
                $cart_abandonment_table,
                $checkout_details,
                array('session_id' => $sessionId)
            );
            if ($wpdb->last_error) {
                $response['status']  = false;
                $response['message'] = $wpdb->last_error;
            } else {
                $response['status']  = true;
                $response['message'] = 'Data successfully updated for wooCommerce cart abandonment recovery';
            }

        } else {
            $simpl_session_id = WC()->session->get('simpl_checkout_session_id_'.$simpl_checkout_data['checkout_order_id']);
            if(isset($simpl_session_id)) {
                $sessionId = $simpl_session_id;        
            } else {
                $sessionId                     = md5(uniqid(wp_rand(), true));
                WC()->session->set('simpl_checkout_session_id_'.$simpl_checkout_data['checkout_order_id'], $sessionId);
            }


            $checkout_details['session_id'] = sanitize_text_field($sessionId);

            // Inserting row into Database.
            $wpdb->insert(
                $cart_abandonment_table,
                $checkout_details
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

    // do_action("woocommerce_order_status_changed");

    return $response;
}
