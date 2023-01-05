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
<div class="show-presentations-sales-day parent speakers_<?=$category->count?>">
    <?php while (have_posts()) : the_post();?>
        <div class="small_card">
            <div class="small_speaker_image">
                <img class="card_image" src="/wp-content/uploads/manualuploads/speakers216px/<?=$post->cf['expert_profile_picture'];?>" alt="summit">
				<?php $posttags = get_the_tags();
				if($posttags){
					foreach($posttags as $tag){
						echo '<div class="featurebadge"><img src="/wp-content/themes/betterlifesummits/images/featuredbadge-05.png" alt="featured"></div>';
					}
				}
				?>
            </div>
            <div class="small_presentation_info">
                <p class="small_card_speaker_name"><?=$post->cf['speaker_name'];?></p>
                <p class="small_card_speaker_title"><?=$post->cf['speaker_title'];?></p>
                <a class="button more_info_btn" href="#" data-featherlight="#<?=$post->ID;?>" data-featherlight-variant="fixwidth">Learn More</a>
            </div>
            <div class="featherlight_modal" id="<?=$post->ID;?>">
                <h3 class="summitcontent_h3"><?=$post->cf['presentation_title'];?></h3>
                <p><span class="summitcontent_presentername"><?=$post->cf['speaker_name'];?></span></p>
                <p><?=$post->cf['expert_profile_text'];?></p>
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
