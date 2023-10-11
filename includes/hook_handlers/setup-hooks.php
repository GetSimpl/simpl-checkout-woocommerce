<?php
    add_action( 'woocommerce_order_refunded', 'order_refunded_hook', 10, 1 );
    add_action( 'woocommerce_checkout_order_processed', 'woocommerce_checkout_order_created_hook', 10, 3);