<?php
add_filter( 'woocommerce_payment_gateways', 'simpl_add_gateway_class' );
add_action( 'plugins_loaded', 'simpl_init_gateway_class' );

function simpl_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_Simpl_Gateway'; // your class name is here
	return $gateways;
}

function simpl_init_gateway_class() {
    class WC_Simpl_Gateway extends WC_Payment_Gateway {
        public function __construct() {

            $this->id = 'simpl'; // payment gateway plugin ID
            $this->icon = 'https://assets.getsimpl.com/images/banner-logo.png'; // URL of the icon that will be displayed on checkout page near your gateway name
            $this->has_fields = true; // in case you need a custom credit card form
            $this->method_title = 'Simpl payment';
            $this->method_description = 'Need more description'; // will be displayed on the options page
        
            // gateways can support subscriptions, refunds, saved payment methods,
            // but in this tutorial we begin with simple payments
            $this->supports = array(
                'refunds'
            );
        
            // Method with all the options fields
            $this->init_form_fields();
        
            // Load the settings.
            $this->init_settings();
            $this->title = $this->get_option( 'title' );
            $this->description = $this->get_option( 'description' );
            $this->enabled = $this->get_option( 'enabled' );
            $this->testmode = 'yes' === $this->get_option( 'testmode' );
            $this->private_key = $this->testmode ? $this->get_option( 'test_private_key' ) : $this->get_option( 'private_key' );
            $this->publishable_key = $this->testmode ? $this->get_option( 'test_publishable_key' ) : $this->get_option( 'publishable_key' );
        
            // This action hook saves the settings
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_filter( 'woocommerce_available_payment_gateways', array(
                $this,
                'remove_simpl_gateway'
             ), 10, 2 );

            // You can also register a webhook here
            // add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );
         }

         public function remove_simpl_gateway( $available_gateways ) {
            global $woocommerce;
            if(WC()->session) {
                $simpl_order_id = WC()->session->get("simpl_order_id");
                $order = wc_get_order((int)$simpl_order_id);
                if($order) {
                    $status = $order->get_status();
                    if($status != "draft") {
                        unset( $available_gateways['simpl'] );                
                    }
                }         
            }
            // $cart_hash = WC()->cart-> get_cart_hash();
            // if(wc_verify_nonce("simpl_cart_".$cart_hash)) {
                // unset( $available_gateways['simpl'] );
            // }
            return $available_gateways;
         }

         public function process_payment( $order_id ) {
    
            $order = wc_get_order( $order_id );
                    
            // Mark as on-hold (we're awaiting the payment)
            $order->update_status( 'on-hold', __( 'Awaiting offline payment', 'wc-gateway-offline' ) );
                    
            // Reduce stock levels
            $order->reduce_order_stock();
                    
            $order->update_status('completed');
            // Remove cart
            WC()->cart->empty_cart();
                    
            // Return thankyou redirect
            return array(
                'result'    => 'success',
                'redirect'  => $this->get_return_url( $order )
            );
        }

        public function process_refund($orderId, $amount = null, $reason = '') {            
            // $simplHttpResponse = wp_remote_post( "https://webhook.site/15d3a3ef-58bc-41bc-9633-0e9f19593c69?test=123", array(
            //     "body" => json_encode(array("order_id" => $order_id)),
            //     "headers" => array("Shopify-Shop-Domain" => "checkout-staging-v2.myshopify.com", "content-type" => "application/json"),
            // ));
            // var_dump($order_id);
            return false;
        }
        
    }
}



?>