(function($){
	'use strict';

	jQuery(document).ready(function($){

		let BJS = {
			options: {},
			vars: {
				ww: 0,
				wh: 0,
			},
			labels: {},
			messages: {
				ajax_error: 'SYSTEM TECHNICAL ERROR'
			},
			routes: {},
			els: {
				body: $('body')
			},
			Core: {
				Init: function(){
					this.initEvents();
					this.eventResizeWindow();
					BJS.Builder.init();
				},
				initEvents: function(){
					$(window)
						.on('resize orientationchange deviceorientation', BJS.Core.eventResizeWindow);

					$(document)
						.on('blur', '[data-trigger="js_action_blur"]', BJS.Core.doAction)
						.on('change', '[data-trigger="js_action_change"]', BJS.Core.doAction)
						.on('click', '[data-trigger="js_action_click"]', BJS.Core.doAction)
						.on('submit', '[data-trigger="js_action_submit"]', BJS.Core.doAction)
						.on('click', '#js_shortcodes_panel_toggle', BJS.Builder.toggleShortcodesPanel);
				},
				eventResizeWindow: function(){
					BJS.vars.ww = $(window).width();
					BJS.vars.wh = $(window).height();
				},
				doAction: function(e){
					const $this = $(this),
						action = $(this).data('action');

					switch(action){
						case "copy_to_clipboard":
							BJS.Builder.copyToClipboard($this);
							break;
						default:
							break;
					}

					e.preventDefault();
				},
			},
			Builder: {
				init: function(){
					this.replaceCharsInDescs();
				},
				toggleShortcodesPanel: function(){
					BJS.els.body.toggleClass('fl-builder-shortcodes-panel-is-showing');
					BJS.Builder.initScrollbars();
				},
				copyToClipboard: function($btn){
					let $parent = $btn.parents('.js_shortcode_wrap'),
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
				replaceCharsInDescs: function(){
					$('.js_field_description').each(function(i, el){
						let text = $(el).text();
						text = text.replace('[#', '[');
						text = text.replace('#]', ']');
						//console.log(text);
						$(el).text(text);
					});
				},
				initScrollbars: function(){
					var scrollers = $('.fl-nanoscroller').nanoScroller({
							alwaysVisible: true,
							preventPageScrolling: true,
							paneClass: 'fl-nanoscroller-pane',
							sliderClass: 'fl-nanoscroller-slider',
							contentClass: 'fl-nanoscroller-content'
						}),
						settingsScroller = scrollers.filter('.fl-builder-settings-fields'),
						pane = settingsScroller.find('.fl-nanoscroller-pane');

					if(pane.length){
						var display = pane.get(0).style.display;
						var content = settingsScroller.find('.fl-nanoscroller-content');

						if(display === "none"){
							content.removeClass('has-scrollbar');
						}else{
							content.addClass('has-scrollbar');
						}
					}
				},
			},
		};

		BJS.Core.Init();
	});

})(jQuery);
