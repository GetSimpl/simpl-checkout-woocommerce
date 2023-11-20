<?php

    include_once 'automatic_discount_plugins/woo_extended_coupon.php';
    include_once 'automatic_discount_plugins/woo_discount_rules.php';

    function simpl_is_auto_applied_coupon($coupon)
    {
        return simpl_wjecf_is_auto_applied_coupon($coupon) || simpl_wdr_is_auto_applied_coupon($coupon);
    }

?>
