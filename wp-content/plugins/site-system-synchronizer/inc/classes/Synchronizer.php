<?php
namespace SiteSystemSynchronizer;

use WP_Query;
use WP_HTTP_Requests_Response;
use ACF_Admin_Tool_Export;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;

class Synchronizer {

	private static $_instance = null;
	private $remote_endpoint_url = 'http://subwp1.digidez.com/wp-json/webhook/v1/';
	private $export_plugins_list = [
		'advanced-custom-fields-pro' => 'Advanced Custom Fields PRO',
		'bb-plugin' => 'Beaver Builder Plugin',
		'better-life-summits' => 'Better Life Summits',
		'bb-custom-modules-for-beaver-builder' => 'Custom Modules For Beaver Builder',
		'site-system-synchronizer' => 'Synchronizer',
		'ultimate-dashboard' => 'Ultimate Dashboard',
		'ultimate-dashboard-pro' => 'Ultimate Dashboard PRO',
		'wpbf-premium' => 'Page Builder Framework Premium Addon',
		'gravityforms' => 'Gravity Forms',
		'assistant' => 'Assistant',
		'disable-gutenberg' => 'Disable Gutenberg',
	];
	private $export_elements = [
		#'acf_posts' => 'ACF posts',
		'plugins_list' => 'Plugins',
		'bb_posts' => 'Beaver Builder Templates',
		'acf_settings' => 'ACF Settings',
		'udb_settings' => 'Ultimate Dashboard Settings',
		'udb_posts' => 'Ultimate Dashboard Posts',
		'wpbf_settings' => 'Page Builder Framework Settings',
		'wpbf_posts' => 'Page Builder Framework Posts',
	];
	private array $plugins_archive_files = [];

	public static function instance(){
		if(is_null(self::$_instance))
			self::$_instance = new Synchronizer();
		return self::$_instance;
	}

	public function initialise(){
		if(wp_doing_ajax()){
			add_action('wp_ajax_post_to_subsites', [$this, 'runApiRequestsToSubSites']);
		}
	}

	public function getElements(){
		$this->export_elements['plugins_list'] = $this->export_plugins_list;

		return $this->export_elements;
	}

	public function runApiRequestsToSubSites(){
		if(!S3_MASTER){
			wp_send_json_error(['message' => __('This is not Master site', S3_TD)]);
		}

		if(!wp_verify_nonce($_POST['nonce'], 's3-ajax-nonce')){
			wp_send_json_error(['message' => __('Bed nonce code', S3_TD)]);
		}

		$res = [];

		$subsite_id = isset($_POST['subsite_id']) ? $_POST['subsite_id'] : 0;

		$ss_posts = $this->getSubSitesPosts($subsite_id);

		if($ss_posts->found_posts == 0){
			wp_send_json_error(['message' => __('Subsite posts not found!', S3_TD)]);
		}

		foreach($ss_posts->posts as $_post){
			$this->remote_endpoint_url = $_post->metas['site_url'].$_post->metas['api_path'];
			
			$checkAPI = $this->checkAPI();
			#Helper::_log($checkAPI);

			$elements = !empty($_post->metas['elements']) ? unserialize($_post->metas['elements']) : [];
			#Helper::_debug($elements); exit;

			if(isset($checkAPI['code']) && $checkAPI['code'] == 'rest_no_route'){
				$res[$_post->ID]['check_api'] = ['message' => __('There are no REST API routes on the site.<br>Please install and activate the "'.S3_PLUGIN_NAME.'" plugin.'), 'status' => 'fail'];
			}elseif(isset($checkAPI['result']) && intval($checkAPI['result']) != 1){
				$res[$_post->ID]['check_api'] = ['message' => __('Plugin "'.S3_PLUGIN_NAME.'" not found or not activated.<br>Please install or activate the "'.S3_PLUGIN_NAME.'" plugin.'), 'status' => 'fail'];
			}else{
				$res[$_post->ID]['check_api'] = ['message' => '', 'status' => 'ok'];

				if(isset($elements['plugins_list']) && !empty($elements['plugins_list'])){
					$res[$_post->ID]['plugins_list'] = $this->sendPluginsList($elements['plugins_list']);
				}
				if(isset($elements['acf_posts']) && $elements['acf_posts'] == 'on'){
					$res[$_post->ID]['acf_posts'] = $this->postACFPosts(); // Don't use this
				}
				if(isset($elements['bb_posts']) && $elements['bb_posts'] == 'on'){
					$res[$_post->ID]['bb_posts'] = $this->postBeaverBuilderPosts();
				}
				if(isset($elements['acf_settings']) && $elements['acf_settings'] == 'on'){
					$res[$_post->ID]['acf_settings'] = $this->postACFSettings();
				}
				if(isset($elements['udb_settings']) && $elements['udb_settings'] == 'on'){
					$res[$_post->ID]['udb_settings'] = $this->postUltimateDashboardSettings();
				}
				if(isset($elements['udb_posts']) && $elements['udb_posts'] == 'on'){
					$res[$_post->ID]['udb_posts'] = $this->postUltimateDashboardPosts();
				}
				if(isset($elements['wpbf_settings']) && $elements['wpbf_settings'] == 'on'){
					$res[$_post->ID]['wpbf_settings'] = $this->postPageBuilderFrameworkSettings();
				}
				if(isset($elements['wpbf_posts']) && $elements['wpbf_posts'] == 'on'){
					$res[$_post->ID]['wpbf_posts'] = $this->postPageBuilderFrameworkPosts();
				}
			}
		}

		wp_send_json_success(['results' => $res, 'message' => __('Synchronization done!', S3_TD)]);
	}

