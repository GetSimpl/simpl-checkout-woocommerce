<?php

include_once PLUGIN_DIR . '/includes/helpers/test_wc_helper.php';

class Test_Order_Endpoint extends WP_UnitTestCase{

	protected $server;

    protected function setUp(): void {
        print_r("HHEHEHEHEHE");
		parent::setUp();
        global $wp_rest_server;
        $this->server = $wp_rest_server = new \WP_REST_Server;
        do_action( 'rest_api_init' );
        // do_action("simpl_init_gateway_class");
	}

    public function test_create_order_with_invalid_checkout_order_id() {
        $request = new WP_REST_Request( 'POST', '/wc-simpl/v1/order' );
        $request["checkout_order_id"] = 12;
        $response = $this->server->dispatch( $request );
        $response_data = $response->get_data();
        $this->assertEquals("bad_request", $response_data["code"]);
        $this->assertEquals( "invalid checkout_order_id", $response_data["message"]);
	}

    public function test_create_order_with_missing_param() {
        $data = create_product();  
        simpl_cart_init_common();
        WC()->cart->add_to_cart($data['product_id'], 1, $data['variant_id']);
        $order = SimplWcCartHelper::create_order_from_cart();
        $request = new WP_REST_Request( 'POST', '/wc-simpl/v1/order' );
        $params = array(
            "checkout_order_id" => $order->get_id(),
            "simpl_cart_token" => ""
        );
        $request->set_query_params($params);
        $response = $this->server->dispatch( $request );
        $response_data = $response->get_data();
        $this->assertEquals("bad_request", $response_data["code"]);
        $this->assertEquals("simpl_cart_token is mandatory", $response_data["message"]);
	}

    public function test_create_order_with_utm_info_and_in_house_shipping() {
        print_r(WC()->payment_gateways());
        $data = create_product();  
        simpl_cart_init_common();
        WC()->cart->add_to_cart($data['product_id'], 1, $data['variant_id']);
        $order = SimplWcCartHelper::create_order_from_cart();
        $request = new WP_REST_Request( 'POST', '/wc-simpl/v1/order' );
        $params = array(
            "checkout_order_id" => $order->get_id(),
            "simpl_cart_token" => "123",
            "simpl_payment_id" => "456",
            "simpl_order_id" => "789",
            "simpl_payment_type" => "UPI"
        );
        $body = `{
            "utm_info": {
                "key": "value"
            },
            "shipping_method": {
                "slug": "test_id",
                "name": "TEST SHIPPING",
                "amount": "10"
            }
        }`;
        $request->set_query_params($params);
        $request->set_body($body);
        $response = $this->server->dispatch( $request );
        $response_data = $response->get_data();
        print_r($response_data);
        $this->assertEquals("bad_request", $response_data["code"]);
        $this->assertEquals("simpl_cart_token is mandatory", $response_data["message"]);
	}

    public function test_create_checkout_with_no_items_payload() {
        $request = new WP_REST_Request( 'POST', '/wc-simpl/v1/checkout' );
        $response = $this->server->dispatch( $request );
        $response_data = $response->get_data();
        $this->assertEquals($response_data["code"], "bad_request");
        $this->assertEquals($response_data["message"], "items cannot be empty");
	}

    public function test_create_checkout_with_invalid_items_payload() {
        $request = new WP_REST_Request( 'POST', '/wc-simpl/v1/checkout' );
        $request["items"] = array(array("product_id" => 121, "variant_id" => 112, "quantity" => 1));
        $response = $this->server->dispatch( $request );        
        $response_data = $response->get_data();
        $this->assertEquals($response_data["code"], "bad_request");
        $this->assertEquals($response_data["message"], "invalid cart items");
	}

    
}
?>