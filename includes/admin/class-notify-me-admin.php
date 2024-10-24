<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Notify_Me_Admin {

    protected static $instance = null;
    private $page_slug = 'pre_order_ultra_subscriptions';
    private $menu_title = 'Pre-Order Subscriptions';
    private $capability = 'manage_woocommerce';

    private function __construct() {
        // Add admin menu
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

        // Enqueue admin scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        add_action( 'wp_ajax_pre_order_ultra_mark_notified', array( $this, 'mark_as_notified' ) );
    }

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Add Admin Menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __( 'Pre-Order Subscriptions', 'pre-order-ultra' ),
            __( 'Pre-Order Subscriptions', 'pre-order-ultra' ),
            $this->capability,
            $this->page_slug,
            array( $this, 'render_subscriptions_page' )
        );
    }

    /**
     * Enqueue Admin Scripts and Styles
     */
    public function enqueue_admin_scripts( $hook ) {
        if ( 'woocommerce_page_' . $this->page_slug !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'pre-order-ultra-admin-css',
            PRE_ORDER_ULTRA_PLUGIN_URL . 'assets/css/admin-pre-order.css',
            array(),
            PRE_ORDER_ULTRA_VERSION
        );

        wp_enqueue_script(
            'pre-order-ultra-admin-js',
            PRE_ORDER_ULTRA_PLUGIN_URL . 'assets/js/admin-pre-order-mark-notified.js',
            array( 'jquery' ),
            PRE_ORDER_ULTRA_VERSION,
            true
        );
    }

    /**
     * Render Subscriptions Page
     */
    public function render_subscriptions_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Pre-Order Subscriptions', 'pre-order-ultra' ); ?></h1>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'ID', 'pre-order-ultra' ); ?></th>
                        <th><?php esc_html_e( 'Name', 'pre-order-ultra' ); ?></th>
                        <th><?php esc_html_e( 'Email', 'pre-order-ultra' ); ?></th>
                        <th><?php esc_html_e( 'Phone Number', 'pre-order-ultra' ); ?></th>
                        <th><?php esc_html_e( 'Product', 'pre-order-ultra' ); ?></th>
                        <th><?php esc_html_e( 'Subscription Date', 'pre-order-ultra' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'pre-order-ultra' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'pre-order-ultra' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $subscription_manager = Subscription_Manager::get_instance();
                    $subscriptions = $subscription_manager->get_all_active_subscriptions();

                    if ( ! empty( $subscriptions ) ) {
                        foreach ( $subscriptions as $subscription ) {
                            $product = wc_get_product( $subscription->product_id );
                            ?>
                            <tr>
                                <td><?php echo esc_html( $subscription->id ); ?></td>
                                <td><?php echo esc_html( $subscription->name ); ?></td>
                                <td><?php echo esc_html( $subscription->email ); ?></td>
                                <td><?php echo esc_html( $subscription->phone_number ); ?></td>
                                <td><?php echo $product ? esc_html( $product->get_name() ) : __( 'N/A', 'pre-order-ultra' ); ?></td>
                                <td><?php echo esc_html( $subscription->subscription_date ); ?></td>
                                <td><?php echo esc_html( ucfirst( $subscription->status ) ); ?></td>
                                <td>
                                    <?php if ( 'active' === $subscription->status ) : ?>
                                        <button class="button button-secondary pre-order-ultra-mark-notified" data-id="<?php echo esc_attr( $subscription->id ); ?>"><?php esc_html_e( 'Mark as Notified', 'pre-order-ultra' ); ?></button>
                                    <?php else : ?>
                                        <?php esc_html_e( 'N/A', 'pre-order-ultra' ); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="8"><?php esc_html_e( 'No active subscriptions found.', 'pre-order-ultra' ); ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Handle marking a subscription as notified via AJAX
     */
    public function mark_as_notified() {
        // Check nonce for security
        check_ajax_referer( 'pre_order_ultra_mark_notified_nonce', 'security' );

        // Get subscription ID
        $subscription_id = isset( $_POST['subscription_id'] ) ? intval( $_POST['subscription_id'] ) : 0;

        if ( empty( $subscription_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid subscription ID.', 'pre-order-ultra' ) ) );
        }

        // Get subscription manager instance
        $subscription_manager = Subscription_Manager::get_instance();

        // Update subscription status
        $updated = $subscription_manager->update_subscription_status( $subscription_id, 'notified' );

        if ( $updated ) {
            wp_send_json_success();
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to update subscription status.', 'pre-order-ultra' ) ) );
        }

        wp_die();
    }

}

Notify_Me_Admin::get_instance();