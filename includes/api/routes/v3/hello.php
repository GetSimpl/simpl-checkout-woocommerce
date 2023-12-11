<?php
namespace Simpl\Checkout\Api\Routes\V3;

include_once SIMPL_ABSPATH . '/includes/api/middlewares/log.php';
include_once SIMPL_ABSPATH . '/includes/api/controllers/v3/hello.php';
include_once SIMPL_ABSPATH . '/includes/lib/web/router.php';

use Simpl\Checkout\Api\Middlewares;
use Simpl\Checkout\Api\V3\Controllers;
use Simpl\Checkout\Lib\Web\Router;

function init_hello_routes(Router $router, string $namespace) {
  $router->get($namespace, '/hello', new Controllers\HelloController, [Middlewares\log1(), Middlewares\log2()]);
}
