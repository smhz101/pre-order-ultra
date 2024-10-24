<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $product;

// Only display the form if pre-order is enabled and the product is not in stock
$enable_pre_order = get_post_meta( $product->get_id(), '_enable_pre_order', true );
$global_pre_orders_enabled = get_option( 'enable_pre_orders_globally', 'no' );
$enable_pre_order_out_of_stock = get_option( 'auto_pre_order_out_of_stock', 'yes' );

if ( ( 'yes' === $enable_pre_order ) || ( 'yes' === $enable_pre_orders_enabled && 'yes' === $enable_pre_order_out_of_stock && ! $product->is_in_stock() ) ) :
    ?>
    <div class="notify-me-container">
        <button id="notify-me-button" class="button notify-me-button"><?php esc_html_e( 'Notify Me When Available', 'pre-order-ultra' ); ?></button>
        
        <div id="notify-me-form" class="notify-me-form hidden">
            <form id="pre_order_ultra_notify_form">
                <?php wp_nonce_field( 'pre_order_ultra_subscribe', 'pre_order_ultra_subscribe_nonce' ); ?>
                <input type="hidden" name="product_id" value="<?php echo esc_attr( $product->get_id() ); ?>">
                
                <p>
                    <label for="pre_order_ultra_name"><?php esc_html_e( 'Name', 'pre-order-ultra' ); ?> *</label>
                    <input type="text" id="pre_order_ultra_name" name="name" required>
                </p>
                
                <p>
                    <label for="pre_order_ultra_email"><?php esc_html_e( 'Email', 'pre-order-ultra' ); ?> *</label>
                    <input type="email" id="pre_order_ultra_email" name="email" required>
                </p>
                
                <p>
                    <label for="pre_order_ultra_phone"><?php esc_html_e( 'Phone Number', 'pre-order-ultra' ); ?></label>
                    <input type="text" id="pre_order_ultra_phone" name="phone_number">
                </p>
                
                <p>
                    <button type="submit" class="button"><?php esc_html_e( 'Subscribe', 'pre-order-ultra' ); ?></button>
                </p>
                
                <div id="pre_order_ultra_form_message"></div>
            </form>
        </div>
    </div>
    <?php
endif;
?>
