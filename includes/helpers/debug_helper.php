<?php

function scwp_console_log( $data ) {
  if (!wp_script_is( 'simpl-debug-helper', 'enqueued' )) {
    wp_enqueue_script( 'simpl-debug-helper' );
  }
  wp_add_inline_script('simpl-debug-helper', 'console.log('. json_encode($data) .');');
}
