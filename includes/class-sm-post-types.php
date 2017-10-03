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
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 6 );
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
		add_action( 'init', array( __CLASS__, 'support_jetpack_omnisearch' ) );
		add_filter( 'rest_api_allowed_post_types', array( __CLASS__, 'rest_api_allowed_post_types' ) );
		add_action( 'sm_flush_rewrite_rules', array( __CLASS__, 'flush_rewrite_rules' ) );
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

		$preacher_label = ( \SermonManager::getOption( 'preacher_label' ) ? strtolower( \SermonManager::getOption( 'preacher_label' ) ) : 'preacher' );

		register_taxonomy( 'wpfc_preacher',
			apply_filters( 'sm_taxonomy_objects_wpfc_preacher', array( 'wpfc_sermon' ) ),
			apply_filters( 'sm_taxonomy_args_wpfc_preacher', array(
				'hierarchical' => false,
				/* Translators: %s: Preachers label (sentence case; plural) */
				'label'        => sprintf( __( '%s', 'sermon-manager-for-wordpress' ), ucwords( $preacher_label ) . 's' ),
				'labels'       => array(
					/* Translators: %s: Preachers label (sentence case; plural) */
					'name'              => sprintf( __( '%s', 'sermon-manager-for-wordpress' ), ucwords( $preacher_label ) . 's' ),
					/* Translators: %s: Preacher label (sentence case; singular) */
					'singular_name'     => sprintf( __( '%s', 'sermon-manager-for-wordpress' ), ucwords( $preacher_label ) ),
					/* Translators: %s: Preachers label (sentence case; plural) */
					'menu_name'         => sprintf( _x( '%s', 'Admin menu name', 'sermon-manager-for-wordpress' ), ucwords( $preacher_label ) . 's' ),
					/* Translators: %s: Preachers label (lowercase; plural) */
					'search_items'      => sprintf( __( 'Search %s', 'sermon-manager-for-wordpress' ), $preacher_label . 's' ),
					/* Translators: %s: Preachers label (lowercase; plural) */
					'all_items'         => sprintf( __( 'All %s', 'sermon-manager-for-wordpress' ), $preacher_label . 's' ),
					'parent_item'       => null, // it's not hierarchical
					'parent_item_colon' => null, // it's not hierarchical
					/* Translators: %s: Preacher label (lowercase; singular) */
					'edit_item'         => sprintf( __( 'Edit %s', 'sermon-manager-for-wordpress' ), $preacher_label ),
					/* Translators: %s: Preacher label (lowercase; singular) */
					'update_item'       => sprintf( __( 'Update %s', 'sermon-manager-for-wordpress' ), $preacher_label ),
					/* Translators: %s: Preacher label (lowercase; singular) */
					'add_new_item'      => sprintf( __( 'Add new %s', 'sermon-manager-for-wordpress' ), $preacher_label ),
					/* Translators: %s: Preacher label (lowercase; singular) */
					'new_item_name'     => sprintf( __( 'New %s name', 'sermon-manager-for-wordpress' ), $preacher_label ),
					/* Translators: %s: Preacher label (lowercase; singular) */
					'not_found'         => sprintf( __( 'No %s found', 'sermon-manager-for-wordpress' ), $preacher_label ),
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
				'label'        => __( 'Series', 'sermon-manager-for-wordpress' ),
				'labels'       => array(
					'name'              => __( 'Series', 'sermon-manager-for-wordpress' ),
					'singular_name'     => __( 'Series', 'sermon-manager-for-wordpress' ),
					'menu_name'         => _x( 'Series', 'Admin menu name', 'sermon-manager-for-wordpress' ),
					'search_items'      => __( 'Search series', 'sermon-manager-for-wordpress' ),
					'all_items'         => __( 'All series', 'sermon-manager-for-wordpress' ),
					'parent_item'       => null, // it's not hierarchical
					'parent_item_colon' => null, // it's not hierarchical
					'edit_item'         => __( 'Edit series', 'sermon-manager-for-wordpress' ),
					'update_item'       => __( 'Update series', 'sermon-manager-for-wordpress' ),
					'add_new_item'      => __( 'Add new series', 'sermon-manager-for-wordpress' ),
					'new_item_name'     => __( 'New series name', 'sermon-manager-for-wordpress' ),
					'not_found'         => __( 'No series found', 'sermon-manager-for-wordpress' ),
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
				'label'        => __( 'Topics', 'sermon-manager-for-wordpress' ),
				'labels'       => array(
					'name'              => __( 'Topics', 'sermon-manager-for-wordpress' ),
					'singular_name'     => __( 'Topic', 'sermon-manager-for-wordpress' ),
					'menu_name'         => _x( 'Topics', 'Admin menu name', 'sermon-manager-for-wordpress' ),
					'search_items'      => __( 'Search topics', 'sermon-manager-for-wordpress' ),
					'all_items'         => __( 'All topics', 'sermon-manager-for-wordpress' ),
					'parent_item'       => null,
					'parent_item_colon' => null,
					'edit_item'         => __( 'Edit topic', 'sermon-manager-for-wordpress' ),
					'update_item'       => __( 'Update topic', 'sermon-manager-for-wordpress' ),
					'add_new_item'      => __( 'Add new topic', 'sermon-manager-for-wordpress' ),
					'new_item_name'     => __( 'New topic name', 'sermon-manager-for-wordpress' ),
					'not_found'         => __( 'No topics found', 'sermon-manager-for-wordpress' ),
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
				'label'        => __( 'Books', 'sermon-manager-for-wordpress' ),
				'labels'       => array(
					'name'              => __( 'Bible books', 'sermon-manager-for-wordpress' ),
					'singular_name'     => __( 'Book', 'sermon-manager-for-wordpress' ),
					'menu_name'         => _x( 'Books', 'Admin menu name', 'sermon-manager-for-wordpress' ),
					'search_items'      => __( 'Search books', 'sermon-manager-for-wordpress' ),
					'all_items'         => __( 'All books', 'sermon-manager-for-wordpress' ),
					'parent_item'       => null,
					'parent_item_colon' => null,
					'edit_item'         => __( 'Edit book', 'sermon-manager-for-wordpress' ),
					'update_item'       => __( 'Update book', 'sermon-manager-for-wordpress' ),
					'add_new_item'      => __( 'Add new book', 'sermon-manager-for-wordpress' ),
					'new_item_name'     => __( 'New book name', 'sermon-manager-for-wordpress' ),
					'not_found'         => __( 'No books found', 'sermon-manager-for-wordpress' ),
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
				'label'        => __( 'Service Types', 'sermon-manager-for-wordpress' ),
				'labels'       => array(
					'name'              => __( 'Service Types', 'sermon-manager-for-wordpress' ),
					'singular_name'     => __( 'Service Type', 'sermon-manager-for-wordpress' ),
					'menu_name'         => _x( 'Service Types', 'Admin menu name', 'sermon-manager-for-wordpress' ),
					'search_items'      => __( 'Search service types', 'sermon-manager-for-wordpress' ),
					'all_items'         => __( 'All service types', 'sermon-manager-for-wordpress' ),
					'parent_item'       => null,
					'parent_item_colon' => null,
					'edit_item'         => __( 'Edit service type', 'sermon-manager-for-wordpress' ),
					'update_item'       => __( 'Update service type', 'sermon-manager-for-wordpress' ),
					'add_new_item'      => __( 'Add new service type', 'sermon-manager-for-wordpress' ),
					'new_item_name'     => __( 'New service type name', 'sermon-manager-for-wordpress' ),
					'not_found'         => __( 'No service types found', 'sermon-manager-for-wordpress' ),
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
				'name'                  => __( 'Sermons', 'sermon-manager-for-wordpress' ),
				'singular_name'         => __( 'Sermon', 'sermon-manager-for-wordpress' ),
				'all_items'             => __( 'All Sermons', 'sermon-manager-for-wordpress' ),
				'menu_name'             => _x( 'Sermons', 'Admin menu name', 'sermon-manager-for-wordpress' ),
				'add_new'               => __( 'Add New', 'sermon-manager-for-wordpress' ),
				'add_new_item'          => __( 'Add new sermon', 'sermon-manager-for-wordpress' ),
				'edit'                  => __( 'Edit', 'sermon-manager-for-wordpress' ),
				'edit_item'             => __( 'Edit sermon', 'sermon-manager-for-wordpress' ),
				'new_item'              => __( 'New sermon', 'sermon-manager-for-wordpress' ),
				'view'                  => __( 'View sermon', 'sermon-manager-for-wordpress' ),
				'view_item'             => __( 'View sermon', 'sermon-manager-for-wordpress' ),
				'search_items'          => __( 'Search sermon', 'sermon-manager-for-wordpress' ),
				'not_found'             => __( 'No sermons found', 'sermon-manager-for-wordpress' ),
				'not_found_in_trash'    => __( 'No sermons found in trash', 'sermon-manager-for-wordpress' ),
				'featured_image'        => __( 'Sermon image', 'sermon-manager-for-wordpress' ),
				'set_featured_image'    => __( 'Set sermon image', 'sermon-manager-for-wordpress' ),
				'remove_featured_image' => __( 'Remove sermon image', 'sermon-manager-for-wordpress' ),
				'use_featured_image'    => __( 'Use as sermon image', 'sermon-manager-for-wordpress' ),
				'insert_into_item'      => __( 'Insert to sermon', 'sermon-manager-for-wordpress' ),
				'uploaded_to_this_item' => __( 'Uploaded to this sermon', 'sermon-manager-for-wordpress' ),
				'filter_items_list'     => __( 'Filter sermon', 'sermon-manager-for-wordpress' ),
				'items_list_navigation' => __( 'Sermon navigation', 'sermon-manager-for-wordpress' ),
				'items_list'            => __( 'Sermon list', 'sermon-manager-for-wordpress' ),
			),
			'description'         => __( 'This is where you can add new sermons to your website.', 'sermon-manager-for-wordpress' ),
			'public'              => true,
			'show_ui'             => true,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-sermon-manager',
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
				'elementor',
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

	/**
	 * Shorthand function for flush_rewrite_rules(true)
	 *
	 * @since 2.7.1
	 */
	public static function flush_rewrite_rules_hard() {
		\flush_rewrite_rules( true );
	}
}

SM_Post_Types::init();
