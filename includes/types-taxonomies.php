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
		'name'               => __( 'Sermons', 'sermon-manager-for-wordpress' ),
		'singular_name'      => __( 'Sermon', 'sermon-manager-for-wordpress' ),
		'add_new'            => __( 'Add New', 'sermon-manager-for-wordpress' ),
		'add_new_item'       => __( 'Add New Sermon', 'sermon-manager-for-wordpress' ),
		'edit_item'          => __( 'Edit Sermon', 'sermon-manager-for-wordpress' ),
		'new_item'           => __( 'New Sermon', 'sermon-manager-for-wordpress' ),
		'view_item'          => __( 'View Sermon', 'sermon-manager-for-wordpress' ),
		'search_items'       => __( 'Search Sermons', 'sermon-manager-for-wordpress' ),
		'not_found'          => __( 'No sermons found', 'sermon-manager-for-wordpress' ),
		'not_found_in_trash' => __( 'No sermons found in Trash', 'sermon-manager-for-wordpress' ),
		'menu_name'          => __( 'Sermons', 'sermon-manager-for-wordpress' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'menu_icon'          => SM_URL . 'includes/img/sm-icon.svg',
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
		'name'                       => \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) : __( 'Preachers', 'sermon-manager-for-wordpress' ),
		'singular_name'              => \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) : __( 'Preacher', 'sermon-manager-for-wordpress' ),
		'menu_name'                  => __( 'Preachers', 'sermon-manager-for-wordpress' ),
		'search_items'               => wp_sprintf( __( 'Search %s', 'sermon-manager-for-wordpress' ), \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) : __( 'Preacher', 'sermon-manager-for-wordpress' ) ),
		'popular_items'              => wp_sprintf( __( 'Most frequent %s', 'sermon-manager-for-wordpress' ), \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) : __( 'Preacher', 'sermon-manager-for-wordpress' ) ),
		'all_items'                  => wp_sprintf( __( 'All %s', 'sermon-manager-for-wordpress' ), \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) : __( 'Preachers', 'sermon-manager-for-wordpress' ) ),
		'edit_item'                  => wp_sprintf( __( 'Edit %s', 'sermon-manager-for-wordpress' ), \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) : __( 'Preacher', 'sermon-manager-for-wordpress' ) ),
		'update_item'                => wp_sprintf( __( 'Update %s', 'sermon-manager-for-wordpress' ), \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) : __( 'Preacher', 'sermon-manager-for-wordpress' ) ),
		'add_new_item'               => wp_sprintf( __( 'Add new %s', 'sermon-manager-for-wordpress' ), \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) : __( 'Preacher', 'sermon-manager-for-wordpress' ) ),
		'new_item_name'              => wp_sprintf( __( 'New %s', 'sermon-manager-for-wordpress' ), \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) : __( 'Preacher', 'sermon-manager-for-wordpress' ) ),
		'separate_items_with_commas' => wp_sprintf( __( 'Separate multiple %s with commas', 'sermon-manager-for-wordpress' ), \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) : __( 'Preachers', 'sermon-manager-for-wordpress' ) ),
		'add_or_remove_items'        => wp_sprintf( __( 'Add or remove %s', 'sermon-manager-for-wordpress' ), \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) : __( 'Preacher', 'sermon-manager-for-wordpress' ) ),
		'choose_from_most_used'      => wp_sprintf( __( 'Choose from most frequent %s', 'sermon-manager-for-wordpress' ), \SermonManager::getOption( 'preacher_label' ) ? \SermonManager::getOption( 'preacher_label' ) : __( 'Preachers', 'sermon-manager-for-wordpress' ) ),
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
		'name'                       => __( 'Sermon Series', 'sermon-manager-for-wordpress' ),
		'singular_name'              => __( 'Sermon Series', 'sermon-manager-for-wordpress' ),
		'menu_name'                  => __( 'Sermon Series', 'sermon-manager-for-wordpress' ),
		'search_items'               => __( 'Search sermon series', 'sermon-manager-for-wordpress' ),
		'popular_items'              => __( 'Most frequent sermon series', 'sermon-manager-for-wordpress' ),
		'all_items'                  => __( 'All sermon series', 'sermon-manager-for-wordpress' ),
		'edit_item'                  => __( 'Edit sermon series', 'sermon-manager-for-wordpress' ),
		'update_item'                => __( 'Update sermon series', 'sermon-manager-for-wordpress' ),
		'add_new_item'               => __( 'Add new sermon series', 'sermon-manager-for-wordpress' ),
		'new_item_name'              => __( 'New sermon series name', 'sermon-manager-for-wordpress' ),
		'separate_items_with_commas' => __( 'Separate sermon series with commas', 'sermon-manager-for-wordpress' ),
		'add_or_remove_items'        => __( 'Add or remove sermon series', 'sermon-manager-for-wordpress' ),
		'choose_from_most_used'      => __( 'Choose from most used sermon series', 'sermon-manager-for-wordpress' ),
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
		'name'                       => __( 'Sermon Topics', 'sermon-manager-for-wordpress' ),
		'singular_name'              => __( 'Sermon Topics', 'sermon-manager-for-wordpress' ),
		'menu_name'                  => __( 'Sermon Topics', 'sermon-manager-for-wordpress' ),
		'search_items'               => __( 'Search sermon topics', 'sermon-manager-for-wordpress' ),
		'popular_items'              => __( 'Most popular sermon topics', 'sermon-manager-for-wordpress' ),
		'all_items'                  => __( 'All sermon topics', 'sermon-manager-for-wordpress' ),
		'edit_item'                  => __( 'Edit sermon topic', 'sermon-manager-for-wordpress' ),
		'update_item'                => __( 'Update sermon topic', 'sermon-manager-for-wordpress' ),
		'add_new_item'               => __( 'Add new sermon topic', 'sermon-manager-for-wordpress' ),
		'new_item_name'              => __( 'New sermon topic', 'sermon-manager-for-wordpress' ),
		'separate_items_with_commas' => __( 'Separate sermon topics with commas', 'sermon-manager-for-wordpress' ),
		'add_or_remove_items'        => __( 'Add or remove sermon topics', 'sermon-manager-for-wordpress' ),
		'choose_from_most_used'      => __( 'Choose from most used sermon topics', 'sermon-manager-for-wordpress' ),
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
		'name'                       => __( 'Book of the Bible', 'sermon-manager-for-wordpress' ),
		'singular_name'              => __( 'Book of the Bible', 'sermon-manager-for-wordpress' ),
		'menu_name'                  => __( 'Book of the Bible', 'sermon-manager-for-wordpress' ),
		'search_items'               => __( 'Search books of the Bible', 'sermon-manager-for-wordpress' ),
		'popular_items'              => __( 'Most popular books of the Bible', 'sermon-manager-for-wordpress' ),
		'all_items'                  => __( 'All books of the Bible', 'sermon-manager-for-wordpress' ),
		'edit_item'                  => __( 'Edit book of the Bible', 'sermon-manager-for-wordpress' ),
		'update_item'                => __( 'Update book of the Bible', 'sermon-manager-for-wordpress' ),
		'add_new_item'               => __( 'Add new books of the Bible', 'sermon-manager-for-wordpress' ),
		'new_item_name'              => __( 'New book of the Bible', 'sermon-manager-for-wordpress' ),
		'separate_items_with_commas' => __( 'Separate books of the Bible with commas', 'sermon-manager-for-wordpress' ),
		'add_or_remove_items'        => __( 'Add or remove books of the Bible', 'sermon-manager-for-wordpress' ),
		'choose_from_most_used'      => __( 'Choose from most used books of the Bible', 'sermon-manager-for-wordpress' ),
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
		'name'                       => __( 'Service Type', 'sermon-manager-for-wordpress' ),
		'singular_name'              => __( 'Service Type', 'sermon-manager-for-wordpress' ),
		'menu_name'                  => __( 'Service Type', 'sermon-manager-for-wordpress' ),
		'search_items'               => __( 'Search service types', 'sermon-manager-for-wordpress' ),
		'popular_items'              => __( 'Most popular service types', 'sermon-manager-for-wordpress' ),
		'all_items'                  => __( 'All service types', 'sermon-manager-for-wordpress' ),
		'edit_item'                  => __( 'Edit service type', 'sermon-manager-for-wordpress' ),
		'update_item'                => __( 'Update service type', 'sermon-manager-for-wordpress' ),
		'add_new_item'               => __( 'Add new service types', 'sermon-manager-for-wordpress' ),
		'new_item_name'              => __( 'New Service Type', 'sermon-manager-for-wordpress' ),
		'separate_items_with_commas' => __( 'Separate service types with commas', 'sermon-manager-for-wordpress' ),
		'add_or_remove_items'        => __( 'Add or remove service types', 'sermon-manager-for-wordpress' ),
		'choose_from_most_used'      => __( 'Choose from most used service types', 'sermon-manager-for-wordpress' ),
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
		'title'        => esc_html__( 'Sermon Details', 'sermon-manager-for-wordpress' ),
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
		'name'        => esc_html__( 'Date Preached', 'sermon-manager-for-wordpress' ),
		'desc'        => '<br>' . wp_sprintf( esc_html__( 'format: %s', 'sermon-manager-for-wordpress' ), '<code>' . $date_format . '</code>' ),
		'id'          => 'sermon_date',
		'type'        => 'text_date_timestamp',
		'date_format' => $date_format,
	) );

	$cmb->add_field( array(
		'name'             => esc_html__( 'Service Type', 'sermon-manager-for-wordpress' ),
		'desc'             => esc_html__( 'Select the type of service. Modify service types in Sermons &rarr; Service Types.', 'sermon-manager-for-wordpress' ),
		'id'               => 'wpfc_service_type',
		'type'             => 'select',
		'show_option_none' => true,
		'options'          => cmb2_get_term_options( 'wpfc_service_type' ),
	) );
	$cmb->add_field( array(
		'name' => esc_html__( 'Main Bible Passage', 'sermon-manager-for-wordpress' ),
		'desc' => wp_sprintf( esc_html__( 'Enter the Bible passage with the full book names, e.g. %s.', 'sermon-manager-for-wordpress' ), '<code>' . esc_html__( 'John 3:16-18', 'sermon-manager-for-wordpress' ) . '</code>' ),
		'id'   => 'bible_passage',
		'type' => 'text',
	) );
	$cmb->add_field( array(
		'name'    => esc_html__( 'Description', 'sermon-manager-for-wordpress' ),
		'desc'    => esc_html__( 'Type a brief description about this sermon, an outline, or a full manuscript', 'sermon-manager-for-wordpress' ),
		'id'      => 'sermon_description',
		'type'    => 'wysiwyg',
		'options' => array( 'textarea_rows' => 7, 'media_buttons' => true, ),
	) );

	$cmb2 = new_cmb2_box( array(
		'id'           => 'wpfc_sermon_files',
		'title'        => esc_html__( 'Sermon Files', 'sermon-manager-for-wordpress' ),
		'object_types' => array( 'wpfc_sermon', ), // Post type
		'context'      => 'normal',
		'priority'     => 'high',
		'show_names'   => true, // Show field names on the left
		// 'cmb_styles' => false, // false to disable the CMB stylesheet
		// 'closed'     => true, // Keep the metabox closed by default
	) );
	$cmb2->add_field( array(
		'name' => esc_html__( 'Location of MP3', 'sermon-manager-for-wordpress' ),
		'desc' => esc_html__( 'Upload an audio file or enter an URL.', 'sermon-manager-for-wordpress' ),
		'id'   => 'sermon_audio',
		'type' => 'file',
		'text' => array(
			'add_upload_file_text' => __( 'Add Sermon Audio', 'sermon-manager-for-wordpress' ) // Change upload button text. Default: "Add or Upload File"
		),
	) );
	$cmb2->add_field( array(
		'name' => esc_html__( 'MP3 Duration', 'sermon-manager-for-wordpress' ),
		'desc' => wp_sprintf( esc_html__( 'Length in %s format (if left blank, will attempt to calculate automatically when you save)', 'sermon-manager-for-wordpress' ), '<code>' . esc_html__( 'hh:mm:ss', 'sermon-manager-for-wordpress' ) . '</code>' ),
		'id'   => '_wpfc_sermon_duration',
		'type' => 'text',
	) );
	$cmb2->add_field( array(
		'name' => esc_html__( 'Video Embed Code', 'sermon-manager-for-wordpress' ),
		'desc' => esc_html__( 'Paste your embed code for Vimeo, Youtube, or other service here', 'sermon-manager-for-wordpress' ),
		'id'   => 'sermon_video',
		'type' => 'textarea_code'
	) );
	$cmb2->add_field( apply_filters( 'sm_cmb2_field_sermon_video_link', array(
			'name' => esc_html__( 'Video Link', 'sermon-manager-for-wordpress' ),
			'desc' => esc_html__( 'Paste your link for Vimeo, Youtube, or other service here', 'sermon-manager-for-wordpress' ),
			'id'   => 'sermon_video_link',
			'type' => 'text'
		) )
	);
	$cmb2->add_field( array(
		'name' => esc_html__( 'Sermon Notes', 'sermon-manager-for-wordpress' ),
		'desc' => esc_html__( 'Upload a pdf file or enter an URL.', 'sermon-manager-for-wordpress' ),
		'id'   => 'sermon_notes',
		'type' => 'file',
		'text' => array(
			'add_upload_file_text' => __( 'Add File', 'sermon-manager-for-wordpress' ) // Change upload button text. Default: "Add or Upload File"
		),
	) );
	$cmb2->add_field( array(
		'name' => esc_html__( 'Bulletin', 'sermon-manager-for-wordpress' ),
		'desc' => esc_html__( 'Upload a pdf file or enter an URL.', 'sermon-manager-for-wordpress' ),
		'id'   => 'sermon_bulletin',
		'type' => 'file',
		'text' => array(
			'add_upload_file_text' => __( 'Add File', 'sermon-manager-for-wordpress' ) // Change upload button text. Default: "Add or Upload File"
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
