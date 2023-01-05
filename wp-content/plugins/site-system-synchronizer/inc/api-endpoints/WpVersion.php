<?php

namespace SiteSystemSynchronizer\ApiEndpoints;


use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

class WpVersion{

	public static $route = 'wp/version';
	private static $_instance = null;

	public static function instance(){
		if(is_null(self::$_instance))
			self::$_instance = new self();
		return self::$_instance;
	}

	public static function getParams(){
		return [
			'methods'             => WP_REST_Server::READABLE,
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
		return [
			'status' => 200,
			'version' => get_bloginfo('version'),
		];
	}
	
	/**
	 * @return array
	 */
	public static function args(){
		return [];
	}
	
}
