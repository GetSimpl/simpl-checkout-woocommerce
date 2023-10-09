<?php
    add_action( 'woocommerce_order_refunded', 'order_hook', 10, 2 );
    add_action('add_hook_status_table', 'run_hook_status_table_migrations');

    function is_valid_version($version_to_run) {
      $current_hook_table_version = get_option( "hook_table_version" );

      if ($current_hook_table_version == null || $current_hook_table_version <= $version_to_run ) {
        return true;
      }

      return false;
    }

    function run_hook_status_table_migrations() {
      // To incrementally update the table, DO NOT change the sequence of these functions 
      // and add subsequent db changes as the last statement of this function
      // Note: We do not need to create migrations for addition of new columns since dbDelta takes care of that
      $err = run_migration_and_update_version(v1_add_hook_status_table_into_db(), 1);
      if ($err != null) {
        throw new Exception("error while running migration");
      }
    }

    function run_migration_and_update_version($query, $version) {
      global $wpdb;

      if (is_valid_version($version)) {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $query );
        
        $err =  $wpdb->last_error;
        if (empty($err)) {
          update_option( "{$wpdb->base_prefix}hook_status_version", $version );
          return null;
        }
      
        return $wpdb -> last_error;
      }
    }

    function v1_add_hook_status_table_into_db(){
      global $wpdb;
      // Check that the table does not already exist before continuing
      $sql = "CREATE TABLE `{$wpdb->base_prefix}hook_status` (
          id bigint(20) NOT NULL AUTO_INCREMENT,
          order_id int(20),
          refund_id int(20),
          topic varchar(20),
          resource varchar(20),
          event varchar(20),
          hook varchar(40),
          data text,
          status varchar(50),
          retry_count int(10) default 0,
          created_at datetime,
          updated_at datetime,
          PRIMARY KEY  (id),
          INDEX order_refund_topic_hook_idx (order_id, refund_id, topic, hook)
      ) $charset_collate;";

      return $sql;
    }