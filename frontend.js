jQuery(document).ready(function($){
	$('.wpsfa-icheck').iCheck({
		checkboxClass: 'icheckbox_flat-blue',
		radioClass: 'iradio_flat-blue'
	});

	$('.wpsfa-icheck').on('ifChecked', function(){
		var $button = $(this),
			$form = $button.closest('#advisor-form'),
			$container = $form.closest('.advisor-container'),
			$productsContent = $container.find('.advisor-products-list'),
			vars = $form.serialize();
		$productsContent.html('<div id="loader-container"><div id="loader"></div></div>');

		$.post(wpsfaAjax.ajaxurl, vars, function(response){
			if (response.success) {
				var html = response.data.html;
				$productsContent.html(html);
				$productsContent.slideDown('fast');
			}
		}, 'json');

	});

});
