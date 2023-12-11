<?php
namespace Simpl\Checkout\Api\Routes\V3;

include_once 'hello.php';
include_once 'cart.php';
include_once SIMPL_ABSPATH . '/includes/lib/web/router.php';

use Simpl\Checkout\Lib\Web\Router;

const ROUTE_NAMESPACE = "simpl/v3";

function init_v3_routes(Router $router) {
  init_hello_routes($router, ROUTE_NAMESPACE);
  init_cart_routes($router, ROUTE_NAMESPACE);
}