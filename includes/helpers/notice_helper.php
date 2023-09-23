<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly   

    function simpl_add_notice($message, $notice_type="success", $data = array()) {
        $notice_message = array("type"=> $notice_type, "message"=>$message);
        $_SESSION["simpl_session_message"]=$notice_message;
    }
    
    function simpl_error_messages() {
        $notice_message = sanitize_term($_SESSION["simpl_session_message"], 'category');
        if($notice_message["type"] == "error") {
            return new WP_Error("user_error", $notice_message["message"]);
        }
    }
