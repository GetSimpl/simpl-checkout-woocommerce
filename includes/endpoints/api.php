<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly      
add_action('rest_api_init', function () {
  register_rest_route('simpl/v1', '/cart', array(
    'methods' => 'POST',
    'callback' => array(new Simpl_Checkout_Cart_Controller, 'create'),
    'permission_callback' => function () {
      return true;
    }
  ));  

  register_rest_route('wc-simpl/v1', '/checkout', array(
    'methods' => 'POST',
    'callback' => array(new Simpl_Checkout_Controller, 'create'),
    'permission_callback' => 'simpl_internal_authenticate'
  ));

  register_rest_route('simpl/v1', '/events', array(
    'methods' => 'POST',
    'callback' => array(new Simpl_Events_Controller, 'publish_events'),
    'permission_callback' => function () {
      return true;
    }
  ));

  register_rest_route('wc-simpl/v1', '/checkout', array(
    'methods' => 'PUT',
    'callback' => array(new Simpl_Checkout_Controller, 'update'),
    'permission_callback' => 'simpl_internal_authenticate'
  ));

  register_rest_route('wc-simpl/v1', '/checkout', array(
    'methods' => 'GET',
    'callback' => array(new Simpl_Checkout_Controller, 'fetch'),
    'permission_callback' => 'simpl_internal_authenticate'
  ));

  register_rest_route('wc-simpl/v1', '/checkout/coupon', array(
    'methods' => 'POST',
    'callback' => array(new Simpl_Checkout_Coupon_Controller, 'apply'),
    'permission_callback' => 'simpl_internal_authenticate'
  ));

  register_rest_route('wc-simpl/v1', '/checkout/coupon', array(
    'methods' => 'DELETE',
    'callback' => array(new Simpl_Checkout_Coupon_Controller, 'remove'),
    'permission_callback' => 'simpl_internal_authenticate'
  ));

  register_rest_route('wc-simpl/v1', '/checkout/coupons', array(
    'methods' => 'DELETE',
    'callback' => array(new Simpl_Checkout_Coupon_Controller, 'remove_all'),
    'permission_callback' => 'simpl_internal_authenticate'
  ));

  register_rest_route('wc-simpl/v1', '/checkout/shipping-method', array(
    'methods' => 'POST',
    'callback' => array(new Simpl_Checkout_Shipping_Controller, 'set_shipping_method'),
    'permission_callback' => 'simpl_internal_authenticate'
  ));

  register_rest_route('wc-simpl/v1', '/order', array(
    'methods' => 'GET',
    'callback' => array(new Simpl_Checkout_Order_Controller, 'fetch'),
    'permission_callback' => 'simpl_internal_authenticate'
  ));

  register_rest_route('wc-simpl/v1', '/order', array(
    'methods' => 'POST',
    'callback' => array(new Simpl_Checkout_Order_Controller, 'create'),
    'permission_callback' => 'simpl_internal_authenticate'
  ));

  register_rest_route('wc-simpl/v1', '/authenticate_simpl', array(
    'methods' => 'POST',
    'callback' => 'simpl_authenticate',
    'permission_callback' => function () {
      return true;
    }
  ));

  register_rest_route('wc-simpl/v1', '/revert_authenticate_simpl', array(
    'methods' => 'GET',
    'callback' => 'simpl_revert_authorization_flag',
    'permission_callback' => function () {
      return true;
    }
  ));
});