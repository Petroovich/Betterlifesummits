<?php
namespace BetterLifeSummits;


class AdminArea{

	public static $options;

	public static function initialise(){
		$self = new self();
		
		if(is_admin()){
			add_action('init', [$self, 'init'], 0);
			add_action('admin_menu', [$self, 'action_admin_menu'], 30);
			add_action('admin_enqueue_scripts', [$self, 'admin_enqueue_scripts']);
		}else{
			add_action('wp_enqueue_scripts', [$self, 'enqueue_scripts']);
		}
	}

	public function init(){}

	public function enqueue_scripts(){
		wp_enqueue_style('b3-builder-style', B3_CSS_URI.'/builder.css');
		wp_enqueue_script('b3-builder-scripts', B3_JS_URI.'/builder.js');
	}

	public function admin_enqueue_scripts(){
		$page = $this->is_plugin_page();
		#Helper::_debug($page); exit;

		if(false !== $page){
			//wp_enqueue_style('wp-color-picker');
			#wp_enqueue_style('jquery-ui', B3_CSS_URI.'/jquery-ui-1.12.1/jquery-ui.min.css');
			wp_enqueue_style('bootstrap', B3_CSS_URI.'/bootstrap-5.2.1/bootstrap.min.css');
			#wp_enqueue_style('bootstrap-select', B3_CSS_URI.'/bootstrap-3.4.1/bootstrap-select.min.css');
			#wp_enqueue_style('font-awesome-style', B3_CSS_URI.'/font-awesome-4.3.0/font-awesome.min.css');
		}
		wp_enqueue_style('b3-style', B3_CSS_URI.'/backend.css');

		if(false !== $page){
			//wp_enqueue_style('b3-settings', B3_CSS_URI.'/settings.css');
			#wp_enqueue_script('jquery-ui', B3_JS_URI.'/jquery-ui.min.js', ['jquery'], '1.12.1');
			wp_enqueue_script('bootstrap', B3_JS_URI.'/bootstrap-5.2.1/bootstrap.min.js');
			#wp_enqueue_script('bootstrap-select', B3_JS_URI.'/bootstrap-select.min.js');
			//wp_enqueue_script('b3-settings', B3_JS_URI.'/settings.js', ['jquery']);
			//wp_enqueue_script('cpa_custom_js', plugins_url( 'jquery.custom.js', __FILE__ ), ['jquery', 'wp-color-picker'], '', true  );
		}

		$params = [
			'lang' => [
				'sending_request' => esc_attr__('Sending request...', B3_TD),
			],
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('bls-ajax-nonce'),
			'days' => 0,
		];
		if(class_exists('ACF')){
			$params['days'] = intval(get_field('how_many_days_does_the_event_have', 'option'));
		}
		wp_register_script('b3-scripts-local', '', [], false, false);
		wp_localize_script('b3-scripts-local', 'b3_globals', $params);
		wp_enqueue_script('b3-scripts-local');

		wp_enqueue_script('b3-scripts-backend', B3_JS_URI.'/backend.js');

	}

	public function is_plugin_page(){
		if(isset($_REQUEST['page']) && $_REQUEST['page'] == BetterLifeSummits::$plugin_slug){
			return BetterLifeSummits::$plugin_slug;
		}

		return false;
	}

	public function action_admin_menu(){
		$capability = 'edit_posts';

		add_menu_page(
			__('Presentations', B3_TD),
			__('Presentations', B3_TD),
			$capability,
			BetterLifeSummits::$plugin_slug,
			[$this, 'display_presentations_page'],
			'dashicons-admin-post',
			20
		);

		add_submenu_page(
			BetterLifeSummits::$plugin_slug,
			__('Add New', B3_TD),
			__('Add New', B3_TD),
			$capability,
			'post-new.php?post_type=presentations',
			null
		);

		add_submenu_page(
			BetterLifeSummits::$plugin_slug,
			__('Categories', B3_TD),
			__('Categories', B3_TD),
			$capability,
			'edit-tags.php?taxonomy=category&post_type=presentations',
			null
		);
	}

	public function display_presentations_page(){
		Presentations::instance()->displayPresentations();
	}

	/** ---------------------- NOT USED METHODS ---------------------- **/

	public static function get_setup_section(){
		if(isset($_REQUEST['page'])){
			return str_replace(BetterLifeSummits::$menu_prefix, '', strtolower($_REQUEST['page']));
		}
		return BetterLifeSummits::$plugin_slug;
	}

	public function register_settings(){
		//If no options exist, create them.
		if(!get_option(BetterLifeSummits::$plugin_slug)){
			update_option(BetterLifeSummits::$plugin_slug, [
				'cron_interval' => '5',
				'b3_update_yml_feed' => 0,
			]);
		}

		register_setting('b3-options', BetterLifeSummits::$plugin_slug, [$this, 'validate_options']);
		$page = $this->get_setup_section();

		switch($page){
			case 'b3':
				add_settings_section(
					'settings_section',
					esc_attr__('Settings', B3_TD),
					[Tools::class, 'inner_section_description'],
					BetterLifeSummits::$plugin_slug
				);

				add_settings_field(
					'cron_interval',
					esc_attr__('Cron upadte interval (minutes)', B3_TD),
					[Tools::class, 'text_field'],
					BetterLifeSummits::$plugin_slug,
					'settings_section',
					[
						'id'      => 'cron_interval',
						'page'    => BetterLifeSummits::$plugin_slug,
						'classes' => ['auto-text'],
						'type'    => 'text',
						'sub_desc'=> '',
						'desc'    => '3 hours = 180 minutes<br>6 hours = 360 minutes<br>12 hours = 720 minutes<br>24 hours = 1440 minutes<br>2 days = 2880 minutes<br>5 days = 7200 minutes<br>10 days = 14400 minutes',
					]
				);

				/*add_settings_field(
					'b3_update_yml_feed',
					esc_attr__('Yandex Market feed auto update via Cron', B3_TD),
					[Tools::class, 'yesno2_field'],
					BetterLifeSummits::$plugin_slug,
					'settings_section',
					[
						'id'      => 'b3_update_yml_feed',
						'page'    => BetterLifeSummits::$plugin_slug,
						'classes' => [],
						'type'    => 'radio',
						'sub_desc'=> '',
						'desc'    => '',
					]
				);*/
				break;
			default:
				break;
		}
	}

	public function validate_options($input){
		Helper::_log('[function '.__FUNCTION__.'] is called');

		$output = array();

		if(isset($input['b3_timestamp'])){
			//$output['b3_timestamp'] = time();
		}

		// merge with current settings
		$output = array_merge(self::$options, $input, $output);

		return $output;
	}

	public function fetch_posts_columns($column){
		global $post;
		switch($column){
			case 'menu_order':
				echo $post->menu_order;
				break;
			case 'slug':
				echo $post->post_name;
				break;
			default:
				break;
		}
	}
	
}
