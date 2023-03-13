<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Simpl_Checkout_Woocommerce
 */

require dirname( dirname( __FILE__ ) ) . '/vendor/autoload.php';
$test_lib_bootstrap_file = dirname( __FILE__ ) . '/includes/bootstrap.php';

// if ( ! file_exists( $test_lib_bootstrap_file ) ) {
//     echo PHP_EOL . "Error : unable to find " . $test_lib_bootstrap_file . PHP_EOL;
//     exit( '' . PHP_EOL );
// }

// require_once $test_lib_bootstrap_file;


$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/simpl-checkout-woocommerce.php';
}

// tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $test_lib_bootstrap_file;
echo PHP_EOL;
echo 'Using Wordpress core : ' . ABSPATH . PHP_EOL;
echo PHP_EOL;
