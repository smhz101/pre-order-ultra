jQuery(document).ready(function ($) {

    // Toggle pre-order fields based on enable_pre_order checkbox
    function togglePreOrderFields() {
        if ($('#_enable_pre_order').is(':checked')) {
            $('.pre-order-settings-panel.hidden').show();  // Show the hidden div
        } else {
            $('.pre-order-settings-panel.hidden').hide();  // Hide the hidden div
        }
    }

    // Toggle date picker based on pre_order_date_mode radio button
    function toggleDateField() {
        if ($('input[name="_pre_order_date_mode"]:checked').val() === 'set_date') {
            $('._pre_order_date_field ').closest('.form-field').show();
        } else {
            $('._pre_order_date_field').closest('.form-field').hide();
        }
    }

    // Toggle input fields based on pre_order_price_type select box
    function togglePriceTypeFields() {
        var selectedType = $('#_pre_order_price_type').val();
        
        // Hide all specific price type fields first
        $('.pre_order_price_type_fixed_price').hide();
        $('.pre_order_price_type_discount_percent').hide();
        $('.pre_order_price_type_discount_fixed').hide();
        $('.pre_order_price_type_increase_percent').hide();
        $('.pre_order_price_type_increase_fixed').hide();
        
        // Show the selected one
        if (selectedType === 'fixed_price') {
            $('.pre_order_price_type_fixed_price').show();
        } else if (selectedType === 'discount_percent') {
            $('.pre_order_price_type_discount_percent').show();
        } else if (selectedType === 'discount_fixed') {
            $('.pre_order_price_type_discount_fixed').show();
        } else if (selectedType === 'increase_percent') {
            $('.pre_order_price_type_increase_percent').show();
        } else if (selectedType === 'increase_fixed') {
            $('.pre_order_price_type_increase_fixed').show();
        }
    }

    // Initial load
    togglePreOrderFields();  // Check the pre-order enable checkbox state
    togglePriceTypeFields(); // Check the price type state

    // On change events
    $('#_enable_pre_order').change(function () {
        togglePreOrderFields();
    });

    $('#_pre_order_price_type').change(function () {
        togglePriceTypeFields();
    });

});