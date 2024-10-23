<?php
/**
 * Plugin Name: Pre-Order Ultra
 * Plugin URI: https://example.com/pre-order-ultra
 * Description: Adds pre-order functionality to your WooCommerce store.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
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
        // Define plugin constants
        $this->define_constants();

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
     * Initialize Core Functionalities
     */
    private function init_core() {
        require_once PRE_ORDER_ULTRA_PLUGIN_PATH . 'includes/core/class-pre-order.php';
        Pre_Order_Core::get_instance();
    }

    /**
     * Initialize Admin Settings
     */
    private function init_admin() {
        require_once PRE_ORDER_ULTRA_PLUGIN_PATH . 'includes/admin/class-admin-settings.php';
        Admin_Settings::get_instance();
    }

    /**
     * Initialize API Endpoints
     */
    private function init_api() {
        require_once PRE_ORDER_ULTRA_PLUGIN_PATH . 'includes/api/class-api-endpoints.php';
        API_Endpoints::get_instance();
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

}

endif;

// Initialize the plugin
function pre_order_ultra_init() {
    return Pre_Order_Ultra::get_instance();
}

pre_order_ultra_init();