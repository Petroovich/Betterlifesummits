<?php
namespace SiteSystemSynchronizer;
use WP_Query;
use WP_HTTP_Requests_Response;

class SubSites {

	private static $_instance = null;
	private $post_type = 'subsite';

	public static function instance(){
		if(is_null(self::$_instance))
			self::$_instance = new SubSites();
		return self::$_instance;
	}

	public function initialise(){
		if(wp_doing_ajax()){
			add_action('wp_ajax_save_sub_site_post', [$this, 'save_post']);
			add_action('wp_ajax_delete_sub_site_post', [$this, 'delete_post']);
		}
	}

	public function displayPage(){
		$admin_page = SiteSystemSynchronizer::$plugin_slug;

		$args['limit'] = isset($_GET['limit']) ? intval($_GET['limit']) : -1;
		$args['paged'] = isset($_GET['paged']) ? intval($_GET['paged']) : 0;
		$args['s'] = isset($_GET['s']) ? $_GET['s'] : '';

		$post_type = isset($_GET['post_type']) ? $_GET['post_type'] : $this->post_type;

		$data = $this->getSubSites($args);
		#Helper::_debug($data);

		$results_count = $data['found_posts'];
		$QUERY_STRING = str_replace('&paged='.$args['paged'],'', $_SERVER['QUERY_STRING']);

		$navi = new Pagination("/wp-admin/edit.php?".$QUERY_STRING);
		$pagination_template = $navi->build($args['limit'], $results_count, $args['paged']);

		$limit_select = [
			['value' => -1, 'title' => 'All', 'selected' => ''],
			['value' => 1, 'title' => 1, 'selected' => ''],
			['value' => 5, 'title' => 5, 'selected' => ''],
			['value' => 10, 'title' => 10, 'selected' => ''],
			['value' => 15, 'title' => 15, 'selected' => ''],
			['value' => 20, 'title' => 20, 'selected' => ''],
			['value' => 25, 'title' => 25, 'selected' => ''],
			['value' => 30, 'title' => 30, 'selected' => ''],
			['value' => 40, 'title' => 40, 'selected' => ''],
			['value' => 50, 'title' => 50, 'selected' => ''],
			['value' => 75, 'title' => 75, 'selected' => ''],
			['value' => 100, 'title' => 100, 'selected' => ''],
		];

		foreach($limit_select as $k => $item){
			if($item['value'] == $args['limit']){
				$limit_select[$k]['selected'] = 'selected="selected"';
			}
		}

		$elements = Synchronizer::instance()->getElements();

		include_once(S3_PLUGIN_DIR.'/views/backend/sub-sites.php');
	}

	private function getSubSites($args): array{
		$res = ['posts' => [], 'found_posts' => 0];

		$args['posts_per_page'] = $args['limit'];
		$args['orderby'] = 'ID';
		$args['order'] = 'DESC';
		$args['post_status'] = 'private';

		$query = DataSource::getSubSitePosts($args);
		$res['found_posts'] = $query->found_posts;

		if($query->post_count){
			$res['posts'] = $query->posts;
		}

		return $res;
	}

	public function save_post(){
		if(!wp_verify_nonce($_POST['nonce'], 's3-ajax-nonce')){
			wp_send_json_error(['message' => __('Bed nonce code', S3_TD)]);
		}

		$form_data = [];
		parse_str($_POST['form_data'], $form_data);

		$post_id = intval($form_data['post_id']);

		$form_data['site_url'] = trim($form_data['site_url']);
		$form_data['site_url'] = trim($form_data['site_url'], '/');
		$form_data['sync_me'] = isset($form_data['sync_me']) ? 'on' : 'off';

		#Helper::_debug($form_data);

		$post_title = empty($form_data['post_title']) ? $form_data['site_url'] : $form_data['post_title'];
		$post_title = str_replace(['https://', 'http://', 'ftp://', '//'], '', $post_title);

		$update = false;

		if($post_id == 0){
			$post_id = DataSource::insert_data([
				'table' => 'prefix_posts',
				'post_title' => $post_title,
				'post_status' => 'private',
				'post_type' => $this->post_type,
			]);
		}else{
			$update = true;
			DataSource::update_data([
				'table' => 'prefix_posts',
				'primary' => ['name' => 'ID', 'value' => $post_id],
				'post_title' => $post_title
			]);
		}

		foreach($form_data as $k => $v){
			if($k != 'post_id' && $k != 'post_title'){
				if($update){
					update_post_meta($post_id, $k, $v);
				}else{
					add_post_meta($post_id, $k , $v);
				}
			}
		}

		wp_send_json_success(['message' => __('Entry successfully saved!<br>The page will reload in a few seconds.', S3_TD)]);
	}

	public function delete_post(){
		if(!wp_verify_nonce($_POST['nonce'], 's3-ajax-nonce')){
			wp_send_json_error(['message' => __('Bed nonce code', S3_TD)]);
		}

		$post_id = intval($_POST['post_id']);

		wp_delete_post($post_id);

		wp_send_json_success(['id' => $post_id, 'message' => __('Entry successfully deleted!<br>The page will reload in a few seconds.', S3_TD)]);
	}
}
