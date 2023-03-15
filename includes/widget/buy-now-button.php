<?php 
$buttonPosition_pdp = WC_Simpl_Settings::cta_position_pdp();

if(WC_Simpl_Settings::showInPdpPage()){
  // hook for pdp page
  add_action( $buttonPosition_pdp, 'simpl_add_to_cart_btn' );
}
if(WC_Simpl_Settings::showInCollectionsPage()){
  // hook for collections page
  add_action( 'woocommerce_after_shop_loop_item', 'simpl_add_to_cart_btn');
}
if(WC_Simpl_Settings::showInCartPage()){
  // hook for cart page
  add_action( 'woocommerce_after_cart_totals', 'simpl_add_to_cart_btn');
}

// footer hook to load script
add_action('wp_footer', 'load_widget_script');

function simpl_add_to_cart_btn(){
  $enabled_only_for_admin = WC_Simpl_Settings::IsSimplEnabledForAdmin() && current_user_can('manage_woocommerce');  
  
  if(WC_Simpl_Settings::IsSimplButtonEnabled() || $enabled_only_for_admin) {
    $color = WC_Simpl_Settings::cta_bg_color();
    $productID = get_the_ID();
    if(is_cart()){
        $page = 'cart';
    } else if (is_shop()){
        $page = 'shop';
    } else{
        $page = 'product';
    }
    
    echo '<div class="simpl-checkout-cta-container simpl-button-container" data-background=' .$color. ' page=' .$page. ' data-product-id=' .$productID. '></div>';
  }
}

function load_widget_script(){
  $script_url = WC_Simpl_Settings::widget_script();
  echo '<script type="text/javascript" src=' .$script_url. '></script>';
}
?>