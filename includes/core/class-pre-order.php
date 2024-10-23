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
    }

    public function enqueue_admin_scripts() {
        wp_enqueue_script( 'pre-order-admin', PRE_ORDER_ULTRA_PLUGIN_URL . '/assets/js/admin-pre-order.js', array( 'jquery' ), PRE_ORDER_ULTRA_VERSION, true );
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

}
