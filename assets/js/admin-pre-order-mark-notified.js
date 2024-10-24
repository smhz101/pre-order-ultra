jQuery(document).ready(function ($) {

    $('.pre-order-ultra-mark-notified').on('click', function (e) {
        e.preventDefault();

        var button = $(this);
        var subscription_id = button.data('id');

        if ( confirm( 'Are you sure you want to mark this subscription as notified?' ) ) {
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'pre_order_ultra_mark_notified',
                    subscription_id: subscription_id,
                    security: preOrderUltraAdmin.nonce
                },
                beforeSend: function () {
                    button.prop('disabled', true).text('Processing...');
                },
                success: function (response) {
                    if ( response.success ) {
                        button.closest('tr').find('td:nth-child(7)').text('Notified');
                        button.remove();
                        alert( 'Subscription marked as notified.' );
                    } else {
                        alert( response.data.message );
                        button.prop('disabled', false).text('Mark as Notified');
                    }
                },
                error: function () {
                    alert( 'An unexpected error occurred.' );
                    button.prop('disabled', false).text('Mark as Notified');
                }
            });
        }
    });

});