<?php
add_action('rest_api_init', function () {
  register_rest_route('simpl/v1', '/cart', array(
    'methods' => 'POST',
    'callback' => 'create_cart',
    'permission_callback' => function () {
      return true;
    }
  ));


  register_rest_route('wc-simpl/v1', '/checkout', array(
    'methods' => 'POST',
    'callback' => 'create_checkout',
    'permission_callback' => 'internal_authenticate'
  ));

  register_rest_route('simpl/v1', '/events', array(
    'methods' => 'POST',
    'callback' => 'create_events',
    'permission_callback' => function () {
      return true;
    }
  ));

  register_rest_route('wc-simpl/v1', '/checkout', array(
    'methods' => 'PUT',
    'callback' => 'update_checkout',
    'permission_callback' => 'internal_authenticate'
  ));

  register_rest_route('wc-simpl/v1', '/checkout', array(
    'methods' => 'GET',
    'callback' => 'fetch_checkout',
    'permission_callback' => 'internal_authenticate'
  ));

  register_rest_route('wc-simpl/v1', '/checkout/coupon', array(
    'methods' => 'POST',
    'callback' => 'apply_coupon',
    'permission_callback' => 'internal_authenticate'
  ));

  register_rest_route('wc-simpl/v1', '/checkout/shipping-method', array(
    'methods' => 'POST',
    'callback' => 'set_shipping_method',
    'permission_callback' => 'internal_authenticate'
  ));

  register_rest_route('wc-simpl/v1', '/checkout/coupon', array(
    'methods' => 'DELETE',
    'callback' => 'remove_coupon',
    'permission_callback' => 'internal_authenticate'
  ));

  register_rest_route('wc-simpl/v1', '/checkout/coupons', array(
    'methods' => 'DELETE',
    'callback' => 'remove_coupons',
    'permission_callback' => 'internal_authenticate'
  ));

  register_rest_route('wc-simpl/v1', '/order', array(
    'methods' => 'POST',
    'callback' => 'create_order',
    'permission_callback' => function () {
      return true;
    }
    // 'permission_callback' => 'internal_authenticate'
  ));

  register_rest_route('wc-simpl/v1', '/authenticate_simpl', array(
    'methods' => 'POST',
    'callback' => 'authenticate_simpl',
    'permission_callback' => function () {
      return true;
    }
  ));

  register_rest_route('wc-simpl/v1', '/revert_authenticate_simpl', array(
    'methods' => 'GET',
    'callback' => 'revert_authorization_flag',
    'permission_callback' => function () {
      return true;
    }
  ));

  register_rest_route( 'wc-simpl/v1', '/publish/events', array(
    'methods' => 'POST',
    'callback' => 'publish_events',
    'permission_callback' => function () {
      return true;
    }
  ));  

});
