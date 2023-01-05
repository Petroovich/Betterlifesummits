(function($){
	'use strict';

	jQuery(document).ready(function($){

		let BJS = {
			options: {},
			vars: {ww: 0, wh: 0},
			labels: {},
			messages: {
				ajax_error: 'SYSTEM TECHNICAL ERROR'
			},
			routes: {
				save_presentation_post: 'save_presentation_post',
			},
			els: {
				edit_entry_modal: $('#quickEdit'),
			},
			Core: {
				Init: function(){
					BJS.options = $.extend(this.options, b3_globals);
					this.initEvents();
					this.eventResizeWindow();
					BJS.ACFModule.init();
					BJS.Category.init();
					BJS.Presentations.init();
				},
				initEvents: function(){
					$(window)
						.on('resize orientationchange deviceorientation', BJS.Core.eventResizeWindow);

					$(document)
						.on('blur', '[data-trigger="js_action_blur"]', BJS.Core.doAction)
						.on('change', '[data-trigger="js_action_change"]', BJS.Core.doAction)
						.on('click', '[data-trigger="js_action_click"]', BJS.Core.doAction)
						.on('submit', '[data-trigger="js_action_submit"]', BJS.Core.doAction);
				},
				eventResizeWindow: function(){
					BJS.vars.ww = $(window).width();
					BJS.vars.wh = $(window).height();
				},
				doAction: function(e){
					const $this = $(this),
						action = $(this).data('action');

					switch(action){
						case "edit_presentation_item":
							BJS.Presentations.edit($this);
							break;
						case "quick_save_presentation_data":
							BJS.Presentations.save($this);
							break;
						default:
							break;
					}

					e.preventDefault();
				},
			},
			Category: {
				init: function(){
					this.updateCategoryChecklist();
				},
				updateCategoryChecklist: function(){
					let $categorychecklist = $('#categorychecklist');
					if($categorychecklist.length){
						$categorychecklist.find('li').hide();
						$categorychecklist.find('li').each(function(i, li){
							let text = $(li).find('label').text();
							let a = text.split(' ');

							if(a[2] !== undefined){
								if(~~a[2] <= BJS.options.days){
									$(li).show();
									//console.log(text);
								}
							}
						});
					}
				},
			},
			ACFModule: {
				el: {
					day_of_event_field: null,
				},
				init: function(){
					this.initDayOfEventSelect2();
					this.initCategoryChecklistCheckbox();
				},
				initDayOfEventSelect2: function(){
					let div = $('div').find('div[data-name="day_of_event"]');

					if(div.length){
						BJS.ACFModule.el.day_of_event_field = acf.getField(div.data('key'));
						BJS.ACFModule.el.day_of_event_field.on('change', function(){
							//console.log(field.val());
							BJS.ACFModule.updateCategoryChecklist(BJS.ACFModule.el.day_of_event_field.val());
						});
					}
				},
				initCategoryChecklistCheckbox: function(){
					let $categorychecklist = $('#categorychecklist');
					if($categorychecklist.length){
						$categorychecklist.find('input[type="checkbox"]').on('change', BJS.ACFModule.changeCategoryChecklist);
					}
				},
				changeCategoryChecklist: function(){
					let $categorychecklist = $('#categorychecklist'),
						$this = $(this),
						value = $this.val();

					//console.log(value);

					$categorychecklist
						.find('input[type="checkbox"]:not(input[value="'+value+'"])')
						.prop('checked', false);

					BJS.ACFModule.updateDayOfEventSelect2(value);
				},
				updateCategoryChecklist: function(value){
					console.log(value);
					let $categorychecklist = $('#categorychecklist');

					if($categorychecklist.length){
						$categorychecklist
							.find('input[type="checkbox"]').prop('checked', false)
							.end()
							.find('input[value="'+value+'"]').prop('checked', true);
					}
				},
				updateDayOfEventSelect2: function(value){
					if(null != BJS.ACFModule.el.day_of_event_field){
						let text = $('#categorychecklist')
							.find('input[value="'+value+'"]')
							.parent('label')
							.text();

						text = $.trim(text);

						BJS.ACFModule.el.day_of_event_field.$el.trigger('click');
						let key = BJS.ACFModule.el.day_of_event_field.$el.data('key');
						//console.log(key);

						$('input[name="acf['+key+']"]').val(value);

						BJS.ACFModule.el.day_of_event_field.$el.val(value);
						BJS.ACFModule.el.day_of_event_field.$el.find('select').val(value);
						BJS.ACFModule.el.day_of_event_field.$el.find('.acf-selection').text(text);
					}else{
						console.log('The day_of_event_field is null.');
					}
				},
			},
			Presentations: {
				form: null,
				init: function(){
					BJS.Presentations.form = BJS.els.edit_entry_modal.find('form');
				},
				save: function($form){
					//console.log($form.serializeArray());

					$.ajax({
						type: "POST",
						url: BJS.options.ajax_url,
						data: {
							'action': BJS.routes.save_presentation_post,
							'nonce': BJS.options.nonce,
							'form_data': $form.serialize()
						},
						dataType: "json"
					}).done(function(response){
						if(response.success === true){
							$form.find('#js_modal_message').html(response.data.message);
							setTimeout(function(){
								BJS.els.edit_entry_modal.modal('hide');
								window.location.reload();
							}, 2000);
						}else{
							$form.find('#js_modal_message').html(response.data.message);
						}
					}).fail(function(){
						console.log("SYSTEM TECHNICAL ERROR");
					});
				},
				edit: function($button){
					let entry_id = $button.data('pid');

					this.fillForm(entry_id);
					BJS.Presentations.form.find('.form-control, [type="reset"], [type="submit"]').removeClass('invisible');
				},
				clearForm: function(){
					$('#js_presentation_id').text('');
					$('#js_modal_message').html('');
					BJS.Presentations.form.find('[type="reset"]').trigger('click');
				},
				fillForm: function(entry_id){
					this.clearForm();

					let $form = BJS.Presentations.form,
						$parent_row = $('tr#js_row_'+entry_id);

					$('#js_presentation_id').text(entry_id);

					$parent_row.find('td.data-col').each(function(){
						let name = $(this).data('name'),
							value = $(this).html(),
							value2 = $(this).data('value'),
							$el = $form.find('[name="'+name+'"]'),
							$l_el = $form.find('[data-name="'+name+'"]');

						//console.log(name, $el.length, $el.prop('type'), value, $l_el.length);

						$l_el.html(value);

						if($el.prop('type') != 'file'){
							if(value2 !== undefined){
								$el.val(value2);
							}else{
								$el.val(value);
							}
							if($el.prop('type') == 'select-one'){
								$el.trigger('change');
							}
							if($el.prop('type') == 'checkbox'){
								let checked = (~~value == 1) ? true : false;
								$el.attr('checked', checked);
							}
						}
					});
				},
			},
		};

		BJS.Core.Init();
	});

})(jQuery);
