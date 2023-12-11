<?php
namespace Simpl\Checkout\Services;

include_once SIMPL_ABSPATH . '/includes/clients/simpl_checkout.php';

use Simpl\Checkout\Clients;

class WidgetService {
  private Clients\SimplCheckoutClient $client;

  public function __construct() {
    $this->client = new Clients\SimplCheckoutClient();
  }

  public function get_master_config(string $widget_session_token) {
    return $this->client->get_master_config($widget_session_token);
  }
}