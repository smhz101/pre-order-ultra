jQuery(document).ready(function ($) {
    // Function to initialize countdown
    function initializeCountdown(releaseDate, productID, text) {
        // Parse the release date
        var countDownDate = new Date(releaseDate).getTime();

        // Update the count down every 1 second
        var x = setInterval(function() {

            // Get today's date and time
            var now = new Date().getTime();

            // Find the distance between now and the release date
            var distance = countDownDate - now;

            // Time calculations for days, hours, minutes and seconds
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Display the result in the respective countdown container
            $('#pre-order-countdown-' + productID).html(text + ' ' + days + 'd ' + hours + 'h '
                + minutes + 'm ' + seconds + 's ');

            // If the count down is finished, write some text
            if (distance < 0) {
                clearInterval(x);
                $('#pre-order-countdown-' + productID).html('Released!');
            }
        }, 1000);
    }

    // Initialize countdowns based on localized data
    if (typeof preOrderCountdown !== 'undefined') {
        initializeCountdown(preOrderCountdown.releaseDate, preOrderCountdown.productID, preOrderCountdown.text);
    }
});
