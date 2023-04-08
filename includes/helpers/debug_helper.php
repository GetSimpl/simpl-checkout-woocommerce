<?php

function console_log($data ) {
    echo '<script>';
    echo 'console.log('. json_encode($data) .')';
    echo '</script>';
  }


function simpl_admin_notice__error($message) {
  $class = 'notice notice-error is-dismissible';
  $message = __( 'Error saving settings: '. $message .'!' );

  printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
}

function simpl_admin_notice__success($message = 'Your settings have been saved successfully') {
  ?>
  <div class="notice notice-success is-dismissible">
      <p><?php _e( $message ); ?></p>
  </div>
  <?php
}