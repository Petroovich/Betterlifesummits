<?php
/**
Plugin Name: Synchronizer
Plugin URI: http://digidez.com/
Description: Site System Synchronizer functionality via WP REST API
Author: Armen Khojoyan
Author URI: http://www.digidez.com
Version: 1.1
Text Domain: s3
License: GPLv2 or later
*/

namespace SiteSystemSynchronizer;

if(!defined('ABSPATH')) exit; // Exit if accessed directly

final class SiteSystemSynchronizer{

	private static $_instance = null;
	public static $plugin_name = "Synchronizer";
	public static $plugin_slug = "site-system-synchronizer";
	public static $menu_prefix = "s3-";

	protected function __construct(){
		#session_start();
		// Activate plugin when new blog is added
		add_action('wpmu_new_blog', [$this, 'activate_new_site']);

	}

	public function __clone(){
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', S3_TD ), '1.0.0' );
	}

	public function __wakeup(){
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', S3_TD ), '1.0.0' );
	}

	public static function instance(){
		if(is_null(self::$_instance))
			self::$_instance = new SiteSystemSynchronizer();
		return self::$_instance;
	}

	public function init(){
		$this->define_constants();
		register_activation_hook(S3_PLUGIN__FILE__, [$this, 'activate']);
		register_deactivation_hook(S3_PLUGIN__FILE__, [$this, 'deactivate']);
		add_action('plugins_loaded', [$this, 'on_plugins_loaded']);
	}

	public static function activate($network_wide){
		if(function_exists('is_multisite') && is_multisite()){
			if($network_wide){
				// Get all blog ids
				$blog_ids = Core::get_blog_ids();

				foreach($blog_ids as $blog_id){
					switch_to_blog($blog_id);
					self::single_activate();
				}

				restore_current_blog();
			}else{
				self::single_activate();
			}
		}else{
			self::single_activate();
		}
	}

	public static function deactivate($network_wide){
		if(function_exists('is_multisite')&& is_multisite()){
			if($network_wide){
				// Get all blog ids
				$blog_ids = Core::get_blog_ids();

				foreach($blog_ids as $blog_id){
					switch_to_blog($blog_id);
					self::single_deactivate();
				}

				restore_current_blog();
			}else{
				self::single_deactivate();
			}
		}else{
			self::single_deactivate();
		}
	}

	public function activate_new_site($blog_id){
		if(1 !== did_action('wpmu_new_blog')) return;

		switch_to_blog($blog_id);
		self::single_activate();
		restore_current_blog();
	}

	private static function single_activate(){
		#require_once('classes/wcpl_core.php');
		#Core::create_tables();
	}

	private static function single_deactivate(){
		#Core::destroy();
	}

	protected function define_constants(){
		if(!defined('S3_MASTER')){
			define('S3_MASTER', false);
		}

		define('S3_PLUGIN_NAME', self::$plugin_name);
		define('S3_PLUGIN__FILE__', __FILE__);
		define('S3_PLUGIN_BASE', plugin_basename(__FILE__));
		define('S3_PLUGIN_DIR_SHORT', basename(dirname(__FILE__)));
		define('S3_PLUGIN_DIR', untrailingslashit(plugin_dir_path(__FILE__)));
		define('S3_PLUGIN_URL', untrailingslashit(plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__))));
		define('S3_TD', 's3');

		define('S3_API_ENDPOINTS_PATH', '/wp-json/webhook/v1/');

		define('S3_ENDPOINTS_DIR', S3_PLUGIN_DIR.'/inc/api-endpoints');

		define('S3_DOWNLOAD_DIR', ABSPATH.'wp-downloads');
		define('S3_DOWNLOAD_URL', site_url('wp-downloads'));

		define('S3_LOG_DIR', ABSPATH.'wp-logs');
		define('S3_CACHE_DIR_NAME', 's3_cache');

		define('S3_ASSETS_DIR', S3_PLUGIN_DIR.'/assets');
		define('S3_ASSETS_URI', S3_PLUGIN_URL.'/assets');

		define('S3_CSS_DIR', S3_ASSETS_DIR.'/css');
		define('S3_CSS_URI', S3_ASSETS_URI.'/css');

		define('S3_JS_DIR', S3_ASSETS_DIR.'/js');
		define('S3_JS_URI', S3_ASSETS_URI.'/js');

		define('S3_IMG_DIR', S3_ASSETS_DIR.'/img');
		define('S3_IMG_URI', S3_ASSETS_URI.'/img');

		define('S3_FONTS_DIR', S3_ASSETS_DIR.'/fonts');
		define('S3_FONTS_URI', S3_ASSETS_URI.'/fonts');

		define('S3_ICONS_DIR', S3_ASSETS_DIR.'/fontawesome');
		define('S3_ICONS_URI', S3_ASSETS_URI.'/fontawesome');

		define('S3_VIEWS_PATH', S3_PLUGIN_DIR.'/views');
	}

	public function includes() {

		require_once('inc/vendor/autoload.php');
		require_once('inc/classes/Device.php');
		require_once('inc/classes/Helper.php');
		require_once('inc/classes/Tools.php');
		require_once('inc/classes/Core.php');
		#require_once('inc/classes/Ajax.php');
		require_once('inc/classes/Pagination.php');
		require_once('inc/classes/Synchronizer.php');
		require_once('inc/classes/SubSites.php');
		require_once('inc/classes/SubSite.php');
		#require_once('inc/classes/Cron.php');
		require_once('inc/classes/Datasource.php');
		require_once('inc/classes/HttpRequest.php');
		require_once('inc/classes/AdminArea.php');
		require_once('inc/classes/PostTypes.php');
		require_once('inc/classes/WebhookRestAPI.php');

		$BaseEndpoint = 'BaseEndpoint.php';
		$endpoints = array_diff(scandir(S3_ENDPOINTS_DIR), ['.', '..', $BaseEndpoint]);

		if(!empty($endpoints)){
			array_unshift($endpoints, $BaseEndpoint);

			foreach($endpoints as $endpoint){
				require_once(S3_ENDPOINTS_DIR.DIRECTORY_SEPARATOR.$endpoint);
			}
		}

	}

	public function on_plugins_loaded(){
		$this->includes();

		Helper::initialise();
		Tools::initialise();
		#Cron::initialise();
		#Ajax::initialise();
		Core::initialise();
		AdminArea::initialise();
		PostTypes::initialise();
		WebhookRestAPI::initialise();
		Synchronizer::instance()->initialise();
		if(S3_MASTER){
			SubSites::instance()->initialise();
		}else{
			SubSite::instance()->initialise();
		}
		
		Core::load_textdomain();
	}

}

SiteSystemSynchronizer::instance()->init();
