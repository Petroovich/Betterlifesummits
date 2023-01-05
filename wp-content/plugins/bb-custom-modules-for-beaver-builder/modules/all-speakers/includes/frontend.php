<?php
global $wp_query,$paged,$post;

$temp = $wp_query;
#$category = get_term_by('slug', $settings->category, 'category');

$args = [
	'post_type'      => 'presentations',
	'posts_per_page' => ($settings->posts_per_page) ?: 6,
	#'cat' => $category->term_id,
	'orderby' => $settings->orderby,
	'order' => $settings->order,
	'include_custom_fields' => true,
];
if($settings->orderby == 'meta_value'){
	$args['meta_key'] = $settings->meta_key;
}
$wp_query = BBCMDataSource::getPosts($args);
?>
<?php if($settings->page_type == 'sales'):?>

    <?php if(have_posts()): $postnum = 0;?>
    <div class="show-presentations-sales-page summit_content_list sales_page ast-row">
        <?php while (have_posts()) : the_post();?>
            <div class="ast-col-lg-3 ast-col-md-3 ast-col-sm-6 ast-col-xs-12 summitcontent_presentation_col">
                <img class="presenter_img" src="/wp-content/uploads/manualuploads/speakers216px/<?=$post->cf['speaker_profile_picture'];?>" alt="summit">
                <h4 class="summitcontent_presentername"><?=$post->cf['speaker_name'];?></h4>
                <h3 class="summitcontent_h3"><?=$post->cf['presentation_title'];?></h3>
                <a class="button more_info_btn" href="#" data-featherlight="#<?=$post->ID;?>" data-featherlight-variant="fixwidth">Learn More</a>
            </div>
            <div class="featherlight_modal" id="<?=$post->ID;?>">
                <h3 class="summitcontent_h3"><?=$post->cf['presentation_title'];?></h3>
                <p><span class="summitcontent_presentername"><?=$post->cf['speaker_name'];?></span></p>
                <p><?=$post->cf['speaker_profile'];?></p>
                <button class="featherlight-close">CLOSE</button>
            </div>
            <?php $postnum++; if($postnum == 4): $postnum = 0;?>
                </div>
                <div class="summit_content_list sales_page ast-row"> <!-- re-starts container -->
            <?php endif;?>
        <?php endwhile;?>
    </div>
    <?php endif;?>

<?php elseif($settings->page_type == 'registration'):?>

	<?php if(have_posts()): $postnum = 0;?>
        <div class="show-presentations-optin-page summit_content_list ast-row">
		<?php while (have_posts()) : the_post();?>
            <div class="ast-col-lg-4 ast-col-md-4 ast-col-sm-12 ast-col-xs-12">
                <img class="presenter_img" src="/wp-content/uploads/manualuploads/speakers216px/<?=$post->cf['speaker_profile_picture'];?>" alt="summit">
                <h4 class="summitcontent_presentername"><?=$post->cf['speaker_name'];?></h4>
                <h3 class="summitcontent_h3"><?=$post->cf['presentation_title'];?></h3>
                <a class="button more_info_btn" href="#" data-featherlight="#<?=$post->ID;?>" data-featherlight-variant="fixwidth">Learn More</a>
            </div>
            <div class="featherlight_modal" id="<?=$post->ID;?>">
                <h3 class="summitcontent_h3"><?=$post->cf['presentation_title'];?></h3>
                <p><span class="summitcontent_presentername"><?=$post->cf['speaker_name'];?></span></p>
                <p><?=$post->cf['speaker_profile'];?></p>
                <button class="featherlight-close">BACK</button>
            </div>
			<?php $postnum++;?>
			<?php if ($postnum == 3):?>
                </div>
                <div class="summit_content_list ast-row">
			<?php endif;?>
			<?php if ($postnum == 3): $postnum = 0;?>
                </div>
                <div class="ast-row">
                    <div class="ast-col-lg-12 ast-col-md-12 ast-col-sm-12 ast-col-xs-12 summit_content_cta_row">
                        <span class="cta_row_intro">Reserve my spot for free:</span>
                        <a class="cta_row_btn cta_btn register_popup" href="#">
                            <span class="fl-button-text">Join Now</span>
                        </a>
                    </div>
                </div>
                <div class="summit_content_list ast-row">
			<?php endif;?>
		<?php endwhile;?>
        </div>
	<?php endif;?>

<?php endif;
$wp_query = $temp;
//CHANGE - add:
wp_reset_postdata();
wp_reset_query();
