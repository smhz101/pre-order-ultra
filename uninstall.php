<?php
/**
 * Uninstall Pre-Order Ultra
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Example: Delete plugin options
delete_option( 'pre_order_ultra_settings' );

// Add more cleanup tasks as needed
