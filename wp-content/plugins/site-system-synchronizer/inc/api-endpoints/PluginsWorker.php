<?php
/** ACF Endpoint */

namespace SiteSystemSynchronizer\ApiEndpoints;

use PHPMailer\PHPMailer\Exception;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;
use SiteSystemSynchronizer\Helper;
use ZipArchive;
use Plugin_Upgrader;

class PluginsWorker extends BaseEndpoint {

	public static $route = 'plugins/worker';
	private static $_instance = null;
	private array $active_plugins;
	private array $inactive_plugins;

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

		return [
			'status'   => 200,
			'data' => self::instance()->doAction($body['action'], $body['plugins'])
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
		add_filter('http_request_args', [$this, 'http_request_args'], 999);

		$res = [];
		
		if($this->sync_global){
			switch($action){
				case "UpdateOrInstallPlugins":
					$res = $this->ImportPlugins($data);
					break;
				default:
					break;
			}
		}
		
		return $res;
	}

	private function ImportPlugins($data): array{
		$res = [];
		#Helper::_log($data);

		require_once ABSPATH.'wp-admin/includes/plugin.php';
		require_once ABSPATH.'wp-admin/includes/file.php';

		$this->active_plugins = get_option('active_plugins', []);

		Helper::clearDownloadFolder();

		if(!empty($data)){
			#$upgrader = new Plugin_Upgrader();

			foreach($data as $plugin_data){
				if(!$this->options['sync_items']['plugins_list'][$plugin_data['folder']]) continue;
				
				$plugin_data['file'] = S3_DOWNLOAD_DIR.DIRECTORY_SEPARATOR.basename($plugin_data['url']);
				#Helper::_log($plugin_data);
				if($result = $this->downloadPluginPackage($plugin_data)){
					$result = $this->updateOrInstallPlugin($plugin_data);
				}else{
					Helper::_log('file_exists = '.($result ? 1 : 0));
				}
			}

			$this->activatePlugins();
		}

		return $res;
	}

	private function downloadPluginPackage($plugin_data): bool{

		$tmp_path = download_url($plugin_data['url']);
		if(is_wp_error($tmp_path)){
			Helper::_log($plugin_data['url']);
			Helper::_log($tmp_path);

			return false;
		}

		try{
			copy($tmp_path, $plugin_data['file']);
		}catch(\Exception $e){
			Helper::_log($e->getMessage());
		}
		@unlink($tmp_path);

		return file_exists($plugin_data['file']);
	}

	private function updateOrInstallPlugin($plugin_data): int{

		$is_exist = $this->isExistPlugin($plugin_data['folder']);
		$status = $this->unzipFile($plugin_data['file']);
		$is_active = $this->isPluginActive($plugin_data['folder']);

		if(!$is_exist || !$is_active){
			/*Helper::_log([
				$plugin_data['folder'],
				$is_exist,
				$is_active,
			]);*/

			$this->inactive_plugins[] = $plugin_data['folder'];

			/*foreach($this->all_plugins as $plugin_path => $plugin_params){
				$folder = explode('/', $plugin_path)[0];
				Helper::_log($folder);
				if($folder == $plugin_data['folder']){
					$status = activate_plugin(WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.$plugin_path);
					if(is_wp_error($status)){
						Helper::_log($status);
					}
				}
			}*/

			/*$status = $this->installPlugin($plugin_data);
			if(is_wp_error($status)){
				Helper::_log($status);
			}*/
		}

		return $status;
	}

	private function activatePlugins(){
		if(empty($this->inactive_plugins)) return;

		$all_plugins = apply_filters('all_plugins', get_plugins());

		foreach($all_plugins as $plugin_path => $plugin_params){
			$folder = explode('/', $plugin_path)[0];
			Helper::_log('activate Plugin: '.$folder);
			if(in_array($folder, $this->inactive_plugins)){
				$status = activate_plugin(WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.$plugin_path);
				if(is_wp_error($status)){
					Helper::_log('activate Plugin: '.$status);
				}
			}
		}
	}

	private function isExistPlugin($name): bool{
		#Helper::_log(__METHOD__);
		$path = WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.$name;

		$res = file_exists($path);
		$res = is_dir($path) ?: $res;
		#$res = is_plugin_active($name) ?: $res;
		#Helper::_log($res);

		return $res;
	}

	private function unzipFile($file): int{
		$status = -1;

		$zip = new ZipArchive();

		if($zip->open($file, ZipArchive::RDONLY) === true){
			$zip->extractTo(WP_PLUGIN_DIR.DIRECTORY_SEPARATOR);
			$status = $zip->status;
			$zip->close();
		}

		return $status;
	}

	public function installPlugin($plugin_data){
		#Helper::_log(__METHOD__);
		require_once ABSPATH.'wp-admin/includes/plugin.php';

		$plugin_file = '';
		$files = array_diff(scandir(WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.$plugin_data['folder']), ['.', '..']);

		foreach($files as $file){
			/*if(!is_readable($plugin_data['folder']."/".$file)){
				continue;
			}*/

			if(Helper::get_file_ext($file) == 'php'){
				$plugin_params = get_plugin_data(WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.$plugin_data['folder'].DIRECTORY_SEPARATOR.$file);
				if(is_array($plugin_params)){
					if(
						(isset($plugin_params['name']) && !empty($plugin_params['name']))
						 ||
						(isset($plugin_params['Name']) && !empty($plugin_params['Name']))
					){
						//$wp_plugins[ plugin_basename( $plugin_file ) ] = $plugin_data;
						$plugin_file = WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.$plugin_data['folder'].DIRECTORY_SEPARATOR.$file;
						break;
					}
				}
			}
		}
		//uasort( $wp_plugins, '_sort_uname_callback' );
		
		#Helper::_log($wp_plugins);

		return empty($plugin_file) ? false : activate_plugin($plugin_file);
	}

	public function isPluginActive($plugin_folder): bool{
		#$this->active_plugins = get_option('active_plugins', []);

		if(empty($this->active_plugins)) return false;

		foreach($this->active_plugins as $plugin){
			if(str_contains($plugin, $plugin_folder)){
				return true;
			}
		}

		return false;
	}

	public function http_request_args($args){
		$args['reject_unsafe_urls'] = false;

		return $args;
	}
}
