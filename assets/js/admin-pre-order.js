jQuery(document).ready(function ($) {
    var $enablePreOrder = $('#_enable_pre_order');
    var $preOrderSettingsPanel = $('.pre-order-settings-panel');
    var $preOrderDateFields = $('.pre_order_date_field');
    var $preOrderPriceType = $('#_pre_order_price_type');

    function togglePreOrderFields() {
        if ($enablePreOrder.is(':checked')) {
            $preOrderSettingsPanel.removeClass('hidden').show();
        } else {
            $preOrderSettingsPanel.addClass('hidden').hide();
        }
    }

    function toggleDateField() {
        var selectedVal = $('input[name="_pre_order_date_mode"]:checked').val();
        console.log('Date mode selected:', selectedVal);
        if (selectedVal === 'set_date') {
            console.log('Showing pre_order_date_field');
            $('#_pre_order_date').closest("p").removeClass('hidden').show();
            $('#_pre_order_date').removeClass('hidden').show();
        } else {
            console.log('Hiding pre_order_date_field');
            $('#_pre_order_date').closest("p").addClass('hidden').hide();
            $('#_pre_order_date').addClass('hidden').hide();
        }
    }

    function togglePriceTypeFields() {
        var selectedType = $preOrderPriceType.val();
        
        // Hide all specific price type fields first
        $('.pre_order_price_type_fixed_price, .pre_order_price_type_discount_percent, .pre_order_price_type_discount_fixed, .pre_order_price_type_increase_percent, .pre_order_price_type_increase_fixed').hide();
        
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
    togglePreOrderFields();
    toggleDateField();
    togglePriceTypeFields();

    // On change events
    $enablePreOrder.change(togglePreOrderFields);
    $('input[name="_pre_order_date_mode"]').change(toggleDateField);
    $preOrderPriceType.change(togglePriceTypeFields);
});
