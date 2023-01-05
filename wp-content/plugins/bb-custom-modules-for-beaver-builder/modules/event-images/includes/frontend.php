<?php


$photo = $module->get_data();
$classes = $module->get_classes();
$src = $module->get_src();
$link = $module->get_link();
$alt = $module->get_alt();
$attrs = $module->get_attributes();
$filetype = pathinfo($src, PATHINFO_EXTENSION);
$rel = $module->get_rel();
$caption = $module->get_caption();

?>
<div class="fl-photo<?=(!empty($settings->crop)) ? ' fl-photo-crop-'.$settings->crop : ''; ?> fl-photo-align-<?=$settings->align; ?>"<?php FLBuilder::print_schema(' itemscope itemtype="https://schema.org/ImageObject"'); ?>>
    <div class="fl-photo-content fl-photo-img-<?=$filetype; ?>">
		<?php if(!empty($link)) : ?>
        <a href="<?=$link; ?>" target="<?=$settings->link_url_target; ?>"<?=$rel; ?> itemprop="url">
        <?php endif; ?>
            <img class="<?=$classes; ?>" src="<?=$src; ?>" alt="<?=$alt; ?>" itemprop="image" <?=$attrs; ?> />
        <?php if(!empty($link)) : ?>
        </a>
	    <?php endif; ?>
		<?php if('hover' === $settings->show_caption) : ?>
            <div class="fl-photo-caption fl-photo-caption-hover" itemprop="caption"><?=$caption; ?></div>
		<?php endif; ?>
    </div>
	<?php if('below' === $settings->show_caption) : ?>
        <div class="fl-photo-caption fl-photo-caption-below" itemprop="caption"><?=$caption; ?></div>
	<?php endif; ?>
</div>
