<?php
include "cart.php";
add_action( 'rest_api_init', function () {
    register_rest_route( 'simpl/v1', '/cart', array(
      'methods' => 'POST',
      'callback' => 'create_cart',
    ) );
  } );
?>