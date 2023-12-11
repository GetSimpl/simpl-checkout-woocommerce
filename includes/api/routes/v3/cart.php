<?php
namespace Simpl\Checkout\Api\Routes\V3;

include_once SIMPL_ABSPATH . '/includes/api/controllers/v3/create_cart.php';
include_once SIMPL_ABSPATH . '/includes/lib/web/router.php';

use Simpl\Checkout\Api\V3\Controllers;
use Simpl\Checkout\Lib\Web\Router;

function init_cart_routes(Router $router, string $namespace) {
  $router->post($namespace, '/cart', new Controllers\CreateCartController);
}
