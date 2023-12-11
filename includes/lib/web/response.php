<?php
namespace Simpl\Checkout\Lib\Web;

include 'error.php';

const STATUS_BAD_REQUEST = 400;
const STATUS_INTERNAL_SERVER_ERROR = 500;
const STATUS_UNAUTHORIZED = 401;
const STATUS_SUCCESS = 200;

class Response {
  private array $data;
  private int $status;
  private array $headers;
  
  private function __construct(array $data, int $status, $headers = array()) {
    $this->data = $data;
    $this->status = $status;
    $this->headers = $headers;
  }

  public function to_wp_rest_response() {
    return new \WP_REST_Response(
      $this->data,
      $this->status,
      $this->headers,
    );
  }

  public static function success($data, string $version, $headers = array()) {
    return new Response(
      array(
        "success" => true,
        "data" => $data,
        "version" => $version
      ),
      STATUS_SUCCESS,
      $headers,
    );
  }

  public static function err(string $code, string $message, int $status, string $version) {
    return new Response(
      array(
        "success" => false,
        "error" => array(
          "code" => $code,
          "message" => $message
        ),
        "version" => $version,
      ),
      $status,
    );
  }

  public static function err_bad_request(string $message, string $version) {
    return new Response(
      array(
        "success" => false,
        "error" => array(
          "code" => BAD_REQUEST,
          "message" => $message
        ),
        "version" => $version,
      ),
      STATUS_BAD_REQUEST,
    );
  }

  public static function err_internal_server(string $message, string $version) {
    return new Response(
      array(
        "success" => false,
        "error" => array(
          "code" => INTERNAL_SERVER_ERROR,
          "message" => $message
        ),
        "version" => $version,
      ),
      STATUS_INTERNAL_SERVER_ERROR,
    );
  }

  public static function err_unauthorized(string $message, string $version) {
    return new Response(
      array(
        "success" => false,
        "error" => array(
          "code" => UNAUTHORIZED,
          "message" => $message
        ),
        "version" => $version,
      ),
      STATUS_UNAUTHORIZED,
    );
  }
}