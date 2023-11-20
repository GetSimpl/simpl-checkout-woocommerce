<?php

function simpl_wjecf_is_auto_applied_coupon($coupon)
{
    if (!is_plugin_active('woocommerce-auto-added-coupons/woocommerce-jos-autocoupon.php')) {
        return false;
    }

    if (class_exists('WJECF_Autocoupon')) {
        $autoCoupon = new WJECF_Autocoupon();
        return $autoCoupon->is_auto_coupon($coupon);
    }
    return $coupon->meta_exists('_wjecf_is_auto_coupon');
}


?>
