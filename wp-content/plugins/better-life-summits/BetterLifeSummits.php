<?php
/**
Plugin Name: Better Life Summits
Plugin URI: http://digidez.com/
Description: Better Life Summits functionality
Author: Armen Khojoyan
Author URI: http://www.digidez.com
Version: 1.0.0
Text Domain: b3
License: GPLv2 or later
*/

namespace BetterLifeSummits;

if(!defined('ABSPATH')) exit; // Exit if accessed directly

final class BetterLifeSummits{

	private static $_instance = null;
	public static $plugin_name = "BetterLifeSummits";
	public static $plugin_slug = "better-life-summits";
	public static $menu_prefix = "bls-";

	protected function __construct(){
		#session_start();
		// Activate plugin when new blog is added
		add_action('wpmu_new_blog', [$this, 'activate_new_site']);

	}

	public function __clone(){
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', B3_TD ), '1.0.0' );
	}

	public function __wakeup(){
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', B3_TD ), '1.0.0' );
	}

	public static function instance(){
		if(is_null(self::$_instance))
			self::$_instance = new BetterLifeSummits();
		return self::$_instance;
	}

	public function init(){
		$this->define_constants();
		register_activation_hook(B3_PLUGIN__FILE__, [$this, 'activate']);
		register_deactivation_hook(B3_PLUGIN__FILE__, [$this, 'deactivate']);
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
		define('B3_PLUGIN__FILE__', __FILE__);
		define('B3_PLUGIN_BASE', plugin_basename(__FILE__));
		define('B3_PLUGIN_DIR_SHORT', basename(dirname(__FILE__)));
		define('B3_PLUGIN_DIR', untrailingslashit(plugin_dir_path(__FILE__)));
		define('B3_PLUGIN_URL', untrailingslashit(plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__))));
		define('B3_TD', 'b3');

		define('B3_LOG_DIR', ABSPATH.'wp-logs');
		define('B3_CACHE_DIR_NAME', 'b3_cache');

		define('B3_ASSETS_DIR', B3_PLUGIN_DIR.'/assets');
		define('B3_ASSETS_URI', B3_PLUGIN_URL.'/assets');

		define('B3_CSS_DIR', B3_ASSETS_DIR.'/css');
		define('B3_CSS_URI', B3_ASSETS_URI.'/css');

		define('B3_JS_DIR', B3_ASSETS_DIR.'/js');
		define('B3_JS_URI', B3_ASSETS_URI.'/js');

		define('B3_IMG_DIR', B3_ASSETS_DIR.'/img');
		define('B3_IMG_URI', B3_ASSETS_URI.'/img');

		define('B3_FONTS_DIR', B3_ASSETS_DIR.'/fonts');
		define('B3_FONTS_URI', B3_ASSETS_URI.'/fonts');

		define('B3_ICONS_DIR', B3_ASSETS_DIR.'/fontawesome');
		define('B3_ICONS_URI', B3_ASSETS_URI.'/fontawesome');

		define('B3_VIEWS_PATH', B3_PLUGIN_DIR.'/views');
	}

	public function includes() {
		require_once('inc/classes/Helper.php');
		require_once('inc/classes/Tools.php');
		require_once('inc/classes/Core.php');
		require_once('inc/classes/Pagination.php');
		require_once('inc/classes/Presentations.php');
		require_once('inc/classes/Datasource.php');
		require_once('inc/classes/PostTypes.php');
		require_once('inc/classes/Posts.php');
		require_once('inc/classes/AdminArea.php');
		require_once('inc/classes/Addons.php');
		require_once('inc/classes/BeaverBuilder.php');
	}

	public function on_plugins_loaded(){
		$this->includes();

		Helper::initialise();
		Tools::initialise();
		Core::initialise();
		PostTypes::initialise();
		Posts::initialise();
		AdminArea::initialise();
		Addons::initialise();
		BeaverBuilder::initialise();
		Presentations::instance()->initialise();

		Core::load_textdomain();
	}

}

BetterLifeSummits::instance()->init();
