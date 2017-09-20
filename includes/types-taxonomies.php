<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

// Create sermon Custom Post Type
add_action( 'init', 'create_wpfc_sermon_types' );
// Create new taxonomies: preachers, sermon series, bible books & topics
add_action( 'init', 'create_wpfc_sermon_taxonomies', 0 );
// Define the metabox and field configurations
add_action( 'cmb2_admin_init', 'wpfc_sermon_metaboxes' );
// make sure service type is set
add_action( 'save_post', 'set_service_type', 99, 3 );

function set_service_type( $post_ID, $post, $update ) {
	if ( isset( $_POST['wpfc_service_type'] ) ) {
		$service_type = $_POST['wpfc_service_type'];

		$term = get_term_by( 'id', $service_type, 'wpfc_service_type' );

		// If service type is not set to "None"
		if ( $term !== false ) {
			$service_type = $term->slug;

			wp_set_object_terms( $post_ID, $service_type, 'wpfc_service_type' );
		}

		return $post;
	}
}

/*
 * Creation of Sermon Post Types and Taxonomies
 * Also all meta boxes
 */

// Determine the correct slug name based on options
function generate_wpfc_slug( $slug_name = null ) {
	if ( trim( \SermonManager::getOption( 'archive_slug' ) ) === '' ) {
		$archive_slug = 'sermons';
	} else {
		$archive_slug = \SermonManager::getOption( 'archive_slug' );
	}

	if ( ! isset( $slug_name ) ) {
		return array( 'slug' => $archive_slug, 'with_front' => false );
	}

	if ( \SermonManager::getOption( 'common_base_slug' ) ) {
		return array( 'slug' => $archive_slug . "/" . $slug_name, 'with_front' => false );
	} else {
		return array( 'slug' => $slug_name, 'with_front' => false );
	}
}

