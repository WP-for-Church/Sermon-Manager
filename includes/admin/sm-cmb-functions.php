<?php
/**
 * CMB2 metaboxes creation related functions.
 *
 * @package SM/Core/Admin/CMB2
 */

defined( 'ABSPATH' ) or die;

/**
 * Define the metaboxes and field configurations.
 */
function wpfc_sermon_metaboxes() {

	$cmb = new_cmb2_box( array(
		'id'           => 'wpfc_sermon_details',
		'title'        => esc_html__( 'Sermon Details', 'sermon-manager-for-wordpress' ),
		'object_types' => array( 'wpfc_sermon' ), // Post type.
		'context'      => 'normal',
		'priority'     => 'high',
		'show_names'   => true, // Show field names on the left.
	) );

	switch ( \SermonManager::getOption( 'date_format' ) ) {
		case '0':
			$date_format_label = 'mm/dd/YYYY';
			$date_format       = 'm/d/Y';
			break;
		case '1':
			$date_format_label = 'dd/mm/YYYY';
			$date_format       = 'd/m/Y';
			break;
		case '2':
			$date_format_label = 'YYYY/mm/dd';
			$date_format       = 'Y/m/d';
			break;
		case '3':
			$date_format_label = 'YYYY/dd/mm';
			$date_format       = 'Y/d/m';
			break;
		default:
			$date_format_label = 'mm/dd/YYYY';
			$date_format       = 'm/d/Y';
			break;
	}

	$cmb->add_field( array(
		'name'        => esc_html__( 'Date Preached', 'sermon-manager-for-wordpress' ),
		// translators: %s date format, effectively <code>d/m/Y</code> or the like.
		'desc'        => esc_html__( '(optional)', 'sermon-manager-for-wordpress' ) . '<br>' . wp_sprintf( esc_html__( 'format: %s', 'sermon-manager-for-wordpress' ), $date_format_label ),
		'id'          => 'sermon_date',
		'type'        => 'text_date_timestamp',
		'date_format' => $date_format,
	) );

	$cmb->add_field( array(
		'name'             => esc_html__( 'Service Type', 'sermon-manager-for-wordpress' ),
		// translators: %s <a href="edit-tags.php?taxonomy=wpfc_service_type&post_type=wpfc_sermon" target="_blank">here</a>.
		'desc'             => wp_sprintf( esc_html__( 'Select the type of service. Modify service types %s.', 'sermon-manager-for-wordpress' ), '<a href="' . admin_url( 'edit-tags.php?taxonomy=wpfc_service_type&post_type=wpfc_sermon' ) . '" target="_blank">here</a>' ),
		'id'               => 'wpfc_service_type',
		'type'             => 'select',
		'show_option_none' => true,
		'options'          => cmb2_get_term_options( 'wpfc_service_type' ),
	) );

	$cmb->add_field( array(
		'name' => esc_html__( 'Main Bible Passage', 'sermon-manager-for-wordpress' ),
		// translators: %s see msgid "John 3:16-18", effectively <code>John 3:16-18</code>.
		'desc' => wp_sprintf( esc_html__( 'Enter the Bible passage with the full book names, e.g. %s.', 'sermon-manager-for-wordpress' ), '<code>' . esc_html__( 'John 3:16-18', 'sermon-manager-for-wordpress' ) . '</code>' ),
		'id'   => 'bible_passage',
		'type' => 'text',
	) );
	$cmb->add_field( array(
		'name'    => esc_html__( 'Description', 'sermon-manager-for-wordpress' ),
		'desc'    => esc_html__( 'Type a brief description about this sermon, an outline, or a full manuscript', 'sermon-manager-for-wordpress' ),
		'id'      => 'sermon_description',
		'type'    => 'wysiwyg',
		'options' => array(
			'textarea_rows' => 7,
			'media_buttons' => true,
		),
	) );

	$cmb2 = new_cmb2_box( array(
		'id'           => 'wpfc_sermon_files',
		'title'        => esc_html__( 'Sermon Files', 'sermon-manager-for-wordpress' ),
		'object_types' => array( 'wpfc_sermon' ),
		'context'      => 'normal',
		'priority'     => 'high',
		'show_names'   => true,
	) );
	$cmb2->add_field( array(
		'name' => esc_html__( 'Location of MP3', 'sermon-manager-for-wordpress' ),
		'desc' => esc_html__( 'Upload an audio file or enter an URL.', 'sermon-manager-for-wordpress' ),
		'id'   => 'sermon_audio',
		'type' => 'file',
		'text' => array(
			'add_upload_file_text' => 'Add Sermon Audio', // Change upload button text. Default: "Add or Upload File".
		),
	) );
	$cmb2->add_field( array(
		'name' => esc_html__( 'MP3 Duration', 'sermon-manager-for-wordpress' ),
		// translators: %s see msgid "hh:mm:ss", effectively <code>hh:mm:ss</code>.
		'desc' => wp_sprintf( esc_html__( 'Length in %s format (if left blank, will attempt to calculate automatically when you save)', 'sermon-manager-for-wordpress' ), '<code>' . esc_html__( 'hh:mm:ss', 'sermon-manager-for-wordpress' ) . '</code>' ),
		'id'   => '_wpfc_sermon_duration',
		'type' => 'text',
	) );
	$cmb2->add_field( array(
		'name' => esc_html__( 'Video Embed Code', 'sermon-manager-for-wordpress' ),
		'desc' => esc_html__( 'Paste your embed code for Vimeo, Youtube, Facebook, or direct video file here', 'sermon-manager-for-wordpress' ),
		'id'   => 'sermon_video',
		'type' => 'textarea_code',
	) );
	$cmb2->add_field( apply_filters( 'sm_cmb2_field_sermon_video_link', array(
		'name' => esc_html__( 'Video Link', 'sermon-manager-for-wordpress' ),
		'desc' => esc_html__( 'Paste your link for Vimeo, Youtube, Facebook, or direct video file here', 'sermon-manager-for-wordpress' ),
		'id'   => 'sermon_video_link',
		'type' => 'text_url',
	) ) );
	$cmb2->add_field( array(
		'name' => esc_html__( 'Sermon Notes', 'sermon-manager-for-wordpress' ),
		'desc' => esc_html__( 'Upload a pdf file or enter an URL.', 'sermon-manager-for-wordpress' ),
		'id'   => 'sermon_notes',
		'type' => 'file',
		'text' => array(
			'add_upload_file_text' => esc_html__( 'Add File', 'sermon-manager-for-wordpress' ),
			// Change upload button text. Default: "Add or Upload File".
		),
	) );
	$cmb2->add_field( array(
		'name' => esc_html__( 'Bulletin', 'sermon-manager-for-wordpress' ),
		'desc' => esc_html__( 'Upload a pdf file or enter an URL.', 'sermon-manager-for-wordpress' ),
		'id'   => 'sermon_bulletin',
		'type' => 'file',
		'text' => array(
			'add_upload_file_text' => esc_html__( 'Add File', 'sermon-manager-for-wordpress' ),
			// Change upload button text. Default: "Add or Upload File".
		),
	) );
}

