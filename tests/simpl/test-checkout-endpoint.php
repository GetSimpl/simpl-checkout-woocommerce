<?php
class Test_Checkout_Endpoint extends WP_UnitTestCase{

	protected $server;

	protected $namespaced_route = 'wc-simpl';


    protected function setUp(): void {
		parent::setUp();
        global $wp_rest_server;
		$this->server = $wp_rest_server = new \WP_REST_Server;
		do_action( 'rest_api_init' );
	}

    public function test_cart() {
        $request = new WP_REST_Request( 'GET', '/wc-simpl/v1/checkout' );
        $response = $this->server->dispatch( $request );
        var_dump($response);
        $this->assertTrue( 200, $response );
	}
}
?>