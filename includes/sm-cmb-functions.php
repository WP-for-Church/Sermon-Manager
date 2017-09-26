<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

// Define the metabox and field configurations
add_action( 'cmb2_admin_init', 'wpfc_sermon_metaboxes' );

/*
 * Creation of all meta boxes
 */

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
		'desc'        => '(optional)<br>(format: ' . $date_format . ')',
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
