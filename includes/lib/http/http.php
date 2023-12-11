<?php
namespace Simpl\Checkout\Lib\http;

include_once 'response.php';

class HttpClient {
  public function get(string $path, array $headers, array $params = array()) {
    $headers = $this->add_default_headers($headers);
    $resp = wp_remote_get($path,
      array(
        "headers" => $headers,
        // "body" => json_encode($params),
      )
    );

    return new Response($resp);
  }

  public function post(string $path, array $headers, $body) {
    $headers = $this->add_default_headers($headers);
    $resp = wp_remote_post($path,
      array(
        "headers" => $headers,
        "body" => json_encode($body),
      )
    );

    return new Response($resp);
  }

  private function add_default_headers(array $headers) {
    if (!isset($headers['content-type'])) {
      $headers['content-type'] = 'application/json';
    }

    return $headers;
  }
}