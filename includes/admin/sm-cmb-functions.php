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
	// Get the date format.
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

	$sermon_details_meta = new_cmb2_box( array(
		'id'           => 'wpfc_sermon_details',
		'title'        => esc_html__( 'Sermon Details', 'sermon-manager-for-wordpress' ),
		'object_types' => array( 'wpfc_sermon' ), // Post type.
		'context'      => 'normal',
		'priority'     => 'high',
		'show_names'   => true, // Show field names on the left.
	) );
	$sermon_details_meta->add_field( array(
		'name'         => esc_html__( 'Date Preached', 'sermon-manager-for-wordpress' ),
		// translators: %s date format, effectively <code>d/m/Y</code> or the like.
		'desc'         => esc_html__( '(optional)', 'sermon-manager-for-wordpress' ) . '<br>' . wp_sprintf( esc_html__( 'format: %s', 'sermon-manager-for-wordpress' ), $date_format_label ),
		'id'           => 'sermon_date',
		'type'         => 'text_date_timestamp',
		'date_format'  => $date_format,
		'autocomplete' => 'off',
	) );
	$sermon_details_meta->add_field( array(
		'name'             => sm_get_taxonomy_field( 'wpfc_service_type', 'singular_name' ),
		// translators: %1$s The singular label. Default Service Type.
		// translators: %2$s The plural label. Default Service Types.
		// translators: %3$s <a href="edit-tags.php?taxonomy=wpfc_service_type&post_type=wpfc_sermon" target="_blank">here</a>.
		'desc'             => wp_sprintf( esc_html__( 'Select the %1$s. Modify the %2$s %3$s.', 'sermon-manager-for-wordpress' ), strtolower( sm_get_taxonomy_field( 'wpfc_service_type', 'singular_name' ) ), strtolower( sm_get_taxonomy_field( 'wpfc_service_type', 'label' ) ), '<a href="' . admin_url( 'edit-tags.php?taxonomy=wpfc_service_type&post_type=wpfc_sermon' ) . '" target="_blank">here</a>' ),
		'id'               => 'wpfc_service_type',
		'type'             => 'select',
		'show_option_none' => true,
		'options'          => cmb2_get_term_options( 'wpfc_service_type' ),
	) );
	$sermon_details_meta->add_field( array(
		'name' => esc_html__( 'Main Bible Passage', 'sermon-manager-for-wordpress' ),
		// translators: %1$s see msgid "John 3:16-18", effectively <code>John 3:16-18</code><br>.
		// translators: %2$s see msgid "John 3:16-18, John 2:11-12", effectively <code>John 3:16-18, Luke 2:1-3</code>.
		'desc' => wp_sprintf( esc_html__( 'Enter the Bible passage with the full book names, e.g. %1$s Or multiple books like %2$s', 'sermon-manager-for-wordpress' ), '<code>' . esc_html__( 'John 3:16-18', 'sermon-manager-for-wordpress' ) . '</code><br>', '<code>' . esc_html__( 'John 3:16-18, Luke 2:1-3', 'sermon-manager-for-wordpress' ) . '</code>' ),
		'id'   => 'bible_passage',
		'type' => 'text',
	) );
	$sermon_details_meta->add_field( array(
		'name'    => esc_html__( 'Description', 'sermon-manager-for-wordpress' ),
		'desc'    => esc_html__( 'Type a brief description about this sermon, an outline, or a full manuscript', 'sermon-manager-for-wordpress' ),
		'id'      => 'sermon_description',
		'type'    => 'wysiwyg',
		'options' => array(
			'textarea_rows' => 7,
			'media_buttons' => true,
		),
	) );

	$sermon_files_meta = new_cmb2_box( array(
		'id'           => 'wpfc_sermon_files',
		'title'        => esc_html__( 'Sermon Files', 'sermon-manager-for-wordpress' ),
		'object_types' => array( 'wpfc_sermon' ),
		'context'      => 'normal',
		'priority'     => 'high',
		'show_names'   => true,
	) );
	$sermon_files_meta->add_field( array(
		'name' => esc_html__( 'Location of MP3', 'sermon-manager-for-wordpress' ),
		'desc' => esc_html__( 'Upload an audio file or enter an URL.', 'sermon-manager-for-wordpress' ),
		'id'   => 'sermon_audio',
		'type' => 'file',
		'text' => array(
			'add_upload_file_text' => 'Add Sermon Audio', // Change upload button text. Default: "Add or Upload File".
		),
	) );
	$sermon_files_meta->add_field( array(
		'name' => esc_html__( 'MP3 Duration', 'sermon-manager-for-wordpress' ),
		// translators: %s see msgid "hh:mm:ss", effectively <code>hh:mm:ss</code>.
		'desc' => wp_sprintf( esc_html__( 'Length in %s format (fill out only for remote files, local files will get data calculated by default)', 'sermon-manager-for-wordpress' ), '<code>' . esc_html__( 'hh:mm:ss', 'sermon-manager-for-wordpress' ) . '</code>' ),
		'id'   => '_wpfc_sermon_duration',
		'type' => 'text',
	) );
	$sermon_files_meta->add_field( array(
		'name' => esc_html__( 'Video Embed Code', 'sermon-manager-for-wordpress' ),
		'desc' => esc_html__( 'Paste your embed code for Vimeo, Youtube, Facebook, or direct video file here', 'sermon-manager-for-wordpress' ),
		'id'   => 'sermon_video',
		'type' => 'textarea_code',
	) );
	$sermon_files_meta->add_field( apply_filters( 'sm_cmb2_field_sermon_video_link', array(
		'name' => esc_html__( 'Video Link', 'sermon-manager-for-wordpress' ),
		'desc' => esc_html__( 'Paste your link for Vimeo, Youtube, Facebook, or direct video file here', 'sermon-manager-for-wordpress' ),
		'id'   => 'sermon_video_link',
		'type' => 'text_url',
	) ) );
	$sermon_files_meta->add_field( array(
		'name' => esc_html__( 'Single Sermon Note', 'sermon-manager-for-wordpress' ),
		'desc' => esc_html__( 'Upload  pdf file.', 'sermon-manager-for-wordpress' ),
		'id'   => 'sermon_notes',
		'type' => 'file',
		'text' => array(
			'add_upload_file_text' => esc_html__( 'Add File', 'sermon-manager-for-wordpress' ),
			// Change upload button text. Default: "Add or Upload File".
		),
	) );
	$sermon_files_meta->add_field( array(
		'name' => esc_html__( 'Multiple Sermon Notes', 'sermon-manager-for-wordpress' ),
		'desc' => esc_html__( 'Upload  pdf files.', 'sermon-manager-for-wordpress' ),
		'id'   => 'sermon_notes_multiple',
		'type' => 'file_list',
		'text' => array(
			'add_upload_file_text' => esc_html__( 'Add File', 'sermon-manager-for-wordpress' ),
			// Change upload button text. Default: "Add or Upload File".
		),
	) );
	$sermon_files_meta->add_field( array(
		'name' => esc_html__( 'Single Bulletin', 'sermon-manager-for-wordpress' ),
		'desc' => esc_html__( 'Upload a pdf file.', 'sermon-manager-for-wordpress' ),
		'id'   => 'sermon_bulletin',
		'type' => 'file',
		'text' => array(
			'add_upload_file_text' => esc_html__( 'Add File', 'sermon-manager-for-wordpress' ),
			// Change upload button text. Default: "Add or Upload File".
		),
	) );
	$sermon_files_meta->add_field( array(
		'name' => esc_html__( 'Multiple Bulletin', 'sermon-manager-for-wordpress' ),
		'desc' => esc_html__( 'Upload pdf files.', 'sermon-manager-for-wordpress' ),
		'id'   => 'sermon_bulletin_multiple',
		'type' => 'file_list',
		'text' => array(
			'add_upload_file_text' => esc_html__( 'Add File', 'sermon-manager-for-wordpress' ),
			// Change upload button text. Default: "Add or Upload File".
		),
	) );

	/**
	 * Allows to add/remove SM CMB2 fields.
	 *
	 * @param CMB2 $sermon_details_meta Sermon Details meta.
	 * @param CMB2 $sermon_files_meta   Sermon Files meta box.
	 */
	do_action( 'sm_cmb2_meta_fields', $sermon_details_meta, $sermon_files_meta );
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
