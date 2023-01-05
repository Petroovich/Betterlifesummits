<div class="container-fluid my-3">

    <nav class="navbar navbar-expand-lg bg-light mb-3">
        <div class="container-fluid">
            <h1 class="navbar-brand m-0 p-0"><?=esc_html(get_admin_page_title());?> <span class="badge rounded-pill text-bg-secondary"><?=$results_count;?></span></h1>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse ms-3" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item mb-0 me-2">
                        <a class="btn btn-outline-primary btn-sm" href="/wp-admin/post-new.php?post_type=<?=$post_type;?>"><?=__('Add New');?></a>
                    </li>
                    <li class="nav-item mb-0">
                        <a class="btn btn-outline-secondary btn-sm" href="javascript:location.reload();"><?=__('Refresh');?></a>
                    </li>
                </ul>
                <form id="presentations_search_form" class="d-flex" role="search" method="get" action="<?=$_SERVER['PHP_SELF'];?>">
                    <input type="hidden" name="post_type" value="<?=$post_type;?>">
                    <input type="hidden" name="page" value="<?=$admin_page;?>">
                    <select name="limit" class="form-control-sm me-2 d-none">
                        <?php foreach($limit_select as $item): ?>
                            <option value="<?=$item['value'];?>" <?=$item['selected'];?>><?=$item['title'];?></option>
                        <?php endforeach; ?>
                    </select>
                    <input class="form-control-sm me-2" type="search" placeholder="<?=esc_attr__('Search');?>" aria-label="Search" name="s" value="<?=$args['s'];?>">
                    <button class="btn btn-outline-success btn-sm me-2" type="submit"><?=esc_attr__('Search', B3_TD);?></button>
                    <a class="btn btn-outline-secondary btn-sm" role="button" href="<?=$_SERVER['PHP_SELF'];?>?page=<?=$admin_page;?>"><?=__('Reset', B3_TD);?></a>
                </form>
            </div>
        </div>
    </nav>

    <nav aria-label="Page navigation">
        <?=$pagination_template;?>
    </nav>

    <div class="bootstrap form--table" data-example-id="hoverable-table">
		<?php if($results_count > 0): ?>
            <table class="wp-list-table widefat posts custom-table">
                <thead>
                    <tr>
                        <th class="text-center check-column"><input type="checkbox"/></th>
                        <th class="id-column"><?=__('ID', B3_TD);?></th>
                        <th class="day-column"><?=__('Day', B3_TD);?></th>
                        <th><?=__('Slot', B3_TD);?></th>
                        <th><?=__('Speaker', B3_TD);?></th>
                        <th><?=__('Headline', B3_TD);?></th>
                        <th><?=__('Speaker rank', B3_TD);?></th>
                        <th colspan="2" class="actions-column text-center"><?=__('Actions', B3_TD);?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th class="text-center check-column"><input type="checkbox"/></th>
                        <th class="id-column"><?=__('ID', B3_TD);?></th>
                        <th class="day-column"><?=__('Day', B3_TD);?></th>
                        <th><?=__('Slot', B3_TD);?></th>
                        <th><?=__('Speaker', B3_TD);?></th>
                        <th><?=__('Headline', B3_TD);?></th>
                        <th><?=__('Speaker rank', B3_TD);?></th>
                        <th colspan="2" class="actions-column text-center"><?=__('Actions', B3_TD);?></th>
                    </tr>
                </tfoot>
                <tbody>
				<?php foreach($data['groups'] as $term_id => $_posts):
                    $term = $data['terms'][$term_id];
                    $class = ($class == 'even') ? 'odd' : 'even';?>
                    <tr class="<?=$class;?>">
                        <td colspan="9" class="group-title"><?=$term->name;?>: <?=$term->description;?></td>
                    </tr>
                    <?php foreach($_posts as $post_id): $_post = $data['posts'][$post_id];?>
                    <tr id="js_row_<?=$_post->ID;?>" class="<?=$class;?>">
                        <td class="text-center check-column"><input type="checkbox" name="item_ids[]" value="<?=$_post->ID;?>"/></td>
                        <td class="data-col id-column" data-name="post_id"><?=$_post->ID;?></td>
                        <td class="data-col day-column" data-name="day_of_event" data-value="<?=$_post->cf['day_of_event']->term_id;?>"><?=$_post->cf['day_of_event']->name;?></td>
                        <td class="data-col" data-name="speaker_slot_of_the_day"><?=$_post->cf['speaker_slot_of_the_day'];?></td>
                        <td class="data-col" data-name="speaker_name"><?=$_post->cf['speaker_name'];?></td>
                        <td class="data-col" data-name="presentation_title"><?=$_post->cf['presentation_title'];?></td>
                        <td class="data-col" data-name="speaker_slot_of_the_event"><?=$_post->cf['speaker_slot_of_the_event'];?></td>
                        <td class="actions-column action-cell">
                            <a class="btn btn-outline-secondary btn-edit" href="javascript:void(0);"
                               data-trigger="js_action_click"
                               data-action="edit_presentation_item"
                               data-pid="<?=$_post->ID;?>"
                               data-bs-toggle="modal"
                               data-bs-target="#quickEdit"><?=__('Quick Edit')?></a>
                        </td>
                        <td class="actions-column action-cell"><a class="btn btn-outline-primary btn-edit" href="<?=admin_url('/post.php?post='.$_post->ID.'&action=edit');?>"><?=__('Edit')?></a></td>
                    </tr>
				    <?php endforeach;?>
				<?php endforeach;?>
                </tbody>
            </table>
		<?php endif; ?>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="quickEdit" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="" data-trigger="js_action_submit" data-action="quick_save_presentation_data">
                    <input type="hidden" name="post_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">Presentation Quick Edit [ID: <span id="js_presentation_id"></span>]</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row my-2">
                            <label class="col-sm-3 control-label bold" for="day_of_event"><?=__('Day', B3_TD);?></label>
                            <div class="col-sm-9">
                                <select id="day_of_event" name="day_of_event" class="form-control">
                                <?php foreach($terms as $term):?>
                                    <option value="<?=$term->term_id;?>"><?=$term->name;?></option>
                                <?php endforeach;?>
                                </select>
                            </div>
                        </div>
                        <div class="row my-2">
                            <label class="col-sm-3 control-label bold" for="speaker_slot_of_the_day"><?=__('Slot', B3_TD);?></label>
                            <div class="col-sm-9">
                                <input type="number" id="speaker_slot_of_the_day" name="speaker_slot_of_the_day" class="form-control" min="0">
                            </div>
                        </div>
                        <div class="row my-2">
                            <label class="col-sm-3 control-label bold" for="speaker_name"><?=__('Speaker', B3_TD);?></label>
                            <div class="col-sm-9">
                                <input type="text" id="speaker_name" name="speaker_name" class="form-control">
                            </div>
                        </div>
                        <div class="row my-2">
                            <label class="col-sm-3 control-label bold" for="presentation_title"><?=__('Headline', B3_TD);?></label>
                            <div class="col-sm-9">
                                <input type="text" id="presentation_title" name="presentation_title" class="form-control">
                            </div>
                        </div>
                        <div class="row my-2">
                            <label class="col-sm-3 control-label bold" for="speaker_slot_of_the_event"><?=__('Speaker rank', B3_TD);?></label>
                            <div class="col-sm-9">
                                <input type="number" id="speaker_slot_of_the_event" name="speaker_slot_of_the_event" class="form-control" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div id="js_modal_message" class="modal-message flex-grow-1"></div>
                        <button type="reset" class="btn btn-danger d-none"><?=__('Reset', B3_TD);?></button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?=__('Cancel', B3_TD);?></button>
                        <button type="submit" class="btn btn-primary"><?=__('Save', B3_TD);?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
