<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$buttonPosition_pdp = Simpl_WC_Settings::cta_position_in_pdp();
$buttonPosition_cart = Simpl_WC_Settings::cta_position_in_cart();
if(Simpl_WC_Settings::can_display_in_pdp_page()){
  // hook for pdp page
  add_action( $buttonPosition_pdp, 'simpl_add_to_cart_btn' );
}
if(Simpl_WC_Settings::can_display_in_collections_page()){
  // hook for collections page
  //add_action( 'woocommerce_after_shop_loop_item', 'simpl_add_to_cart_btn');
}
if(Simpl_WC_Settings::can_display_in_cart_page()){
  // hook for cart page
  add_action( $buttonPosition_cart, 'simpl_add_to_cart_btn');
}

// footer hook to load script
add_action('wp_footer', 'simpl_load_widget_script');

function simpl_add_to_cart_btn(){  
  $queries = array();
  parse_str(sanitize_title_for_query($_SERVER['QUERY_STRING']), $queries);
  $simpl_pre_qa_env = (isset($queries[SIMPL_PRE_QA_QUERY_PARAM_KEY]) && $queries[SIMPL_PRE_QA_QUERY_PARAM_KEY] == SIMPL_PRE_QA_QUERY_PARAM_VALUE);
  $enabled_only_for_admin = Simpl_WC_Settings::is_simpl_enabled_for_admins() && current_user_can('manage_woocommerce');  
  if(Simpl_WC_Settings::is_simpl_button_enabled() || $enabled_only_for_admin || $simpl_pre_qa_env) {
    $color = Simpl_WC_Settings::cta_color();
    $buttonText = Simpl_WC_Settings::cta_text();
    $productID = get_the_ID();
    
    if(is_cart()){
        $page = 'cart';
    } else if (is_shop()){
        $page = 'shop';
    } else{
        $page = 'product';
    }

    echo '<div class="simpl-checkout-cta-container simpl-button-container" data-background="' . esc_attr($color) . '" page=' . esc_attr($page) . ' data-product-id=' . esc_attr($productID) . ' data-text="' . esc_attr($buttonText) . '"></div>';
  }
}

function simpl_load_widget_script(){
  $script_url = Simpl_WC_Settings::widget_script_url();
  wp_register_script( 'simpl-widget-script', $script_url );
	wp_enqueue_script( 'simpl-widget-script' );
}
