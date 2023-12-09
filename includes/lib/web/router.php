<?php
namespace Simpl\Checkout\Lib\Web;

include_once 'handler.php';
include_once 'controller.php';

use Simpl\Checkout\Lib\Web;
use Simpl\Checkout\Lib\Web\Controller;

class Router {
  var $routes = array();

  public function get(string $namespace, string $endpoint, Controller $handler, array $middlewares = array()) {
    $this->add_route("GET", $namespace, $endpoint, $handler, $middlewares);
  }

  public function post(string $namespace, string $endpoint, Controller $handler, array $middlewares = array()) {
    $this->add_route("POST", $namespace, $endpoint, $handler, $middlewares);
  }

  public function put(string $namespace, string $endpoint, Controller $handler, array $middlewares = array()) {
    $this->add_route("PUT", $namespace, $endpoint, $handler, $middlewares);
  }

  public function delete(string $namespace, string $endpoint, Controller $handler, array $middlewares = array()) {
    $this->add_route("DELETE", $namespace, $endpoint, $handler, $middlewares);
  }

  public function init() {
    add_action('rest_api_init', function() {
      foreach($this->routes as $routes) {
        foreach($routes as $route) {
          register_rest_route($route['namespace'], $route['path'], array(
            'method' => $route['method'],
            'callback' => [new Handler($route['callback'], $route['middlewares']), 'serve'],
            'permission_callback' => function() {
              return true;
            }
          ));
        }
      }
    });
  }

  private function add_route(string $method, string $namespace, string $endpoint, Controller $handler, array $middlewares) {
    if (!array_key_exists($namespace, $this->routes)) {
      $this->routes[$namespace] = array();
    }

    array_push($this->routes[$namespace], array(
      "namespace" => $namespace,
      "path" => $endpoint,
      "callback" => $handler,
      "method" => $method,
      "middlewares" => $middlewares
    ));
  }
}
