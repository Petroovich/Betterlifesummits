<?php
/** Ultimate Dashboard Endpoint */

namespace SiteSystemSynchronizer\ApiEndpoints;

use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

class UdbData extends BaseEndpoint{

	public static $route = 'udb/data';
	private static $_instance = null;

	public static function instance(){
		if(is_null(self::$_instance))
			self::$_instance = new self();
		return self::$_instance;
	}

	public function getParams(): array{
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
	public static function callback(WP_REST_Request $Request): array{
		$body = json_decode($Request->get_body(), true);

		#Helper::_log($body['domain']);

		self::instance()->master_domain = $body['domain'];
		self::instance()->self_domain = site_url();

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
	public static function args(): array{
		return [];
	}

	/********** Custom methods ***********/

	private function doAction($action, $data): array{
		$res = [];
		
		if($this->sync_global){
			switch($action){
				case "UpdateOrInsertOptions":
					if($this->options['sync_items']['udb_settings']){
						$res = $this->UpdateOrInsertOptions($data);
					}
					break;
				case "UpdateOrInsertPosts":
					if($this->options['sync_items']['udb_posts']){
						$res = $this->UpdateOrInsertPosts($data);
					}
					break;
				default:
					break;
			}
		}
		
		return $res;
	}

	/** OPTIONS **/
/*
	private function UpdateOrInsertOptions(array $options): array{
		$res = [];

		if(!empty($options)){
			
			$download_media_files = false;
			
			foreach($options as $option){
				$this->theOption($option);
				$this->findOptionMediaFiles($option);
				$option = $this->replaceOptionDomain($option);

				unset($option['option_id']);

				$option['table'] = 'prefix_options';

				if($this->isNewOption()){
					#Helper::_log(['new option', $option['option_name']]);
					
					$options_id = DataSource::insert_data($option);
					$ret[] = $options_id;

					$download_media_files = true;
				}else{
					#Helper::_log(['update option', $this->exist_option['option_id'], $option['option_name']]);

					$option['primary']['name'] = 'option_id';
					$option['primary']['value'] = intval($this->exist_option['option_id']);
					$ret[] = DataSource::update_data($option);

					$download_media_files = true;
				}

			}

			if($download_media_files){
				$this->downloadMediaFiles();
			}
		}

		return $res;
	}

	private function theOption($option){
		$sql = sprintf(
			"SELECT * FROM prefix_options WHERE option_name = '%s'",
			$option['option_name']
		);
		$this->exist_option = DataSource::get_row($sql, ARRAY_A);
	}

	private function replaceOptionDomain($option, $find = '', $replace = ''): array{
		$option_value = $option['option_value'];
		#Helper::_log($option_value);

		if(strstr($option_value, ':{') !== false){
			$arr = $this->_replaceOptionDomainInArray(unserialize($option_value), $find, $replace);
			$option_value = serialize($arr);
		}else{
			if(!empty($find) && !empty($replace)){
				$option_value = str_replace($find, $replace, $option_value);
			}else{
				$option_value = str_replace(
					[$this->master_domain, basename($this->master_domain)],
					[$this->self_domain, basename($this->self_domain)],
					$option_value
				);
			}
		}

		#Helper::_log($option_value);

		$option['option_value'] = $option_value;

		return $option;
	}

	private function _replaceOptionDomainInArray($arr, $find = '', $replace = ''){
		foreach($arr as $k => $v){
			if(is_array($v)){
				$arr[$k] = $this->_replaceOptionDomainInArray($v, $find, $replace);
			}else{
				if(!empty($find) && !empty($replace)){
					$arr[$k] = str_replace($find, $replace, $v);
				}else{
					$arr[$k] = str_replace(
						[$this->master_domain, basename($this->master_domain)],
						[$this->self_domain, basename($this->self_domain)],
						$v
					);
				}
			}
		}

		return $arr;
	}

	private function isNewOption(): bool{
		return is_null($this->exist_option);
	}
*/
	/** MEDIA FILES **/
/*
	private function findOptionMediaFiles($option){
		#Helper::_log(basename($this->master_domain));
		$master_domain = basename($this->master_domain);
		#$self_domain = basename($this->self_domain);

		if(!empty($option['option_value'])){
			if(strstr($option['option_value'], $master_domain) !== false){
				$output_array = [];
				preg_match_all('/((http|https):\/\/('.$master_domain.'\/)([\w\-\.,@?^=%&amp;:\/~\+#]*[\w\-\@?^=%&amp;\/~\+#])?)/', $option['option_value'], $output_array);

				if(!empty($output_array[0])){
					$links = array_values(array_unique($output_array[0]));

					if(!empty($links)){
						foreach($links as $link){
							$_link = $link;

							if(!empty($_link)){
								if(strstr($_link, '?') !== false){
									$_link = explode('?', $_link)[0];
								}
								$_link = basename($_link);
								$filetype = wp_check_filetype($_link);

								if($filetype['ext']){
									if(in_array($filetype['ext'], ['png', 'jpg', 'jpeg', 'gif', 'bmp'])){
										$this->media_files[$option['option_name']][] = $link;
									}
								}

							}

						}
					}

				}

			}
		}
	}

	private function downloadMediaFiles(){
		if(empty($this->media_files)) return;

		#$this->media_files = array_unique($this->media_files);
		$wp_upload_dir = wp_upload_dir();
		#Helper::_log($wp_upload_dir);

		$added = [];

		foreach($this->media_files as $option_name => $files){
			foreach($files as $media_file){
				if(!in_array($media_file, $added)){

					$this->media_files[$option_name]['path'] = $wp_upload_dir['path'].'/'.basename($media_file);
					$this->media_files[$option_name]['find_url'] = str_replace($this->master_domain, $this->self_domain, $media_file);
					$this->media_files[$option_name]['replace_url'] = $wp_upload_dir['url'].'/'.basename($media_file);

					if($this->isNewAttachment($option_name)){
						file_put_contents($wp_upload_dir['path'].'/'.basename($media_file), file_get_contents($media_file));
						$added[] = $media_file;

						if($this->media_files[$option_name]['find_url'] != $this->media_files[$option_name]['replace_url']){
							$this->replaceOptionValue(
								$option_name,
								$this->media_files[$option_name]['find_url'],
								$this->media_files[$option_name]['replace_url']
							);
						}

						$this->createAttachment($this->media_files[$option_name]);
					}
				}
			}
		}

		#Helper::_log($this->media_files);

	}

	private function isNewAttachment($option_name): bool{
		$res = true;

		#Helper::_log([$option_name, $this->media_files[$option_name]]);

		$_sql = sprintf("SELECT * FROM prefix_options WHERE option_name = '%s' AND option_value LIKE '%%%s%%'", $option_name, $this->media_files[$option_name]['replace_url']);
		$option = DataSource::get_row($_sql, ARRAY_A);

		if(!empty($option)){
			$_sql = sprintf("SELECT ID FROM prefix_posts WHERE guid = '%s'", $this->media_files[$option_name]['replace_url']);
			$attachment_id = DataSource::get_var($_sql);
			if($attachment_id){
				if(file_exists($this->media_files[$option_name]['path'])){
					$res = false;
				}
			}
		}
		#Helper::_log($_sql);
		#Helper::_log($option);

		return $res;
	}

	private function replaceOptionValue($option_name, $find, $replace){
		$option = DataSource::get_row(sprintf("SELECT * FROM prefix_options WHERE option_name = '%s'", $option_name), ARRAY_A);
		#Helper::_log($option);
		$option = $this->replaceOptionDomain($option, $find, $replace);
		#Helper::_log($option);

		$option['table'] = 'prefix_options';
		$option['primary']['name'] = 'option_id';
		$option['primary']['value'] = intval($option['option_id']);

		$option_id = DataSource::update_data($option);
		#Helper::_log($option_id);
	}

	private function createAttachment($file_params){
		// Проверим тип поста, который мы будем использовать в поле 'post_mime_type'.
		$filetype = wp_check_filetype(basename($file_params['path']), null);

		// Подготовим массив с необходимыми данными для вложения.
		$attachment = [
			'guid' => $file_params['replace_url'],
			'post_mime_type' => $filetype['type'],
			'post_title' => preg_replace('/\.[^.]+$/', '', basename($file_params['path'])),
			'post_content' => '',
			'post_status' => 'inherit'
		];

		// Вставляем запись в базу данных.
		$attach_id = wp_insert_attachment($attachment, $file_params['path']);

		// Подключим нужный файл, если он еще не подключен
		// wp_generate_attachment_metadata() зависит от этого файла.
		require_once(ABSPATH.'wp-admin/includes/image.php');

		// Создадим метаданные для вложения и обновим запись в базе данных.
		$attach_data = wp_generate_attachment_metadata($attach_id, $file_params['path']);
		wp_update_attachment_metadata($attach_id, $attach_data);
	}
*/
	/** POSTS **/
/*
	private function UpdateOrInsertPosts(array $posts): array{
		$res = [];
		#Helper::_log($posts);

		if(!empty($posts)){

			foreach($posts as $_post){
				$this->thePost($_post);
				$_post = $this->replacePostDomain($_post);
				$metas = $_post['metas'] ?? [];
				#Helper::_log($_post); break;

				unset($_post['ID'], $_post['filter'], $_post['metas']);

				if($this->isNewPost()){
					#Helper::_log(['new post', self::$exist_post->ID, $_post]);
					$_post['table'] = 'prefix_posts';
					$post_id = DataSource::insert_data($_post);
					$this->insertOrUpdatePostMetas($post_id, $metas);
					$ret[] = $post_id;
				}elseif($this->maybeUpdatePost($_post)){
					#Helper::_log(['update post', self::$exist_post->ID, $_post]);
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

	private function isNewPost(): bool{
		return is_null($this->exist_post);
	}

	private function maybeUpdatePost($_post): bool{
		if(!is_null($this->exist_post)){
			if(strtotime($this->exist_post->post_modified_gmt) < strtotime($_post['post_modified_gmt'])){
				return true;
			}
		}

		return false;
	}

	private function insertOrUpdatePostMetas($post_id, $meta_data){
		if(!empty($meta_data)){
			foreach($meta_data as $meta_key => $meta_value){
				$meta_value = maybe_unserialize($meta_value[0]);
				#Helper::_log($meta_value);
				update_post_meta($post_id, $meta_key, $meta_value);
			}
		}
	}

	private function replacePostDomain($_post): array{
		$_post['guid'] = str_replace($this->master_domain, $this->self_domain, $_post['guid']);

		return $_post;
	}
*/
}
