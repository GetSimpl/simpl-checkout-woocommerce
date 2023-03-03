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
            
            // You can also register a webhook here
            // add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );
         }

         public function is_available() {
            return true;
         }

         public function process_payment( $order_id ) {
    
            $order = wc_get_order( $order_id );
                    
            // Mark as on-hold (we're awaiting the payment)
            $order->update_status( 'on-hold', __( 'Awaiting offline payment', 'wc-gateway-offline' ) );
                    
            // Reduce stock levels
            $order->reduce_order_stock();
                    
            $order->update_status( ‘completed’ );
            // Remove cart
            WC()->cart->empty_cart();
                    
            // Return thankyou redirect
            return array(
                'result'    => 'success',
                'redirect'  => $this->get_return_url( $order )
            );
        }
        
    }
}



?>