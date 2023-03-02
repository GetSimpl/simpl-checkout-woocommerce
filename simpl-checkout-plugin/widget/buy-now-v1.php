<?php 
$buttonPosition = get_option("wc_settings_tab_simpl_button_position");

add_action( $buttonPosition, 'simpl_add_to_cart_btn' );
add_action( 'woocommerce_after_cart_totals', 'simpl_add_to_cart_btn');
add_action( 'woocommerce_after_shop_loop_item', 'simpl_add_to_cart_btn');
add_action('wp_footer', 'load_widget_script');

function simpl_add_to_cart_btn(){    
    global $product;
    $isCartPage = is_cart();
    $productID = get_the_ID();
    echo '<div>123456</div>';
    echo '<div class="simpl-checkout-cta-container" data-product-id=' .$productID. ' isCartPage=' .$isCartPage. '></div>';
}

function load_widget_script(){
    echo '<script type="text/javascript" src="http://localhost:4300"></script>';
}
?>
