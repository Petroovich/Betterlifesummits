<?php

namespace SiteSystemSynchronizer\ApiEndpoints;

use SiteSystemSynchronizer\DataSource;
use SiteSystemSynchronizer\Helper;
use SiteSystemSynchronizer\Core;
use SiteSystemSynchronizer\Synchronizer;

class BaseEndpoint{

	public $exist_post = null;
	public $exist_option = null;
	public string $master_domain = '';
	public string $self_domain = '';
	public array $media_files = [];
	public bool $use_update_post_content_logic = false;
	public $options = [];
	public bool $sync_global = true;
	
	public function __construct(){
		#if(S3_MASTER) return false;
		
		if(empty($this->options)){
			$this->options                = Core::get_options();
			$this->options['sync_global'] = (isset($this->options['sync_global']) && $this->options['sync_global'] == 'on') ? true : false;
			$this->sync_global            = $this->options['sync_global'];
			
			$elements = Synchronizer::instance()->getElements();
			
			foreach($elements as $el_id => $data){
				if($el_id == 'plugins_list'){
					foreach($data as $k => $v){
						$this->options['sync_items']['plugins_list'][$k] = (isset($this->options['sync_items']['plugins_list'][$k]) && $this->options['sync_items']['plugins_list'][$k] == 'on') ? true : false;
					}
				}else{
					$this->options['sync_items'][$el_id] = (isset($this->options['sync_items'][$el_id]) && $this->options['sync_items'][$el_id] == 'on') ? true : false;
				}
			}
			#Helper::_log($this->options);
		}
	}

	public function setUpdatePostContentLogic($value){
		$this->use_update_post_content_logic = $value;
	}

	/** OPTIONS **/

	public function UpdateOrInsertOptions(array $options): array{
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

	public function theOption($option){
		$sql = sprintf(
			"SELECT * FROM prefix_options WHERE option_name = '%s'",
			$option['option_name']
		);
		$this->exist_option = DataSource::get_row($sql, ARRAY_A);
	}

	public function replaceOptionDomain($option, $find = '', $replace = ''): array{
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

	public function _replaceOptionDomainInArray($arr, $find = '', $replace = ''){
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

	public function isNewOption(): bool{
		return is_null($this->exist_option);
	}

	/** MEDIA FILES **/

	public function findOptionMediaFiles($option){
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

	public function downloadMediaFiles(){
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

	public function isNewAttachment($option_name): bool{
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

	public function replaceOptionValue($option_name, $find, $replace){
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

	public function createAttachment($file_params){
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

	/** POSTS **/

	public function UpdateOrInsertPosts(array $posts): array{
		$res = [];
		#Helper::_log($posts);

		if(!empty($posts)){

			foreach($posts as $_post){
				$this->thePost($_post);
				$_post = $this->replacePostDomain($_post);
				$metas = $_post['metas'] ?? [];

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

					if($this->use_update_post_content_logic){
						if(!isset($this->exist_post->metas['update_post_content']) || intval($this->exist_post->metas['update_post_content']) == 1){
							$ret[] = DataSource::update_data($_post);
						}
					}else{
						$ret[] = DataSource::update_data($_post);
					}

					if(!isset($this->exist_post->metas['update_post_metas']) || intval($this->exist_post->metas['update_post_metas']) == 1){
						$this->insertOrUpdatePostMetas($this->exist_post->ID, $metas);
					}
				}else{
					Helper::_log(['exist post not need update', $this->exist_post->ID]);
					#Helper::_log($this->exist_post);
				}

				#break;
			}
		}

		return $res;
	}

	public function thePost($_post){
		$sql = sprintf(
			"SELECT * FROM prefix_posts WHERE post_name = '%s' AND post_type = '%s'",
			$_post['post_name'],
			$_post['post_type']
		);
		$this->exist_post = DataSource::get_row($sql);

		if(!is_null($this->exist_post)){
			$result = DataSource::get_results(DataSource::prepare("SELECT meta_key, meta_value FROM prefix_postmeta WHERE post_id = %d", [$this->exist_post->ID]), ARRAY_A);
			if(!is_null($result))
				$this->exist_post->metas = Helper::formatMetasAsKeyValue($result);
		}
	}

	public function isNewPost(): bool{
		return is_null($this->exist_post);
	}

	public function maybeUpdatePost($_post): bool{
		$res = false;

		if(!is_null($this->exist_post)){
			if(strtotime($this->exist_post->post_modified_gmt) < strtotime($_post['post_modified_gmt'])){
				$res = true;
			}
		}

		return $res;
	}

	public function insertOrUpdatePostMetas($post_id, $meta_data){
		if(!empty($meta_data)){
			foreach($meta_data as $meta_key => $meta_value){
				$meta_value = maybe_unserialize($meta_value);
				#Helper::_log($meta_value);
				update_post_meta($post_id, $meta_key, $meta_value);
			}
		}
	}

	public function replacePostDomain($_post): array{
		$_post['guid'] = str_replace($this->master_domain, $this->self_domain, $_post['guid']);

		return $_post;
	}

}
