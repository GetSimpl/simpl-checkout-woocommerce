<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly      
function scwp_console_log( $data ) {
  if (!wp_script_is( 'simpl-debug-helper', 'enqueued' )) {
    wp_register_script( 'simpl-debug-helper', '' );
		wp_enqueue_script( 'simpl-debug-helper' );
  }

  wp_add_inline_script('simpl-debug-helper', 'console.log('. json_encode($data) .');', false);
}
