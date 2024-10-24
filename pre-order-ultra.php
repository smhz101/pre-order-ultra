<?php
/**
 * Plugin Name: Pre-Order Ultra
 * Plugin URI: https://wpthemepress.com/plugins/pre-order-ultra
 * Description: Adds pre-order functionality to your WooCommerce store.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://muzammil.dev
 * Text Domain: pre-order-ultra
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Pre_Order_Ultra' ) ) :

final class Pre_Order_Ultra {

    /**
     * Plugin Instance
     *
     * @var Pre_Order_Ultra
     */
    protected static $instance = null;

    /**
     * Constructor
     */
    private function __construct() {
        // Register activation and deactivation hooks
        register_activation_hook( __FILE__, array( $this, 'on_activation' ) );
        register_deactivation_hook( __FILE__, array( $this, 'on_deactivation' ) );

        // Define plugin constants
        $this->define_constants();

        // Include required filies
        $this->init_includes();

        // Load text domain for translations
        add_action( 'init', array( $this, 'load_textdomain' ) );

        // Initialize core functionalities
        $this->init_core();

        // Initialize admin settings
        if ( is_admin() ) {
            $this->init_admin();
        }

        // Initialize API
        $this->init_api();
    }

    /**
     * Get Plugin Instance
     *
     * @return Pre_Order_Ultra
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Define constants used in the plugin
     */
    private function define_constants() {
        define( 'PRE_ORDER_ULTRA_VERSION', '1.0.0' );
        define( 'PRE_ORDER_ULTRA_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
        define( 'PRE_ORDER_ULTRA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Load Text Domain
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'pre-order-ultra', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Intialize Cron Job
     */
    public function init_includes() {
        require_once PRE_ORDER_ULTRA_PLUGIN_PATH . 'includes/core/class-pre-order.php';
        
        require_once PRE_ORDER_ULTRA_PLUGIN_PATH . 'includes/core/class-cron-job.php';
        require_once PRE_ORDER_ULTRA_PLUGIN_PATH . 'includes/subscriptions/class-subscription-manager.php';
        require_once PRE_ORDER_ULTRA_PLUGIN_PATH . 'includes/subscriptions/class-subscription-handler.php';

        require_once PRE_ORDER_ULTRA_PLUGIN_PATH . 'includes/api/class-api-endpoints.php';
    }

    /**
     * Initialize Core Functionalities
     */
    private function init_core() {
        Pre_Order_Core::get_instance();
    }

    /**
     * Initialize Admin Settings
     */
    private function init_admin() {
        require_once PRE_ORDER_ULTRA_PLUGIN_PATH . 'includes/admin/class-admin-settings.php';
        require_once PRE_ORDER_ULTRA_PLUGIN_PATH . 'includes/admin/class-notify-me-admin.php';
    
        Admin_Settings::get_instance();
    }

    /**
     * Initialize API Endpoints
     */
    private function init_api() {
        API_Endpoints::get_instance();
    }

    /**
     * Handle Plugin Activation
     */
    public function on_activation() {
        // Create the custom subscription table
        $this->create_subscription_table();

        // Schedule the cron event
        if ( ! wp_next_scheduled( 'pre_order_ultra_send_notifications' ) ) {
            wp_schedule_event( time(), 'hourly', 'pre_order_ultra_send_notifications' );
        }
    }

    /**
     * Handle Plugin Deactivation
     */
    public function on_deactivation() {
        // Unschedule the cron event
        $timestamp = wp_next_scheduled( 'pre_order_ultra_send_notifications' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'pre_order_ultra_send_notifications' );
        }
    }

    public function table_name() {
        global $wpdb;
    
        // Define table name with prefix
        return $wpdb->prefix . 'pre_order_subscriptions';
    }

    /**
     * Create the subscriptions table
     */
    private function create_subscription_table() {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        global $wpdb;
    
        // Define table name with prefix
        $table_name = $this->table_name();
        $charset_collate = $wpdb->get_charset_collate();
    
        // SQL query to create the table
        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NULL,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone_number VARCHAR(20) NULL,
            product_id BIGINT(20) UNSIGNED NOT NULL,
            subscription_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            status VARCHAR(20) DEFAULT 'active' NOT NULL,
            PRIMARY KEY  (id),
            KEY product_id (product_id),
            KEY email (email)
        ) $charset_collate;";
    
        // Use maybe_create_table to avoid recreating an existing table
        maybe_create_table($table_name, $sql);
    }

}

endif;

// Initialize the plugin
function pre_order_ultra_init() {
    return Pre_Order_Ultra::get_instance();
}

pre_order_ultra_init();