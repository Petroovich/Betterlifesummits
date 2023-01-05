<?php
/** Beaver Builder Endpoint */

namespace SiteSystemSynchronizer\ApiEndpoints;

use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

class BeaverBuilder extends BaseEndpoint{

	public static $route = 'beaver_builder';
	private static $_instance = null;

	#private $exist_post = null;
	#private $master_domain = '';
	#private $site_url = '';

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
		$error = new WP_Error();

		$body = $Request->get_body();

		if(empty($body)){
			$error->add(400, 'Field "body" is required');
		}

		if($error->has_errors()){
			$error->add_data(['status' => 400], 400);
			return $error;
		}else return true;

	}

	/**
	 * @param WP_REST_Request $Request
	 *
	 * @return array
	 */
	public static function callback(WP_REST_Request $Request){
		$body = json_decode($Request->get_body(), true);

		#Helper::_log($body['domain']);

		self::instance()->master_domain = $body['domain'];
		self::instance()->site_url = site_url();

		if(isset($body['useUpdatePostContentLogic']))
			self::instance()->setUpdatePostContentLogic($body['useUpdatePostContentLogic']);

		return [
			'status'   => 200,
			'data' => self::instance()->doAction($body['action'], $body['posts'])
		];
	}

	/**
	 * @return array
	 */
	public static function args(){
		return [];
	}

	/********** Custom methods ***********/

	private function doAction($action, $data): array{
		$res = [];
		
		if($this->sync_global){
			switch($action){
				case "UpdateOrInsertPosts":
					if($this->options['sync_items']['bb_posts']){
						$res = $this->UpdateOrInsertPosts($data);
					}
					break;
				default:
					break;
			}
		}
		
		return $res;
	}

/*
	private function UpdateOrInsertPosts(array $posts): array{
		$res = [];

		if(!empty($posts)){

			foreach($posts as $_post){
				$this->thePost($_post);
				$_post = $this->replaceDomain($_post);
				$metas = $_post['metas'] ?? [];
				#Helper::_log($_post); break;

				unset($_post['ID'], $_post['filter'], $_post['metas']);

				if($this->isNewPost()){
					#Helper::_log(['new post', $this->exist_post->ID, $_post]);
					$_post['table'] = 'prefix_posts';
					$post_id = DataSource::insert_data($_post);
					$this->insertOrUpdatePostMetas($post_id, $metas);
					$ret[] = $post_id;
				}elseif($this->maybeUpdatePost($_post)){
					#Helper::_log(['update post', $this->exist_post->ID, $_post]);
					$_post['table'] = 'prefix_posts';
					$_post['primary']['name'] = 'ID';
					$_post['primary']['value'] = intval($this->exist_post->ID);
					$ret[] = DataSource::update_data($_post);
					$this->insertOrUpdatePostMetas($this->exist_post->ID, $metas);
				}else{
					Helper::_log(['exist post not need update', $this->exist_post->ID]);
				}

				#break;
			}
		}

		return $res;
	}

	private function thePost($_post){
		$sql = sprintf(
			"SELECT * FROM prefix_posts WHERE post_name = '%s' AND post_type = '%s'",
			$_post['post_name'],
			$_post['post_type']
		);

		$this->exist_post = DataSource::get_row($sql);
	}

	private function replaceDomain($_post): array{
		$_post['guid'] = str_replace($this->master_domain, $this->site_url, $_post['guid']);

		return $_post;
	}

	private function isNewPost(): bool{
		return is_null($this->exist_post);
	}

	private function maybeUpdatePost($_post): bool{
		if(!is_null($this->exist_post)){
			#Helper::_log([strtotime($this->exist_post->post_modified_gmt), strtotime($_post['post_modified_gmt'])]);
			if(strtotime($this->exist_post->post_modified_gmt) < strtotime($_post['post_modified_gmt'])){
				return true;
			}
		}

		return false;
	}

	private function insertOrUpdatePostMetas($post_id, $meta_data){
		if(!empty($meta_data))
			foreach($meta_data as $meta_key => $meta_value)
				update_post_meta($post_id, $meta_key, $meta_value);
	}
*/

}