	private function getSubSitesPosts($subsite_id): WP_Query{
		$args = [
			'include_custom_fields' => false,
			'include_post_metas' => true,
			'post_status' => 'private',
			'posts_per_page' => -1,
			'orderby' => 'ID',
			'order' => 'ASC',
			'meta_key'   => 'sync_me',
			'meta_value' => 'on'
		];

		if($subsite_id > 0){
			$args['p'] = $subsite_id;
		}

		return DataSource::getSubSitePosts($args);
	}

	public static function toJsonElementsData($data){
		return json_encode(unserialize($data));
	}

	/** ------------------ DATA EXPORT METHODS ------------------ **/
	
	private function checkAPI(): array{
		$res = ['error' => 0, 'message' => esc_attr__('Data received successfully', S3_TD)];
		
		$body = [
			'action' => 'CheckPluginExist',
			'plugin_name' => S3_PLUGIN_DIR_SHORT,
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
		
		$result = wp_remote_post($this->remote_endpoint_url.'check/api', $data);
		$res = array_merge($res, json_decode($result['body'], true));

		return $res;
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

	private function sendPluginsList($approved_plugins_list): array{
		$res = ['error' => 0, 'message' => esc_attr__('Data received successfully', S3_TD), 'result' => ''];

		if(!class_exists('ZipArchive')) return $res;

		$body = [
			'action' => 'UpdateOrInstallPlugins',
			'domain' => site_url(),
			'plugins' => $this->getPluginsArchiveFiles($approved_plugins_list),
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

	private function getPluginsArchiveFiles($approved_plugins_list): array{
		$result = [];

		if(!empty($this->plugins_archive_files))
			return $this->plugins_archive_files;

		Helper::clearDownloadFolder();

		$plugins_dirs = array_diff(scandir(WP_PLUGIN_DIR), ['.', '..']);

		foreach($plugins_dirs as $plugin_folder){
			if(!in_array($plugin_folder, array_keys($this->export_plugins_list))) continue;

			if(isset($approved_plugins_list[$plugin_folder])){
				if($approved_plugins_list[$plugin_folder] != 'on') continue;
			}else continue;

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

		$this->plugins_archive_files = $result;

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
