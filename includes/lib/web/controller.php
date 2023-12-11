<?php

namespace Simpl\Checkout\Lib\Web;

include_once 'request.php';
include_once 'response.php';

interface Controller {
  public function handle(Request $req): Response;
}