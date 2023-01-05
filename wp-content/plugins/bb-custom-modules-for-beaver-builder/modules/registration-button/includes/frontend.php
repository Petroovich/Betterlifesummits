<?php

$button_node_id = "fl-node-$id";

if(isset($settings->id) && !empty($settings->id)){
	$button_node_id = $settings->id;
}

$btn_classes = ['fl-button', $settings->css_class];
if('enable' == $settings->icon_animation){
    $btn_classes[] = 'fl-button-icon-animation';
}
$btn_classes = implode(' ', $btn_classes);
?>
<div class="<?=$module->get_classname();?>">
    <a href="<?=$settings->link;?>" class="<?=$btn_classes;?>" data-featherlight="#registration_popup" data-featherlight-variant="fixwidth" role="button" <?=$module->get_rel();?>>
		<?php if(!empty($settings->icon) && ('before' == $settings->icon_position || !isset($settings->icon_position))) :?>
            <i class="fl-button-icon fl-button-icon-before <?=$settings->icon;?>" aria-hidden="true"></i>
		<?php endif; ?>
		<?php if(!empty($settings->text)) : ?>
            <span class="fl-button-text"><?=$settings->text;?></span>
		<?php endif; ?>
		<?php if(!empty($settings->icon) && 'after' == $settings->icon_position) :?>
            <i class="fl-button-icon fl-button-icon-after <?=$settings->icon;?>" aria-hidden="true"></i>
		<?php endif; ?>
    </a>
</div>
