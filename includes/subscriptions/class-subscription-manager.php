<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Subscription_Manager {

    protected static $instance = null;
    private $table_name;

    private function __construct() {
        $pre_order_ultra_init =  pre_order_ultra_init();
        $this->table_name = $pre_order_ultra_init->table_name();
    }

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Add a new subscription
     *
     * @param array $data
     * @return int|false
     */
    public function add_subscription( $data ) {
        global $wpdb;

        $inserted = $wpdb->insert(
            $this->table_name,
            array(
                'user_id'          => isset( $data['user_id'] ) ? intval( $data['user_id'] ) : null,
                'name'             => sanitize_text_field( $data['name'] ),
                'email'            => sanitize_email( $data['email'] ),
                'phone_number'     => isset( $data['phone_number'] ) ? sanitize_text_field( $data['phone_number'] ) : null,
                'product_id'       => intval( $data['product_id'] ),
                'subscription_date'=> current_time( 'mysql' ),
                'status'           => 'active',
            ),
            array(
                '%d',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
            )
        );

        if ( false !== $inserted ) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Get subscriptions by product ID
     *
     * @param int $product_id
     * @return array
     */
    public function get_subscriptions_by_product( $product_id ) {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE product_id = %d AND status = %s",
                $product_id,
                'active'
            )
        );

        return $results;
    }

    /**
     * Update subscription status
     *
     * @param int $subscription_id
     * @param string $status
     * @return int|false
     */
    public function update_subscription_status( $subscription_id, $status ) {
        global $wpdb;

        $updated = $wpdb->update(
            $this->table_name,
            array( 'status' => sanitize_text_field( $status ) ),
            array( 'id' => intval( $subscription_id ) ),
            array( '%s' ),
            array( '%d' )
        );

        return $updated;
    }

    /**
     * Get all active subscriptions
     *
     * @return array
     */
    public function get_all_active_subscriptions() {
        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT * FROM {$this->table_name} WHERE status = 'active'"
        );

        return $results;
    }

    /**
     * Check if a subscription already exists for a user and product
     *
     * @param int $user_id
     * @param int $product_id
     * @return bool
     */
    public function subscription_exists( $user_id, $product_id ) {
        global $wpdb;

        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE user_id = %d AND product_id = %d AND status = %s",
                $user_id,
                $product_id,
                'active'
            )
        );

        return $result > 0;
    }

    /**
     * Check if a guest subscription already exists for email and product
     *
     * @param string $email
     * @param int $product_id
     * @return bool
     */
    public function guest_subscription_exists( $email, $product_id ) {
        global $wpdb;

        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE email = %s AND product_id = %d AND status = %s",
                $email,
                $product_id,
                'active'
            )
        );

        return $result > 0;
    }

}
