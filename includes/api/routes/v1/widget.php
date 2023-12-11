<?php
namespace Simpl\Checkout\Api\Routes\V1;

include_once SIMPL_ABSPATH . '/includes/api/controllers/v1/get_master_config.php';
include_once SIMPL_ABSPATH . '/includes/lib/web/router.php';

use Simpl\Checkout\Api\V1\Controllers;
use Simpl\Checkout\Lib\Web\Router;

function init_widget_routes(Router $router, string $namespace) {
  $router->get($namespace, '/widget/master-config', new Controllers\GetMasterConfigController);
}
