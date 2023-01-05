<?php
use SiteSystemSynchronizer\Synchronizer;
use SiteSystemSynchronizer\Helper;
?>
<div class="container-fluid my-3">
    <div class="js_ajax_message loader"><?=esc_attr__('Sending request...', S3_TD);?></div>

	<nav class="navbar navbar-expand-lg bg-light mb-3">
		<div class="container-fluid">
			<h1 class="navbar-brand m-0 p-0"><?=__('Sub sites', S3_TD);?> <?=esc_html(get_admin_page_title());?> <span class="badge rounded-pill text-bg-secondary"><?=$results_count;?></span></h1>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse ms-3" id="navbarSupportedContent">
				<ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-center">
					<li class="nav-item mb-0 me-2">
						<a class="btn btn-outline-primary btn-sm" href="javascript:void(0);" data-trigger="js_action_click" data-action="add_sub_site" data-bs-toggle="modal" data-bs-target="#formModal"><?=__('Add New');?></a>
					</li>
					<li class="nav-item mb-0 me-2">
						<a role="button" class="btn btn-outline-secondary btn-sm" href="javascript:location.reload();"><?=__('Refresh');?></a>
					</li>
					<li class="nav-item mb-0 me-2">
                        <a role="button"
                           class="btn btn-outline-success btn-sm"
                           href="javascript:void(0);"
                           data-trigger="js_action_click"
                           data-action="start_sync_all"><?=__('Sync all');?></a>
					</li>
					<li class="nav-item mb-0">
                        <div id="js_result_info" class="result-message"></div>
					</li>
				</ul>
				<form id="subsites_search_form" class="d-none" role="search" method="get" action="<?=$_SERVER['PHP_SELF'];?>">
					<input type="hidden" name="post_type" value="<?=$post_type;?>">
					<input type="hidden" name="page" value="<?=$admin_page;?>">
					<select name="limit" class="form-control-sm me-2 d-none">
						<?php foreach($limit_select as $item): ?>
							<option value="<?=$item['value'];?>" <?=$item['selected'];?>><?=$item['title'];?></option>
						<?php endforeach; ?>
					</select>
					<input class="form-control-sm me-2" type="search" placeholder="<?=esc_attr__('Search');?>" aria-label="Search" name="s" value="<?=$args['s'];?>">
					<button class="btn btn-outline-success btn-sm me-2" type="submit"><?=esc_attr__('Search', S3_TD);?></button>
					<a class="btn btn-outline-secondary btn-sm" role="button" href="<?=$_SERVER['PHP_SELF'];?>?page=<?=$admin_page;?>"><?=__('Reset', S3_TD);?></a>
				</form>
			</div>
		</div>
	</nav>
	
	<nav aria-label="Page navigation">
		<?=$pagination_template;?>
	</nav>
	
	<div class="bootstrap form--table" data-example-id="hoverable-table">
		<?php if($results_count > 0): ?>
			<table class="wp-list-table widefat posts custom-table-s">
				<thead>
                    <tr>
                        <th class="text-center check-column"><input type="checkbox"/></th>
                        <th class="id-column"><?=__('ID', S3_TD);?></th>
                        <th><?=__('Title', S3_TD);?></th>
                        <th><?=__('URL', S3_TD);?></th>
                        <th><?=__('API Path', S3_TD);?></th>
                        <th><?=__('Synchronize', S3_TD);?></th>
                        <th><?=__('Sync status', S3_TD);?></th>
                        <th colspan="3" class="actions-column text-center"><?=__('Actions', S3_TD);?></th>
                    </tr>
				</thead>
				<tfoot>
                    <tr>
                        <th class="text-center check-column"><input type="checkbox"/></th>
                        <th class="id-column"><?=__('ID', S3_TD);?></th>
                        <th><?=__('Title', S3_TD);?></th>
                        <th><?=__('URL', S3_TD);?></th>
                        <th><?=__('API Path', S3_TD);?></th>
                        <th><?=__('Synchronize', S3_TD);?></th>
                        <th><?=__('Sync status', S3_TD);?></th>
                        <th colspan="3" class="actions-column text-center"><?=__('Actions', S3_TD);?></th>
                    </tr>
				</tfoot>
				<tbody>
                <?php foreach($data['posts'] as $_post):
                    $class = ($class == 'even') ? 'odd' : 'even';
                    #Helper::_debug($_post->metas);?>
					<tr id="js_row_<?=$_post->ID;?>" class="<?=$class;?>" data-elements='<?=Synchronizer::toJsonElementsData($_post->metas['elements']);?>'>
						<td class="text-center check-column"><input type="checkbox" name="item_ids[]" value="<?=$_post->ID;?>"/></td>
						<td class="data-col id-column" data-name="post_id"><?=$_post->ID;?></td>
						<td class="data-col" data-name="post_title"><?=$_post->post_title;?></td>
						<td class="data-col" data-name="site_url"><?=$_post->metas['site_url'];?></td>
						<td class="data-col" data-name="api_path"><?=$_post->metas['api_path'];?></td>
						<td class="data-col js_sync_me" data-name="sync_me"><?=$_post->metas['sync_me'];?></td>
						<td class="data-col"><div class="js_sync_status"></div><div class="js_sync_message"></div></td>
                        <td class="actions-column action-cell">
                            <?php if($_post->metas['sync_me'] == 'on'):?>
                            <a role="button"
                               class="btn btn-outline-success btn-edit"
                               href="javascript:void(0);"
                               data-trigger="js_action_click"
                               data-action="start_sync_process"
                               data-pid="<?=$_post->ID;?>"><?=__('Sync');?></a>
                            <?php endif;?>
                        </td>
						<td class="actions-column action-cell">
							<a role="button"
                               class="btn btn-outline-secondary btn-edit"
                               href="javascript:void(0);"
							   data-trigger="js_action_click"
							   data-action="edit_sub_site"
							   data-pid="<?=$_post->ID;?>"
							   data-bs-toggle="modal"
							   data-bs-target="#formModal"><?=__('Edit')?></a>
						</td>
						<td class="actions-column action-cell">
                            <a role="button"
                               class="btn btn-outline-danger btn-edit"
                               href="javascript:void(0);"
                               data-trigger="js_action_click"
                               data-action="delete_sub_site"
                               data-pid="<?=$_post->ID;?>"
                               data-title="<?=$_post->post_title;?>"><?=__('Delete')?></a>
                        </td>
					</tr>
                <?php endforeach;?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	
	<!-- Modal -->
	<div class="modal fade" id="formModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="js_modal_title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<form method="post" action="" data-trigger="js_action_submit" data-action="save_sub_site">
					<input type="hidden" name="post_id" value="0">
					<div class="modal-header">
						<h5 class="modal-title" id="js_modal_title"><?=__('Add new sub site', S3_TD);?></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
                        <div class="row my-2">
                            <label class="col-sm-3 control-label bold" for="site_url"><?=__('Site Title', S3_TD);?></label>
                            <div class="col-sm-9">
                                <input type="text" id="post_title" name="post_title" class="form-control" placeholder="Site name">
                            </div>
                        </div>
                        <div class="row my-2">
                            <label class="col-sm-3 control-label bold" for="site_url"><?=__('Site full URL', S3_TD);?></label>
                            <div class="col-sm-9">
                                <input type="text" id="site_url" name="site_url" class="form-control" placeholder="<?=__('https://www.example.com');?>" required>
                            </div>
                        </div>
                        <div class="row my-2">
                            <label class="col-sm-3 control-label bold" for="api_path"><?=__('API Path', S3_TD);?></label>
                            <div class="col-sm-9">
                                <input type="text" id="api_path" name="api_path" class="form-control" value="<?=S3_API_ENDPOINTS_PATH;?>" required readonly>
                            </div>
                        </div>
						<div class="row my-2">
							<label class="col-sm-3 control-label bold" for="sync_me"><?=__('Synchronize', S3_TD);?></label>
							<div class="col-sm-9">
								<input type="checkbox" id="sync_me" name="sync_me" class="form-control" checked>
							</div>
						</div>
						<div class="row my-2">
							<label class="col-sm-3 control-label bold"><?=__('Synchronize Custom items', S3_TD);?></label>
							<div class="col-sm-9">
                                <?php foreach($elements as $el_id => $element):?>
                                    <?php if($el_id == 'plugins_list'):?>
                                        <?php foreach($element as $p_id => $plugin):?>
                                            <label class="control-label bold small d-block">
                                                <input type="checkbox" id="<?=$p_id;?>" name="elements[plugins_list][<?=$p_id;?>]" class="form-control">
												<?=__('Plugin');?> "<?=$plugin;?>"
                                            </label>
										<?php endforeach;?>
                                    <?php else:?>
                                        <label class="control-label bold small d-block">
                                            <input type="checkbox" id="<?=$el_id;?>" name="elements[<?=$el_id;?>]" class="form-control">
                                            <?=$element;?>
                                        </label>
                                    <?php endif;?>
                                <?php endforeach;?>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<div id="js_modal_message" class="modal-message flex-grow-1"></div>
						<button type="reset" class="btn btn-outline-danger d-none"><?=__('Reset', S3_TD);?></button>
						<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?=__('Cancel', S3_TD);?></button>
						<button type="submit" class="btn btn-outline-primary"><?=__('Save', S3_TD);?></button>
					</div>
				</form>
			</div>
		</div>
	</div>

</div>
