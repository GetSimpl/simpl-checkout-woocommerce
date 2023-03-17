<?php
    include_once 'api.php';
    include_once "public/cart.php";
    include_once "public/auth.php";
    include_once "internal/checkout.php";
    include_once "internal/coupons.php";
    include_once "internal/shipping-method.php";
    include_once "internal/order.php";

    include_once SIMPL_PLUGIN_DIR . "/includes/simpl_integration/simpl_integration.php";
    include_once SIMPL_PLUGIN_DIR . "/includes/helpers/cart_helper.php";
    include_once SIMPL_PLUGIN_DIR . "/includes/helpers/wc_helper.php";
    include_once SIMPL_PLUGIN_DIR . "/includes/helpers/validators.php";
?>