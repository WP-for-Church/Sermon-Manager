<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

/**
 * Class made to replace old functions for registering post types and taxonomies
 *
 * @since 2.7
 */
class SM_Post_Types {
	/**
	 * Hooks into WordPress filtering functions
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 4 );
		add_action( 'init', array( __CLASS__, 'register_meta_keys' ), 5 );
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
		add_action( 'init', array( __CLASS__, 'support_jetpack_omnisearch' ) );
		add_filter( 'rest_api_allowed_post_types', array( __CLASS__, 'rest_api_allowed_post_types' ) );
		add_action( 'sm_flush_rewrite_rules', array( __CLASS__, 'flush_rewrite_rules' ) );
	}

	public static function register_meta_keys() {
		$keys = array(
			'sermon_date' => array(
				'type'              => 'integer',
				'description'       => 'Date when sermon was preached.',
				'single'            => true,
				'sanitize_callback' => 'intval',
				'show_in_rest'      => true,
			)
		);

		foreach ( $keys as $key => $args ) {
			register_meta( 'wpfc_sermon', $key, $args, false );
		}
	}

	/**
	 * Register core taxonomies.
	 */
	public static function register_taxonomies() {
		if ( ! is_blog_installed() ) {
			return;
		}

		if ( taxonomy_exists( 'wpfc_preacher' ) ) {
			return;
		}

		do_action( 'sm_register_taxonomy' );

		$permalinks = sm_get_permalink_structure();

		register_taxonomy( 'wpfc_preacher',
			apply_filters( 'sm_taxonomy_objects_wpfc_preacher', array( 'wpfc_sermon' ) ),
			apply_filters( 'sm_taxonomy_args_wpfc_preacher', array(
				'hierarchical' => false,
				'label'        => __( 'Preachers', 'sermon-manager' ),
				'labels'       => array(
					'name'              => __( \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) . 's' : 'Preachers', 'sermon-manager' ),
					'singular_name'     => __( \SermonManager::getOption( 'preacher_label' ) ?: 'Preacher', 'sermon-manager' ),
					'menu_name'         => _x( \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) . 's' : 'Preachers', 'Admin menu name', 'sermon-manager' ),
					'search_items'      => __( 'Search' . ( \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) . 's' : 'preachers' ), 'sermon-manager' ),
					'all_items'         => __( 'All ' . ( \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) . 's' : 'preachers' ), 'sermon-manager' ),
					'parent_item'       => null, // it's not hierarchical
					'parent_item_colon' => null, // it's not hierarchical
					'edit_item'         => __( 'Edit ' . ( \SermonManager::getOption( 'preacher_label' ) ?: 'preacher' ), 'sermon-manager' ),
					'update_item'       => __( 'Update ' . ( \SermonManager::getOption( 'preacher_label' ) ?: 'preacher' ), 'sermon-manager' ),
					'add_new_item'      => __( 'Add new ' . ( \SermonManager::getOption( 'preacher_label' ) ?: 'preacher' ), 'sermon-manager' ),
					'new_item_name'     => __( 'New ' . ( \SermonManager::getOption( 'preacher_label' ) ?: 'preacher' ) . ' name', 'sermon-manager' ),
					'not_found'         => __( 'No ' . ( \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) . 's' : 'preacher' ) . ' found', 'sermon-manager' ),
				),
				'show_ui'      => true,
				'query_var'    => true,
				'show_in_rest' => true,
				'rewrite'      => array( 'slug' => $permalinks['wpfc_preacher'], 'with_front' => false ),
			) ) );

		register_taxonomy( 'wpfc_sermon_series',
			apply_filters( 'sm_taxonomy_objects_wpfc_sermon_series', array( 'wpfc_sermon' ) ),
			apply_filters( 'sm_taxonomy_args_wpfc_sermon_series', array(
				'hierarchical' => false,
				'label'        => __( 'Series', 'sermon-manager' ),
				'labels'       => array(
					'name'              => __( 'Series', 'sermon-manager' ),
					'singular_name'     => __( 'Series', 'sermon-manager' ),
					'menu_name'         => _x( 'Series', 'Admin menu name', 'sermon-manager' ),
					'search_items'      => __( 'Search series', 'sermon-manager' ),
					'all_items'         => __( 'All series', 'sermon-manager' ),
					'parent_item'       => null, // it's not hierarchical
					'parent_item_colon' => null, // it's not hierarchical
					'edit_item'         => __( 'Edit series', 'sermon-manager' ),
					'update_item'       => __( 'Update series', 'sermon-manager' ),
					'add_new_item'      => __( 'Add new series', 'sermon-manager' ),
					'new_item_name'     => __( 'New series name', 'sermon-manager' ),
					'not_found'         => __( 'No series found', 'sermon-manager' ),
				),
				'show_ui'      => true,
				'query_var'    => true,
				'show_in_rest' => true,
				'rewrite'      => array( 'slug' => $permalinks['wpfc_sermon_series'], 'with_front' => false ),
			) ) );

		register_taxonomy( 'wpfc_sermon_topics',
			apply_filters( 'sm_taxonomy_objects_wpfc_sermon_topics', array( 'wpfc_sermon' ) ),
			apply_filters( 'sm_taxonomy_args_wpfc_sermon_topics', array(
				'hierarchical' => false,
				'label'        => __( 'Topics', 'sermon-manager' ),
				'labels'       => array(
					'name'              => __( 'Topics', 'sermon-manager' ),
					'singular_name'     => __( 'Topic', 'sermon-manager' ),
					'menu_name'         => _x( 'Topics', 'Admin menu name', 'sermon-manager' ),
					'search_items'      => __( 'Search topics', 'sermon-manager' ),
					'all_items'         => __( 'All topics', 'sermon-manager' ),
					'parent_item'       => null,
					'parent_item_colon' => null,
					'edit_item'         => __( 'Edit topic', 'sermon-manager' ),
					'update_item'       => __( 'Update topic', 'sermon-manager' ),
					'add_new_item'      => __( 'Add new topic', 'sermon-manager' ),
					'new_item_name'     => __( 'New topic name', 'sermon-manager' ),
					'not_found'         => __( 'No topics found', 'sermon-manager' ),
				),
				'show_ui'      => true,
				'query_var'    => true,
				'show_in_rest' => true,
				'rewrite'      => array( 'slug' => $permalinks['wpfc_sermon_topics'], 'with_front' => false ),
			) ) );

		register_taxonomy( 'wpfc_bible_book',
			apply_filters( 'sm_taxonomy_objects_wpfc_bible_book', array( 'wpfc_sermon' ) ),
			apply_filters( 'sm_taxonomy_args_wpfc_bible_book', array(
				'hierarchical' => false,
				'label'        => __( 'Books', 'sermon-manager' ),
				'labels'       => array(
					'name'              => __( 'Bible books', 'sermon-manager' ),
					'singular_name'     => __( 'Book', 'sermon-manager' ),
					'menu_name'         => _x( 'Books', 'Admin menu name', 'sermon-manager' ),
					'search_items'      => __( 'Search books', 'sermon-manager' ),
					'all_items'         => __( 'All books', 'sermon-manager' ),
					'parent_item'       => null,
					'parent_item_colon' => null,
					'edit_item'         => __( 'Edit book', 'sermon-manager' ),
					'update_item'       => __( 'Update book', 'sermon-manager' ),
					'add_new_item'      => __( 'Add new book', 'sermon-manager' ),
					'new_item_name'     => __( 'New book name', 'sermon-manager' ),
					'not_found'         => __( 'No books found', 'sermon-manager' ),
				),
				'show_ui'      => true,
				'query_var'    => true,
				'show_in_rest' => true,
				'rewrite'      => array( 'slug' => $permalinks['wpfc_bible_book'], 'with_front' => false ),
			) ) );

		register_taxonomy( 'wpfc_service_type',
			apply_filters( 'sm_taxonomy_objects_wpfc_service_type', array( 'wpfc_sermon' ) ),
			apply_filters( 'sm_taxonomy_args_wpfc_service_type', array(
				'hierarchical' => false,
				'label'        => __( 'Service Types', 'sermon-manager' ),
				'labels'       => array(
					'name'              => __( 'Service Types', 'sermon-manager' ),
					'singular_name'     => __( 'Type', 'sermon-manager' ),
					'menu_name'         => _x( 'Types', 'Admin menu name', 'sermon-manager' ),
					'search_items'      => __( 'Search types', 'sermon-manager' ),
					'all_items'         => __( 'All types', 'sermon-manager' ),
					'parent_item'       => __( 'Parent type', 'sermon-manager' ),
					'parent_item_colon' => __( 'Parent type:', 'sermon-manager' ),
					'edit_item'         => __( 'Edit type', 'sermon-manager' ),
					'update_item'       => __( 'Update type', 'sermon-manager' ),
					'add_new_item'      => __( 'Add new type', 'sermon-manager' ),
					'new_item_name'     => __( 'New type name', 'sermon-manager' ),
					'not_found'         => __( 'No types found', 'sermon-manager' ),
				),
				'show_ui'      => true,
				'query_var'    => true,
				'show_in_rest' => true,
				'rewrite'      => array( 'slug' => $permalinks['wpfc_service_type'], 'with_front' => false ),
			) ) );

		do_action( 'sm_after_register_taxonomy' );
	}

	/**
	 * Register core post types.
	 */
	public static function register_post_types() {
		if ( ! is_blog_installed() || post_type_exists( 'wpfc_sermon' ) ) {
			return;
		}

		do_action( 'sm_register_post_type' );

		$permalinks = sm_get_permalink_structure();

		register_post_type( 'wpfc_sermon', apply_filters( 'sm_register_post_type_wpfc_sermon', array(
			'labels'              => array(
				'name'                  => __( 'Sermons', 'sermon-manager' ),
				'singular_name'         => __( 'Sermon', 'sermon-manager' ),
				'all_items'             => __( 'All Sermons', 'sermon-manager' ),
				'menu_name'             => _x( 'Sermons', 'Admin menu name', 'sermon-manager' ),
				'add_new'               => __( 'Add New', 'sermon-manager' ),
				'add_new_item'          => __( 'Add new sermon', 'sermon-manager' ),
				'edit'                  => __( 'Edit', 'sermon-manager' ),
				'edit_item'             => __( 'Edit sermon', 'sermon-manager' ),
				'new_item'              => __( 'New sermon', 'sermon-manager' ),
				'view'                  => __( 'View sermon', 'sermon-manager' ),
				'view_item'             => __( 'View sermon', 'sermon-manager' ),
				'search_items'          => __( 'Search sermon', 'sermon-manager' ),
				'not_found'             => __( 'No sermons found', 'sermon-manager' ),
				'not_found_in_trash'    => __( 'No sermons found in trash', 'sermon-manager' ),
				'featured_image'        => __( 'Sermon image', 'sermon-manager' ),
				'set_featured_image'    => __( 'Set sermon image', 'sermon-manager' ),
				'remove_featured_image' => __( 'Remove sermon image', 'sermon-manager' ),
				'use_featured_image'    => __( 'Use as sermon image', 'sermon-manager' ),
				'insert_into_item'      => __( 'INSERT INTO sermon', 'sermon-manager' ),
				'uploaded_to_this_item' => __( 'Uploaded to this sermon', 'sermon-manager' ),
				'filter_items_list'     => __( 'Filter sermon', 'sermon-manager' ),
				'items_list_navigation' => __( 'Sermon navigation', 'sermon-manager' ),
				'items_list'            => __( 'Sermon list', 'sermon-manager' ),
			),
			'description'         => __( 'This is where you can add new sermons to your website.', 'sermon-manager' ),
			'public'              => true,
			'show_ui'             => true,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'show_in_menu'        => true,
			'menu_icon'           => SERMON_MANAGER_URL . 'includes/img/sm-icon.svg',
			'hierarchical'        => false,
			'rewrite'             => array( 'slug' => $permalinks['wpfc_sermon'], 'with_front' => false ),
			'query_var'           => true,
			'show_in_nav_menus'   => true,
			'show_in_rest'        => true,
			'has_archive'         => true,
			'supports'            => array(
				'title',
				'thumbnail',
				'publicize',
				'wpcom-markdown',
				'comments',
				'entry-views',
			)
		) ) );

		do_action( 'sm_after_register_post_type' );
	}

	/**
	 * Flush rewrite rules.
	 */
	public static function flush_rewrite_rules() {
		flush_rewrite_rules();
	}

	/**
	 * Add Sermon Support to Jetpack Omnisearch.
	 */
	public static function support_jetpack_omnisearch() {
		if ( class_exists( 'Jetpack_Omnisearch_Posts' ) ) {
			new Jetpack_Omnisearch_Posts( 'wpfc_sermon' );
		}
	}

	/**
	 * Add sermon support for Jetpack related posts.
	 *
	 * @param  array $post_types
	 *
	 * @return array
	 */
	public static function rest_api_allowed_post_types( $post_types ) {
		$post_types[] = 'wpfc_sermon';

		return $post_types;
	}
}

SM_Post_Types::init();
