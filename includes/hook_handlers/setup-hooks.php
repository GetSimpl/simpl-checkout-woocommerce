<?php
    add_action( 'save_post_shop_order', 'order_updated_hook', 10, 1 ); // for partial refunds and order updates
    add_action( 'woocommerce_order_fully_refunded', 'order_updated_hook', 10, 1 ); // for full refunds
    add_action( 'woocommerce_order_status_cancelled', 'order_updated_hook', 10, 1 ); // for order cancellations
    add_action( 'woocommerce_checkout_order_processed', 'order_created_hook', 10, 3);
    add_action( 'woocommerce_checkout_update_order_review', 'checkout_update_order_hook', 10, 1);