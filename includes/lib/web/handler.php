<?php
namespace Simpl\Checkout\Lib\Web;

include_once 'controller.php';
use Simpl\Checkout\Lib\Web\Controller;

class Handler {
  var $middlewares;
  var $callback = array();

  public function __construct(Controller $callback, array $middlewares) {
    $this->callback = $callback;
    $this->middlewares = $middlewares;
  }

  public function serve(\WP_REST_Request $request) {
    foreach($this->middlewares as $middleware) {
      $middleware($request);
    }
    return $this->callback->handle($request);
  }
}