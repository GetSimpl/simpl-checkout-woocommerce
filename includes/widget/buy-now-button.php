<?php 
$buttonPosition_pdp = WC_Simpl_Settings::cta_position_in_pdp();
$buttonPosition_cart = WC_Simpl_Settings::cta_position_in_cart();

if(WC_Simpl_Settings::can_display_in_pdp_page()){
  // hook for pdp page
  add_action( $buttonPosition_pdp, 'simpl_add_to_cart_btn' );
}
if(WC_Simpl_Settings::can_display_in_collections_page()){
  // hook for collections page
  add_action( 'woocommerce_after_shop_loop_item', 'simpl_add_to_cart_btn');
}
if(WC_Simpl_Settings::can_display_in_cart_page()){
  // hook for cart page
  add_action( $buttonPosition_cart, 'simpl_add_to_cart_btn');
}

// footer hook to load script
add_action('wp_footer', 'load_widget_script');

function simpl_add_to_cart_btn(){
  $enabled_only_for_admin = WC_Simpl_Settings::is_simpl_enabled_for_admins() && current_user_can('manage_woocommerce');  

  if(WC_Simpl_Settings::is_simpl_button_enabled() || $enabled_only_for_admin) {
    $color = WC_Simpl_Settings::cta_color();
    $buttonText = WC_Simpl_Settings::cta_text();
    $productID = get_the_ID();
    if(is_cart()){
        $page = 'cart';
    } else if (is_shop()){
        $page = 'shop';
    } else{
        $page = 'product';
    }

    $button = getWidgetConfig();
    console_log($button);
    $template = SIMPL_PLUGIN_DIR . './includes/widget/template.php';
    load_template($template, false, array("data-background" => $color, "page" => $page, "data-product-id" => $productID, "data-text" => $buttonText, "data-button" => $button));
  }
}

function load_widget_script(){
  updateCtaWithAdminSettings();
  $script_url = WC_Simpl_Settings::widget_script_url();
  echo '<script type="text/javascript" src=' .$script_url. '></script>';
}
