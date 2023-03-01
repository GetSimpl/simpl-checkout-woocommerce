<?php
include "cart.php";
include "auth.php";
add_action( 'rest_api_init', function () {
    register_rest_route( 'simpl/v1', '/cart', array(
      'methods' => 'POST',
      'callback' => 'create_cart',
    ) );

    register_rest_route( 'wc-simpl/v1/', '/test', array(
      'methods' => 'GET',
      'callback' => 'test_auth'
    ) );

    register_rest_route( 'wc-simpl/v1/', '/authenticate_simpl', array(
      'methods' => 'GET',
      'callback' => 'authenticate_simpl'
    ) );

    //shipping list
    //create order
    //create
  } );
?>