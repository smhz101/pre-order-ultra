<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class API_Endpoints {

    protected static $instance = null;

    private function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register REST API Routes
     */
    public function register_routes() {
        register_rest_route( 'pre-order-ultra/v1', '/orders', array(
            'methods'  => 'GET',
            'callback' => array( $this, 'get_orders' ),
            'permission_callback' => array( $this, 'permissions_check' ),
        ) );
    }

    /**
     * Get Orders
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_orders( WP_REST_Request $request ) {
        // Fetch and return orders with pre-order status
        $orders = array(); // Placeholder for actual orders fetching logic
        return new WP_REST_Response( $orders, 200 );
    }

    /**
     * Permissions Check
     *
     * @param WP_REST_Request $request
     * @return bool
     */
    public function permissions_check( WP_REST_Request $request ) {
        return current_user_can( 'manage_options' );
    }

}
