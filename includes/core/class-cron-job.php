<?php 

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Cron_Job {

    protected static $instance = null;
    private $table_name;

    private function __construct() {
        // Schedule cron event
        add_action( 'pre_order_ultra_send_notifications', array( $this, 'send_notifications' ) );
    }

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Send notifications to subscribers when products are in stock
     */
    public function send_notifications() {
        // Get all active subscriptions
        $subscription_manager = Subscription_Manager::get_instance();
        $active_subscriptions = $subscription_manager->get_all_active_subscriptions();

        if ( empty( $active_subscriptions ) ) {
            return;
        }

        foreach ( $active_subscriptions as $subscription ) {
            $product = wc_get_product( $subscription->product_id );

            if ( ! $product || $product->is_in_stock() ) {
                // Send notification
                $this->notify_user( $subscription, $product );

                // Update subscription status to 'notified'
                $subscription_manager->update_subscription_status( $subscription->id, 'notified' );
            }
        }
    }

    /**
     * Notify the user via email/SMS
     *
     * @param object $subscription
     * @param object $product
     */
    private function notify_user( $subscription, $product ) {
        // Prepare notification details
        $to = $subscription->email;
        $subject = __( 'Your Pre-Order is Now Available!', 'pre-order-ultra' );
        $product_name = $product->get_name();
        $product_link = get_permalink( $product->get_id() );

        $message = sprintf(
            __( 'Good news! The product you pre-ordered, "%s", is now available. <a href="%s">Click here</a> to purchase it now.', 'pre-order-ultra' ),
            $product_name,
            $product_link
        );

        // Send email
        wp_mail( $to, $subject, $message );

        // TODO: Implement SMS notification if phone_number is available
        // Example:
        // if ( ! empty( $subscription->phone_number ) ) {
        //     // Integrate with an SMS gateway to send SMS
        // }
    }

}