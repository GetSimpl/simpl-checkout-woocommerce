<?php
    add_action( 'woocommerce_order_refunded', 'order_updated_hook', 10, 1 );
    add_action( 'woocommerce_order_status_cancelled', 'order_updated_hook', 100, 1 );
    add_action( 'woocommerce_checkout_order_processed', 'order_created_hook', 10, 3);
    add_action( 'woocommerce_checkout_update_order_review', 'checkout_update_order_hook', 10, 1);