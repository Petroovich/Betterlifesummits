<?php
namespace SiteSystemSynchronizer;
use WP_Query;
use WP_HTTP_Requests_Response;

class SubSite {

	private static $_instance = null;

	public static function instance(){
		if(is_null(self::$_instance))
			self::$_instance = new SubSite();
		return self::$_instance;
	}

	public function initialise(){
		if(wp_doing_ajax()){
			add_action('wp_ajax_save_current_site_options', [$this, 'save_options']);
		}
	}

	public function displayPage(){
		$admin_page = SiteSystemSynchronizer::$plugin_slug;

		$elements = Synchronizer::instance()->getElements();
		$options = get_option(Core::getSlug());
		
		#Helper::_debug($options);
		
		include_once(S3_PLUGIN_DIR.'/views/backend/sub-site.php');
	}

	public function save_options(){
		if(!wp_verify_nonce($_POST['nonce'], 's3-ajax-nonce')){
			wp_send_json_error(['message' => __('Bed nonce code', S3_TD)]);
		}
		
		$form_data = [];
		parse_str($_POST['form_data'], $form_data);
		
		$form_data['sync_global'] = isset($form_data['sync_global']) ? 'on' : 'off';
		
		$result = update_option(Core::getSlug(), $form_data);
		
		if($result){
			wp_send_json_success(['message' => __('Options successfully saved!<br>The page will reload in a few seconds.', S3_TD)]);
		}else{
			wp_send_json_error(['message' => __('Error while saving options.<br>Try again later.', S3_TD)]);
		}
	}
	
}
