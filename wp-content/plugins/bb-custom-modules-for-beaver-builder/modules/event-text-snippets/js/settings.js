(function($){

	let BJS = {
		init: function(){
			let form = $('.fl-builder-settings:visible'),
				js_copy_to_clipboard = form.find( '.js_copy_to_clipboard');

			$(document).on('click', '.js_copy_to_clipboard', this._copyToClipboard);

			//js_copy_to_clipboard.on('click', this._copyToClipboard);
		},

		_copyToClipboard: function(){
			let $parent = $(this).parents('.js_shortcode_wrap'),
				$input = $parent.find('.js_shortcode'),
				text = $input.val(),
				$js_copy_notify = $parent.find('.js_copy_notify');

			$js_copy_notify.fadeIn(200).delay(2000).fadeOut(400);

			if(navigator && navigator.clipboard && navigator.clipboard.writeText){
				return navigator.clipboard.writeText(text);
			}else{
				$input.select();
				document.execCommand('copy');
			}

		},
	};

	BJS.init();

	/*FLBuilder.registerModuleHelper('event-text-snippets', {

		init: function(){
			alert();
			var form = $('.fl-builder-settings:visible'),
				js_copy_to_clipboard = form.find( '.js_copy_to_clipboard');

			js_copy_to_clipboard.on('click', this._copyToClipboard);
		},

		_copyToClipboard: function(){
			var $parent = $(this).parents('.js_shortcode_wrap'),
				$input = $parent.find('.js_shortcode'),
				text = $input.text(),
				$js_copy_notify = $parent.find('.js_copy_notify');

			if(navigator && navigator.clipboard && navigator.clipboard.writeText){
				return navigator.clipboard.writeText(text);
			}else{
				$input.select();
				document.execCommand('copy');
			}

			$js_copy_notify.fadeIn(200).delay(1000).fadeOut(200);
		},

	});*/


})(jQuery);
