jQuery(document).ready(function ($) {
    // Toggle the display of the form
    $('#notify-me-button').on('click', function (e) {
        e.preventDefault();
        $('#notify-me-form').toggleClass('hidden');
    });

    // Handle form submission
    $('#pre_order_ultra_notify_form').on('submit', function (e) {
        e.preventDefault();

        var form = $(this);
        var product_id = form.find('input[name="product_id"]').val();
        var name = form.find('input[name="name"]').val();
        var email = form.find('input[name="email"]').val();
        var phone_number = form.find('input[name="phone_number"]').val();

        // Simple validation
        if ( !name || !email ) {
            $('#pre_order_ultra_form_message').html('<p class="error">' + preOrderUltra.error_message + '</p>');
            return;
        }

        // AJAX request
        $.ajax({
            type: 'POST',
            url: preOrderUltra.ajax_url,
            data: {
                action: 'pre_order_ultra_subscribe',
                security: preOrderUltra.nonce,
                product_id: product_id,
                name: name,
                email: email,
                phone_number: phone_number
            },
            beforeSend: function () {
                form.find('button[type="submit"]').prop('disabled', true).text('Submitting...');
                $('#pre_order_ultra_form_message').html('');
            },
            success: function (response) {
                if ( response.success ) {
                    $('#pre_order_ultra_form_message').html('<p class="success">' + response.data.message + '</p>');
                    form[0].reset();
                    $('#notify-me-form').addClass('hidden');
                } else {
                    $('#pre_order_ultra_form_message').html('<p class="error">' + response.data.message + '</p>');
                }
            },
            error: function () {
                $('#pre_order_ultra_form_message').html('<p class="error">An unexpected error occurred. Please try again later.</p>');
            },
            complete: function () {
                form.find('button[type="submit"]').prop('disabled', false).text('Subscribe');
            }
        });
    });
});
