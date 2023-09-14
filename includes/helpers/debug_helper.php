<?php

function console_log($data ) {
    echo '<script>';
    echo esc_html('console.log('. json_encode($data) .')');
    echo '</script>';
  }
