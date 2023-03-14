<?php 
$buttonPosition_pdp = WC_Simpl_Settings::cta_position_pdp();
// hook for pdp page
add_action( $buttonPosition_pdp, 'simpl_add_to_cart_btn' );
// hook for cart page
add_action( 'woocommerce_after_cart_totals', 'simpl_add_to_cart_btn');
// hook for collections page
add_action( 'woocommerce_after_shop_loop_item', 'simpl_add_to_cart_btn');
// footer hook to load script
add_action('wp_footer', 'load_widget_script');

function simpl_add_to_cart_btn(){
  // add_action('wp_enqueue_scripts', 'register_scripts');
  $enabled_only_for_admin = WC_Simpl_Settings::IsSimplEnabledForAdmin() && current_user_can('manage_woocommerce');  
  
  if(WC_Simpl_Settings::IsSimplButtonEnabled() || $enabled_only_for_admin) {
    $color = WC_Simpl_Settings::cta_bg_color();
    $isCartPage = is_cart();
    $productID = get_the_ID();
    if(is_cart()){
        $page = 'cart';
    } else if (is_shop()){
        $page = 'shop';
    } else{
        $page = 'product';
    }
    
    echo '<div class="simpl-checkout-cta-container simpl-button-container" data-background=' .$color. ' page=' .$page. ' data-product-id=' .$productID. ' isCartPage=' .$isCartPage. '></div>';
  }
}

function load_widget_script(){
  echo '<script type="text/javascript" src="https://simpl-terraform-ap-south-1.s3.ap-south-1.amazonaws.com/widget-script/staging/simpl-checkout-woocommerce-widget.iife.js?response-content-disposition=inline&X-Amz-Security-Token=IQoJb3JpZ2luX2VjEBgaCmFwLXNvdXRoLTEiSDBGAiEAyhT%2Fz0iTooAMMCjbg14gK5tqeuepV42p5g5nj%2FmtgXACIQD3azseXUbvxOrLAkpxZUUR500YAxqNuBMiBveCfItzwyq5AgjR%2F%2F%2F%2F%2F%2F%2F%2F%2F%2F8BEAQaDDcyNTgyNzY4Njg5OSIMOOHpoNZ5fhrEr1pfKo0CkE4cA%2BLJb57CJhW6McFUedhcMiiw%2B1x%2BSJ79POOvE%2FzB3eDrppSDnNNlGHstFTqMyRH8EXxFvHhwdEqAfrvwP1q89Kch3SQILhuml%2Bpxpj1ihlfgUlKypXEg86KZxIfdhg3tRuy6xkg%2FPSJHG3OCeTgBx7yX8p1f49CD6BGU6XNJWu7mR8U7mermU5E0PlOWLO%2FXeMKB8ESrEypNAbz4pkxdhnNvIBGFQ0k5ep%2FFKnvsidDu2JGatNyGdlusRXbp05iPK%2BQW1I5qDC%2FaZCr6PKd1HuiKdtMTg14%2FRNac6l5Zv0N5uLxMAFslBDTzwK%2FjVsw6e4KjHgG2gQlgsMgXbVj%2B9xHYrWHhmJ%2FFAywwocnAoAY63gH%2FtB3cz2XuBileSPxwn9NYxaqzfGqilhB9GZwNqfsrsoR0c4ZBtExiMGs0FE%2BLByOR%2Bte8ZI2%2FubFsf%2BkPli0rBt2bGb5%2Bco6YpZaHxjRI5b6vyoIR5mgHPNgBt4LoAuMtegVZE7sgmBobVKchXHieNp5VuikTGReJrFURKafDEvK1L2Gt6%2BOekPk6n3CWve7Wyvw4jyT%2FcPnAuBuzDTsoFFN7nlRCInezstQQfWVj%2F%2BNekDF0M%2BmQG78StCdKeAGaJyOvPwQfYokS9WIE%2Blf4BQvUMlgf4xqtQ%2FYchw8%3D&X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Date=20230314T120649Z&X-Amz-SignedHeaders=host&X-Amz-Expires=300&X-Amz-Credential=ASIA2R7VTSHZ5EYEVFZ5%2F20230314%2Fap-south-1%2Fs3%2Faws4_request&X-Amz-Signature=2d118aecd8e1c0c1b29dbe9d86954c3a138efb83dbb50f5a50da0e99759fb62a"></script>';
}

function register_scripts () {
  wp_register_script( 'jquery-simpl', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jqueryexample.min.js');
}
?>