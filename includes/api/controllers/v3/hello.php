<?php

namespace Simpl\Checkout\Api\V3\Controllers;

include_once SIMPL_ABSPATH . '/includes/lib/web/controller.php';
include_once SIMPL_ABSPATH . '/includes/lib/web/request.php';
include_once SIMPL_ABSPATH . '/includes/lib/web/response.php';
include_once SIMPL_ABSPATH . '/includes/lib/web/api_version.php';

use Simpl\Checkout\Lib\Web\Controller;
use Simpl\Checkout\Lib\Web\Request;
use Simpl\Checkout\Lib\Web\Response;
use Simpl\Checkout\Lib\Web\V3_API;

class HelloController implements Controller {
  public function handle(Request $req): Response {
    return Response::success($get->params(), V3_API);
  }
}