// Create sermon Custom Post Type
function create_wpfc_sermon_types() {

	$labels = array(
		'name'               => __( 'Sermons', 'sermon-manager' ),
		'singular_name'      => __( 'Sermon', 'sermon-manager' ),
		'add_new'            => __( 'Add New', 'sermon-manager' ),
		'add_new_item'       => __( 'Add New Sermon', 'sermon-manager' ),
		'edit_item'          => __( 'Edit Sermon', 'sermon-manager' ),
		'new_item'           => __( 'New Sermon', 'sermon-manager' ),
		'view_item'          => __( 'View Sermon', 'sermon-manager' ),
		'search_items'       => __( 'Search Sermons', 'sermon-manager' ),
		'not_found'          => __( 'No sermons found', 'sermon-manager' ),
		'not_found_in_trash' => __( 'No sermons found in Trash', 'sermon-manager' ),
		'menu_name'          => __( 'Sermons', 'sermon-manager' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'menu_icon'          => SERMON_MANAGER_URL . 'includes/img/sm-icon.svg',
		'capability_type'    => 'post',
		'has_archive'        => true,
		'rewrite'            => generate_wpfc_slug(),
		'hierarchical'       => false,
		'supports'           => array( 'title', 'comments', 'thumbnail', 'entry-views' )
	);
	register_post_type( 'wpfc_sermon', $args );
}

// Create new taxonomies: preachers, sermon series, bible books & topics
function create_wpfc_sermon_taxonomies() {

	//Preachers
	$labels = array(
		'name'                       => __( \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) . 's' : 'Preachers', 'sermon-manager' ),
		'singular_name'              => __( \SermonManager::getOption( 'preacher_label' ) ?: 'Preacher', 'sermon-manager' ),
		'menu_name'                  => __( \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) . 's' : 'Preachers', 'sermon-manager' ),
		'search_items'               => __( 'Search' . ( \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) . 's' : 'preachers' ), 'sermon-manager' ),
		'popular_items'              => __( 'Most frequent ' . ( \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) . 's' : 'preachers' ), 'sermon-manager' ),
		'all_items'                  => __( 'All ' . ( \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) . 's' : 'preachers' ), 'sermon-manager' ),
		'edit_item'                  => __( 'Edit ' . ( \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) . 's' : 'preachers' ), 'sermon-manager' ),
		'update_item'                => __( 'Update ' . ( \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) . 's' : 'preachers' ), 'sermon-manager' ),
		'add_new_item'               => __( 'Add new ' . ( \SermonManager::getOption( 'preacher_label' ) ?: 'preacher' ), 'sermon-manager' ),
		'new_item_name'              => __( 'New ' . ( \SermonManager::getOption( 'preacher_label' ) ?: 'preacher' ) . ' name', 'sermon-manager' ),
		'separate_items_with_commas' => __( 'Separate multiple ' . ( \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) . 's' : 'preachers' ) . ' with commas', 'sermon-manager' ),
		'add_or_remove_items'        => __( 'Add or remove ' . ( \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) . 's' : 'preachers' ), 'sermon-manager' ),
		'choose_from_most_used'      => __( 'Choose from most frequent ' . ( \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) . 's' : 'preachers' ), 'sermon-manager' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
	);

	register_taxonomy( 'wpfc_preacher', 'wpfc_sermon', array(
		'hierarchical' => false,
		'labels'       => $labels,
		'show_ui'      => true,
		'query_var'    => true,
		'rewrite'      => generate_wpfc_slug( \SermonManager::getOption( 'preacher_label' ) ? sanitize_title( \SermonManager::getOption( 'preacher_label' ) ) : 'preacher' ),
	) );

	//Sermon Series
	$labels = array(
		'name'                       => __( 'Sermon Series', 'sermon-manager' ),
		'singular_name'              => __( 'Sermon Series', 'sermon-manager' ),
		'menu_name'                  => __( 'Sermon Series', 'sermon-manager' ),
		'search_items'               => __( 'Search sermon series', 'sermon-manager' ),
		'popular_items'              => __( 'Most frequent sermon series', 'sermon-manager' ),
		'all_items'                  => __( 'All sermon series', 'sermon-manager' ),
		'edit_item'                  => __( 'Edit sermon series', 'sermon-manager' ),
		'update_item'                => __( 'Update sermon series', 'sermon-manager' ),
		'add_new_item'               => __( 'Add new sermon series', 'sermon-manager' ),
		'new_item_name'              => __( 'New sermon series name', 'sermon-manager' ),
		'separate_items_with_commas' => __( 'Separate sermon series with commas', 'sermon-manager' ),
		'add_or_remove_items'        => __( 'Add or remove sermon series', 'sermon-manager' ),
		'choose_from_most_used'      => __( 'Choose from most used sermon series', 'sermon-manager' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
	);

	register_taxonomy( 'wpfc_sermon_series', 'wpfc_sermon', array(
		'hierarchical' => false,
		'labels'       => $labels,
		'show_ui'      => true,
		'query_var'    => true,
		'rewrite'      => generate_wpfc_slug( 'series' ),
	) );

	//Sermon Topics
	$labels = array(
		'name'                       => __( 'Sermon Topics', 'sermon-manager' ),
		'singular_name'              => __( 'Sermon Topics', 'sermon-manager' ),
		'menu_name'                  => __( 'Sermon Topics', 'sermon-manager' ),
		'search_items'               => __( 'Search sermon topics', 'sermon-manager' ),
		'popular_items'              => __( 'Most popular sermon topics', 'sermon-manager' ),
		'all_items'                  => __( 'All sermon topics', 'sermon-manager' ),
		'edit_item'                  => __( 'Edit sermon topic', 'sermon-manager' ),
		'update_item'                => __( 'Update sermon topic', 'sermon-manager' ),
		'add_new_item'               => __( 'Add new sermon topic', 'sermon-manager' ),
		'new_item_name'              => __( 'New sermon topic', 'sermon-manager' ),
		'separate_items_with_commas' => __( 'Separate sermon topics with commas', 'sermon-manager' ),
		'add_or_remove_items'        => __( 'Add or remove sermon topics', 'sermon-manager' ),
		'choose_from_most_used'      => __( 'Choose from most used sermon topics', 'sermon-manager' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
	);

	register_taxonomy( 'wpfc_sermon_topics', 'wpfc_sermon', array(
		'hierarchical' => false,
		'labels'       => $labels,
		'show_ui'      => true,
		'query_var'    => true,
		'rewrite'      => generate_wpfc_slug( 'topics' ),
	) );

	//Books of the Bible
	$labels = array(
		'name'                       => __( 'Book of the Bible', 'sermon-manager' ),
		'singular_name'              => __( 'Book of the Bible', 'sermon-manager' ),
		'menu_name'                  => __( 'Book of the Bible', 'sermon-manager' ),
		'search_items'               => __( 'Search books of the Bible', 'sermon-manager' ),
		'popular_items'              => __( 'Most popular books of the Bible', 'sermon-manager' ),
		'all_items'                  => __( 'All books of the Bible', 'sermon-manager' ),
		'edit_item'                  => __( 'Edit book of the Bible', 'sermon-manager' ),
		'update_item'                => __( 'Update book of the Bible', 'sermon-manager' ),
		'add_new_item'               => __( 'Add new books of the Bible', 'sermon-manager' ),
		'new_item_name'              => __( 'New book of the Bible', 'sermon-manager' ),
		'separate_items_with_commas' => __( 'Separate books of the Bible with commas', 'sermon-manager' ),
		'add_or_remove_items'        => __( 'Add or remove books of the Bible', 'sermon-manager' ),
		'choose_from_most_used'      => __( 'Choose from most used books of the Bible', 'sermon-manager' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
	);

	register_taxonomy( 'wpfc_bible_book', 'wpfc_sermon', array(
		'hierarchical' => false,
		'labels'       => $labels,
		'show_ui'      => true,
		'query_var'    => true,
		'rewrite'      => generate_wpfc_slug( 'book' ),
	) );

	//Service Type
	$labels = array(
		'name'                       => __( 'Service Type', 'sermon-manager' ),
		'singular_name'              => __( 'Service Type', 'sermon-manager' ),
		'menu_name'                  => __( 'Service Type', 'sermon-manager' ),
		'search_items'               => __( 'Search service types', 'sermon-manager' ),
		'popular_items'              => __( 'Most popular service types', 'sermon-manager' ),
		'all_items'                  => __( 'All service types', 'sermon-manager' ),
		'edit_item'                  => __( 'Edit service type', 'sermon-manager' ),
		'update_item'                => __( 'Update service type', 'sermon-manager' ),
		'add_new_item'               => __( 'Add new service types', 'sermon-manager' ),
		'new_item_name'              => __( 'New Service Type', 'sermon-manager' ),
		'separate_items_with_commas' => __( 'Separate service types with commas', 'sermon-manager' ),
		'add_or_remove_items'        => __( 'Add or remove service types', 'sermon-manager' ),
		'choose_from_most_used'      => __( 'Choose from most used service types', 'sermon-manager' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
	);

	register_taxonomy( 'wpfc_service_type', 'wpfc_sermon', array(
		'hierarchical' => false,
		'labels'       => $labels,
		'show_ui'      => true,
		'query_var'    => true,
		'rewrite'      => generate_wpfc_slug( 'service-type' ),
	) );
}

/**
 * Gets a number of terms and displays them as options
 *
 * @param  string       $taxonomy Taxonomy terms to retrieve. Default is category.
 * @param  string|array $args     Optional. get_terms optional arguments
 *
 * @return array                  An array of options that matches the CMB2 options array
 */
function cmb2_get_term_options( $taxonomy = 'category' ) {

	$args['taxonomy'] = $taxonomy;

	// $defaults = array( 'taxonomy' => 'category' );

	$taxonomy = $args['taxonomy'];

	$args = array(
		'hide_empty' => false
	);

	$terms = (array) get_terms( $taxonomy, $args );

	// Initate an empty array
	$term_options = array();
	if ( ! empty( $terms ) ) {
		foreach ( $terms as $term ) {
			$term_options[ $term->term_id ] = $term->name;
		}
	}

	return $term_options;
}

// sanitize the field
add_filter( 'cmb2_sanitize_text_number', 'sm_cmb2_sanitize_text_number', 10, 2 );
function sm_cmb2_sanitize_text_number( $null, $new ) {
	$new = preg_replace( "/[^0-9]/", "", $new );

	return $new;
}

// Define the metabox and field configurations
function wpfc_sermon_metaboxes() {

	$cmb = new_cmb2_box( array(
		'id'           => 'wpfc_sermon_details',
		'title'        => __( 'Sermon Details', 'sermon-manager' ),
		'object_types' => array( 'wpfc_sermon', ), // Post type
		'context'      => 'normal',
		'priority'     => 'high',
		'show_names'   => true, // Show field names on the left
		// 'cmb_styles' => false, // false to disable the CMB stylesheet
		// 'closed'     => true, // Keep the metabox closed by default
	) );

	$date_format = 'm/d/Y';
	if ( \SermonManager::getOption( 'date_format' ) !== '' ) {
		switch ( \SermonManager::getOption( 'date_format' ) ) {
			case '0':
				$date_format = 'm/d/Y';
				break;
			case '1':
				$date_format = 'd/m/Y';
				break;
			case '2':
				$date_format = 'Y/m/d';
				break;
			case '3':
				$date_format = 'Y/d/m';
				break;
		}
	}

	$cmb->add_field( array(
		'name'        => __( 'Date Preached', 'sermon-manager' ),
		'desc'        => '<br>(format: ' . $date_format . ')',
		'id'          => 'sermon_date',
		'type'        => 'text_date_timestamp',
		'date_format' => $date_format,
	) );

	$cmb->add_field( array(
		'name'             => __( 'Service Type', 'sermon-manager' ),
		'desc'             => __( 'Select the type of service. Modify service types in Sermons -> Service Types.', 'sermon-manager' ),
		'id'               => 'wpfc_service_type',
		'type'             => 'select',
		'show_option_none' => true,
		'options'          => cmb2_get_term_options( 'wpfc_service_type' ),
	) );
	$cmb->add_field( array(
		'name' => __( 'Main Bible Passage', 'sermon-manager' ),
		'desc' => __( 'Enter the Bible passage with the full book names,e.g. "John 3:16-18".', 'sermon-manager' ),
		'id'   => 'bible_passage',
		'type' => 'text',
	) );
	$cmb->add_field( array(
		'name'    => __( 'Description', 'sermon-manager' ),
		'desc'    => __( 'Type a brief description about this sermon, an outline, or a full manuscript', 'sermon-manager' ),
		'id'      => 'sermon_description',
		'type'    => 'wysiwyg',
		'options' => array( 'textarea_rows' => 7, 'media_buttons' => true, ),
	) );

	$cmb2 = new_cmb2_box( array(
		'id'           => 'wpfc_sermon_files',
		'title'        => __( 'Sermon Files', 'sermon-manager' ),
		'object_types' => array( 'wpfc_sermon', ), // Post type
		'context'      => 'normal',
		'priority'     => 'high',
		'show_names'   => true, // Show field names on the left
		// 'cmb_styles' => false, // false to disable the CMB stylesheet
		// 'closed'     => true, // Keep the metabox closed by default
	) );
	$cmb2->add_field( array(
		'name' => __( 'Location of MP3', 'sermon-manager' ),
		'desc' => __( 'Upload an audio file or enter an URL.', 'sermon-manager' ),
		'id'   => 'sermon_audio',
		'type' => 'file',
		'text' => array(
			'add_upload_file_text' => 'Add Sermon Audio' // Change upload button text. Default: "Add or Upload File"
		),
	) );
	$cmb2->add_field( array(
		'name' => __( 'MP3 Duration', 'sermon-manager' ),
		'desc' => __( 'Length in <code>hh:mm:ss</code> format (if left blank, will attempt to calculate automatically when you save)', 'sermon-manager' ),
		'id'   => '_wpfc_sermon_duration',
		'type' => 'text',
	) );
	$cmb2->add_field( array(
		'name' => __( 'Video Embed Code', 'sermon-manager' ),
		'desc' => __( 'Paste your embed code for Vimeo, Youtube, or other service here', 'sermon-manager' ),
		'id'   => 'sermon_video',
		'type' => 'textarea_code'
	) );
	$cmb2->add_field( apply_filters( 'sm_cmb2_field_sermon_video_link', array(
			'name' => __( 'Video Link', 'sermon-manager' ),
			'desc' => __( 'Paste your link for Vimeo, Youtube, or other service here', 'sermon-manager' ),
			'id'   => 'sermon_video_link',
			'type' => 'text'
		) )
	);
	$cmb2->add_field( array(
		'name' => __( 'Sermon Notes', 'sermon-manager' ),
		'desc' => __( 'Upload a pdf file or enter an URL.', 'sermon-manager' ),
		'id'   => 'sermon_notes',
		'type' => 'file',
		'text' => array(
			'add_upload_file_text' => 'Add File' // Change upload button text. Default: "Add or Upload File"
		),
	) );
	$cmb2->add_field( array(
		'name' => __( 'Bulletin', 'sermon-manager' ),
		'desc' => __( 'Upload a pdf file or enter an URL.', 'sermon-manager' ),
		'id'   => 'sermon_bulletin',
		'type' => 'file',
		'text' => array(
			'add_upload_file_text' => 'Add File' // Change upload button text. Default: "Add or Upload File"
		),
	) );

}

/**
 * Build <option> fields for <select> element
 *
 * @param string $taxonomy Taxonomy name
 * @param string $default  Force a default value regardless the query var
 *
 * @return string HTML <option> fields
 *
 * @since 2.5.0 added $default
 */
function wpfc_get_term_dropdown( $taxonomy, $default = '' ) {
	// reset var
	$html = '';

	foreach ( get_terms( $taxonomy ) as $term ) {
		$html .= '<option value="' . $term->slug . '" ' . ( ( $default === '' ? $term->slug === get_query_var( $taxonomy ) : $term->slug === $default ) ? 'selected' : '' ) . '>' . $term->name . '</option>';
	}

	return $html;
}
