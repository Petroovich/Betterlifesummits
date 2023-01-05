<?php

class BBCMDataSource {

	public static function initialise(){}

	public static function insert_data($post_data){
		global $wpdb;

		$id = 0;

		if(!empty($post_data) && isset($post_data['table'])){

			$db_table = str_replace('prefix_', $wpdb->prefix, $post_data['table']);
			unset($post_data['table'], $post_data['primary']);

			if($wpdb->insert($db_table, $post_data)){
				$id = $wpdb->insert_id;
			}else{
				return $wpdb->last_error;
			}

			unset($db_table, $post_data);
		}

		return $id;
	}

	public static function delete_data($post_data){
		global $wpdb;
		$ret = false;

		if(!empty($post_data) && isset($post_data['table'])){
			$db_table = $post_data['table'];
			$db_table = str_replace('prefix_', $wpdb->prefix, $db_table);
			unset($post_data['table'], $post_data['primary']);

			$where = isset($post_data['where']) ? $post_data['where'] : [];

			$ret = $wpdb->delete($db_table, $where);

			unset($db_table, $post_data);
		}

		return $ret;
	}

	public static function update_data($post_data): int{
		global $wpdb;

		$id = 0;

		if(!empty($post_data) && isset($post_data['table'])){

			$value = $post_data['primary']['value'];
			$name = $post_data['primary']['name'];
			$db_table = $post_data['table'];
			$db_table = str_replace('prefix_', $wpdb->prefix, $db_table);
			unset($post_data['table'], $post_data['primary']);

			$wpdb->update($db_table, $post_data, [$name => $value]);

			unset($db_table, $post_data);
		}

		return $id;
	}

	public static function query($sql){
		global $wpdb;
		$sql = str_replace('prefix_', $wpdb->prefix, $sql);
		return $wpdb->query($sql);
	}

	public static function get_var($sql){
		global $wpdb;

		$sql = str_replace('prefix_', $wpdb->prefix, $sql);

		return $wpdb->get_var($sql);
	}

	public static function get_row($sql, $output = OBJECT){
		global $wpdb;

		$sql = str_replace('prefix_', $wpdb->prefix, $sql);

		return $wpdb->get_row($sql, $output);
	}

	public static function get_col($sql){
		global $wpdb;

		$sql = str_replace('prefix_', $wpdb->prefix, $sql);

		return $wpdb->get_col($sql);
	}

	public static function get_results($sql, $output = OBJECT){
		global $wpdb;

		$sql = str_replace('prefix_', $wpdb->prefix, $sql);

		return $wpdb->get_results($sql, $output);
	}

	public static function prepare($sql, $input_array){
		global $wpdb;

		return $wpdb->prepare($sql, $input_array);
	}

	public static function get_found_rows(){
		return self::get_var("SELECT FOUND_ROWS()");
	}

	public static function db_input($string){
		global $wpdb;
		return $wpdb->_real_escape($string);
	}

	/** CPTs **/

	public static function getCatsList($taxonomy = 'category'){
		$res = [];

		$cats = get_terms(['taxonomy' => $taxonomy]);
		#\SiteSystemSynchronizer\Helper::_dd($cats);

		foreach($cats as $cat)
			$res[$cat->slug] = sprintf('%s (%d)', $cat->name, $cat->count);

		return $res;
	}

	public static function formatMetasAsKeyValue($array): array{
		$new_array = [];

		if(empty($array)) return $array;

		foreach($array as $v)
			$new_array[$v['meta_key']] = $v['meta_value'];

		return $new_array;
	}

	public static function getPostCustomFields($post, $only_metas = false){
		$cf = [];

		if($only_metas || !class_exists('ACF')){
			$result = self::get_results(self::prepare("SELECT meta_key, meta_value FROM prefix_postmeta WHERE post_id = %d AND meta_key NOT LIKE %s", [$post->ID, '_edit%']), ARRAY_A);
			$cf = self::formatMetasAsKeyValue($result);
			#$cf = get_post_meta($post->ID, '', true);
		}else{
			$cf = get_fields($post->ID);
		}

		return $cf;
	}

	public static function getEntries($params = [], $post_args = []){
		$_posts = new WP_Query($post_args);
		#BBCMLoader::_debug($_posts);

		if($params['include_custom_fields'] || $params['include_post_metas']){
			foreach($_posts->posts as $k => $_post){
				if($params['include_custom_fields']){
					$_posts->posts[$k]->cf = self::getPostCustomFields($_post);
				}
				if($params['include_post_metas']){
					$_posts->posts[$k]->metas = self::getPostCustomFields($_post, true);
				}
			}
		}

		return $_posts;
	}

	public static function getPosts($params = []): WP_Query{
		$args = wp_parse_args($params, [
			'post_type'      => 'presentations',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'offset'         => 0,
			'orderby'        => 'date',
			'order'          => 'DESC',
		]);

		return self::getEntries($params, $args);
	}


	
}
