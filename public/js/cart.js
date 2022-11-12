'use strict';

(function ($) {

    var cart = undefined;
    $(".consumerAddToCart").on('click', function (e) {
        e.preventDefault();

        var id = $(this).attr('id');
        var cart = localStorage.getItem('consumerCart');
        if (cart == undefined) {
            localStorage.setItem('consumerCart', `{"p${id}" : "0"}`);
        } else if (JSON.parse(cart).hasOwnProperty(`p${id}`)) {
        } else {
            JSON.parse(cart) += 1;
            localStorage.setItem('consumerCart', `${cart}`);
        }
        console.log(localStorage.getItem('consumerCart'));
    });
})(jQuery);