<?php
    include_once 'api.php';
    include_once "public/cart.php";
    include_once "public/auth.php";
    include_once "public/events.php";
    include_once "internal/checkout.php";
    include_once "internal/coupons.php";
    include_once "internal/shipping-method.php";
    include_once "internal/order.php";

	// Defined error CODE for API
	define('SIMPL_HTTP_ERROR_USER_NOTICE','user_error');
	define('SIMPL_HTTP_ERROR_BAD_REQUEST','bad_request');
	define('SIMPL_HTTP_ERROR_CART_CREATE','cart_creation_error');


    include_once SIMPL_PLUGIN_DIR . "/includes/simpl_integration/simpl_integration.php";
    include_once SIMPL_PLUGIN_DIR . "/includes/helpers/cart_helper.php";
    include_once SIMPL_PLUGIN_DIR . "/includes/helpers/wc_helper.php";
    include_once SIMPL_PLUGIN_DIR . "/includes/helpers/validators.php";
