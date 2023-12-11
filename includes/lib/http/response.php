<?php
namespace Simpl\Checkout\Lib\Http;

class Response {
  private $resp;

  public function __construct($resp) {
    $this->resp = $resp;
  }

  public function body() {
    return json_decode(wp_remote_retrieve_body($this->resp));
  }

  public function is_success() {
    return !is_wp_error($this->resp);
  }

  public function headers() {
    return wp_remote_retrieve_headers($this->resp);
  }

  public function status() {
    return wp_remote_retrieve_response_code($this->resp);
  }

  public function error() {
    return $this->resp->get_error_message();
  }
}