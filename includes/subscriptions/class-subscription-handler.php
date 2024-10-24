<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Subscription_Handler {

    protected static $instance = null;

    private function __construct() {
        // AJAX actions for logged-in users
        add_action( 'wp_ajax_pre_order_ultra_subscribe', array( $this, 'handle_subscription' ) );

        // AJAX actions for guests
        add_action( 'wp_ajax_nopriv_pre_order_ultra_subscribe', array( $this, 'handle_subscription' ) );
    }

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Handle subscription AJAX request
     */
    public function handle_subscription() {
        // Check nonce for security
        check_ajax_referer( 'pre_order_ultra_subscribe_nonce', 'security' );

        // Get POST data
        $product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
        $name = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
        $email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
        $phone_number = isset( $_POST['phone_number'] ) ? sanitize_text_field( $_POST['phone_number'] ) : '';

        // Validate required fields
        if ( empty( $product_id ) || empty( $name ) || empty( $email ) ) {
            wp_send_json_error( array( 'message' => __( 'Please fill in all required fields.', 'pre-order-ultra' ) ) );
        }

        // Check if product exists
        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            wp_send_json_error( array( 'message' => __( 'Invalid product.', 'pre-order-ultra' ) ) );
        }

        // Check if product is already in stock
        if ( $product->is_in_stock() ) {
            wp_send_json_error( array( 'message' => __( 'This product is already in stock.', 'pre-order-ultra' ) ) );
        }

        // Get subscription manager instance
        $subscription_manager = Subscription_Manager::get_instance();

        // Check if user is logged in
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();

            // Check if subscription already exists
            if ( $subscription_manager->subscription_exists( $user_id, $product_id ) ) {
                wp_send_json_error( array( 'message' => __( 'You have already subscribed to this product.', 'pre-order-ultra' ) ) );
            }

            // Add subscription
            $result = $subscription_manager->add_subscription( array(
                'user_id'      => $user_id,
                'name'         => $name,
                'email'        => $email,
                'phone_number' => $phone_number,
                'product_id'   => $product_id,
            ) );

            if ( $result ) {
                wp_send_json_success( array( 'message' => __( 'Thank you! You will be notified when this product is available.', 'pre-order-ultra' ) ) );
            } else {
                wp_send_json_error( array( 'message' => __( 'An error occurred. Please try again later.', 'pre-order-ultra' ) ) );
            }

        } else {
            // For guests, check if subscription already exists
            if ( $subscription_manager->guest_subscription_exists( $email, $product_id ) ) {
                wp_send_json_error( array( 'message' => __( 'You have already subscribed to this product.', 'pre-order-ultra' ) ) );
            }

            // Add subscription
            $result = $subscription_manager->add_subscription( array(
                'name'         => $name,
                'email'        => $email,
                'phone_number' => $phone_number,
                'product_id'   => $product_id,
            ) );

            if ( $result ) {
                wp_send_json_success( array( 'message' => __( 'Thank you! You will be notified when this product is available.', 'pre-order-ultra' ) ) );
            } else {
                wp_send_json_error( array( 'message' => __( 'An error occurred. Please try again later.', 'pre-order-ultra' ) ) );
            }
        }

        wp_die();
    }

}
