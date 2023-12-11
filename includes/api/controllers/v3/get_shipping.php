<?php
namespace Simpl\Checkout\Api\V3\Controllers;

include_once SIMPL_ABSPATH . '/includes/services/shipping.php';
include_once SIMPL_ABSPATH . '/includes/lib/web/controller.php';
include_once SIMPL_ABSPATH . '/includes/lib/web/response.php';
include_once SIMPL_ABSPATH . '/includes/lib/web/request.php';
include_once SIMPL_ABSPATH . '/includes/lib/web/error.php';
include_once SIMPL_ABSPATH . '/includes/lib/web/api_version.php';

use Simpl\Checkout\Services;
use Simpl\Checkout\Lib\Web\Controller;
use Simpl\Checkout\Lib\Web\Response;
use Simpl\Checkout\Lib\Web\Request;
use Simpl\Checkout\Lib\Web\V3_API;
use Simpl\Checkout\Lib\Web;

class GetShippingController implements Controller {
  private $service;
  
  public function __construct() {
    $this->service = new Services\ShippingService();
  }

  public function handle(Request $req): Response {
    try {
      $resp = $this->service->get_shipping();
      return Response::success($resp, V3_API);
    } catch (\Exception $e) {
      return Response::err_internal_server($e->getMessage(), V3_API);
    }
  }
}