add_action( 'cmb2_admin_init', 'wpfc_sermon_metaboxes' );

/**
 * Gets a number of terms and displays them as options
 *
 * @param string $taxonomy Taxonomy terms to retrieve. Default is category.
 *
 * @return array An array of options that matches the CMB2 options array
 */
function cmb2_get_term_options( $taxonomy = 'category' ) {
	$args['taxonomy'] = $taxonomy;
	$taxonomy         = $args['taxonomy'];

	$args = array(
		'hide_empty' => false,
	);

	$terms = (array) get_terms(
		array(
			'taxonomy' => $taxonomy,
		) + $args );

	// Initialize an empty array.
	$term_options = array();
	if ( ! empty( $terms ) ) {
		foreach ( $terms as $term ) {
			$term_options[ $term->term_id ] = $term->name;
		}
	}

	return $term_options;
}

/**
 * Sanitizes the number.
 *
 * @param null           $null  Unused.
 * @param string|integer $value Value to sanitize.
 *
 * @return null|string|string[]
 */
function sm_cmb2_sanitize_text_number( $null = null, $value = '' ) {
	$value = preg_replace( '/[^0-9]/', '', $value );

	return $value;
}

add_filter( 'cmb2_sanitize_text_number', 'sm_cmb2_sanitize_text_number', 10, 2 );
