<?php
namespace Simpl\Checkout\Api\Routes\V3;

include_once SIMPL_ABSPATH . '/includes/api/middlewares/session.php';
include_once SIMPL_ABSPATH . '/includes/api/middlewares/load_wc.php';
include_once SIMPL_ABSPATH . '/includes/api/controllers/v3/apply_coupon.php';
include_once SIMPL_ABSPATH . '/includes/api/controllers/v3/remove_coupons.php';
include_once SIMPL_ABSPATH . '/includes/lib/web/router.php';

use Simpl\Checkout\Api\Middlewares;
use Simpl\Checkout\Api\V3\Controllers;
use Simpl\Checkout\Lib\Web\Router;

function init_coupon_routes(Router $router, string $namespace) {
  $router->post($namespace, '/checkout/coupon', new Controllers\ApplyCouponController, [Middlewares\load_wc_core(), Middlewares\init_wc_session()]);

  $router->delete($namespace, '/checkout/coupons', new Controllers\RemoveCouponsController, [Middlewares\load_wc_core(), Middlewares\init_wc_session()]);
}
