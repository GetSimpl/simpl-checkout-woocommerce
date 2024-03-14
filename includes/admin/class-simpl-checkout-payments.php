<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function simpl_init_gateway_class() {
    
    class Simpl_WC_Gateway extends WC_Payment_Gateway {

        public function __construct() {
            $this->id                 = SIMPL_PAYMENT_GATEWAY;
            //$this->icon               = 'https://assets.getsimpl.com/images/banner-logo.png';
            $this->has_fields         = false;
            $this->method_title       = SIMPL_PAYMENT_METHOD;
            $this->method_description = 'Allows payments by Paylater, Pay-in-3, Credit/Debit Cards, NetBanking, UPI, and multiple Wallets';
    
            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();
    
            // Define user set variables.
            $this->title        = $this->get_option( 'title' );
    
            // Actions.
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );            
            add_filter('woocommerce_available_payment_gateways', array( $this, 'remove_simpl_gateway'), 10, 2 );
        }

        // TODO: Need to handle. With this, refund with Simpl starts appearing in order refunds.
        // public $supports = array(
        //     'products',
        //     'refunds'
        // );

        // Probably to restrict other plugins to use Simpl payment mode.
        public function remove_simpl_gateway($available_gateways) {

            if (WC()->session) {
                $simpl_order_id = WC()->session->get("simpl_order_id");
                $order = wc_get_order((int)$simpl_order_id);

                if (!$order) {
                    unset($available_gateways[SIMPL_PAYMENT_GATEWAY]);
                }
                if ($order) {
                    $status = $order->get_status();
                    if ($status != "checkout-draft") {
                        unset($available_gateways[SIMPL_PAYMENT_GATEWAY]);
                    }
                }
            }
            
            return $available_gateways;
        }
    
        /**
         * Initialise Gateway Settings Form Fields.
         */
        public function init_form_fields() {
    
            $this->form_fields = array(
                'enabled'      => array(
                    'title'   => 'Enable/Disable',
                    'type'    => 'checkbox',
                    'label'   => 'Enable Simpl Checkout',
                    'default' => 'yes',
                ),
                'title'        => array(
                    'title'       => 'Title',
                    'type'        => 'safe_text',
                    'description' => 'This controls the title which the user sees during checkout.',
                    'default'     => SIMPL_PAYMENT_METHOD,
                    'desc_tip'    => true,
                )
            );
        }

        public function process_payment($order_id) {
            
            $order = wc_get_order($order_id);

            $order->update_status('pending');
            //This is required. Store credit is debited on this action
            do_action( 'woocommerce_checkout_order_processed', $order_id, array(), $order );
            
            if( $order->get_payment_method("edit") == SIMPL_PAYMENT_METHOD_COD ) {
                $order->update_status('processing');
                
                // Need to do the following specifically for COD. These are done by the payment_complete method for other modes.
                // Reduce stock levels 
                $order->reduce_order_stock();                
            } else {
                // Complete order payment.
                $order->payment_complete();
            }

            WC()->cart->empty_cart();

            // Return thankyou redirect - redirection doesn't happen yet. The URL is pushed to 3pp.
            return array(
                'result'    => 'success',
                'redirect'  => $this->get_return_url($order)
            );
        }
    }
}

/**
 * Add the Gateway to WooCommerce
 **/
function simpl_add_gateway_class($methods) {
    $methods[] = 'Simpl_WC_Gateway';
    return $methods;
}