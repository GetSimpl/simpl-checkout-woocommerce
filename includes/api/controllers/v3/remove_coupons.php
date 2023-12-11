<?php
namespace Simpl\Checkout\Api\V3\Controllers;

include_once SIMPL_ABSPATH . '/includes/services/coupon.php';
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

class RemoveCouponsController implements Controller {
  private $service;
  
  public function __construct() {
    $this->service = new Services\CouponService();
  }

  public function handle(Request $req): Response {
    try {
      $resp = $this->service->remove_all();
      return Response::success($resp, V3_API);
    } catch (\Exception $e) {
      return Response::err_internal_server($e->getMessage(), V3_API);
    }
  }
}