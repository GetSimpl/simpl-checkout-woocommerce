<?php
namespace Simpl\Checkout\Lib\Web;

include_once 'controller.php';
include_once 'response.php';
include_once 'request.php';

use Simpl\Checkout\Lib\Web\Controller;
use Simpl\Checkout\Lib\Web\Response;
use Simpl\Checkout\Lib\Web\Request;

class Handler {
  var $middlewares;
  var $callback = array();

  public function __construct(Controller $callback, array $middlewares) {
    $this->callback = $callback;
    $this->middlewares = $middlewares;
  }

  public function serve(\WP_REST_Request $request): \WP_REST_Response {
    foreach($this->middlewares as $middleware) {
      $middleware($request);
    }

    $req = new Request($request);
    return $this->callback->handle($req)->to_wp_rest_response();
  }
}