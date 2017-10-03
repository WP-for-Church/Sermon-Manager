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
		'desc'        => esc_html__( '(optional)', 'sermon-manager-for-wordpress' ) . '<br>' . wp_sprintf(  esc_html__( 'format: %s', 'sermon-manager-for-wordpress' ), $date_format ),
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
			'add_upload_file_text' => 'Add Sermon Audio' // Change upload button text. Default: "Add or Upload File"
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
			'add_upload_file_text' => esc_html__( 'Add File', 'sermon-manager-for-wordpress' ) // Change upload button text. Default: "Add or Upload File"
		),
	) );
	$cmb2->add_field( array(
		'name' => esc_html__( 'Bulletin', 'sermon-manager-for-wordpress' ),
		'desc' => esc_html__( 'Upload a pdf file or enter an URL.', 'sermon-manager-for-wordpress' ),
		'id'   => 'sermon_bulletin',
		'type' => 'file',
		'text' => array(
			'add_upload_file_text' => esc_html__( 'Add File', 'sermon-manager-for-wordpress' ) // Change upload button text. Default: "Add or Upload File"
		),
	) );
}
