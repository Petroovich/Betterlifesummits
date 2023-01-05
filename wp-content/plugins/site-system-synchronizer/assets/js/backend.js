(function($){
	'use strict';

	jQuery(document).ready(function($){

		const BJS = {
			options: {},
			vars: {},
			labels: {},
			messages: {
				delete_confirm: 'Are you sure you want to remove this site "{title}"?',
				ajax_error: 'SYSTEM TECHNICAL ERROR',
			},
			routes: {
				post_to_subsites: "post_to_subsites",
				save_sub_site_post: "save_sub_site_post",
				delete_sub_site_post: "delete_sub_site_post",
				save_sub_site_options: "save_current_site_options",
			},
			els: {
				edit_entry_modal: $('#formModal'),
				js_result_info: $('#js_result_info'),
			},
			Core: {
				Init: function(){
					BJS.options = $.extend(this.options, s3_globals);

					this.initEvents();
					BJS.SubSitePosts.init();
				},
				initEvents: function(){
					$(window)
						.on('scroll', BJS.Core.eventScrollWindow)
						.on('resize orientationchange deviceorientation', BJS.Core.eventResizeWindow);

					$(document)
						.on('blur', '[data-trigger="js_action_blur"]', BJS.Core.doAction)
						.on('change', '[data-trigger="js_action_change"]', BJS.Core.doAction)
						.on('click', '[data-trigger="js_action_click"]', BJS.Core.doAction)
						.on('submit', '[data-trigger="js_action_submit"]', BJS.Core.doAction);
				},
				doAction: function(e){
					const $this = $(this),
						action = $(this).data('action');

					switch(action){
						case "start_sync_all":
							BJS.API.startSyncAllProcess($this);
							break;
						case "start_sync_process":
							//console.log($this);
							BJS.API.startSyncProcess($this);
							break;
						case "add_sub_site":
							BJS.SubSitePosts.add($this);
							break;
						case "edit_sub_site":
							BJS.SubSitePosts.edit($this);
							break;
						case "save_sub_site":
							BJS.SubSitePosts.save($this);
							break;
						case "delete_sub_site":
							BJS.SubSitePosts.delete($this);
							break;
						case "save_sub_site_options":
							BJS.SubSite.saveOptions($this);
							break;
						default:
							break;
					}

					e.preventDefault();
				},
			},
			Common: {},
			API: {
				startSyncAllProcess: function($btn){
					$btn.attr('disabled', true).addClass('loader2 disabled').text('Processing...');

					let i = 0;

					$('[data-action="start_sync_process"]').each(function(){
						let $sbtn = $(this);

						setTimeout(function(){
							BJS.API.startSyncProcess($sbtn);
						}, i);

						i += 2000;

						//$sbtn.trigger('click');
						//$sbtn.attr('disabled', true).addClass('loader2 disabled').text('Processing...');
					});

					$btn.attr('disabled', false).removeClass('loader2 disabled').text('Sync All');
				},
				startSyncProcess: function($btn){
					let subsite_id = $btn.data('pid');

					$('#js_row_'+subsite_id)
						.find('.js_sync_status').removeClass('ok fail')
						.end()
						.find('.js_sync_message').removeClass('ok fail').text('');

					BJS.els.js_result_info.text('');

					$.ajax({
						type: "POST",
						url: BJS.options.ajax_url,
						data: {
							'action': BJS.routes.post_to_subsites,
							'nonce': BJS.options.nonce,
							'subsite_id': subsite_id,
						},
						dataType: "json",
						beforeSend: function(xhr){
							$btn.attr('disabled', true).addClass('loader2 disabled').text('Processing...');
						}
					}).done(function(response){
						BJS.els.js_result_info.html(response.data.message);

						if(response.success === true){
							console.log(response.data.results);
							$.each(response.data.results, function(entry_id, result){
								console.log(entry_id, result);
								$('#js_row_'+entry_id)
									.find('.js_sync_status').addClass(result.check_api.status)
									.end()
									.find('.js_sync_message').addClass(result.check_api.status).html(result.check_api.message);
							});
						}

						$btn.removeClass('loader2');
						$btn.attr('disabled', false).removeClass('loader2 disabled').text('Sync');
					}).fail(function(){
						BJS.els.js_result_info.html(response.data.message);

						$btn.removeClass('loader2');
						$btn.attr('disabled', false).removeClass('loader2 disabled').text('Sync');
					});
				},
			},
			SubSitePosts: {
				form: null,
				init: function(){
					BJS.SubSitePosts.form = BJS.els.edit_entry_modal.find('form');
				},
				add: function($btn){
					this.clearForm();

					$('#js_modal_title').text('Add new sub site');
				},
				edit: function($btn){
					let entry_id = $btn.data('pid');

					this.fillForm(entry_id);

					$('#js_modal_title').text('Edit sub site [ID: '+entry_id+']');

					BJS.SubSitePosts.form.find('.form-control, [type="reset"], [type="submit"]').removeClass('invisible');
				},
				save: function($form){
					//console.log($form.serializeArray());

					$.ajax({
						type: "POST",
						url: BJS.options.ajax_url,
						data: {
							'action': BJS.routes.save_sub_site_post,
							'nonce': BJS.options.nonce,
							'form_data': $form.serialize()
						},
						dataType: "json"
					}).done(function(response){
						BJS.els.js_result_info.html(response.data.message);
						$form.find('#js_modal_message').html(response.data.message);

						if(response.success === true){
							setTimeout(function(){
								BJS.els.edit_entry_modal.modal('hide');
								window.location.reload();
							}, 2000);
						}
					}).fail(function(){
						console.log("SYSTEM TECHNICAL ERROR");
					});
				},
				delete: function($btn){
					let title = $btn.data('title'),
						entry_id = $btn.data('pid');

					if(!confirm(BJS.messages.delete_confirm.replace('{title}', title))){
						return false;
					}

					$.ajax({
						type: "POST",
						url: BJS.options.ajax_url,
						data: {
							'action': BJS.routes.delete_sub_site_post,
							'nonce': BJS.options.nonce,
							'post_id': entry_id,
						},
						dataType: "json"
					}).done(function(response){
						BJS.els.js_result_info.html(response.data.message);

						if(response.success === true){
							setTimeout(function(){
								window.location.reload();
							}, 2000);
						}
					}).fail(function(){
						console.log("SYSTEM TECHNICAL ERROR");
					});
				},
				clearForm: function(){
					$('#js_modal_message').html('');
					BJS.SubSitePosts.form.find('[type="reset"]').trigger('click');
				},
				fillForm: function(entry_id){
					this.clearForm();

					let $form = BJS.SubSitePosts.form,
						$parent_row = $('tr#js_row_'+entry_id),
						elements = $parent_row.data('elements');

					//console.log(JSON.parse(elements));
					//console.log(elements);

					$('#js_presentation_id').text(entry_id);

					$parent_row.find('td.data-col').each(function(){
						let name = $(this).data('name'),
							value = $(this).html(),
							value2 = $(this).data('value'),
							$el = $form.find('[name="'+name+'"]'),
							$l_el = $form.find('[data-name="'+name+'"]');

						//console.log(name, $el.length, $el.prop('type'), value, $l_el.length);

						$l_el.html(value);

						if($el.prop('type') !== 'file'){
							if(value2 !== undefined){
								$el.val(value2);
							}else{
								$el.val(value);
							}
							if($el.prop('type') === 'select-one'){
								$el.trigger('change');
							}
							if($el.prop('type') === 'checkbox'){
								let checked = (value === 'on');
								$el.prop('checked', checked);
							}
						}
					});

					$.each(elements, function(k, value){
						//console.log(k, value);
						if(k === 'plugins_list'){
							$.each(value, function(k2, value2){
								let checked = (value2 === 'on');
								$form.find('#'+k2).prop('checked', checked);
							});
						}else{
							let checked = (value === 'on');
							$form.find('#'+k).prop('checked', checked);
						}
					});
				},
			},
			SubSite: {
				saveOptions: function($form){
					console.log($form.serializeArray());

					$.ajax({
						type: "POST",
						url: BJS.options.ajax_url,
						data: {
							'action': BJS.routes.save_sub_site_options,
							'nonce': BJS.options.nonce,
							'form_data': $form.serialize()
						},
						dataType: "json"
					}).done(function(response){
						BJS.els.js_result_info.html(response.data.message);
						$form.find('#js_modal_message').html(response.data.message);

						if(response.success === true){
							setTimeout(function(){
								$form.find('#js_modal_message').text('');
								//window.location.reload();
							}, 2000);
						}
					}).fail(function(){
						console.log("SYSTEM TECHNICAL ERROR");
					});
				},
			},
		};

		BJS.Core.Init();
	});

})(jQuery);
