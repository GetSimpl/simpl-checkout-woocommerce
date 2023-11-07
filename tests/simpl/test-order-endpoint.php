<?php

use Automattic\WooCommerce\StoreApi\Utilities\OrderController;

include_once PLUGIN_DIR . '/includes/helpers/test_wc_helper.php';

class WC_Payment_Gateways {
    function get_available_payment_gateways() {}
}

class Test_Order_Endpoint extends WP_UnitTestCase{

	protected $server;
    /**
	 * @var WC_Payment_Gateways
	 */
    private $mock_gateways;
    
    /**
	 * @var WC_Simpl_Gateway
	 */
    private $mock_simpl_payment_gateway;

    protected function setUp(): void {
		parent::setUp();
        global $wp_rest_server;
        $this->server = $wp_rest_server = new \WP_REST_Server;
        do_action( 'rest_api_init' );
        $this->mock_simpl_payment_gateway = Mockery::mock( WC_Simpl_Gateway::class );
        $this->mock_gateways = Mockery::mock( WC_Payment_Gateways::class );
	}

    public function test_create_order_bad_request_invalid_checkout_order_id() {
        $request = new WP_REST_Request( 'POST', '/wc-simpl/v1/order' );
        $request["checkout_order_id"] = 12;
        $response = $this->server->dispatch( $request );
        $response_data = $response->get_data();
        
        $this->assertEquals("bad_request", $response_data["code"]);
        $this->assertEquals( "invalid checkout_order_id", $response_data["message"]);
	}

    public function test_create_order_bad_request_missing_param() {
        $data = simpl_test_create_product();  
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

    public function test_create_order_bad_request_simpl_not_enabled() {
        $this->setUp();
        $data = simpl_test_create_product();  
        simpl_cart_init_common();
        simpl_test_create_cart($data);
        $order = SimplWcCartHelper::create_order_from_cart();

        WC()->payment_gateways = $this->mock_gateways;
        $this->mock_gateways->expects('get_available_payment_gateways')->once()->andReturn(array(SIMPL_PAYMENT_GATEWAY => false));
        
        $request = new WP_REST_Request( 'POST', '/wc-simpl/v1/order' );
        $params = array(
            "checkout_order_id" => $order->get_id(),
            "simpl_cart_token" => "123",
            "simpl_payment_id" => "456",
            "simpl_order_id" => "789",
            "simpl_payment_type" => "UPI"
        );
        $request->set_query_params($params);
        $response = $this->server->dispatch( $request );
        $response_data = $response->get_data();

        $this->assertEquals(SIMPL_HTTP_ERROR_BAD_REQUEST, $response_data["code"]);
        $this->assertEquals("simpl payment is not configured", $response_data["message"]);
	}

    public function test_create_order_success() {
        $this->setUp();
        $data = simpl_test_create_product();  
        simpl_cart_init_common();
        simpl_test_create_cart($data);
        $order = SimplWcCartHelper::create_order_from_cart();
        simpl_test_set_default_shipping_method_in_order($order);
    
        WC()->payment_gateways = $this->mock_gateways;
        $this->mock_gateways->expects('get_available_payment_gateways')->once()->andReturn(array(SIMPL_PAYMENT_GATEWAY => $this->mock_simpl_payment_gateway));
        $this->mock_simpl_payment_gateway->expects('process_payment')->with($order->get_id())->andReturn(array("result" => "success", "redirect" => ""));
        
        $request = new WP_REST_Request( 'POST', '/wc-simpl/v1/order' );
        $params = array(
            "checkout_order_id" => $order->get_id(),
            "simpl_cart_token" => "123",
            "simpl_payment_id" => "456",
            "simpl_order_id" => "789",
            "simpl_payment_type" => "UPI"
        );
        $request->set_query_params($params);
        $response = $this->server->dispatch( $request );
        $response_data = $response->get_data();

        $this->assertEquals($order->get_id(), $response_data["id"]);
        $this->assertEquals(SIMPL_ORDER_STATUS_CHECKOUT, $response_data["status"]);
        $this->assert_shipping(simpl_test_get_shipping_method($order), $response_data["shipping_methods"]);
	}

    public function test_create_order_success_with_in_house_shipping() {
        $this->setUp();
        $data = simpl_test_create_product();  
        simpl_cart_init_common();
        simpl_test_create_cart($data);
        $order = SimplWcCartHelper::create_order_from_cart();
        $shipping = array(
            "slug" => "test_id",
            "name" => "TEST SHIPPING",
            "amount" => 10
        );

        WC()->payment_gateways = $this->mock_gateways;
        $this->mock_gateways->expects('get_available_payment_gateways')->once()->andReturn(array(SIMPL_PAYMENT_GATEWAY => $this->mock_simpl_payment_gateway));
        $this->mock_simpl_payment_gateway->expects('process_payment')->with($order->get_id())->andReturn(array("result" => "success", "redirect" => ""));
        
        $request = new WP_REST_Request( 'POST', '/wc-simpl/v1/order' );
        $params = array(
            "checkout_order_id" => $order->get_id(),
            "simpl_cart_token" => "123",
            "simpl_payment_id" => "456",
            "simpl_order_id" => "789",
            "simpl_payment_type" => "UPI"
        );
        $body = array(
            "shipping_method" => $shipping
        );
        $request->set_header( 'content-type', 'application/json' );
        $request->set_query_params($params);
        $request->set_body(json_encode($body));
        $response = $this->server->dispatch( $request );
        $response_data = $response->get_data();

        $this->assertEquals($order->get_id(), $response_data["id"]);
        $this->assertEquals(SIMPL_ORDER_STATUS_CHECKOUT, $response_data["status"]);
        $this->assert_shipping($shipping, $response_data["shipping_methods"]);
	}

    protected function assert_shipping($expected, $actual) {
        $this->assertEquals($expected["slug"], $actual["slug"]);
        $this->assertEquals($expected["name"], $actual["name"]);
        $this->assertEquals($expected["amount"], $actual["amount"]);
    } 
}
?>