<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pre_Order_Core {

    /**
     * Instance
     *
     * @var Pre_Order_Core
     */
    protected static $instance = null;

    /**
     * Constructor
     */
    private function __construct() {
        // Hooks and filters
        add_action( 'init', array( $this, 'init_integratio' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
    }

    /**
     * Get Instance
     *
     * @return Pre_Order_Core
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init_integratio() {
        $this->init_woocommerce_integration();
        $this->init_cron_job();
        $this->init_subscription_handler();
    }

    public function enqueue_admin_scripts() {
        wp_enqueue_script( 'pre-order-admin', PRE_ORDER_ULTRA_PLUGIN_URL . '/assets/js/admin-pre-order.js', array( 'jquery' ), PRE_ORDER_ULTRA_VERSION, true );
    }
    
    /**
     * Enqueue Frontend Scripts and Styles
     */
    public function enqueue_frontend_scripts() {
        if ( is_product() ) {
            wp_enqueue_script(
                'pre-order-ultra-notify-me',
                PRE_ORDER_ULTRA_PLUGIN_URL . 'assets/js/notify-me.js',
                array( 'jquery' ),
                PRE_ORDER_ULTRA_VERSION,
                true
            );

            wp_localize_script( 'pre-order-ultra-notify-me', 'preOrderUltra', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'pre_order_ultra_subscribe_nonce' ),
            ) );

            wp_enqueue_style(
                'pre-order-ultra-notify-me-css',
                PRE_ORDER_ULTRA_PLUGIN_URL . 'assets/css/notify-me.css',
                array(),
                PRE_ORDER_ULTRA_VERSION
            );
        }
    }

    /**
     * Initialize WooCommerce Integration
     */
    private function init_woocommerce_integration() {
        if ( class_exists( 'WooCommerce' ) ) {
            require_once PRE_ORDER_ULTRA_PLUGIN_PATH . 'includes/integrations/class-integration-woocommerce.php';
            Integration_WooCommerce::get_instance();
        }
    }

    /**
     * Initialize Cron Job
     */
    private function init_cron_job() {
        Cron_Job::get_instance();
    }

    /**
     * Initialize Subscription Handler
     */
    private function init_subscription_handler() {
        Subscription_Handler::get_instance();
    }

}
