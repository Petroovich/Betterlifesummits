<?php

/**
 * A class that handles loading custom modules and custom
 * fields if the builder is installed and activated.
 */
class BBCMLoader{

	/**
	 * Displaying debug information
	 * @param array $data
	 * @param bool $echo
	 * @param bool $strip_tags
	 * @param bool $show_for_users
	 *
	 * @return array|mixed|string
	 */
	public static function _debug($data = [], $show_for_users = false, $format = 'html', $echo = true, $strip_tags = true){
		if(current_user_can('manage_options') || $show_for_users){
			$count = 0;

			if(is_array($data) || is_object($data)){
				$count = count($data);
				if($echo)
					$data = print_r($data, true);
			}

			if($echo)
				$data = htmlspecialchars($data);

			if($strip_tags)
				$data = strip_tags($data);

			if($echo){
				switch($format){
					case "html":
						echo '<pre class="debug"><div class="d-title">Debug info:(', $count, ')</div><div class="d-content">', $data, '</div></pre>';
						break;
					case "json":
						echo json_encode($data);
						break;
					case "raw":
						print_r($data);
						break;
				}
			}else{
				switch($format){
					case "html":
						return '<pre class="debug"><ul class="d-content">'.array_map(function($k, $v){
								return sprintf('<li>%s: %s</li>', $k, $v);},
								array_keys($data), $data).'</ul></pre>';
						break;
					case "json":
						return json_encode($data);
						break;
					case "raw":
						return print_r($data, true);
						break;
				}
			}
		}
	}

	/**
	 * Initializes the class once all plugins have loaded.
	 */
	static public function init(){
		add_action('plugins_loaded', [__CLASS__, 'setup_hooks']);
	}

	/**
	 * Setup hooks if the builder is installed and activated.
	 */
	static public function setup_hooks(){
		if(!class_exists('FLBuilder')){
			return;
		}

		// Load custom modules.
		add_action('init', [__CLASS__, 'load_modules']);

		// Register custom fields.
		add_filter('fl_builder_custom_fields', [__CLASS__, 'register_fields']);

		// Enqueue custom field assets.
		#add_action('init', [__CLASS__, 'enqueue_field_assets']);
	}

	/**
	 * Loads our custom modules.
	 */
	static public function load_modules(){
		#self::_debug(__METHOD__);
		require_once FL_BB_CUSTOM_MODULES_DIR.'modules/registration-button/FLCMRegistrationButton.php';
		require_once FL_BB_CUSTOM_MODULES_DIR.'modules/purchase-button/FLCMPurchaseButton.php';
		require_once FL_BB_CUSTOM_MODULES_DIR.'modules/presentations-optin-day/FLCMShowPresentationsOptinDay.php';
		require_once FL_BB_CUSTOM_MODULES_DIR.'modules/presentations-sales-day/FLCMShowPresentationsSalesDay.php';
		require_once FL_BB_CUSTOM_MODULES_DIR.'modules/presentations-schedule-day/FLCMShowPresentationsScheduleDay.php';
		require_once FL_BB_CUSTOM_MODULES_DIR.'modules/all-speakers/FLCMAllSpeakers.php';
		require_once FL_BB_CUSTOM_MODULES_DIR.'modules/event-images/FLCMEventImages.php';
		#require_once FL_BB_CUSTOM_MODULES_DIR.'modules/event-text-snippets/FLCMEventTextSnippets.php';
	}

	/**
	 * Registers our custom fields.
	 */
	static public function register_fields($fields){
		$fields['shortcode'] = FL_BB_CUSTOM_MODULES_DIR.'fields/shortcode-field.php';
		return $fields;
	}

	/**
	 * Enqueues our custom field assets only if the builder UI is active.
	 */
	static public function enqueue_field_assets(){
		if(!FLBuilderModel::is_builder_active()){
			return;
		}

		wp_enqueue_style('shortcode-fields', FL_BB_CUSTOM_MODULES_URL.'assets/css/shortcode-fields.css', [], '');
		wp_enqueue_script('shortcode-fields', FL_BB_CUSTOM_MODULES_URL.'assets/js/shortcode-fields.js', [], '', true);
	}

}

BBCMLoader::init();
