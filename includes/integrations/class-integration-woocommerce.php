<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Integration_WooCommerce {

    protected static $instance = null;

    private function __construct() {
        // Hooks for product admin fields
        add_action( 'woocommerce_product_data_tabs', array( $this, 'add_pre_order_product_data_tab' ) );
        add_action( 'woocommerce_product_data_panels', array( $this, 'add_pre_order_product_data_fields' ) );
        add_action( 'woocommerce_process_product_meta', array( $this, 'save_pre_order_settings' ) );

        // Frontend adjustments
        add_filter( 'woocommerce_get_price_html', array( $this, 'adjust_pre_order_price' ), 10, 2 );
        add_filter( 'woocommerce_is_purchasable', array( $this, 'check_purchasable' ), 10, 2 );
        add_action( 'woocommerce_single_product_summary', array( $this, 'display_pre_order_notice' ), 20 );
        add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'pre_order_button_text' ) );
        add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'pre_order_button_text' ) );

        // Order management
        add_action( 'woocommerce_thankyou', array( $this, 'process_pre_order' ), 10, 1 );
        add_action( 'woocommerce_order_status_changed', array( $this, 'handle_pre_order_notification' ), 10, 4 );
        
        // Cart adjustments
        add_filter( 'woocommerce_cart_item_name', array( $this, 'add_pre_order_label_in_cart' ), 10, 3 );
    }

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Add the "Pre-Order" tab to product data.
     *
     * @param array $tabs
     * @return array
     */
    public function add_pre_order_product_data_tab( $tabs ) {
        $tabs['pre_order_ultra'] = array(
            'label'    => __( 'Pre-Order', 'pre-order-ultra' ),
            'target'   => 'pre_order_product_data',
            'class'    => array(),
            'priority' => 21,
        );
        return $tabs;
    }

    /**
     * Add fields to the "Pre-Order" tab.
     */
    public function add_pre_order_product_data_fields() {
        global $post;

        // Get the global setting for enabling pre-orders
        $global_pre_orders_enabled = get_option( 'enable_pre_orders_globally', 'no' );

        // Get the product-level pre-order setting (fallback to global if not set)
        $enable_pre_order = get_post_meta( $post->ID, '_enable_pre_order', true );

        error_log('POST Global: ' . $global_pre_orders_enabled);
        error_log('POST Post: ' . $enable_pre_order);

        // If global pre-orders are enabled and the product-level setting is not set, use the global setting
        if ( 'yes' === $global_pre_orders_enabled && empty( $enable_pre_order ) ) {
            $enable_pre_order = 'yes';
        } else {
            // If the product-level setting is defined, use it; otherwise, use 'no' (or fallback)
            $enable_pre_order = ! empty( $enable_pre_order ) ? $enable_pre_order : 'no';
        }

        // Get pre-order metadata for this product
        $pre_order_date = get_post_meta( $post->ID, '_pre_order_date', true );
        $pre_order_price_type = get_post_meta( $post->ID, '_pre_order_price_type', true );
        $pre_order_fixed_price = get_post_meta( $post->ID, '_pre_order_fixed_price', true );

        // Get additional pre-order metadata for discount and increase fields
        $pre_order_discount_percent = get_post_meta( $post->ID, '_pre_order_discount_percent', true );
        $pre_order_discount_fixed = get_post_meta( $post->ID, '_pre_order_discount_fixed', true );
        $pre_order_increase_percent = get_post_meta( $post->ID, '_pre_order_increase_percent', true );
        $pre_order_increase_fixed = get_post_meta( $post->ID, '_pre_order_increase_fixed', true );

        ?>
        <div id="pre_order_product_data" class="panel woocommerce_options_panel">
            <div class="options_group pre-order-fields">
                <?php
                // Enable Pre-Order Option
                woocommerce_wp_checkbox( array(
                    'id'          => '_enable_pre_order',
                    'label'       => __( 'Enable Pre-Order', 'pre-order-ultra' ),
                    'description' => __( 'Allow customers to pre-order this product.', 'pre-order-ultra' ),
                    'value'       => $enable_pre_order ? 'yes' : 'no',
                ) ); 
                ?>

                <div class="pre-order-settings-panel hidden">
                    <?php
                    // Pre-Order Availability Date
                    woocommerce_wp_radio( array(
                        'id'          => '_pre_order_date_mode',
                        'label'       => __( 'Set product availability date', 'pre-order-ultra' ),
                        'options'     => array(
                            'no_date' => __( 'No date - end pre-order mode manually', 'pre-order-ultra' ),
                            'set_date' => __( 'Choose a date from the calendar', 'pre-order-ultra' )
                        ),
                        'value'       => $pre_order_date ? 'set_date' : 'no_date'
                    ) );

                    // Pre-Order Date Picker
                    woocommerce_wp_text_input( array(
                        'id'            => '_pre_order_date',
                        'label'         => __( 'Availability date and time', 'pre-order-ultra' ),
                        'description'   => __( 'Set the date when this product will become available.', 'pre-order-ultra' ),
                        'type'          => 'datetime-local',
                        'value'         => $pre_order_date,
                        'wrapper_class' => $pre_order_date ? '' : 'hidden',
                    ) );

                    // Pre-Order Pricing
                    woocommerce_wp_select( array(
                        'id'          => '_pre_order_price_type',
                        'label'       => __( 'Pre-Order Price', 'pre-order-ultra' ),
                        'options'     => array(
                            'use_selling_price' => __( 'Use the selling price', 'pre-order-ultra' ),
                            'fixed_price'       => __( 'Set a fixed pre-order price', 'pre-order-ultra' ),
                            'discount_percent'  => __( 'Discount a percentage % of the selling price', 'pre-order-ultra' ),
                            'discount_fixed'    => __( 'Discount a fixed amount of the selling price', 'pre-order-ultra' ),
                            'increase_percent'  => __( 'Increase a percentage % of the selling price', 'pre-order-ultra' ),
                            'increase_fixed'    => __( 'Increase a fixed amount of the selling price', 'pre-order-ultra' ),
                        ),
                        'value'       => $pre_order_price_type,
                    ) );

                    // Fixed Price Input (shown only if fixed price is selected)
                    woocommerce_wp_text_input( array(
                        'id'          => '_pre_order_fixed_price',
                        'label'       => __( 'Pre-Order Fixed Price', 'pre-order-ultra' ),
                        'description' => __( 'Set the fixed price for pre-ordering this product.', 'pre-order-ultra' ),
                        'type'        => 'number',
                        'value'       => $pre_order_fixed_price,
                        'wrapper_class' => 'pre_order_price_type_fixed_price hidden',
                    ) );

                    // Discount Percent Input (shown only if discount_percent is selected)
                    woocommerce_wp_text_input( array(
                        'id'          => '_pre_order_discount_percent',
                        'label'       => __( 'Pre-Order Discount Percent', 'pre-order-ultra' ),
                        'description' => __( 'Set the discount percentage for pre-ordering this product.', 'pre-order-ultra' ),
                        'type'        => 'number',
                        'value'       => $pre_order_discount_percent,
                        'wrapper_class' => 'pre_order_price_type_discount_percent hidden',
                    ) );

                    // Discount Fixed Amount Input (shown only if discount_fixed is selected)
                    woocommerce_wp_text_input( array(
                        'id'          => '_pre_order_discount_fixed',
                        'label'       => __( 'Pre-Order Discount Fixed Amount', 'pre-order-ultra' ),
                        'description' => __( 'Set the fixed discount amount for pre-ordering this product.', 'pre-order-ultra' ),
                        'type'        => 'number',
                        'value'       => $pre_order_discount_fixed,
                        'wrapper_class' => 'pre_order_price_type_discount_fixed hidden',
                    ) );

                    // Increase Percent Input (shown only if increase_percent is selected)
                    woocommerce_wp_text_input( array(
                        'id'          => '_pre_order_increase_percent',
                        'label'       => __( 'Pre-Order Increase Percent', 'pre-order-ultra' ),
                        'description' => __( 'Set the price increase percentage for pre-ordering this product.', 'pre-order-ultra' ),
                        'type'        => 'number',
                        'value'       => $pre_order_increase_percent,
                        'wrapper_class' => 'pre_order_price_type_increase_percent hidden',
                    ) );

                    // Increase Fixed Amount Input (shown only if increase_fixed is selected)
                    woocommerce_wp_text_input( array(
                        'id'          => '_pre_order_increase_fixed',
                        'label'       => __( 'Pre-Order Increase Fixed Amount', 'pre-order-ultra' ),
                        'description' => __( 'Set the fixed price increase for pre-ordering this product.', 'pre-order-ultra' ),
                        'type'        => 'number',
                        'value'       => $pre_order_increase_fixed,
                        'wrapper_class' => 'pre_order_price_type_increase_fixed hidden',
                    ) );
                    ?>
                </div>
            </div>
        </div>
        <?php
    }    

    /**
     * Save pre-order settings for a product.
     *
     * @param int $post_id
     */
    public function save_pre_order_settings( $post_id ) {
        // Check if the global setting is enabled
        $global_pre_orders_enabled = get_option( 'enable_pre_orders_globally', 'no' );

        // Save the product-level pre-order setting
        $enable_pre_order = isset( $_POST['_enable_pre_order'] ) ? 'yes' : 'no';

        // If the global setting is enabled but the product-level setting is manually unchecked, save the product-level setting
        if ( 'yes' === $global_pre_orders_enabled && 'no' === $enable_pre_order ) {
            update_post_meta( $post_id, '_enable_pre_order', $enable_pre_order );
        } elseif ( 'yes' === $enable_pre_order ) {
            update_post_meta( $post_id, '_enable_pre_order', 'yes' );
        } else {
            delete_post_meta( $post_id, '_enable_pre_order' );
        }

        // Save pre-order date mode and date
        $pre_order_date_mode = isset( $_POST['_pre_order_date_mode'] ) ? $_POST['_pre_order_date_mode'] : 'no_date';
        update_post_meta( $post_id, '_pre_order_date_mode', $pre_order_date_mode );

        if ( $pre_order_date_mode === 'set_date' && isset( $_POST['_pre_order_date'] ) ) {
            update_post_meta( $post_id, '_pre_order_date', sanitize_text_field( $_POST['_pre_order_date'] ) );
        } else {
            delete_post_meta( $post_id, '_pre_order_date' );
        }

        // Save pre-order pricing
        if ( isset( $_POST['_pre_order_price_type'] ) ) {
            update_post_meta( $post_id, '_pre_order_price_type', sanitize_text_field( $_POST['_pre_order_price_type'] ) );
        }
        if ( isset( $_POST['_pre_order_fixed_price'] ) ) {
            update_post_meta( $post_id, '_pre_order_fixed_price', sanitize_text_field( $_POST['_pre_order_fixed_price'] ) );
        }
        if ( isset( $_POST['_pre_order_discount_percent'] ) ) {
            update_post_meta( $post_id, '_pre_order_discount_percent', sanitize_text_field( $_POST['_pre_order_discount_percent'] ) );
        }
        if ( isset( $_POST['_pre_order_discount_fixed'] ) ) {
            update_post_meta( $post_id, '_pre_order_discount_fixed', sanitize_text_field( $_POST['_pre_order_discount_fixed'] ) );
        }
        if ( isset( $_POST['_pre_order_increase_percent'] ) ) {
            update_post_meta( $post_id, '_pre_order_increase_percent', sanitize_text_field( $_POST['_pre_order_increase_percent'] ) );
        }
        if ( isset( $_POST['_pre_order_increase_fixed'] ) ) {
            update_post_meta( $post_id, '_pre_order_increase_fixed', sanitize_text_field( $_POST['_pre_order_increase_fixed'] ) );
        }
    }

    /**
     * Check if product is purchasable
     *
     * @param bool $purchasable
     * @param WC_Product $product
     * @return bool
     */
    public function check_purchasable( $purchasable, $product ) {
        // Fetch the settings from WooCommerce
        $enable_pre_order_out_of_stock = get_option( 'auto_pre_order_out_of_stock', 'yes' );
        $pre_order_status_auto = get_option( 'apply_pre_order_status_auto', 'no' );
        
        // If the product is out of stock and global option is enabled, allow pre-order
        if ( 'yes' === $enable_pre_order_out_of_stock && ! $product->is_in_stock() ) {
            return true;
        }

        // Additional checks for specific pre-order settings
        $enable_pre_order = get_post_meta( $product->get_id(), '_enable_pre_order', true );
        $pre_order_date = get_post_meta( $product->get_id(), '_pre_order_date', true );

        if ( 'yes' === $enable_pre_order && ! empty( $pre_order_date ) ) {
            return true;
        }

        return $purchasable;
    }

    /**
     * Display pre-order availability notice.
     */
    public function display_pre_order_notice() {
        global $product;
        
        // Check if pre-order is enabled for the product
        $enable_pre_order = get_post_meta( $product->get_id(), '_enable_pre_order', true );
        if ( 'yes' !== $enable_pre_order ) {
            return;
        }

        // Get pre-order date and display the notice
        $pre_order_date = get_post_meta( $product->get_id(), '_pre_order_date', true );
        if ( ! empty( $pre_order_date ) ) {
            $availability_message = sprintf(
                __( 'This item is available for pre-order. It will be released on %s.', 'pre-order-ultra' ),
                date_i18n( get_option( 'date_format' ), strtotime( $pre_order_date ) )
            );
            echo '<p class="pre-order-notice">' . esc_html( $availability_message ) . '</p>';
        }
    }

    /**
     * Modify Add to Cart Button Text for Pre-Order Products
     */
    public function pre_order_button_text( $text ) {
        global $product;
    
        // Ensure WooCommerce product is available
        if ( ! $product ) {
            return $text;
        }
    
        // Check if pre-order is enabled for the product
        $enable_pre_order = get_post_meta( $product->get_id(), '_enable_pre_order', true );
    
        if ( 'yes' === $enable_pre_order ) {
            // First, check for product-specific pre-order button text
            $pre_order_button_text = get_post_meta( $product->get_id(), '_pre_order_button_text', true );
    
            // If no product-specific text, fallback to global option
            if ( empty( $pre_order_button_text ) ) {
                $pre_order_button_text = get_option( 'pre_order_add_to_cart_text', __( 'Pre-Order Now', 'pre-order-ultra' ) );
            }
    
            // Set the button text
            $text = $pre_order_button_text;
        }
    
        return $text;
    }   

    /**
     * Process Pre-Order in WooCommerce thank you page
     *
     * @param int $order_id
     */
    public function process_pre_order( $order_id ) {
        $order = wc_get_order( $order_id );

        foreach ( $order->get_items() as $item_id => $item ) {
            $product_id = $item->get_product_id();
            $enable_pre_order = get_post_meta( $product_id, '_enable_pre_order', true );
            $pre_order_date = get_post_meta( $product_id, '_pre_order_date', true );

            if ( 'yes' === $enable_pre_order && ! empty( $pre_order_date ) ) {
                // Set order item meta or status as pre-order
                $item->add_meta_data( '_pre_order', 'yes', true );
                $item->save();
            }
        }
    }

    /**
     * Handle Pre-Order Notifications on Order Status Change
     *
     * @param int $order_id
     * @param string $old_status
     * @param string $new_status
     * @param WC_Order $order
     */
    public function handle_pre_order_notification( $order_id, $old_status, $new_status, $order ) {
        if ( 'yes' === get_option( 'pre_order_notification_email', 'yes' ) ) {
            // Check if the order contains any pre-order items
            foreach ( $order->get_items() as $item ) {
                if ( $item->get_meta( '_pre_order' ) === 'yes' ) {
                    // Send pre-order notification to customer
                    $customer_email = $order->get_billing_email();
                    wp_mail( $customer_email, __( 'Your Pre-Order Update', 'pre-order-ultra' ), __( 'Your pre-order has been processed!', 'pre-order-ultra' ) );
                }
            }
        }
    }

    /**
     * Modify the product price for pre-order products
     */
    public function adjust_pre_order_price( $price_html, $product ) {
        // Check if pre-order is enabled for the product
        $enable_pre_order = get_post_meta( $product->get_id(), '_enable_pre_order', true );
        if ( 'yes' !== $enable_pre_order ) {
            return $price_html;
        }

        // Get pre-order price type and other pre-order metadata
        $pre_order_price_type = get_post_meta( $product->get_id(), '_pre_order_price_type', true );
        $pre_order_fixed_price = get_post_meta( $product->get_id(), '_pre_order_fixed_price', true );
        $pre_order_discount_percent = get_post_meta( $product->get_id(), '_pre_order_discount_percent', true );
        $pre_order_discount_fixed = get_post_meta( $product->get_id(), '_pre_order_discount_fixed', true );
        $pre_order_increase_percent = get_post_meta( $product->get_id(), '_pre_order_increase_percent', true );
        $pre_order_increase_fixed = get_post_meta( $product->get_id(), '_pre_order_increase_fixed', true );

        // Get the original price
        $original_price = $product->get_regular_price();

        // Apply pre-order price logic based on selected price type
        switch ( $pre_order_price_type ) {
            case 'fixed_price':
                $price_html = wc_price( $pre_order_fixed_price );
                break;
            case 'discount_percent':
                $discounted_price = $original_price * ( ( 100 - $pre_order_discount_percent ) / 100 );
                $price_html = wc_price( $discounted_price );
                break;
            case 'discount_fixed':
                $discounted_price = $original_price - $pre_order_discount_fixed;
                $price_html = wc_price( $discounted_price );
                break;
            case 'increase_percent':
                $increased_price = $original_price * ( ( 100 + $pre_order_increase_percent ) / 100 );
                $price_html = wc_price( $increased_price );
                break;
            case 'increase_fixed':
                $increased_price = $original_price + $pre_order_increase_fixed;
                $price_html = wc_price( $increased_price );
                break;
            case 'use_selling_price':
            default:
                $price_html = wc_price( $original_price );
                break;
        }

        return $price_html;
    }

    /**
     * Add a custom label to pre-order items in the cart.
     *
     * @param string $product_name The product name.
     * @param array $cart_item The cart item data.
     * @param string $cart_item_key The cart item key.
     * @return string Modified product name.
     */
    public function add_pre_order_label_in_cart( $product_name, $cart_item, $cart_item_key ) {
        $product = wc_get_product( $cart_item['product_id'] );
        $enable_pre_order = get_post_meta( $product->get_id(), '_enable_pre_order', true );

        if ( 'yes' === $enable_pre_order ) {
            $product_name .= ' <span class="pre-order-label">(' . __( 'Pre-Order', 'pre-order-ultra' ) . ')</span>';
        }

        return $product_name;
    }

}