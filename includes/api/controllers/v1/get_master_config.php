<?php
namespace Simpl\Checkout\Api\V1\Controllers;

include_once SIMPL_ABSPATH . '/includes/services/widget.php';
include_once SIMPL_ABSPATH . '/includes/lib/web/controller.php';
include_once SIMPL_ABSPATH . '/includes/lib/web/response.php';
include_once SIMPL_ABSPATH . '/includes/lib/web/request.php';
include_once SIMPL_ABSPATH . '/includes/lib/web/error.php';
include_once SIMPL_ABSPATH . '/includes/lib/web/api_version.php';

use Simpl\Checkout\Services;
use Simpl\Checkout\Lib\Web\Controller;
use Simpl\Checkout\Lib\Web\Response;
use Simpl\Checkout\Lib\Web\Request;
use Simpl\Checkout\Lib\Web\V1_API;
use Simpl\Checkout\Lib\Web;

const SIMPL_SESSION_HEADER_KEY = 'simpl-widget-session-token';

class GetMasterConfigController implements Controller {
  private $service;

  public function __construct() {
    $this->service = new Services\WidgetService();
  }

  public function handle(Request $req): Response {
    $widget_session_token = $req->header(SIMPL_SESSION_HEADER_KEY) ?? '';

    try {
      $config = $this->service->get_master_config($widget_session_token);
      return Response::success($config["data"], V1_API);
    } catch(\Exception $e) {
      return Response::err_internal_server($e->getMessage(), V1_API);
    }
  }
}