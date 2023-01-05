<?php

namespace SiteSystemSynchronizer;

use ACF_Admin_Tool_Export;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;

class Ajax{

	private $remote_endpoint_url = 'http://subwp1.digidez.com/wp-json/webhook/v1/';
	private $export_plugins_list = [
		'bb-custom-modules-for-beaver-builder',
		'better-life-summits',
		'site-system-synchronizer',
		#'gravityforms',
	];

	public static function initialise(){
		$self = new self();

		add_action('wp_ajax_post_to_subsites', [$self, 'postToSubSites']);
		#add_action('wp_ajax_nopriv_post_to_subsites', [$self, 'postToSubSites']);

	}

	public function postToSubSites(){
		$res = [];

		$res[] = $this->postBeaverBuilderPosts();
		#$res[] = $this->postACFPosts(); // Don't use this
		$res[] = $this->postACFSettings();
		$res[] = $this->postUltimateDashboardSettings();
		$res[] = $this->postUltimateDashboardPosts();
		$res[] = $this->postPageBuilderFrameworkSettings();
		$res[] = $this->postPageBuilderFrameworkPosts();
		$res[] = $this->sendPluginsList();

		#Helper::_log($res);

		die(json_encode($res));
	}

	private function postPageBuilderFrameworkSettings(): array{
		$res = ['error' => 0, 'message' => esc_attr__('Data received successfully', S3_TD), 'result' => ''];

		if(!defined('ULTIMATE_DASHBOARD_PLUGIN_DIR')) return $res;

		$udb_settings = DataSource::get_results("SELECT * FROM prefix_options WHERE option_name LIKE 'wpbf%'", ARRAY_A);

		if(!empty($udb_settings)){
			#Helper::_log($udb_settings);

			$body = [
				'action' => 'UpdateOrInsertOptions',
				'domain' => site_url(),
				'posts' => $udb_settings,
			];

			$data = [
				'body' => json_encode($body),
				'headers' => ['Content-Type' => 'application/json'],
				'timeout' => 60,
				'httpversion' => '1.1',
				'sslverify' => false,
				'redirection' => 15,
			];

			#Helper::_log($data);

			$res['result'] = wp_remote_post($this->remote_endpoint_url.'wpbf/data', $data);
		}

		return $res;
	}

	private function postPageBuilderFrameworkPosts(): array{
		$res = ['error' => 0, 'message' => esc_attr__('Data received successfully', S3_TD), 'result' => ''];

		$posts = DataSource::getPageBuilderFrameworkPosts([
			'include_custom_fields' => false,
			'include_post_metas' => true,
			'posts_per_page' => -1,
			'orderby' => 'ID',
			'order' => 'ASC',
		]);

		#Helper::_log($posts->posts);

		$res['found_posts'] = $posts->found_posts;

		$body = [
			'action' => 'UpdateOrInsertPosts',
			'useUpdatePostContentLogic' => true,
			'domain' => site_url(),
			'posts' => $posts->posts,
		];

		$data = [
			'body' => json_encode($body),
			'headers' => ['Content-Type' => 'application/json'],
			'timeout' => 60,
			'httpversion' => '1.1',
			'sslverify' => false,
			'redirection' => 15,
		];

		#Helper::_log($data);

		$res['result'] = wp_remote_post($this->remote_endpoint_url.'wpbf/data', $data);

		return $res;
	}

	private function postUltimateDashboardSettings(): array{
		$res = ['error' => 0, 'message' => esc_attr__('Data received successfully', S3_TD), 'result' => ''];

		if(!defined('ULTIMATE_DASHBOARD_PLUGIN_DIR')) return $res;

		$udb_settings = DataSource::get_results("SELECT * FROM prefix_options WHERE option_name LIKE 'udb%'", ARRAY_A);

		if(!empty($udb_settings)){
			#Helper::_log($udb_settings);

			$body = [
				'action' => 'UpdateOrInsertOptions',
				'domain' => site_url(),
				'posts' => $udb_settings,
			];

			$data = [
				'body' => json_encode($body),
				'headers' => ['Content-Type' => 'application/json'],
				'timeout' => 60,
				'httpversion' => '1.1',
				'sslverify' => false,
				'redirection' => 15,
			];

			#Helper::_log($data);

			$res['result'] = wp_remote_post($this->remote_endpoint_url.'udb/data', $data);
		}

		return $res;
	}

