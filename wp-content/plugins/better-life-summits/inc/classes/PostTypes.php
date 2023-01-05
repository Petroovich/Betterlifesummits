<?php
namespace BetterLifeSummits;

class PostTypes {

	public static $post_types_taxonomies = [];

    public static function initialise(){
        $self = new self();

        // define all action hooks here and document if not self explanatory
        add_action('init', [$self, 'postTypeFunction'], 0);
    }

    private function createLabels($name, $plural_name = ''){
    	if(empty($plural_name)){
		    $plural_name = $name;
		
		    if(substr($name, -1, 1) == 's'){
			    $plural_name .= 'es';
		    }else{
			    $plural_name .= 's';
		    }
	    }
    	
	    $labels = [
		    'name'                  => _x($plural_name, 'Post Type General Name', B3_TD),
		    'singular_name'         => _x($name, 'Post Type Singular Name', B3_TD),
		    'menu_name'             => __($plural_name, B3_TD),
		    'name_admin_bar'        => __($plural_name, B3_TD),
		    'archives'              => __($plural_name, B3_TD),
		    'attributes'            => __('Item Attributes', B3_TD),
		    'parent_item_colon'     => __('Parent Item:', B3_TD),
		    'all_items'             => __('All '.$plural_name, B3_TD),
		    'add_new_item'          => __('Add new '.$name, B3_TD),
		    'add_new'               => __('Add new '.$name, B3_TD),
		    'new_item'              => __('New '.$name, B3_TD),
		    'edit_item'             => __('Edit '.$name, B3_TD),
		    'update_item'           => __('Update '.$name, B3_TD),
		    'view_item'             => __('View '.$name, B3_TD),
		    'view_items'            => __('View items', B3_TD),
		    'search_items'          => __('Search Item', B3_TD),
		    'not_found'             => __('Not found', B3_TD),
		    'not_found_in_trash'    => __('Not found in Trash', B3_TD),
		    'featured_image'        => __($name.' image', B3_TD),
		    'set_featured_image'    => __('Set image', B3_TD),
		    'remove_featured_image' => __('Remove image', B3_TD),
		    'use_featured_image'    => __('Use as '.$name.' image', B3_TD),
		    'insert_into_item'      => __('insert into item', B3_TD),
		    'uploaded_to_this_item' => __('Uploaded to this item', B3_TD),
		    'items_list'            => __('Items list', B3_TD),
		    'items_list_navigation' => __('Items list navigation', B3_TD),
		    'filter_items_list'     => __('Filter items list', B3_TD),
	    ];

	    return $labels;
    }

	public function postTypeFunction(){
		self::create_presentations_cpt();
		self::create_backendpages_cpt();
	}
	
	public function create_presentations_cpt(){
		$cpt = 'presentations';
		$name = 'Presentation';
		$labels = $this->createLabels($name);
		$args = [
			'label'               => __($name, B3_TD),
			'description'         => __($name.'s', B3_TD),
			'labels'              => $labels,
			'supports'            => ['title', 'thumbnail', 'custom-fields', 'revisions', 'post-formats', 'page-attributes'],
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_rest'        => false,
			'hierarchical'        => false,
			'rest_base'           => '',
			'exclude_from_search' => false,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-admin-post',
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'query_var'           => true,
			'taxonomies'          => ['category'],
			'rewrite'             => ['slug' => $cpt, 'with_front' => false, 'pages' => false, 'feeds' => false, 'feed' => false],
			'capabilities' => [
				#'create_posts' => 'do_not_allow'
			],
		];

		register_post_type($cpt, $args);
		#self::$post_types_taxonomies[$cpt.'-cat'] = ['cpt' => $cpt, 'label' => ['name' => 'Categories', 'singular_name' => 'Category'], 'args' => ['public' => false]];
	}
	
	public function create_backendpages_cpt(){
		$cpt = 'backendpages';
		$name = 'Backend Page';
		$labels = $this->createLabels($name);
		$args = [
			'label'               => __($name, B3_TD),
			'description'         => __($name.'s', B3_TD),
			'labels'              => $labels,
			'supports'            => ['title', 'custom-fields', 'post-formats', 'page-attributes'],
			'hierarchical'        => false,
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_rest'        => false,
			'rest_base'           => '',
			'show_in_menu'        => true,
			'exclude_from_search' => false,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-admin-post',
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => true,
			'can_export'          => false,
			'has_archive'         => false,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'query_var'           => true,
			#'taxonomies'          => [$cpt.'-cat'],
			'rewrite'             => ['slug' => $cpt, 'with_front' => false, 'pages' => false, 'feeds' => false, 'feed' => false],
			'capabilities' => [
				#'create_posts' => 'do_not_allow'
			],
		];

		register_post_type($cpt, $args);
		#self::$post_types_taxonomies[$cpt.'-cat'] = ['cpt' => $cpt, 'label' => ['name' => 'Categories', 'singular_name' => 'Category'], 'args' => ['public' => false]];
	}
	
}
