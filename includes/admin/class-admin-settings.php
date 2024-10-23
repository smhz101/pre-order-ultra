<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Admin_Settings {

    protected static $instance = null;

    private function __construct() {
        add_filter( 'woocommerce_get_sections_products', array( $this, 'add_pre_order_settings_section' ) );
        add_filter( 'woocommerce_get_settings_products', array( $this, 'pre_order_settings' ), 10, 2 );
    }

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Add Pre-Order Section under WooCommerce > Settings > Products
     *
     * @param array $sections
     * @return array
     */
    public function add_pre_order_settings_section( $sections ) {
        $sections['pre_order'] = __( 'Pre-Order', 'pre-order-ultra' );
        return $sections;
    }

    /**
     * Add settings to the new Pre-Order section
     *
     * @param array $settings
     * @param string $current_section
     * @return array
     */
    public function pre_order_settings( $settings, $current_section ) {
        if ( 'pre_order' === $current_section ) {
            $settings = array(
                array(
                    'title' => __( 'Pre-Order Settings', 'pre-order-ultra' ),
                    'type'  => 'title',
                    'id'    => 'pre_order_settings_section',
                ),
                // Enable pre-orders globally
                array(
                    'title'    => __( 'Enable Pre-Orders Globally', 'pre-order-ultra' ),
                    'id'       => 'enable_pre_orders_globally',
                    'type'     => 'checkbox',
                    'default'  => 'no',
                    'desc'     => __( 'Allow all products to be available for pre-order by default.', 'pre-order-ultra' ),
                ),
                // Automatically enable pre-order for out-of-stock products
                array(
                    'title'    => __( 'Automatically Enable Pre-Order for Out-of-Stock Products', 'pre-order-ultra' ),
                    'id'       => 'auto_pre_order_out_of_stock',
                    'type'     => 'checkbox',
                    'default'  => 'yes',
                    'desc'     => __( 'Automatically activate the pre-order mode for out-of-stock products.', 'pre-order-ultra' ),
                ),
                // Option to apply pre-order status automatically
                array(
                    'title'    => __( 'Apply Pre-Order Status Automatically', 'pre-order-ultra' ),
                    'id'       => 'apply_pre_order_status_auto',
                    'type'     => 'checkbox',
                    'default'  => 'no',
                    'desc'     => __( 'Automatically set new products as available for pre-order.', 'pre-order-ultra' ),
                ),
                // Pre-order stock management
                array(
                    'title'    => __( 'Pre-Order Stock Management', 'pre-order-ultra' ),
                    'id'       => 'pre_order_stock_management',
                    'type'     => 'checkbox',
                    'default'  => 'yes',
                    'desc'     => __( 'Enable stock management for pre-order products.', 'pre-order-ultra' ),
                ),
                // Charge fee for pre-order products
                array(
                    'title'    => __( 'Charge Fee for Pre-Order Products', 'pre-order-ultra' ),
                    'id'       => 'pre_order_fee',
                    'type'     => 'checkbox',
                    'default'  => 'no',
                    'desc'     => __( 'Charge an additional fee for pre-ordered products.', 'pre-order-ultra' ),
                ),
                // Pre-order discount
                array(
                    'title'    => __( 'Offer Pre-Order Discount', 'pre-order-ultra' ),
                    'id'       => 'pre_order_discount',
                    'type'     => 'checkbox',
                    'default'  => 'no',
                    'desc'     => __( 'Apply a fixed or percentage discount to the product price for pre-orders.', 'pre-order-ultra' ),
                ),
                // Pre-order notification emails
                array(
                    'title'    => __( 'Pre-Order Notification Email', 'pre-order-ultra' ),
                    'id'       => 'pre_order_notification_email',
                    'type'     => 'checkbox',
                    'default'  => 'yes',
                    'desc'     => __( 'Send notification emails to customers when they place a pre-order.', 'pre-order-ultra' ),
                ),
                // Button text settings
                array(
                    'title'    => __( 'Add to Cart Button Text', 'pre-order-ultra' ),
                    'id'       => 'pre_order_add_to_cart_text',
                    'type'     => 'text',
                    'default'  => __( 'Pre-Order Now', 'pre-order-ultra' ),
                    'desc'     => __( 'Set the button text for pre-order products.', 'pre-order-ultra' ),
                ),
                array(
                    'title'    => __( 'Place Order Button Text', 'pre-order-ultra' ),
                    'id'       => 'pre_order_place_order_text',
                    'type'     => 'text',
                    'default'  => __( 'Place Pre-Order Now', 'pre-order-ultra' ),
                    'desc'     => __( 'Set the button text for placing pre-orders.', 'pre-order-ultra' ),
                ),
                // Product message settings
                array(
                    'title'    => __( 'Single Product Page Message', 'pre-order-ultra' ),
                    'id'       => 'pre_order_single_product_message',
                    'type'     => 'textarea',
                    'default'  => __( 'This item will be released on {availability_date}.', 'pre-order-ultra' ),
                    'desc'     => __( 'Set the message displayed on the product page for pre-order products.', 'pre-order-ultra' ),
                ),
                array(
                    'title'    => __( 'Shop Loop Product Message', 'pre-order-ultra' ),
                    'id'       => 'pre_order_shop_loop_message',
                    'type'     => 'textarea',
                    'default'  => __( 'Available {availability_date}.', 'pre-order-ultra' ),
                    'desc'     => __( 'Set the message displayed in the shop loop for pre-order products.', 'pre-order-ultra' ),
                ),
                // Availability date title text
                array(
                    'title'    => __( 'Availability Date Title Text', 'pre-order-ultra' ),
                    'id'       => 'pre_order_availability_date_text',
                    'type'     => 'text',
                    'default'  => __( 'Available', 'pre-order-ultra' ),
                    'desc'     => __( 'Set the title for the availability date display.', 'pre-order-ultra' ),
                ),
                // Staging/Test mode
                array(
                    'title'    => __( 'Staging/Test Mode', 'pre-order-ultra' ),
                    'id'       => 'pre_order_staging_test',
                    'type'     => 'checkbox',
                    'default'  => 'no',
                    'desc'     => __( 'Disable automated pre-order processing for staging/test environments.', 'pre-order-ultra' ),
                ),
                array(
                    'type' => 'sectionend',
                    'id'   => 'pre_order_settings_section',
                ),
            );
        }

        return $settings;
    }

}