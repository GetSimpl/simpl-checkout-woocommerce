<?php
namespace Simpl\Checkout\Lib\Web;

include_once 'router.php';
use Simpl\Checkout\Lib\Web\Router;

class Server {
  private $router = null;

  public function __construct(Router $router) {
    $this->router = $router;
  }

  public function start() {
    $this->router->init();
  }
}