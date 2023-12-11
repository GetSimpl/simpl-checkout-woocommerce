<?php
namespace Simpl\Checkout\Clients;

include_once SIMPL_ABSPATH . '/includes/lib/http/http.php';

use Simpl\Checkout\Lib\Http\HttpClient;

const SIMPL_SESSION_HEADER_KEY = 'simpl-widget-session-token';

const CREATE_CART_API_PATH = "api/v1/wc/cart";
const GET_MASTER_CONFIG_PATH = "api/v1/wc/widget/master-config";

class SimplCheckoutClient {
  private string $client_id;
  private string $base_url;
  private string $store_url;
  private HttpClient $client;

  public function __construct() {
    $client_credentials = \WC_Simpl_Settings::merchant_credentials();
    $this->client_id = $client_credentials["client_id"];
    $this->store_url = \WC_Simpl_Settings::store_url();
    $this->base_url = \WC_Simpl_Settings::simpl_host();

    $this->client = new HttpClient();
  }

  public function create_cart(array $body) {
    $request_url = sprintf('https://%s/%s', $this->base_url, CREATE_CART_API_PATH);
    
    $resp = $this->client->post($request_url, array(
      "shop-domain" => $this->store_url,
    ), $body);

    if (!$resp->is_success()) {
      throw new \Exception($resp->error());
    }

    $resp_body = $resp->body();
    if ($resp_body->success) {
      return array(
        "redirection_url" => $resp_body->redirection_url,
      );
    }

    throw new \Exception($resp_body->error->message);
  }

  public function get_master_config(string $widget_session_token) {
    $request_url = sprintf('https://%s/%s?shop=%s', $this->base_url, GET_MASTER_CONFIG_PATH, $this->store_url);

    $resp = $this->client->get($request_url, array(
      SIMPL_SESSION_HEADER_KEY => $widget_session_token,
    ));

    if (!$resp->is_success()) {
      throw new \Exception($resp->error());
    }

    $resp_body = $resp->body();
    if ($resp_body->success) {
      $widget_session_token = $resp->headers()[SIMPL_SESSION_HEADER_KEY];

      return array(
        "widget_session_token" => $widget_session_token,
        "data" => $resp_body->data,
      );
    }

    throw new \Exception($resp_body->error->message);
  }
}