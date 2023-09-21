<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$buttonPosition_pdp = SCWP_Settings::scwp_cta_position_in_pdp();
$buttonPosition_cart = SCWP_Settings::scwp_cta_position_in_cart();
if(SCWP_Settings::scwp_can_display_in_pdp_page()){
  // hook for pdp page
  add_action( $buttonPosition_pdp, 'scwp_add_to_cart_btn' );
}
if(SCWP_Settings::scwp_can_display_in_collections_page()){
  // hook for collections page
  //add_action( 'woocommerce_after_shop_loop_item', 'scwp_add_to_cart_btn');
}
if(SCWP_Settings::scwp_can_display_in_cart_page()){
  // hook for cart page
  add_action( $buttonPosition_cart, 'scwp_add_to_cart_btn');
}

// footer hook to load script
add_action('wp_footer', 'scwp_load_widget_script');

function scwp_add_to_cart_btn(){
  $queries = array();
  parse_str($_SERVER['QUERY_STRING'], $queries);
  $simpl_pre_qa_env = (isset($queries[SIMPL_PRE_QA_QUERY_PARAM_KEY]) && $queries[SIMPL_PRE_QA_QUERY_PARAM_KEY] == SIMPL_PRE_QA_QUERY_PARAM_VALUE);
  $enabled_only_for_admin = SCWP_Settings::scwp_is_simpl_enabled_for_admins() && current_user_can('manage_woocommerce');  
  if(SCWP_Settings::scwp_is_simpl_button_enabled() || $enabled_only_for_admin || $simpl_pre_qa_env) {
    $color = SCWP_Settings::scwp_cta_color();
    $buttonText = SCWP_Settings::scwp_cta_text();
    $productID = get_the_ID();
    
    if(is_cart()){
        $page = 'cart';
    } else if (is_shop()){
        $page = 'shop';
    } else{
        $page = 'product';
    }

    echo '<div class="simpl-checkout-cta-container simpl-button-container" data-background="' . esc_attr($color) . '" page=' . esc_attr($page) . ' data-product-id=' . $productID . ' data-text="' . esc_attr($buttonText) . '"></div>';
  }
}

function scwp_load_widget_script(){
  $script_url = SCWP_Settings::scwp_widget_script_url();
  wp_register_script( 'simpl-widget-script', $script_url );
	wp_enqueue_script( 'simpl-widget-script' );
}
