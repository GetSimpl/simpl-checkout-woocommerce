<?php
    add_action( 'woocommerce_order_refunded', 'order_hook', 10, 2 );
    add_action('add_hook_status_table', 'add_hook_status_table_into_db');

    function add_hook_status_table_into_db(){
        global $wpdb;
      
        // set the default character set and collation for the table
        $charset_collate = $wpdb->get_charset_collate();
      
        // Check that the table does not already exist before continuing
        $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}hook_status` (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            order_id int(20),
            refund_id int(20),
            topic varchar(20),
            resource varchar(20),
            event varchar(20),
            hook varchar(40),
            data text,
            status varchar(20),
            retry_count int(10) default 0,
            created_at datetime,
            updated_at datetime,
            PRIMARY KEY (id),
            INDEX order_refund_topic_hook_idx (order_id, refund_id, topic, hook)
        ) $charset_collate;";
      
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
        $is_error = empty( $wpdb->last_error );
      
        return $is_error;
      }