	private function postUltimateDashboardPosts(): array{
		$res = ['error' => 0, 'message' => esc_attr__('Data received successfully', S3_TD), 'result' => ''];

		if(!defined('ULTIMATE_DASHBOARD_PLUGIN_DIR')) return $res;

		$posts = DataSource::getUltimateDashboardPosts([
			'include_custom_fields' => false,
			'include_post_metas' => true,
			'posts_per_page' => -1,
			'orderby' => 'ID',
			'order' => 'ASC',
		]);

		#Helper::_debug($posts->posts);

		$res['found_posts'] = $posts->found_posts;

		$body = [
			'action' => 'UpdateOrInsertPosts',
			'useUpdatePostContentLogic' => false,
			'domain' => site_url(),
			'posts' => $posts->posts,
		];

		$data = [
			'body' => json_encode($body),
			'headers' => ['Content-Type' => 'application/json'],
			'timeout' => 60,
			'httpversion' => '1.1',
			'sslverify' => false,
			'redirection' => 15,
		];

		#Helper::_log($data);

		$res['result'] = wp_remote_post($this->remote_endpoint_url.'udb/data', $data);

		return $res;
	}

	private function postACFSettings(): array{
		$res = ['error' => 0, 'message' => esc_attr__('Data received successfully', S3_TD), 'result' => ''];

		if(!class_exists('ACF')) return $res;

		$acf_group_posts = DataSource::getACFGroupPosts([
			'include_custom_fields' => false,
			'include_post_metas' => false,
			'posts_per_page' => -1,
			'orderby' => 'ID',
			'order' => 'ASC',
		]);

		if(!$acf_group_posts->found_posts) return $res;

		$selected = [];
		foreach($acf_group_posts->posts as $_post)
			$selected[] = $_post->post_name;

		$json = [];

		foreach($selected as $key){
			// load field group
			$field_group = acf_get_field_group($key);

			// validate field group
			if(empty($field_group)){
				continue;
			}

			// load fields
			$field_group['fields'] = acf_get_fields($field_group);

			// prepare for export
			$field_group = acf_prepare_field_group_for_export($field_group);

			// add to json array
			$json[] = $field_group;
		}

		#Helper::_log($json);

		$body = [
			'action' => 'ImportSettings',
			'domain' => site_url(),
			'posts' => $json,
		];

		$data = [
			'body' => json_encode($body),
			'headers' => ['Content-Type' => 'application/json'],
			'timeout' => 60,
			'httpversion' => '1.1',
			'sslverify' => false,
			'redirection' => 15,
		];

		#Helper::_log($data);

		$res['result'] = wp_remote_post($this->remote_endpoint_url.'acf/data', $data);

		return $res;
	}

	private function postACFPosts(): array{
		$res = ['error' => 0, 'message' => esc_attr__('Data received successfully', S3_TD), 'result' => ''];

		if(!class_exists('ACF')) return $res;

		$posts = DataSource::getACFPosts([
			'include_custom_fields' => false,
			'include_post_metas' => true,
			'posts_per_page' => -1,
			'orderby' => 'ID',
			'order' => 'ASC',
		]);

		#Helper::_debug($posts->posts);

		$res['found_posts'] = $posts->found_posts;

		$body = [
			'action' => 'UpdateOrInsertPosts',
			'useUpdatePostContentLogic' => false,
			'domain' => site_url(),
			'posts' => $posts->posts,
		];

		$data = [
			'body' => json_encode($body),
			'headers' => ['Content-Type' => 'application/json'],
			'timeout' => 60,
			'httpversion' => '1.1',
			'sslverify' => false,
			'redirection' => 15,
		];

		#Helper::_log($data);

		$res['result'] = wp_remote_post($this->remote_endpoint_url.'acf/data', $data);

		return $res;
	}

