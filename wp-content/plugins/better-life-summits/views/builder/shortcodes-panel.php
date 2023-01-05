<div id="js_shortcodes_panel" class="fl-builder-shortcodes-panel ui-draggable ui-resizable fl-builder-ui-pinned-container">
    <div class="fl-builder--panel-arrow" style="right: auto; left: 50%;">
        <svg width="29px" height="15px" viewBox="0 0 29 15" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
            <g transform="translate(-260.000000, -14.000000)">
                <polygon transform="translate(274.142136, 28.142136) rotate(-315.000000) translate(-274.142136, -28.142136) " points="264.142136 18.1421356 284.142136 18.1421356 264.142136 38.1421356"></polygon>
            </g>
        </svg>
    </div>
    <div class="header">
        <div class="drag-handle">
            <svg viewBox="0 0 6 30" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <g fill-rule="nonzero">
                    <polygon points="0 2 6 2 6 0 0 0"></polygon>
                    <polygon points="0 6 6 6 6 4 0 4"></polygon>
                    <polygon points="0 10 6 10 6 8 0 8"></polygon>
                    <polygon points="0 14 6 14 6 12 0 12"></polygon>
                    <polygon points="0 18 6 18 6 16 0 16"></polygon>
                    <polygon points="0 22 6 22 6 20 0 20"></polygon>
                    <polygon points="0 26 6 26 6 24 0 24"></polygon>
                    <polygon points="0 30 6 30 6 28 0 28"></polygon>
                </g>
            </svg>
        </div>
        <div class="fl-builder--tabs ui-draggable-handle">
            <div class="fl-builder--tab-wrap">
                <div class="title">Helps, custom modules and shortcodes</div>
            </div>
        </div>
    </div>
    <div class="fl-builder--panel-content">

        <div data-tab="modules" class="fl-builder--panel-view fl-nanoscroller is-showing has-scrollbar">
            <div class="fl-nanoscroller-content" tabindex="0" style="right: -17px;">
                <div class="builder-blocks-section-content ui-sortable">

                    <?php foreach($shortcodes_list as $item):?>
                        <div class="shortcode-item">
                            <label><?=$item['label'];?></label>
                            <div class="js_shortcode_wrap shortcode-input-wrap">
                                <div class="d-flex flex-row flex-nowrap justify-space-between">
                                    <input type="text" value="<?=$item['shortcode'];?>" class="shortcode" <?=$item['readonly'];?> />
                                    <a role="button" title="Click to copy shortcode" data-trigger="js_action_click" data-action="copy_to_clipboard" class="d-block pl-10 cursor-pointer"><svg xmlns="http://www.w3.org/2000/svg" class="d-block h-38 w-38 active:bg-blue active:color-white color-blue radius-5" viewBox="0 0 20 20" fill="currentColor"><path d="M7 9a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9z"></path><path d="M5 3a2 2 0 00-2 2v6a2 2 0 002 2V5h8a2 2 0 00-2-2H5z"></path></svg></a>
                                </div>
                                <div class="js_copy_notify copy-notify radius-5 mt-5 bg-blue">Shortcode copied</div>
                            </div>
                            <div class="js_field_description field-description"><?=$item['description'];?></div>
                        </div>
                    <?php endforeach;?>

                </div>
            </div>
            <div class="fl-nanoscroller-pane">
                <div class="fl-nanoscroller-slider"></div>
            </div>
        </div>
    </div>
</div>
