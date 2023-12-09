<?php

namespace Simpl\Checkout\Lib\Web;

interface Controller {
  public function handle(\WP_REST_Request $request);
}