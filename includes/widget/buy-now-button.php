<?php 
$buttonPosition_pdp = WC_Simpl_Settings::cta_position_in_pdp();
$buttonPosition_cart = WC_Simpl_Settings::cta_position_in_cart();
if(WC_Simpl_Settings::can_display_in_pdp_page()){
  // hook for pdp page
  add_action( $buttonPosition_pdp, 'simpl_add_to_cart_btn' );
}
if(WC_Simpl_Settings::can_display_in_collections_page()){
  // hook for collections page
  //add_action( 'woocommerce_after_shop_loop_item', 'simpl_add_to_cart_btn');
}
if(WC_Simpl_Settings::can_display_in_cart_page()){
  // hook for cart page
  add_action( $buttonPosition_cart, 'simpl_add_to_cart_btn');
}

// footer hook to load script
add_action('wp_footer', 'load_widget_script');

function simpl_add_to_cart_btn(){
  $queries = array();
  parse_str($_SERVER['QUERY_STRING'], $queries);
  $simpl_pre_qa_env = (isset($queries[SIMPL_PRE_QA_QUERY_PARAM_KEY]) && $queries[SIMPL_PRE_QA_QUERY_PARAM_KEY] == SIMPL_PRE_QA_QUERY_PARAM_VALUE);
  $enabled_only_for_admin = WC_Simpl_Settings::is_simpl_enabled_for_admins() && current_user_can('manage_woocommerce');  
  if(WC_Simpl_Settings::is_simpl_button_enabled() || $enabled_only_for_admin || $simpl_pre_qa_env) {
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

    echo '<div class="simpl-checkout-cta-container simpl-button-container" data-background="' . $color . '" page=' . $page . ' data-product-id=' . $productID . ' data-text="' . $buttonText . '"></div>';
  }
}

function load_widget_script(){
  $script_url = WC_Simpl_Settings::widget_script_url();
  wp_register_script( 'simpl-widget-script', $script_url );
	wp_enqueue_script( 'simpl-widget-script' );
}
