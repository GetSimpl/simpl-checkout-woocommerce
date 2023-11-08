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
        
        $this->assertEquals($response_data["code"], "bad_request");
        $this->assertEquals($response_data["message"], "invalid checkout_order_id");
	}

    public function test_fetch_checkout_with_valid_checkout_order_id() {
        $data = simpl_test_create_product();  
        simpl_cart_init_common();
        WC()->cart->add_to_cart($data['product_id'], 1, $data['variant_id']);
        $order = SimplWcCartHelper::create_order_from_cart();
        
        $request = new WP_REST_Request( 'GET', '/wc-simpl/v1/checkout' );
        $request["checkout_order_id"] = $order->get_id();
        $request["merchant_additional_details"] = array(""=> "");
        $response = $this->server->dispatch( $request );
        $response_data = $response->get_data();

        $this->assertEquals($response_data["source"], "cart");
        $this->assertEquals($response_data["cart"]["total_price"], '10.00');
        $this->assertEquals($response_data["cart"]["item_subtotal_price"], '10.00');
        $this->assertEquals($response_data["cart"]["checkout_order_id"], $order->get_id());
	}

    public function test_create_checkout_with_valid_items_payload() {
        $data = simpl_test_create_product();    
        simpl_cart_init_common();
        WC()->cart->add_to_cart($data['product_id'], 1, $data['variant_id']);

        $request = new WP_REST_Request( 'POST', '/wc-simpl/v1/checkout' );
        $request["items"] = array(array("product_id" => $data['product_id'], "variant_id" => $data['variant_id'], "quantity" => 1, "attributes" => array()));
        $request["shipping_address"] = array("city"=> "chennai", "country" => "india", "line1" => "123", "line2" => "456", "state" => "Delhi");
        $request["billing_address"] = array("email" => "test@gmail.com", "city"=> "chennai", "country" => "india", "line1" => "123", "line2" => "456", "state" => "Delhi");
        $request["merchant_additional_details"] = array(""=> "");
        $response = $this->server->dispatch( $request );
        $response_data = $response->get_data();

        $this->assertEquals($response_data["source"], "cart");
        $this->assertEquals($response_data["cart"]["total_price"], '10.00');
        $this->assertEquals($response_data["cart"]["shipping_address"], array("city"=> "chennai", "country" => "India", "state" => "Delhi"));
        $this->assertEquals($response_data["cart"]["billing_address"], array("email" => "test@gmail.com", "city"=> "chennai", "country" => "India", "state" => "Delhi"));
        $this->assertEquals($response_data["cart"]["item_subtotal_price"], '10.00');
        $this->assertNull($response_data["cart"]["items"][0]["attributes"]);
        $this->assertNotNull($response_data["cart"]["checkout_order_id"]);
	}

    public function test_create_checkout_with_valid_item_attributes() {
        $data = simpl_test_create_product();      
        simpl_cart_init_common();
        WC()->cart->add_to_cart($data['product_id'], 1, $data['variant_id']);

        $request = new WP_REST_Request( 'POST', '/wc-simpl/v1/checkout' );
        $request["items"] = array(array("product_id" => $data['product_id'], "variant_id" => $data['variant_id'], "quantity" => 1, "attributes" => $data['variation']));
        $request["shipping_address"] = array("city"=> "chennai", "country" => "india", "line1" => "123", "line2" => "456", "state" => "Delhi");
        $request["billing_address"] = array("email" => "test@gmail.com", "city"=> "chennai", "country" => "india", "line1" => "123", "line2" => "456", "state" => "Delhi");
        $request["merchant_additional_details"] = array(""=> "");
        $response = $this->server->dispatch( $request );
        $response_data = $response->get_data();

        $this->assertEquals($response_data["source"], "cart");
        $this->assertEquals($response_data["cart"]["total_price"], '10.00');
        $this->assertEquals($response_data["cart"]["shipping_address"], array("city"=> "chennai", "country" => "India", "state" => "Delhi"));
        $this->assertEquals($response_data["cart"]["billing_address"], array("email" => "test@gmail.com", "city"=> "chennai", "country" => "India", "state" => "Delhi"));
        $this->assertEquals($response_data["cart"]["item_subtotal_price"], '10.00');
        $this->assertEquals($response_data["cart"]["items"][0]["attributes"], $data["variation"]);
        $this->assertNotNull($response_data["cart"]["checkout_order_id"]);
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
        $request["items"] = array(array("product_id" => 1121, "variant_id" => 112, "quantity" => 1, "attributes" => array(), "item_data" => array()));
        $response = $this->server->dispatch( $request );        
        $response_data = $response->get_data();

        $this->assertEquals($response_data["code"], "bad_request");
        $this->assertEquals($response_data["message"], "invalid cart items");
	}

    
}
?>