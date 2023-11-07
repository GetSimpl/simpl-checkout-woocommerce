<?php
use Wdr\App\Controllers\ManageDiscount;

function simpl_wdr_is_auto_applied_coupon($coupon)
{
    if (!is_plugin_active('woo-discount-rules/woo-discount-rules.php')) {
        return false;
    }

    $active_coupon_codes = array();
    if (class_exists('Wdr\App\Controllers\ManageDiscount')) {
        $discount_manager = new ManageDiscount();
        foreach ($discount_manager->getDiscountRules() as $rule) {
            array_push($active_coupon_codes, strtolower($rule->getTitle()));
            if ($rule->hasProductDiscount()) {
                $label = $rule->getProductAdjustments()->cart_label;
                if ($label) {
                    array_push($active_coupon_codes, strtolower($label));
                }
            }
            if ($rule->hasCartDiscount()) {
                $label = $rule->getCartAdjustments()->label;
                if ($label) {
                    array_push($active_coupon_codes, strtolower($label));
                }
            }
            if ($rule->hasBulkDiscount()) {
                $label = $rule->getBulkAdjustments()->cart_label;
                if ($label) {
                    array_push($active_coupon_codes, strtolower($label));
                }
            }
        }
    }
    
    return in_array($coupon->get_code(), $active_coupon_codes);
}


?>
