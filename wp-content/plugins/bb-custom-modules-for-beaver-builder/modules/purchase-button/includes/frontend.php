<?php

$button_node_id = "fl-node-$id";

if(isset($settings->id) && !empty($settings->id)){
	$button_node_id = $settings->id;
}

$btn_classes = ['fl-button', 'cta_btn', 'buybtn', $settings->css_class];
if('enable' == $settings->icon_animation){
    $btn_classes[] = 'fl-button-icon-animation';
}
$btn_classes = implode(' ', $btn_classes);

if($settings->click_action == 'auto'){
	//START define times used for timing logic

	//current time of wordpress installation using time zone set in general settings
	$current_wp_time = date('Y-m-d H:i:s', current_time('timestamp', 0));
	$current_wp_time_dateonly = date('Ymd', strtotime($current_wp_time));
	//echo '$current_wp_time ' . $current_wp_time;
	//echo '$current_wp_time_dateonly ' . $current_wp_time_dateonly;

	//deduct 8 hours from current time so participants who join late night Friday can still get early bird, 8 hours deducted from a summit start time of 11am is 3am Eastern and midnight Pacific
	$deduct_8_hours = '-3hours';
	$current_wp_time_minus8hours = date('Y-m-d H:i:s', strtotime($deduct_8_hours, strtotime($current_wp_time)));
	$current_wp_time_minus8hours_dateonly = date('Ymd', strtotime($current_wp_time_minus8hours));
	//echo '$current_wp_time_minus8hours ' . $current_wp_time_minus8hours;
	//echo '$current_wp_time_minus8hours_dateonly ' . $current_wp_time_minus8hours_dateonly;

	//Get start date of summit from ACF options
	$summit_start_date = get_field('summit_start_date', 'option');
	$summit_start_date_dateonly = date('Ymd', strtotime($summit_start_date));
	//echo '$summit_start_date ' . $summit_start_date;
	//echo '$summit_start_date_dateonly ' . $summit_start_date_dateonly;

	//END define times used for logic

	$early_bird_cart_link = get_field('early_bird_cart_link', 'option');
	$digital_package_cart_link = get_field('digital_package_cart_link', 'option');

	$customer_email = $_GET['email'];
	$referral = $_GET['referral'];

	if(($current_wp_time_minus8hours_dateonly < $summit_start_date_dateonly)){
		$link = $early_bird_cart_link.'/?passthrough[customer_email]='.$customer_email.'&ref='.$referral;
	}else{
		$link = $digital_package_cart_link.'/?passthrough[customer_email]='.$customer_email.'&ref='.$referral;
	}
}elseif($settings->click_action == 'link'){
    $link = $settings->link;
}
?>
<?php if($settings->click_action != 'shortcode'):?>
    <div class="<?=$module->get_classname();?>">
        <a href="<?=$link;?>" class="<?=$btn_classes;?>" role="button" <?=$module->get_rel();?>>
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
<?php else:?>
    <?=do_shortcode($settings->shortcode);?>
<?php endif;?>
