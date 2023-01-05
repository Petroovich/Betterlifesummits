<?php
namespace SiteSystemSynchronizer;

use WP_Query;

class DataSource {

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

	public static function getPostCustomFields($post, $only_metas = false){
		$cf = [];

		if($only_metas || !class_exists('ACF')){
			$result = self::get_results(self::prepare("SELECT meta_key, meta_value FROM prefix_postmeta WHERE post_id = %d AND meta_key NOT LIKE %s", [$post->ID, '_edit%']), ARRAY_A);
			$cf = Helper::formatMetasAsKeyValue($result);
			#$cf = get_post_meta($post->ID, '', true);
		}else{
			$cf = get_fields($post->ID);
		}

		return $cf;
	}

	public static function getPosts($params = [], $post_args = []){
		$_posts = new WP_Query($post_args);

		if(!isset($params['include_post_metas'])){
			$params['include_post_metas'] = false;
		}
		if(!isset($params['include_post_terms'])){
			$params['include_post_terms'] = false;
		}
		if(!isset($params['remove_post_content'])){
			$params['remove_post_content'] = false;
		}
		if(!isset($params['remove_post_fields'])){
			$params['remove_post_fields'] = [];
		}

		if($params['include_custom_fields'] || $params['include_post_metas'] || $params['include_post_terms'] || $params['remove_post_content'] || $params['remove_post_fields']){
			foreach($_posts->posts as $k => $_post){
				if($params['include_custom_fields']){
					$_posts->posts[$k]->cf = self::getPostCustomFields($_post);
				}
				if($params['include_post_metas']){
					$_posts->posts[$k]->metas = self::getPostCustomFields($_post, true);
				}
				if($params['include_post_terms']){
					$_posts->posts[$k]->terms = wp_get_post_terms($_post->ID, ['category'], ['fields' => 'all']);
				}
				if($params['remove_post_content']){
					$_posts->posts[$k]->post_content = '';
				}
				if(!empty($params['remove_post_fields'])){
					foreach($params['remove_post_fields'] as $field)
						unset($_posts->posts[$k]->$field);
				}
			}
		}

		return $_posts;
	}

	public static function getACFGroupPosts($params = []): WP_Query{
		$args = wp_parse_args($params, [
			'post_type'      => 'acf-field-group',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'offset'         => 0,
			'orderby'        => 'date',
			'order'          => 'DESC',
		]);

		return self::getPosts($params, $args);
	}

	public static function getACFPosts($params = []): WP_Query{
		$args = wp_parse_args($params, [
			'post_type'      => ['acf-field-group', 'acf-field'],
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'offset'         => 0,
			'orderby'        => 'date',
			'order'          => 'DESC',
		]);

		return self::getPosts($params, $args);
	}

	public static function getUltimateDashboardPosts($params = []): WP_Query{
		$args = wp_parse_args($params, [
			'post_type'      => ['udb_widgets', 'udb_admin_page'],
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'offset'         => 0,
			'orderby'        => 'post_type',
			'order'          => 'ASC',
		]);

		return self::getPosts($params, $args);
	}

	public static function getPageBuilderFrameworkPosts($params = []): WP_Query{
		$args = wp_parse_args($params, [
			'post_type'      => 'wpbf_hooks',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'offset'         => 0,
			'orderby'        => 'post_type',
			'order'          => 'ASC',
		]);

		return self::getPosts($params, $args);
	}

	public static function getBeaverBuilderPosts($params = []): WP_Query{
		$args = wp_parse_args($params, [
			'post_type'      => 'fl-builder-template',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'offset'         => 0,
			'orderby'        => 'date',
			'order'          => 'DESC',
		]);

		return self::getPosts($params, $args);
	}

	public static function getSubSitePosts($params = []): WP_Query{
		$args = wp_parse_args($params, [
			'post_type'      => 'subsite',
			'post_status'    => 'private',
			'posts_per_page' => -1,
			'offset'         => 0,
			'orderby'        => 'date',
			'order'          => 'DESC',
		]);

		$params['include_post_metas'] = true;
		$params['include_custom_fields'] = false;
		$params['include_post_terms'] = false;
		$params['remove_post_content'] = false;
		$params['remove_post_fields'] = [
			'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_excerpt', 'post_status',
			'comment_status', 'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged',
			'post_modified', 'post_modified_gmt', 'post_content_filtered', 'post_parent', 'guid',
			'menu_order', 'post_type', 'post_mime_type', 'comment_count', 'filter'
		];

		return self::getPosts($params, $args);
	}

}
