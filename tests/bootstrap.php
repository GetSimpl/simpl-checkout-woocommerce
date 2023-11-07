<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Simpl_Checkout_Woocommerce
 */

$test_lib_bootstrap_file = dirname( __FILE__ ) . '/includes/bootstrap.php';
if ( ! file_exists( $test_lib_bootstrap_file ) ) {
    echo PHP_EOL . "Error : unable to find " . $test_lib_bootstrap_file . PHP_EOL;
    exit( '' . PHP_EOL );
}

// set plugin and options for activation
$GLOBALS[ 'wp_tests_options' ] = array(
    'active_plugins' => array(
        'woocommerce/woocommerce.php',
        '/var/www/html/wp-content/plugins/woocommerce/vendor/autoload.php',
        '/woocommerce/packages/woocommerce-blocks/src/StoreApi/Utilities/OrderController.php',
        basename(realpath(dirname(__FILE__) . '/../')) . '/simpl-checkout-woocommerce.php'
    ),
    'wpsp_test' => true
);


// if ( ! file_exists( $test_lib_bootstrap_file ) ) {
//     echo PHP_EOL . "Error : unable to find " . $test_lib_bootstrap_file . PHP_EOL;
//     exit( '' . PHP_EOL );
// }

// Start up the WP testing environment.
require $test_lib_bootstrap_file;
require PLUGIN_DIR . '/vendor/autoload.php';

echo PHP_EOL;
echo 'Using Wordpress core : ' . ABSPATH . PHP_EOL;
echo PHP_EOL;
