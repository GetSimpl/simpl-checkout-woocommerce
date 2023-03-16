<?php

include_once PLUGIN_DIR . '/includes/helpers/test_wc_helper.php';

class Test_Checkout_Endpoint extends WP_UnitTestCase{

	protected $server;

    protected function setUp(): void {
		parent::setUp();
        global $wp_rest_server;
        $this->server = $wp_rest_server = new \WP_REST_Server;
        do_action( 'rest_api_init' );
	}

    public function test_fetch_checkout_with_invalid_checkout_order_id() {
        $request = new WP_REST_Request( 'GET', '/wc-simpl/v1/checkout' );
        $request["checkout_order_id"] = 12;
        $response = $this->server->dispatch( $request );
        $response_data = $response->get_data();
        var_dump($response_data["code"]);
        $this->assertEquals($response_data["code"], "not_found");
        $this->assertEquals($response_data["message"], "invalid checkout_order_id");
	}

    public function test_fetch_checkout_with_valid_checkout_order_id() {
        $data = create_product();        
        initCartCommon();
        WC()->cart->add_to_cart($data['product_id'], 1, $data['variant_id']);
        $order = create_order_from_cart();
        $request = new WP_REST_Request( 'GET', '/wc-simpl/v1/checkout' );
        $request["checkout_order_id"] = $order->get_id();
        $response = $this->server->dispatch( $request );
        $response_data = $response->get_data();
        $this->assertEquals($response_data["source"], "cart");
        $this->assertEquals($response_data["cart"]["total_price"], 100000000);
        $this->assertEquals($response_data["cart"]["item_subtotal_price"], 100000000);
        $this->assertEquals($response_data["cart"]["checkout_order_id"], $order->get_id());

	}
}
?>