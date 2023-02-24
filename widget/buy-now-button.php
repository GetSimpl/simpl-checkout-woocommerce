<?php 
add_action( 'woocommerce_after_add_to_cart_button', 'simpl_add_to_cart_btn' );
function simpl_add_to_cart_btn(){
  // add_action('wp_enqueue_scripts', 'register_scripts');
  $activatedFlag = get_option("wc_settings_tab_simpl_button_activated");
  $buttonText = get_option("wc_settings_tab_simpl_button_text");
  $productID = get_the_ID();
  if($activatedFlag == "yes") {
    $tempTest = SIMPL_PLUGIN_DIR . 'templates/button.php';
    load_template( $tempTest, false, array("button_text" => $buttonText, "product_id" => $productID) );    
  }
}

function register_scripts () {
  wp_register_script( 'jquery-simpl', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jqueryexample.min.js');
}
?>