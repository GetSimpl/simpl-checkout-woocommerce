<?php

// Simpl order actions
add_action( 'woocommerce_order_refunded', 'order_updated_hook' );
add_action( 'woocommerce_order_status_cancelled', 'order_updated_hook' );

// Native order actions
add_action( 'woocommerce_checkout_order_processed', 'order_created_hook', 10, 3);
add_action( 'woocommerce_checkout_update_order_review', 'checkout_update_order_hook' );

// Simpl sync order actions
add_filter( 'woocommerce_order_actions', 'simpl_add_sync_order_action' );
add_action( 'woocommerce_order_action_simpl_sync_order', 'simpl_sync_order_hook' );
