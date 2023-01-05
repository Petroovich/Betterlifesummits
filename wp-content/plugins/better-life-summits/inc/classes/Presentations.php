<?php
namespace BetterLifeSummits;
use WP_Query;
use WP_HTTP_Requests_Response;

class Presentations {

	private static $_instance = null;
	private $post_type = 'presentations';
	private $days = 0;

	public static function instance(){
		if(is_null(self::$_instance))
			self::$_instance = new Presentations();
		return self::$_instance;
	}

	public function initialise(){
		if(wp_doing_ajax()){
			add_action('wp_ajax_save_presentation_post', [$this, 'save_post']);
		}
	}

	public function displayPresentations(){
		$admin_page = BetterLifeSummits::$plugin_slug;
		$this->days = intval(get_field('how_many_days_does_the_event_have', 'option'));

		$args['limit'] = isset($_GET['limit']) ? intval($_GET['limit']) : -1;
		$args['paged'] = isset($_GET['paged']) ? intval($_GET['paged']) : 0;
		$args['s'] = isset($_GET['s']) ? $_GET['s'] : '';

		$post_type = isset($_GET['post_type']) ? $_GET['post_type'] : $this->post_type;

		$data = $this->getPresentations($args);

		#Helper::_debug($row_data);
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

		$terms = get_terms([
			'taxonomy' => 'category',
			'hide_empty' => false,
			'orderby' => 'slug',
			'custom_sorting' => 1,
			'slice_terms_by_days' => $this->days
		]);
		#Helper::_debug($terms);

		include_once(B3_PLUGIN_DIR.'/views/backend/presentations.php');
	}

	private function getPresentations($args): array{
		$res = ['groups' => [], 'terms' => [], 'posts' => [], 'found_posts' => 0];

		$args['posts_per_page'] = $args['limit'];
		$args['orderby'] = 'meta_value';
		$args['order'] = 'ASC';
		$args['meta_key'] = 'speaker_slot_of_the_day';

		$query = DataSource::getPresentationsPosts($args);
		$res['found_posts'] = $query->found_posts;

		if($query->post_count){
			$term_nums = $groups = [];
			foreach($query->posts as $_post){
				$term_id = $_post->cf['day_of_event']->term_id;
				$num = str_replace('day-', '', $_post->cf['day_of_event']->slug);
				if($num <= $this->days){
					$term_nums[$num] = $term_id;
					$groups[$term_id][] = $_post->ID;
					$res['terms'][$term_id] = $_post->terms[0];
					$res['posts'][$_post->ID] = $_post;
				}
			}
		}

		ksort($term_nums);

		foreach($term_nums as $term_id)
			$res['groups'][$term_id] = $groups[$term_id];

		unset($groups, $term_nums);

		#Helper::_debug($res);
		#Helper::_debug($query->posts);

		return $res;
	}

	public function save_post(){
		if(!wp_verify_nonce($_POST['nonce'], 'bls-ajax-nonce')){
			wp_send_json_error(['message' => __('Bed nonce code', B3_TD)]);
		}

		$form_data = [];
		parse_str($_POST['form_data'], $form_data);

		foreach($form_data as $k => $v){
			if($k != 'post_id'){
				update_post_meta($form_data['post_id'], $k , $v);
			}
		}

		wp_send_json_success(['message' => __('Entry successfully saved!<br>The page will reload in a few seconds.', B3_TD)]);
	}

}