	private function postBeaverBuilderPosts(): array{
		$res = ['error' => 0, 'message' => esc_attr__('Data received successfully', S3_TD), 'result' => ''];

		if(!defined('FL_BUILDER_VERSION')) return $res;

		$posts = DataSource::getBeaverBuilderPosts([
			'include_custom_fields' => false,
			'include_post_metas' => true,
			'posts_per_page' => -1,
			'orderby' => 'ID',
			'order' => 'ASC',
		]);

		#Helper::_debug($posts->posts);

		$res['found_posts'] = $posts->found_posts;

		$body = [
			'action' => 'UpdateOrInsertPosts',
			'useUpdatePostContentLogic' => false,
			'domain' => site_url(),
			'posts' => $posts->posts,
		];

		$data = [
			'body' => json_encode($body),
			'headers' => ['Content-Type' => 'application/json'],
			'timeout' => 60,
			'httpversion' => '1.1',
			'sslverify' => false,
			'redirection' => 15,
		];

		#Helper::_log($data);

		$res['result'] = wp_remote_post($this->remote_endpoint_url.'beaver_builder', $data);

		return $res;
	}

	private function sendPluginsList(): array{
		$res = ['error' => 0, 'message' => esc_attr__('Data received successfully', S3_TD), 'result' => ''];

		if(!class_exists('ZipArchive')) return $res;

		$body = [
			'action' => 'UpdateOrInstallPlugins',
			'domain' => site_url(),
			'plugins' => $this->getPluginsArchiveFiles(),
		];

		#Helper::_log($body);

		$data = [
			'body' => json_encode($body),
			'headers' => ['Content-Type' => 'application/json'],
			'timeout' => 60,
			'httpversion' => '1.1',
			'sslverify' => false,
			'redirection' => 15,
		];

		$res['result'] = wp_remote_post($this->remote_endpoint_url.'plugins/worker', $data);

		return $res;

	}

	private function getPluginsArchiveFiles(): array{
		$result = [];

		Helper::clearDownloadFolder();

		$plugins_dirs = array_diff(scandir(WP_PLUGIN_DIR), ['.', '..']);

		foreach($plugins_dirs as $plugin_folder){
			if(!in_array($plugin_folder, $this->export_plugins_list)) continue;

			$date = date('Y-m-d-H-i-s');

			$dst = sprintf('%s%s%s-%s%s', S3_DOWNLOAD_DIR, DIRECTORY_SEPARATOR, $date, $plugin_folder, '.zip');
			$source = sprintf('%s%s%s', WP_PLUGIN_DIR, DIRECTORY_SEPARATOR, $plugin_folder);

			if(!file_exists($source)){
				Helper::_log($source.' NOT EXIST');
				continue;
			}

			$result[] = [
				'status' => $this->createZipFile($source, $dst),
				'folder' => $plugin_folder,
				'url' => sprintf('%s%s%s-%s%s', S3_DOWNLOAD_URL, DIRECTORY_SEPARATOR, $date, $plugin_folder, '.zip'),
			];
		}

		return $result;
	}

	private function createZipFile($source, $dst){
		$zip = new ZipArchive();
		$zip->open($dst, ZipArchive::CREATE | ZipArchive::OVERWRITE);

		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($source, FilesystemIterator::CURRENT_AS_SELF),
			RecursiveIteratorIterator::SELF_FIRST
		);

		#$zip->addFile(str_replace($source, '', $source));

		foreach($files as $name => $file){
			#Helper::_log($file->getBasename());

			if(!$file->isDir()){
				$filePath = $file->getRealPath();
				$relativePath = substr($filePath, strlen(WP_PLUGIN_DIR.DIRECTORY_SEPARATOR));
				$zip->addFile($filePath, $relativePath);
			}
		}

		$status = $zip->status;
		$zip->close();

		return $status;
	}


}
