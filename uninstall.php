<?php
/**
 * Uninstall Pre-Order Ultra
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Remove scheduled event (if still scheduled)
$timestamp = wp_next_scheduled('pre_order_ultra_send_notifications');
if ($timestamp) {
    wp_unschedule_event($timestamp, 'pre_order_ultra_send_notifications');
}

// Clean up options, custom tables, or other persistent data
global $wpdb;

// Remove custom table
$table_name = $wpdb->prefix . 'pre_order_subscriptions';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Delete plugin options
delete_option( 'pre_order_ultra_settings' );