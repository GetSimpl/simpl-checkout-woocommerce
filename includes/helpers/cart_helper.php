<?php   
function initCartCommon()
{ 
    if (defined('WC_ABSPATH')) {
        // WC 3.6+ - Cart and other frontend functions are not included for REST requests.
        include_once WC_ABSPATH . 'includes/wc-cart-functions.php'; // nosemgrep: file-inclusion
        include_once SIMPL_PLUGIN_DIR . "/helpers/notice_helper.php";
    }

    if (null === WC()->session) {
        $session_class = apply_filters('woocommerce_session_handler', 'WC_Session_Handler');
        WC()->session  = new $session_class();
        WC()->session->init();        
    }

    if (null === WC()->customer) {
        WC()->customer = new WC_Customer(get_current_user_id(), true);
    }

    if (null === WC()->cart) {
        WC()->cart = new WC_Cart();
        WC()->cart->get_cart();
    }
}

function get_shipping_methods() {
    $shipping_methods_count = 0;
        $shipping_methods_array = array();
        foreach ( WC()->cart->get_shipping_packages() as $package_id => $package ) {
            // Check if a shipping for the current package exist
            if ( WC()->session->__isset( 'shipping_for_package_'.$package_id ) ) {
                // Loop through shipping rates for the current package
                foreach ( WC()->session->get( 'shipping_for_package_'.$package_id )['rates'] as $shipping_rate_id => $shipping_rate ) {
                    $rate_id     = $shipping_rate->get_id(); // same thing that $shipping_rate_id variable (combination of the shipping method and instance ID)
                    $method_id   = $shipping_rate->get_method_id(); // The shipping method slug
                    $label_name  = $shipping_rate->get_label(); // The label name of the method
                    $cost        = $shipping_rate->get_cost(); // The cost without tax
                    $tax_cost    = $shipping_rate->get_shipping_tax(); // The tax cost
                    $taxes       = $shipping_rate->get_taxes(); // The taxes details (array)
                    $shipping_methods_array[$shipping_methods_count] = array("id" => $rate_id, "slug" => $method_id, "name" => $label_name, "amount" => $cost, "total_tax" => $tax_cost, "taxes" => $taxes);
                    $shipping_methods_count += 1;
                }
            }
        }
        return $shipping_methods_array;
}

function get_applied_discounts_from_cart() {
    $applied_discounts = array();
    $applied_discount_count = 0;
    foreach(WC()->cart->get_coupons() as $coupon_code => $coupon) {
        $applied_discounts[$applied_discount_count] = array("code" => $coupon_code, "amount" => $coupon->get_amount(), "free_shipping" => $coupon->enable_free_shipping());
        $applied_discount_count += 1;
    }
    return $applied_discounts;
}

function getCartLineItem($cart) {
    $i = 0;

    foreach($cart as $item_id => $item) { 
        $product =  wc_get_product( $item['product_id']); 
        $price = round($item['line_subtotal']*100) + round($item['line_subtotal_tax']*100);

       $data[$i]['sku'] = $product->get_sku();
       $data[$i]['quantity'] = (int)$item['quantity'];
       $data[$i]['title'] = mb_substr($product->get_title(), 0, 125, "UTF-8");
       $data[$i]['description'] = mb_substr($product->get_title(), 0, 250,"UTF-8");
       $productImage = $product->get_image_id()?? null;
       $data[$i]['image'] = $productImage? wp_get_attachment_url( $productImage ) : null;
       $data[$i]['url'] = $product->get_permalink();
       $data[$i]['price'] = (empty($product->get_price())=== false) ? $price/$item['quantity'] : 0;
       $data[$i]['variant_id'] = $item['variation_id'];
       $data[$i]['product_id'] = $item['product_id'];
       $data[$i]['offer_price'] = (empty($productDetails['sale_price'])=== false) ? (int) $productDetails['sale_price']*100 : $price/$item['quantity'];
       $i++;
    } 

    return $data;
}
?>