<?php
namespace Simpl\Checkout\Api\Routes\V3;

include_once SIMPL_ABSPATH . '/includes/api/middlewares/session.php';
include_once SIMPL_ABSPATH . '/includes/api/middlewares/load_wc.php';
include_once SIMPL_ABSPATH . '/includes/api/controllers/v3/get_shipping.php';
include_once SIMPL_ABSPATH . '/includes/api/controllers/v3/apply_shipping.php';
include_once SIMPL_ABSPATH . '/includes/lib/web/router.php';

use Simpl\Checkout\Api\Middlewares;
use Simpl\Checkout\Api\V3\Controllers;
use Simpl\Checkout\Lib\Web\Router;

function init_shipping_routes(Router $router, string $namespace) {
  $router->get($namespace, '/shipping', new Controllers\GetShippingController, [Middlewares\load_wc_core(), Middlewares\init_wc_session()]);

  $router->post($namespace, '/checkout/shipping', new Controllers\ApplyShippingController, [Middlewares\load_wc_core(), Middlewares\init_wc_session()]);
}
