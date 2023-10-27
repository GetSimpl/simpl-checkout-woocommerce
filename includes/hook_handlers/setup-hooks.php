<?php

// Simpl order actions
add_action( 'woocommerce_order_refunded', 'order_updated_hook', 10, 1 );
add_action( 'woocommerce_order_status_cancelled', 'order_updated_hook', 10, 1 );

// Native order actions
add_action( 'woocommerce_checkout_order_processed', 'order_created_hook', 10, 3);
add_action( 'woocommerce_checkout_update_order_review', 'checkout_update_order_hook', 10, 1);

// Simpl sync order actions
add_action( 'woocommerce_order_actions', 'simpl_add_sync_order_action' );
add_action( 'woocommerce_order_action_wc_custom_order_action', 'simpl_process_sync_order_action' );
