<?php
namespace Simpl\Checkout\Lib\Web;

const HEADER_USER_AGENT = "User-Agent";
const HEADER_PLATFORM = "Platform";
const HEADER_ORIGIN = "Origin";
const HEADER_SIMPL_WIDGET_SESSION = "simpl-widget-session-token";

class Request {
  private \WP_REST_Request $req;

  public function __construct(\WP_REST_Request $req) {
    $this->req = $req;
  }

  public function params() {
    return $this->req->get_params();
  }

  public function param(string $key) {
    return $this->req->get_param($key);
  }

  public function header(string $key) {
    return $this->req->get_header($key);
  }

  public function headers() {
    return $this->req->get_headers();
  }

  public function user_agent() {
    return $this->header(HEADER_USER_AGENT);
  }

  public function platform() {
    return $this->header(HEADER_PLATFORM);
  }

  public function origin() {
    return $this->header(HEADER_ORIGIN);
  }

  public function client_information() {
    return array(
      "user_agent" => $this->user_agent(),
      "platform" => $this->platform(),
      "origin" => $this->origin(),
    );
  }

  public function unique_id() {
    return $this->header(HEADER_SIMPL_WIDGET_SESSION);
  }
}