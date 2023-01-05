<?php
namespace SiteSystemSynchronizer\ApiEndpoints;

use SiteSystemSynchronizer\Helper;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

class CheckAPI {

	public static $route = 'check/api';
	private static $_instance = null;

	public static function instance(){
		if(is_null(self::$_instance))
			self::$_instance = new self();
		return self::$_instance;
	}

	public static function getParams(){
		return [
			'methods'             => WP_REST_Server::EDITABLE,
			'permission_callback' => [__CLASS__, 'permission_callback'],
			'callback'            => [__CLASS__, 'callback'],
			'args'                => [__CLASS__, 'args'],
		];
	}

	/**
	 * @param WP_REST_Request $Request
	 *
	 * @return bool|WP_Error
	 */
	public static function permission_callback(WP_REST_Request $Request){
		return true;
	}
	
	/**
	 * @param WP_REST_Request $Request
	 *
	 * @return array
	 */
	public static function callback(WP_REST_Request $Request){
		/*$body = json_decode($Request->get_body(), true);

		$path = WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.$body['plugin_name'];
		$res = file_exists($path);
		$res = is_dir($path) ?: $res;

		if($res){
			$is_active = PluginsWorker::instance()->isPluginActive($body['plugin_name']);
			if(!$is_active){
				$res = PluginsWorker::instance()->installPlugin(['folder' => $body['plugin_name']]);
			}
		}*/

		return [
			'status' => 200,
			'result' => defined('S3_PLUGIN_BASE'),
		];
	}
	
	/**
	 * @return array
	 */
	public static function args(){
		return [];
	}

}
