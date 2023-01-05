<?php
namespace SiteSystemSynchronizer;


class AdminArea{

	public static $options;

	public static function initialise(){
		$self = new self();
		
		if(is_admin()){
			add_action('init', [$self, 'init'], 0);
			add_action('admin_menu', [$self, 'action_admin_menu'], 30);
			add_action('admin_enqueue_scripts', [$self, 'admin_enqueue_scripts']);
		}
		add_action('admin_init', [$self, 'register_settings'], 10);
	}

	public function init(){}

	public function admin_enqueue_scripts(){
		$page = $this->is_plugin_page();
		#Helper::_debug($page); exit;

		if(false !== $page){
			//wp_enqueue_style('wp-color-picker');
			#wp_enqueue_style('jquery-ui', S3_CSS_URI.'/jquery-ui-1.12.1/jquery-ui.min.css');
			wp_enqueue_style('bootstrap', S3_CSS_URI.'/bootstrap-5.2.1/bootstrap.min.css');
			#wp_enqueue_style('bootstrap-select', S3_CSS_URI.'/bootstrap-3.4.1/bootstrap-select.min.css');
			#wp_enqueue_style('font-awesome-style', S3_CSS_URI.'/font-awesome-4.3.0/font-awesome.min.css');
		}
		wp_enqueue_style('s3-style', S3_CSS_URI.'/backend.css');

		if(false !== $page){
			//wp_enqueue_style('s3-settings', S3_CSS_URI.'/settings.css');
			#wp_enqueue_script('jquery-ui', S3_JS_URI.'/jquery-ui.min.js', ['jquery'], '1.12.1');
			wp_enqueue_script('bootstrap', S3_JS_URI.'/bootstrap-5.2.1/bootstrap.min.js');
			#wp_enqueue_script('bootstrap-select', S3_JS_URI.'/bootstrap-select.min.js');
			//wp_enqueue_script('s3-settings', S3_JS_URI.'/settings.js', ['jquery']);
			//wp_enqueue_script('cpa_custom_js', plugins_url( 'jquery.custom.js', __FILE__ ), ['jquery', 'wp-color-picker'], '', true  );
		}

		$params = [
			'lang' => [
				'sending_request' => esc_attr__('Sending request...', S3_TD),
			],
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('s3-ajax-nonce'),
		];
		wp_register_script('s3-scripts-local', '', [], false, false);
		wp_localize_script('s3-scripts-local', 's3_globals', $params);
		wp_enqueue_script('s3-scripts-local');

		wp_enqueue_script('s3-scripts-backend', S3_JS_URI.'/backend.js');

	}

	public function is_plugin_page(){
		if(isset($_REQUEST['page']) && $_REQUEST['page'] == SiteSystemSynchronizer::$plugin_slug){
			return SiteSystemSynchronizer::$plugin_slug;
		}

		return false;
	}

	public function action_admin_menu(){
		$capability = 'manage_options';

		add_menu_page(
			__('Synchronizer', S3_TD),
			__('Synchronizer', S3_TD),
			$capability,
			SiteSystemSynchronizer::$plugin_slug,
			[$this, 'display_synchronizer_page'],
			'dashicons-editor-table',
			30
		);
	}

	public function display_synchronizer_page(){
		if(S3_MASTER){
			SubSites::instance()->displayPage();
		}else{
			#Helper::_debug(S3_MASTER); exit;
			SubSite::instance()->displayPage();
		}
	}


	/** ---------------------- NOT USED METHODS ---------------------- **/

	public function register_settings(){
		$slug = Core::getSlug();
		
		if(!get_option($slug)){
			$elements = Synchronizer::instance()->getElements();
			
			foreach($elements as $el_id => $data){
				if($el_id == 'plugins_list'){
					foreach($data as $k => $v){
						$elements[$el_id][$k] = 'on';
					}
				}else{
					$elements[$el_id] = 'on';
				}
			}
		
			update_option($slug, [
				'sync_global' => 'on',
				'sync_items' => $elements
			]);
		}

		register_setting($slug, $slug, [$this, 'validate_options']);
		
		#Helper::_debug($elements); exit;
		/*
		add_settings_section(
			'settings_section',
			esc_attr__('Sync Options', S3_TD),
			[Tools::class, 'inner_section_description'],
			SiteSystemSynchronizer::$plugin_slug
		);
		
		add_settings_field(
			'global_sync',
			esc_attr__('Global Synchronization', S3_TD),
			[Tools::class, 'check_field'],
			SiteSystemSynchronizer::$plugin_slug,
			'settings_section',
			[
				'id'      => 'global_sync',
				'page'    => SiteSystemSynchronizer::$plugin_slug,
				'classes' => [],
				'type'    => 'checkbox',
				'sub_desc'=> '',
				'desc'    => '',
			]
		);
		
		add_settings_field(
			'label_1',
			esc_attr__('Custom posts, templates and settings', S3_TD),
			[Tools::class, 'raw_html'],
			SiteSystemSynchronizer::$plugin_slug,
			'settings_section',
		);
		
		foreach($elements as $id => $name){
			if($id == 'plugins_list'){
				add_settings_field(
					'label_2',
					esc_attr__('Plugins for export', S3_TD),
					[Tools::class, 'raw_html'],
					SiteSystemSynchronizer::$plugin_slug,
					'settings_section',
				);
				foreach($name as $k => $v){
					add_settings_field(
						$k,
						$v,
						[Tools::class, 'check_field'],
						SiteSystemSynchronizer::$plugin_slug,
						'settings_section',
						[
							'id'      => $k,
							'page'    => 'sync_items[plugins_list]',
							'classes' => [],
							'type'    => 'checkbox',
							'sub_desc'=> '',
							'desc'    => '',
						]
					);
				}
			}else{
				add_settings_field(
					$id,
					$name,
					[Tools::class, 'check_field'],
					SiteSystemSynchronizer::$plugin_slug,
					'settings_section',
					[
						'id'      => $id,
						'page'    => 'sync_items',
						'classes' => [],
						'type'    => 'checkbox',
						'sub_desc'=> '',
						'desc'    => '',
					]
				);
			}
		}
		*/
	}

	public function validate_options($input){
		Helper::_log('[function '.__FUNCTION__.'] is called');
		#Cron::stop();

		$output = [];

		if(isset($input['s3_timestamp'])){
			//$output['s3_timestamp'] = time();
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
