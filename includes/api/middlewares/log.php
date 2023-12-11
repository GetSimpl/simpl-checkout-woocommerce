<?php
namespace Simpl\Checkout\Api\Middlewares;

function log1() {
  return function(\WP_REST_Request $request) {
    error_log("aahahahahahah");
  };
}

function log2() {
  return function(\WP_REST_Request $request) {
    error_log("alalalalallaal");
  };
}
