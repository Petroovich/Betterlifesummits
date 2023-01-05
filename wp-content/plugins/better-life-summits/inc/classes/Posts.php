<?php

namespace BetterLifeSummits;

class Posts {
	
	public static function initialise(){
		add_action('save_post', [__CLASS__, 'save_post'], 10, 3);
	}

	public static function save_post($post_id, $post, $update){
		if(!$update) return;
		
		if($post->post_type == 'wpbf_hooks'){
			$value = is_super_admin(get_current_user_id()) ? 1 : 0;
			update_post_meta($post_id, 'update_post_content', $value);
			update_post_meta($post_id, 'update_post_metas', 1);
		}
	}
	
}
