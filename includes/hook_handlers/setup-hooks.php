<?php

// Simpl order actions
add_action( 'woocommerce_order_refunded', 'order_refunded_hook' );
add_action( 'woocommerce_order_status_cancelled', 'order_cancelled_hook' );

// Native order actions
add_action( 'woocommerce_order_status_processing', 'order_created_hook', 10, 2);
add_action( 'woocommerce_checkout_update_order_review', 'checkout_update_order_hook' );

// Simpl sync order actions
add_filter( 'woocommerce_order_actions', 'simpl_add_sync_order_action' );
add_action( 'woocommerce_order_action_simpl_sync_order', 'simpl_sync_order_hook' );

// Simpl plugin activate/de-activate hooks
register_activation_hook( SIMPL_PLUGIN_FILE_URL, 'plugin_status_activate_hook' );
register_deactivation_hook( SIMPL_PLUGIN_FILE_URL, 'plugin_status_deactivate_hook' );
