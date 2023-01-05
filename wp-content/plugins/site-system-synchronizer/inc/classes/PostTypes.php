<?php
namespace SiteSystemSynchronizer;

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
		    'name'                  => _x($plural_name, 'Post Type General Name', S3_TD),
		    'singular_name'         => _x($name, 'Post Type Singular Name', S3_TD),
		    'menu_name'             => __($plural_name, S3_TD),
		    'name_admin_bar'        => __($plural_name, S3_TD),
		    'archives'              => __($plural_name, S3_TD),
		    'attributes'            => __('Item Attributes', S3_TD),
		    'parent_item_colon'     => __('Parent Item:', S3_TD),
		    'all_items'             => __('All '.$plural_name, S3_TD),
		    'add_new_item'          => __('Add new '.$name, S3_TD),
		    'add_new'               => __('Add new '.$name, S3_TD),
		    'new_item'              => __('New '.$name, S3_TD),
		    'edit_item'             => __('Edit '.$name, S3_TD),
		    'update_item'           => __('Update '.$name, S3_TD),
		    'view_item'             => __('View '.$name, S3_TD),
		    'view_items'            => __('View items', S3_TD),
		    'search_items'          => __('Search Item', S3_TD),
		    'not_found'             => __('Not found', S3_TD),
		    'not_found_in_trash'    => __('Not found in Trash', S3_TD),
		    'featured_image'        => __($name.' image', S3_TD),
		    'set_featured_image'    => __('Set image', S3_TD),
		    'remove_featured_image' => __('Remove image', S3_TD),
		    'use_featured_image'    => __('Use as '.$name.' image', S3_TD),
		    'insert_into_item'      => __('insert into item', S3_TD),
		    'uploaded_to_this_item' => __('Uploaded to this item', S3_TD),
		    'items_list'            => __('Items list', S3_TD),
		    'items_list_navigation' => __('Items list navigation', S3_TD),
		    'filter_items_list'     => __('Filter items list', S3_TD),
	    ];

	    return $labels;
    }

	public function postTypeFunction(){
		self::create_cpt_subsites();
	}
	
	public function create_cpt_subsites(){
		$cpt = 'subsite';
		$name = 'SubSite';
		$labels = $this->createLabels($name);
		$args = [
			'label'               => __($name, S3_TD),
			'description'         => __($name.'s', S3_TD),
			'labels'              => $labels,
			'supports'            => ['title', 'custom-fields', 'page-attributes'],
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => false,
			'show_in_menu'        => false,
			'show_in_rest'        => false,
			'hierarchical'        => false,
			'rest_base'           => '',
			'exclude_from_search' => true,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-admin-post',
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => false,
			'can_export'          => false,
			'has_archive'         => false,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'query_var'           => true,
			#'taxonomies'          => ['category'],
			'rewrite'             => ['slug' => $cpt, 'with_front' => false, 'pages' => false, 'feeds' => false, 'feed' => false],
			'capabilities' => [
				#'create_posts' => 'do_not_allow'
			],
		];

		register_post_type($cpt, $args);
	}


}
