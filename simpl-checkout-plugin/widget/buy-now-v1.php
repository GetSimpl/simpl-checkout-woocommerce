<?php 
$buttonPosition = get_option("wc_settings_tab_simpl_button_position");

add_action( $buttonPosition, 'simpl_add_to_cart_btn' );
add_action( 'woocommerce_after_cart_totals', 'simpl_add_to_cart_btn');
add_action( 'woocommerce_after_shop_loop_item', 'simpl_add_to_cart_btn');
add_action('wp_footer', 'load_widget_script');

function simpl_add_to_cart_btn(){    
    global $product;
    $color = get_option("wc_settings_tab_simpl_button_bg");
    $isCartPage = is_cart();
    $productID = get_the_ID();
    if(is_cart()){
        $page = 'cart';
    } else if (is_shop()){
        $page = 'shop';
    } else{
        $page = 'product';
    }
    
    echo '<div class="simpl-checkout-cta-container" data-background=' .$color. ' page=' .$page. ' data-product-id=' .$productID. ' isCartPage=' .$isCartPage. '></div>';
}

function load_widget_script(){
    echo '<script type="text/javascript" src="http://localhost:4300"></script>';
}
?>
