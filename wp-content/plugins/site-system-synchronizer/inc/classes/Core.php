<?php
namespace SiteSystemSynchronizer;


class Core{
	
	private static $slug = "s3_settings";
	public static $options = [];
	
	public static function initialise(){
		#$self = new self();
		#self::get_options();
	}
	
	public static function getSlug(){
		return self::$slug;
	}
	
	public static function set_option($key = '', $value = '', $update = false){
		if($key == '')
			return false;
		
		self::$options[$key] = $value;
		
		if($update){
			self::update_options();
		}
	}
	
	public static function update_options($options = []){
		if(!empty($options)){
			self::$options = $options;
		}
		
		return update_option(self::$slug, self::$options);
	}
	
	public static function get_option($key = ''){
		$settings = self::get_options();
		#Helper::_debug($settings); exit;
		
		return !empty($settings[$key]) ? $settings[$key] : false;
	}
	
	public static function get_options(){
		if(isset(self::$options) && is_array(self::$options) && !empty(self::$options)){
			return self::$options;
		}
		
		return self::$options = get_option(self::$slug, []);
	}
	
	public static function get_blog_ids(){
		global $wpdb;

		return $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs WHERE archived = '0' AND spam = '0' AND deleted = '0'");
	}

	public static function load_textdomain(){
		load_plugin_textdomain(S3_TD, false, S3_PLUGIN_DIR_SHORT.'/languages/');
	}

	public static function create_tables(){
		global $wpdb;
		require_once ABSPATH.'wp-admin/includes/upgrade.php';
		#WCPL_Helper::_debug(self::$sql_structure);

		//$wpdb->show_errors();
		$charset_collate = '';

		if($wpdb->has_cap('collation')){
			$charset_collate = $wpdb->get_charset_collate();
		}

		self::$sql_structure = include_once S3_PLUGIN_DIR."/inc/sql/structure.php";

		$sql_tables = self::$sql_structure['tables'];
		$sql_views = self::$sql_structure['views'];

		if(!empty($sql_tables)){
			$sql_tables = str_replace(['{charset_collate}', '{prefix}'], [$charset_collate, $wpdb->prefix], $sql_tables);
			dbDelta($sql_tables);
		}
		if(!empty($sql_views)){
			$sql_views = str_replace(['{db_name}', '{prefix}'], [DB_NAME, $wpdb->prefix], $sql_views);
			dbDelta($sql_views);
		}
	}

}
