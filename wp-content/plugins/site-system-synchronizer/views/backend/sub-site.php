<?php
use SiteSystemSynchronizer\Core;
use SiteSystemSynchronizer\Synchronizer;
use SiteSystemSynchronizer\Helper;
?>
<div class="container-fluid my-3">
    <div class="js_ajax_message loader"><?=esc_attr__('Sending request...', S3_TD);?></div>

	<nav class="navbar navbar-expand-lg bg-light mb-3">
		<div class="container-fluid d-block">
			<h1 class="navbar-brand m-0 p-0"><?=__('Sync Options', S3_TD);?></h1>
			<small class="d-block"><?=__('You can enable or disable updating data, plugins during synchronization', S3_TD);?></small>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
		</div>
	</nav>
	
	<div class="bg-light pt-2 px-4 pb-4">
		<form method="post" action="options.php" class="options">
			<?php
			/*settings_fields(Core::getSlug());
			do_settings_sections(\SiteSystemSynchronizer\SiteSystemSynchronizer::$plugin_slug);
			submit_button();*/
			?>
		</form>
		<form method="post" action="#" data-trigger="js_action_submit" data-action="save_sub_site_options">
			<div class="body">
				<div class="my-2">
					<label class="control-label bold" for="sync_global">
						<input type="checkbox" id="sync_global" name="sync_global" class="form-control" <?php if($options['sync_global'] == 'on'):?>checked<?php endif;?> />
						<?=__('Global Synchronization', S3_TD);?>
					</label>
				</div>
				<div class="bold mb-3 mt-4"><?=__('Synchronize Custom posts, templates and settings', S3_TD);?></div>
				<div class="my-2">
                    <?php foreach($elements as $el_id => $element):?>
                        <?php if($el_id == 'plugins_list'):?>
		                    <div class="bold mb-3 mt-4"><?=__('Synchronize plugins', S3_TD);?></div>
                            <?php foreach($element as $p_id => $plugin):?>
                                <label class="control-label bold small d-block">
                                    <input type="checkbox" id="<?=$p_id;?>" name="sync_items[plugins_list][<?=$p_id;?>]" class="form-control" <?php if(isset($options['sync_items']['plugins_list'][$p_id]) && $options['sync_items']['plugins_list'][$p_id] == 'on'):?>checked<?php endif;?> />
									<?=$plugin;?>
                                </label>
							<?php endforeach;?>
                        <?php else:?>
                            <label class="control-label bold small d-block">
                                <input type="checkbox" id="<?=$el_id;?>" name="sync_items[<?=$el_id;?>]" class="form-control" <?php if(isset($options['sync_items'][$el_id]) && $options['sync_items'][$el_id] == 'on'):?>checked<?php endif;?> />
                                <?=$element;?>
                            </label>
                        <?php endif;?>
                    <?php endforeach;?>
				</div>
			</div>
			<div class="footer">
				<div id="js_modal_message" class="modal-message flex-grow-1"></div>
				<button type="submit" class="btn btn-outline-primary"><?=__('Save', S3_TD);?></button>
			</div>
		</form>
	</div>

</div>
