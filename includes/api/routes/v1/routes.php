<?php
namespace Simpl\Checkout\Api\Routes\V1;

include_once 'widget.php';
include_once SIMPL_ABSPATH . '/includes/lib/web/router.php';

use Simpl\Checkout\Lib\Web\Router;

const ROUTE_NAMESPACE = "simpl/v1";

function init_v1_routes(Router $router) {
  init_widget_routes($router, ROUTE_NAMESPACE);
}