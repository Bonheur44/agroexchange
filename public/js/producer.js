'use strict';

(function ($) {

    /*------------------
        Add Product
    --------------------*/
    $("#addProduct").on('click', function (e) {
        e.preventDefault();
        let ring = $(this).find('.lds-dual-ring');
        ring.css('display', 'inline-block');
		var fd = new FormData();

	   	fd.append('name', $("#name").val());
	   	fd.append('cat', $("#cat").val());
	   	fd.append('price', $("#price").val());
	   	fd.append('unit', $("#unit").val());
	   	fd.append('qty', $("#quantity").val());
	   	fd.append('image', $('#image')[0].files[0]);

	   	$.ajax({
            type: 'POST',
		  	url: '/producer/addProductData',
		  	data: fd,
		  	contentType: false,
		  	processData: false,
		  	success: function (res) {
                const p = $('.addProduct .err');
                ring.css('display', 'none');
                for (let i = 0; i <= 7; i++) {
                    p[i].innerHTML = res[i];
                }
                if (res.res) {
                    $('.addProduct .alert').addClass('alert-success');
                }
                $('.addProduct .alert').html(res.res);
			},
	   	});
	});

})(jQuery);