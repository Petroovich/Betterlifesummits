<?php
namespace BetterLifeSummits;

use WP_Term_Query;

class Addons{

	private static $cats = null;
	private static $filter_cats = false;

	public static function initialise(){
		$self = new self();

		add_filter('acf/fields/taxonomy/query', [$self, 'acf_fields_taxonomy_query'], 10, 3);
		add_filter('get_terms', [$self, 'sorting_terms'], 10, 4);
		add_filter('get_terms', [$self, 'slice_terms'], 20, 4);
		#add_filter('wp_terms_checklist_args', [$self, 'wp_terms_checklist_args'], 1, 2);
		#add_filter('acf/load_field/name=day_of_event', [$self, 'acf_load_day_of_event_field_choices']);

		add_action('saved_category', [$self, 'saved_category'], 10, 3);
		add_filter('acf/update_value', [$self, 'acf_update_value'], 10, 3);
	}

	/**
	 * Test Function/
	 * NOT USED.
	 * @param $field
	 * @return mixed
	 */
	public static function acf_load_day_of_event_field_choices($field){

		#Helper::_debug($field); exit;

		// reset choices
		$field['choices'] = [];

		$days = intval(get_field('how_many_days_does_the_event_have', 'option'));
		#Helper::_debug($days);

		$terms = get_terms(['taxonomy' => 'category', 'hide_empty' => false, 'orderby' => 'slug']);
		#Helper::_debug($terms);

		if(!empty($terms)){
			for($i = 1; $i <= $days; $i++){
				foreach($terms as $term){
					if(str_contains($term->slug, 'day-')){
						$num = explode('-', $term->slug)[1];
						if($num == $i){
							$field['choices'][$term->term_id] = $term;
						}
					}
				}
			}
		}


		// return the field
		return $field;

	}

	/**
	 * Function for filtering terms by event days count
	 *
	 * @param $args
	 * @param $field
	 * @param $post_id
	 * @return mixed
	 */
	public static function acf_fields_taxonomy_query($args, $field, $post_id){
		$args['orderby'] = 'name';
		$args['order'] = 'DESC';
		$args['custom_sorting'] = 1;

		$days = intval(get_field('how_many_days_does_the_event_have', 'option'));
		$terms = get_terms(['taxonomy' => 'category', 'hide_empty' => false, 'orderby' => 'slug', 'custom_sorting' => 0]);
		$include = [];

		if(!empty($terms)){
			for($i = 1; $i <= $days; $i++){
				foreach($terms as $term){
					if($term->slug == 'day-'.$i){
						$include[] = $term->term_id;
					}
				}
			}
		}

		if(!empty($include)){
			$args['include'] = $include;
		}

		return $args;
	}

	public function wp_terms_checklist_args($args, $post_id){
		self::$filter_cats = true;

		$args['filter_cats'] = true;

		return $args;
	}

	/**
	 * Function for `get_terms` filter-hook.
	 *
	 * @param array         $terms      Array of found terms.
	 * @param array|null    $taxonomies An array of taxonomies if known.
	 * @param array         $args       An array of get_terms() arguments.
	 * @param WP_Term_Query $term_query The WP_Term_Query object.
	 *
	 * @return array
	 */
	public static function sorting_terms($terms, $taxonomies, $args, $term_query){
		global $current_screen;
		#Helper::_debug($args);

		if((!is_null($current_screen) && $current_screen->id == 'presentations') ||
			(isset($args['custom_sorting']) && $args['custom_sorting'] == 1)){

			foreach($taxonomies as $taxonomy){
				if($taxonomy == 'category'){
					#Helper::_debug($taxonomy);

					/*if(!is_null(self::$cats) && !empty(self::$cats['category'])){
						return self::$cats['category'];
					}*/

					$sorted_terms = $other_terms = [];
					foreach($terms as $term){
						if(str_contains($term->slug, 'day-')){
							$num = explode('-', $term->slug)[1];
							$sorted_terms[$num] = $term;
						}else{
							$other_terms[] = $term;
						}
					}

					if(!empty($sorted_terms)){
						ksort($sorted_terms);
						$terms = array_merge($sorted_terms, $other_terms);
						#Helper::_debug($terms);
						#self::$cats['category'] = $terms;
					}
				}
			}

			/*if(isset($args['filter_cats'])){
				$days = intval(get_field('how_many_days_does_the_event_have', 'option'));
				if(!empty($terms)){
					Helper::_debug($terms);
					$_terms = $terms;
					$terms = [];

					for($i = 1; $i <= $days; $i++){
						foreach($_terms as $term){
							if($term->slug == 'day-'.$i){
								$terms[] = $term;
							}
						}
					}
				}
				#self::$filter_cats = false;
			}*/
		}

		return $terms;
	}

	public static function slice_terms($terms, $taxonomies, $args, $term_query){

		if(isset($args['slice_terms_by_days'])){

			foreach($taxonomies as $taxonomy){
				if($taxonomy == 'category'){
					$sorted_terms = [];
					foreach($terms as $term){
						if(str_contains($term->slug, 'day-')){
							$num = explode('-', $term->slug)[1];
							if($num <= $args['slice_terms_by_days'])
								$sorted_terms[$num] = $term;
						}
					}

					if(!empty($sorted_terms)){
						ksort($sorted_terms);
						$terms = $sorted_terms;
					}
				}
			}

		}

		return $terms;
	}

	/**
	 * Hook after category save
	 *
	 * @param $term_id
	 * @param $tt_id
	 * @param $taxonomy
	 * @return void
	 */
	public static function saved_category($term_id, $tt_id, $taxonomy){
		$term = get_term($term_id);

		if(str_contains($term->slug, 'day-')){
			$num = explode('-', $term->slug)[1];

			$field = acf_maybe_get_field('theme_day_'.$num, 'option');

			if(!empty($field)){
				$field_post = DataSource::get_row(DataSource::prepare("SELECT * FROM prefix_posts WHERE ID = %d", [$field['ID']]));
				$field_option_tmp = DataSource::get_row(DataSource::prepare("SELECT * FROM prefix_options WHERE option_value = %s", [$field['key']]));
				$field_option = DataSource::get_row(DataSource::prepare("SELECT * FROM prefix_options WHERE option_name = %s", [trim($field_option_tmp->option_name, '_')]));

				unset($field_option_tmp);

				$post_content = unserialize($field_post->post_content);
				$post_content['default_value'] = $term->description;
				$post_content = serialize($post_content);

				DataSource::update_data([
					'table' => 'prefix_posts',
					'primary' => ['name' => 'ID', 'value' => $field_post->ID],
					'post_content' => $post_content
				]);

				DataSource::update_data([
					'table' => 'prefix_options',
					'primary' => ['name' => 'option_id', 'value' => $field_option->option_id],
					'option_value' => $term->description
				]);

			}
		}

	}

	/**
	 * Hook after ACF update
	 * @param $value
	 * @param $post_id
	 * @param $field
	 * @return mixed
	 */
	public static function acf_update_value($value, $post_id, $field){

		if($post_id == 'options'){
			if(str_contains($field['name'], 'theme_day_')){
				$slug = sprintf('day-%d', explode('_', $field['name'])[2]);
				$term = get_term_by('slug', $slug, 'category');
				#Helper::_debug($term); exit;

				DataSource::update_data([
					'table' => 'prefix_term_taxonomy',
					'primary' => ['name' => 'term_id', 'value' => $term->term_id],
					'description' => $value
				]);
			}
		}

		return $value;
	}

}

