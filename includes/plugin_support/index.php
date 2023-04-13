<?php
include_once 'abandoned_cart.php';
include_once 'cod.php';
define(
    "PLUGIN_SUPPORTED",
    array(
        'cod::smart_cod' => 'wc-smart-cod/wc-smart-cod.php',
        'abandoned_cart::cart_flow' => 'woo-cart-abandonment-recovery/woo-cart-abandonment-recovery.php'
    )
);
