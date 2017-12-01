<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

add_action( 'admin_init', 'wpfc_maybe_change_downloads_upload_dir', 999 );
add_action( 'admin_menu', 'wpfc_remove_service_type_meta_box' );
add_action( 'admin_init', 'wpfc_taxonomy_short_description_actions' );

if ( preg_match( '/3.(6|7)/', get_bloginfo( 'version' ) ) ) {
	add_action( 'right_now_content_table_end', 'wpfc_right_now' );
} else {
	add_action( 'dashboard_glance_items', 'wpfc_dashboard' );
}

add_filter( 'wpfc_validate_file', 'wpfc_sermon_audio_validate', 10, 3 );

/**
 * Checks if we should change the dir, it will change it if we should
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

/**
 * Set Upload Directory
 *
 * Sets the upload dir to sermons. This function is called from
 * edd_change_downloads_upload_dir()
 *
 * @since 1.9
 * @return array Upload directory information
 */
function wpfc_change_downloads_upload_dir( $upload ) {

	// Override the year / month being based on the post publication date, if year/month organization is enabled
	if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
		// Generate the yearly and monthly dirs
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
 * Enclose audio file for podcast on save and store in custom fields.
 * Using meta boxes validation filter.
 * Added by T Hyde 9 Oct 2013; Updated by Jack 4/4/14
 *
 * @param $new
 * @param $post_id
 * @param $field
 *
 * @return $new unchanged
 */
function wpfc_sermon_audio_validate( $new, $post_id, $field ) {
	// only for sermon audio
	if ( $field['id'] != 'sermon_audio' ) {
		return $new;
	}
	$audio = get_post_meta( $post_id, 'sermon_audio', true );
	// Stop if PowerPress plugin is active
	// Solves conflict regarding enclosure field: http://wordpress.org/support/topic/breaks-blubrry-powerpress-plugin?replies=6
	if ( defined( 'POWERPRESS_VERSION' ) ) {
		return false;
	}
	// Populate enclosure field with URL, length and format, if valid URL found
	// This will set the length of the enclosure automatically
	do_enclose( $audio, $post_id );
	// Set duration as post meta
	$current         = get_post_meta( $post_id, 'sermon_audio', true );
	$currentduration = get_post_meta( $post_id, '_wpfc_sermon_duration', true );
	// only grab if different (getting data from dropbox can be a bit slow)
	if ( $new != '' && ( $new != $current || empty( $currentduration ) ) ) {
		// get file data
		$duration = wpfc_mp3_duration( $new );
		// store in hidden custom fields
		update_post_meta( $post_id, '_wpfc_sermon_duration', $duration );
	} elseif ( $new == '' ) {
		// clean up if file removed
		delete_post_meta( $post_id, '_wpfc_sermon_duration' );
	}

	return $new;
}

/**
 * Remove Service Type meta box since there is a custom way of assigning it
 */
function wpfc_remove_service_type_meta_box() {
	remove_meta_box( 'tagsdiv-wpfc_service_type', 'wpfc_sermon', 'side' );
}

/**
 * Adds sermon count to "At a Glance" screen
 */
function wpfc_dashboard() {
	// get current sermon count
	$num_posts = wp_count_posts( 'wpfc_sermon' );
	// format the number to current locale
	$num = number_format_i18n( $num_posts->publish );
	// put correct singular or plural text
	// translators: %s integer count of sermons
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


/*
Taxonomy Short Description
http://wordpress.mfields.org/plugins/taxonomy-short-description/
Shortens the description shown in the administration panels for all categories, tags and custom taxonomies.
V: 1.3.1
Copyright 2011  Michael Fields  michael@mfields.org

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License version 2 as published by
the Free Software Foundation.

Function names have been modified to prevent conflicts.
*/

// Actions.
function wpfc_taxonomy_short_description_actions() {
	$taxonomies = get_taxonomies();
	foreach ( $taxonomies as $taxonomy ) {
		$config = get_taxonomy( $taxonomy );
		if ( isset( $config->show_ui ) && true == $config->show_ui ) {
			add_action( 'manage_' . $taxonomy . '_custom_column', 'wpfc_taxonomy_short_description_rows', 10, 3 );
			add_action( 'manage_edit-' . $taxonomy . '_columns', 'wpfc_taxonomy_short_description_columns' );
			add_filter( 'manage_edit-' . $taxonomy . '_sortable_columns', 'wpfc_taxonomy_short_description_columns' );
		}
	}
}

// Term Columns.
// Remove the default "Description" column. Add a custom "Short Description" column.
function wpfc_taxonomy_short_description_columns( $columns ) {
	$position = 0;
	$iterator = 1;
	foreach ( $columns as $column => $display_name ) {
		if ( 'name' == $column ) {
			$position = $iterator;
		}
		$iterator ++;
	}
	if ( 0 < $position ) {
		/* Store all columns up to and including "Name". */
		$before = $columns;
		array_splice( $before, $position );

		/* All of the other columns are stored in $after. */
		$after = $columns;
		$after = array_diff( $columns, $before );

		/* Prepend a custom column for the short description. */
		$after                              = array_reverse( $after, true );
		$after['mfields_short_description'] = $after['description'];
		$after                              = array_reverse( $after, true );

		/* Remove the original description column. */
		unset( $after['description'] );

		/* Join all columns back together. */
		$columns = $before + $after;
	}

	return $columns;
}


// Term Rows. - Display the shortened description in each row's custom column.
function wpfc_taxonomy_short_description_rows( $string, $column_name, $term ) {
	if ( 'mfields_short_description' == $column_name ) {
		global $taxonomy;
		$string = term_description( $term, $taxonomy );
		$string = wpfc_taxonomy_short_description_shorten( $string, apply_filters( 'mfields_taxonomy_short_description_length', 130 ) );
	}

	return $string;
}

// Shorten a string to a given length.
function wpfc_taxonomy_short_description_shorten( $string, $max_length = 23, $append = '&#8230;', $encoding = 'utf8' ) {

	/* Sanitize $string. */
	$string = strip_tags( $string );
	$string = trim( $string );
	$string = html_entity_decode( $string, ENT_QUOTES, 'UTF-8' );
	$string = rtrim( $string, '-' );

	/* Sanitize $max_length */
	if ( 0 == abs( (int) $max_length ) ) {
		$max_length = 23;
	}

	/* Return early if the php "mbstring" extension is not installed. */
	if ( ! function_exists( 'mb_substr' ) ) {
		$length = strlen( $string );
		if ( $length > $max_length ) {
			return substr_replace( $string, $append, $max_length );
		}

		return $string;
	}

	/* Count how many characters are in the string. */
	$length = strlen( utf8_decode( $string ) );

	/* String is longer than max-length. It needs to be shortened. */
	if ( $length > $max_length ) {

		/* Shorten the string to max-length */
		$short = substr( $string, 0, $max_length );

		/*
		 * A word has been cut in half during shortening.
		 * If the shortened string contains more than one word
		 * the last word in the string will be removed.
		 */
		if ( 0 !== strpos( $string, $short . ' ', 0 ) ) {
			$pos = strpos( $short, ' ' );
			if ( false !== $pos ) {
				$short = strpos( $short, 0, $pos );
			}
		}

		/* Append shortened string with the value of $append preceeded by a non-breaking space. */
		$string = $short . ' ' . $append;
	}

	return $string;
}

/**
 * Returns duration of an MP3 file
 *
 * @param string $mp3_url URL to the MP3 file
 *
 * @return string duration
 */
function wpfc_mp3_duration( $mp3_url ) {
	if ( empty( $mp3_url ) ) {
		return '';
	}

	if ( ! class_exists( 'getID3' ) ) {
		require_once ABSPATH . 'wp-includes/ID3/getid3.php';
	}

	// create a temporary file for the MP3 file
	$filename = tempnam( '/tmp', 'getid3' );

	if ( file_put_contents( $filename, file_get_contents( $mp3_url ) ) ) {
		$getID3       = new getID3;
		$ThisFileInfo = $getID3->analyze( $filename );
		unlink( $filename );
	}

	$duration = isset( $ThisFileInfo['playtime_string'] ) ? $ThisFileInfo['playtime_string'] : '';

	return $duration;
}
