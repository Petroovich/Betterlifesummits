<?php
global $wp_query,$paged,$post;

$temp = $wp_query;
$category = get_term_by('slug', $settings->category, 'category');

$args = [
	'post_type'      => 'presentations',
	'posts_per_page' => ($settings->posts_per_page) ?: 6,
	'cat' => $category->term_id,
	'orderby' => $settings->orderby,
	'order' => $settings->order,
	'include_custom_fields' => true,
];
if($settings->orderby == 'meta_value'){
	$args['meta_key'] = $settings->meta_key;
}
$wp_query = BBCMDataSource::getPosts($args);
?>
<?php if(have_posts()):?>
<div class="show-presentations-schedule-day parent speakers_<?=$category->count?>">
    <?php while (have_posts()) : the_post();?>
        <div class="card">
            <div class="speaker_info">
                <img class="card_image" src="/wp-content/uploads/manualuploads/speakers216px/<?=$post->cf['speaker_profile_picture'];?>" alt="summit">
				<?php if($settings->summit_delivered_live == "yes"):
					$scheduledtime_pacific = $post->cf['speaker_slot_of_the_day'] + 7; //start at 8am = 7 + 1 for first presentation, etc.
					$scheduledtime_eastern = $post->cf['speaker_slot_of_the_day'] + 10; //start at 11am = 10 + 1 for first presentation, etc.
                ?>
                    <div class="schedule_plannedtime_wrapper">Speaker #<?=$post->cf['speaker_slot_of_the_day']; ?></div>
                <?php endif;?>
                <h3 class="card_speaker_name"><?=$post->cf['speaker_name'];?></h3>
				<?php $posttags = get_the_tags(); if($posttags): foreach($posttags as $tag):?>
                    <div class="featurebadge"><img src="/wp-content/themes/betterlifesummits/images/featuredbadge-05.png" alt="featured"></div>
                <?php endforeach; endif;?>
            </div>
            <h4 class="card_presentation_title"><?=$post->cf['presentation_title'];?></h4>
			<?php $password = $_GET['password']; if($password == "unlock"):?>
                <hr><p><?=$post->cf['speaker_title'];?></p>
            <?php endif;?>
            <a class="button more_info_btn" href="#" data-featherlight="#<?=$post->ID;?>" data-featherlight-variant="fixwidth">Learn More</a>
            <div class="presentation_info_mobile">
                <h3 class="card_speaker_name_mobile"><?=$post->cf['speaker_name'];?></h3>
                <h4 class="card_presentation_title"><?=$post->cf['presentation_title'];?></h4>
                <a class="button more_info_btn" href="#" data-featherlight="#<?=$post->ID;?>" data-featherlight-variant="fixwidth">Learn More</a>
            </div>
            <div class="featherlight_modal" id="<?=$post->ID;?>">
                <h3 class="summitcontent_h3"><?=$post->cf['presentation_title'];?></h3>
                <p><span class="summitcontent_presentername"><?=$post->cf['speaker_name'];?></span></p>
                <p><?=$post->cf['speaker_profile'];?></p>
                <button class="featherlight-close">BACK</button>
            </div>
        </div>

	<?php endwhile;?>
</div>
<?php endif;
$wp_query = $temp;
//CHANGE - add:
wp_reset_postdata();
wp_reset_query();
