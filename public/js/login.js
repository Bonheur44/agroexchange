'use strict';

(function ($) {

    /*------------------
        Login
    --------------------*/
    $('#login').on('click', function(e) {
        e.preventDefault();
        let ring = $(this).find('.lds-dual-ring');
        ring.css('display', 'inline-block');
        $.ajax({
            type: "POST",
            url: '/loginData',
            data: { loginData: [$('#email').val(), $('#password').val()] },
            success: function(res) {
                const p = $('.login .err');
                ring.css('display', 'none');
		        for (let i = 0; i <= 2; i++) {
                    p[i].innerHTML = res[i];
		        }
                (res.res)? document.location.href = res.route:'';
            }
        });
    });

    /*------------------
        RegisterConsumer
    --------------------*/
    $('#registerConsumer').on('click', function(e) {
        e.preventDefault();
        let ring = $(this).find('.lds-dual-ring');
        ring.css('display', 'inline-block');
        $.ajax({
            type: "POST",
            url: '/registerConsumerData',
            data: { registerConsumerData: [$('#name').val(), $('#email').val(), $('#pass').val(), $('#confirm').val()] },
            success: function(res) {
                const p = $('.registerConsumer .err');
                ring.css('display', 'none');
                    for (let i = 0; i <= 4; i++) {
                    p[i].innerHTML = res[i];
                }
                if (res.res) {
                    $('.registerConsumer .alert').addClass('alert-success');
                    document.location.href = $('.registerConsumer').attr('name');
                }
                $('.registerConsumer .alert').html(res.res);
            }
        });
    });

    /*------------------
        RegisterProducer
    --------------------*/
    $('#registerProducer').on('click', function(e) {
        e.preventDefault();
        let ring = $(this).find('.lds-dual-ring');
        ring.css('display', 'inline-block');
        $.ajax({
            type: "POST",
            url: '/registerProducerData',
            data: { registerProducerData: [$('#name').val(), $('#email').val(), $('#pass').val(), $('#confirm').val()] },
            success: function(res) {

                const p = $('.registerProducer .err');
                ring.css('display', 'none');
                    for (let i = 0; i <= 4; i++) {
                    p[i].innerHTML = res[i];
                }
                if (res.res) {
                    $('.registerProducer .alert').addClass('alert-success');
                    document.location.href = $('.registerProducer').attr('name');
                }
                $('.registerProducer .alert').html(res.res);
            }
        });
    });

    $('.logout').on('click', function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: '/logout',
            success: function (res) {
                document.location.href = '/login';
            }
        });
    });

})(jQuery);