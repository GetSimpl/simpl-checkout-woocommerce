<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly   
   
function scwp_add_gateway_class($gateways)
{
    $gateways[] = 'SCWP_Gateway'; // your class name is here
    return $gateways;
}

function scwp_init_gateway_class()
{
    class SCWP_Gateway extends WC_Payment_Gateway
    {
        public function scwp_construct()
        {

            $this->id = 'simpl_checkout_payment'; // payment gateway plugin ID
            $this->icon = 'https://assets.getsimpl.com/images/banner-logo.png'; // URL of the icon that will be displayed on checkout page near your gateway name
            $this->has_fields = true;
            $this->method_title = 'Simpl checkout payment';
            $this->method_description = 'Payment mode for Simpl checkout'; // will be displayed on the options page

            // gateways can support subscriptions, refunds, saved payment methods,
            $this->supports = array(
                'refunds'
            );

            // Method with all the options fields
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->enabled = $this->get_option('enabled');

            // This action hook saves the settings
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_filter('woocommerce_available_payment_gateways', array(
                $this,
                'scwp_remove_simpl_gateway'
            ), 10, 2);

            // You can also register a webhook here
            // add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );
        }

        public function scwp_remove_simpl_gateway($available_gateways)
        {
            if (WC()->session) {
                $simpl_order_id = WC()->session->get("simpl_order_id");
                $order = wc_get_order((int)$simpl_order_id);

                if (!$order) {
                    unset($available_gateways['simpl_checkout_payment']);
                }
                if ($order) {
                    $status = $order->get_status();
                    if ($status != "checkout-draft") {
                        unset($available_gateways['simpl_checkout_payment']);
                    }
                }
            }
            return $available_gateways;
        }

        public function scwp_process_payment($order_id)
        {

            $order = wc_get_order($order_id);

            // // Mark as on-hold (we're awaiting the payment)
            // $order->update_status( 'on-hold', __( 'Awaiting offline payment', 'wc-gateway-offline' ) );

            // Reduce stock levels            
            $order->update_status('pending');
            $order->update_status('processing');

            $order->reduce_order_stock();
            // Remove cart
            WC()->cart->empty_cart();

            // Return thankyou redirect
            return array(
                'result'    => 'success',
                'redirect'  => $this->get_return_url($order)
            );
        }
    }
}
