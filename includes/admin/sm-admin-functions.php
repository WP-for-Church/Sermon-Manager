<?php
/**
 * Functions used in admin area.
 *
 * @package SM/Core/Admin
 */

defined( 'ABSPATH' ) or die;

/**
 * Get all Sermon Manager screen ids.
 *
 * @return array Screen IDs
 * @since 2.9
 */
/*function sm_get_screen_ids() {
	$screen_ids = array(
		'wpfc_sermon',
		'edit-wpfc_sermon',
		'edit-wpfc_preacher',
		'edit-wpfc_sermon_series',
		'edit-wpfc_sermon_topics',
		'edit-wpfc_bible_book',
		'edit-wpfc_service_type',
		'wpfc_sermon_page_sm-settings',
		'wpfc_sermon_page_sm-import-export',
	);

	return apply_filters( 'sm_screen_ids', $screen_ids );
}*/

/**
 * Checks if we should change the dir, it will change it if we should.
 *
 * @return void
 */
function wpfc_maybe_change_downloads_upload_dir() {
	global $pagenow;

	if ( ! empty( $_REQUEST['post_id'] ) && ( 'async-upload.php' == $pagenow || 'media-upload.php' == $pagenow ) ) {
		if ( 'wpfc_sermon' == get_post_type( $_REQUEST['post_id'] ) ) {
			add_filter( 'upload_dir', 'wpfc_change_downloads_upload_dir' );
		}
	}
}

add_action( 'admin_init', 'wpfc_maybe_change_downloads_upload_dir', 999 );

/**
 * Set Upload Directory.
 *
 * Sets the upload dir to sermons. This function is called from
 * edd_change_downloads_upload_dir().
 *
 * @since 1.9
 *
 * @param array $upload Upload directory information.
 *
 * @return array Modified upload directory information.
 */
function wpfc_change_downloads_upload_dir( $upload ) {
	// Override the year / month being based on the post publication date, if year/month organization is enabled.
	if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
		// Generate the yearly and monthly dirs.
		$time             = current_time( 'mysql' );
		$y                = substr( $time, 0, 4 );
		$m                = substr( $time, 5, 2 );
		$upload['subdir'] = "/$y/$m";
	}

	$upload['subdir'] = '/sermons' . $upload['subdir'];
	$upload['path']   = $upload['basedir'] . $upload['subdir'];
	$upload['url']    = $upload['baseurl'] . $upload['subdir'];

	return $upload;
}

/**
 * Remove Service Type meta box since there is a custom way of assigning it
 */
function wpfc_remove_service_type_meta_box() {
	remove_meta_box( 'tagsdiv-wpfc_service_type', 'wpfc_sermon', 'side' );
}

add_action( 'admin_menu', 'wpfc_remove_service_type_meta_box' );

/**
 * Adds sermon count to "At a Glance" screen
 */
function wpfc_dashboard() {
	// Get current sermon count.
	$num_posts = wp_count_posts( 'wpfc_sermon' );
	// Format the number to current locale.
	$num = number_format_i18n( $num_posts->publish );
	// Put correct singular or plural text
	// translators: %s integer count of sermons.
	$text = wp_sprintf( esc_html( _n( '%s sermon', '%s sermons', intval( $num_posts->publish ), 'sermon-manager-for-wordpress' ) ), $num );

	$count = '<li class="sermon-count">';

	if ( current_user_can( 'edit_posts' ) ) {
		$count .= '<a href="' . admin_url( 'edit.php?post_type=wpfc_sermon' ) . '">' . $text . '</a>';
	} else {
		$count .= $text;
	}

	$count .= '</li>';
	$count .= "<style>.sermon-count a:before { content: '\\f330' !important;}</style>";
	echo $count;
}

add_action( 'dashboard_glance_items', 'wpfc_dashboard' );

/**
 * Register required actions.
 */
function wpfc_taxonomy_short_description_actions() {
	$taxonomies = get_taxonomies();
	foreach ( $taxonomies as $taxonomy ) {
		if ( ! in_array( $taxonomy, array(
			'wpfc_preacher',
			'wpfc_sermon_series',
			'wpfc_sermon_topics',
			'wpfc_bible_book',
			'wpfc_service_type',
		) ) ) {
			continue;
		}

		add_action( 'manage_' . $taxonomy . '_custom_column', 'wpfc_taxonomy_short_description_rows', 100, 3 );
		add_action( 'manage_edit-' . $taxonomy . '_columns', 'wpfc_taxonomy_short_description_columns' );
		add_filter( 'manage_edit-' . $taxonomy . '_sortable_columns', 'wpfc_taxonomy_short_description_columns' );
	}
}

add_action( 'admin_init', 'wpfc_taxonomy_short_description_actions' );

/**
 * Replace existing column with custom so it can be modified.
 *
 * @param array $columns Existing columns.
 *
 * @return array
 */
function wpfc_taxonomy_short_description_columns( $columns ) {
	$position = 0;
	$iterator = 1;
	foreach ( $columns as $column => $display_name ) {
		if ( 'name' == $column ) {
			$position = $iterator;
			break;
		}
		$iterator ++;
	}
	if ( 0 < $position ) {
		$columns = array_slice( $columns, 0, $position, true ) + array( 'short_description' => 'Description' ) + array_slice( $columns, $position + 1, count( $columns ) - 1, true );

		return $columns;
	}

	return $columns;
}

/**
 * Add short description content.
 *
 * @param mixed  $default     Default content.
 * @param string $column_name Column name.
 * @param int    $term        Term ID.
 *
 * @return mixed|string
 */
function wpfc_taxonomy_short_description_rows( $default, $column_name, $term ) {
	if ( 'short_description' == $column_name ) {
		global $taxonomy;
		$default = term_description( $term, $taxonomy );
		$default = wp_trim_words( $default, 10 );
	}

	return $default;
}
