<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

add_action( 'admin_init', 'wpfc_maybe_change_downloads_upload_dir', 999 );
add_action( 'admin_menu', 'wpfc_remove_service_type_meta_box' );
add_action( 'load-edit.php', 'wpfc_sermon_order_attach' );
add_action( 'admin_init', 'wpfc_taxonomy_short_description_actions' );

if ( preg_match( '/3.(6|7)/', get_bloginfo( 'version' ) ) ) {
	add_action( 'right_now_content_table_end', 'wpfc_right_now' );
} else {
	add_action( 'dashboard_glance_items', 'wpfc_dashboard' );
}

add_filter( 'wpfc_validate_file', 'wpfc_sermon_audio_validate', 10, 3 );
add_filter( 'post_updated_messages', 'wpfc_sermon_updated_messages' );
add_action( 'manage_wpfc_sermon_posts_custom_column', 'wpfc_sermon_columns' );
add_filter( 'manage_edit-wpfc_sermon_columns', 'wpfc_sermon_edit_columns' );
add_filter( 'manage_edit-wpfc_sermon_sortable_columns', 'wpfc_column_register_sortable' );

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
 * Change various messages
 *
 * @param array $messages Existing messages
 *
 * @return array
 */
function wpfc_sermon_updated_messages( $messages ) {
	global $post, $post_ID;

	$messages['wpfc_sermon'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => sprintf( __( 'Sermon updated. <a href="%s">View sermon</a>', 'sermon-manager' ), esc_url( get_permalink( $post_ID ) ) ),
		2  => __( 'Custom field updated.', 'sermon-manager' ),
		3  => __( 'Custom field deleted.', 'sermon-manager' ),
		4  => __( 'Sermon updated.', 'sermon-manager' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Sermon restored to revision from %s', 'sermon-manager' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => sprintf( __( 'Sermon published. <a href="%s">View sermon</a>', 'sermon-manager' ), esc_url( get_permalink( $post_ID ) ) ),
		7  => __( 'Sermon saved.', 'sermon-manager' ),
		8  => sprintf( __( 'Sermon submitted. <a target="_blank" href="%s">Preview sermon</a>', 'sermon-manager' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		9  => sprintf( __( 'Sermon scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview sermon</a>', 'sermon-manager' ),
			// translators: Publish box date format, see http://php.net/date
			date_i18n( __( 'M j, Y @ G:i', 'sermon-manager' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( 'Sermon draft updated. <a target="_blank" href="%s">Preview sermon</a>', 'sermon-manager' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);

	return $messages;
}

/**
 * Calls ordering function on init
 */
function wpfc_sermon_order_attach() {
	add_filter( 'request', 'wpfc_sermon_order' );
}

/**
 * Orders the sermons when order is requested
 *
 * @param array $vars Request parameters
 *
 * @return array modified request parameters
 */
function wpfc_sermon_order( $vars ) {
	if ( isset( $vars['post_type'] ) && $vars['post_type'] === 'wpfc_sermon' ) {
		if ( isset( $vars['orderby'] ) ) {
			switch ( $vars['orderby'] ) {
				case 'passage':
					$vars = array_merge( $vars, array(
						'meta_key' => 'bible_passage',
						'orderby'  => 'meta_value'
					) );
					break;
				case 'preached':
					$vars = array_merge( $vars, array(
						'meta_key'       => 'sermon_date',
						'orderby'        => 'meta_value_num',
						'meta_value_num' => time(),
						'meta_compare'   => '<=',
					) );
					break;
			}
		}
	}

	return $vars;
}

/**
 * Register edit.php columns
 *
 * @return array The columns
 */
function wpfc_sermon_edit_columns() {
	$columns = array(
		"cb"       => "<input type=\"checkbox\" />",
		"title"    => __( 'Sermon Title', 'sermon-manager' ),
		"preacher" => __( \SermonManager::getOption( 'preacher_label' ) ?: 'Preacher', 'sermon-manager' ),
		"series"   => __( 'Sermon Series', 'sermon-manager' ),
		"topics"   => __( 'Topics', 'sermon-manager' ),
		"views"    => __( 'Views', 'sermon-manager' ),
		"preached" => __( 'Date Preached', 'sermon-manager' ),
		"passage"  => __( 'Bible Passage', 'sermon-manager' ),
	);

	return $columns;
}

/**
 * Echo data for sermon data columns in edit.php
 *
 * @param string $column The column being requested
 *
 * @return void
 */
function wpfc_sermon_columns( $column ) {
	global $post;

	if ( empty( $post->ID ) ) {
		echo 'Error. Can\'t find sermon ID.';

		return;
	}

	switch ( $column ) {
		case "preacher":
			$data = get_the_term_list( $post->ID, 'wpfc_preacher', '', ', ', '' );
			break;
		case "series":
			$data = get_the_term_list( $post->ID, 'wpfc_sermon_series', '', ', ', '' );
			break;
		case "topics":
			$data = get_the_term_list( $post->ID, 'wpfc_sermon_topics', '', ', ', '' );

			// Sometimes corrupted data gets cached, clearing the cache might help
			if ( $data instanceof WP_Error ) {
				if ( get_transient( 'wpfc_topics_cache_cleared' ) ) {
					wp_cache_delete( $post->ID, 'wpfc_sermon_topics_relationships' );
					$data = get_the_term_list( $post->ID, 'wpfc_sermon_topics', '', ', ', '' );
					set_transient( 'wpfc_topics_cache_cleared', 1, 60 * 60 );
				}
			}

			break;
		case "views":
			$data = wpfc_entry_views_get( array( 'post_id' => $post->ID ) );
			break;
		case "preached":
			$data = sm_get_the_date( '', $post );
			break;
		case "passage":
			$data = get_post_meta( $post->ID, 'bible_passage', true );
			break;
		default:
			$data = '';
	}

	if ( $data instanceof WP_Error ) {
		echo '<strong>Error:</strong> ' . $data->get_error_message();

		return;
	}

	echo $data;

	return;
}

/**
 * Register the column as sortable
 * @url https://gist.github.com/scribu/906872
 */
function wpfc_column_register_sortable() {
	$columns = array(
		"title"    => "title",
		"preached" => "preached",
		"preacher" => "preacher",
		"series"   => "series",
		"topics"   => "topics",
		"views"    => "views",
		"passage"  => "passage"
	);

	return $columns;
}

/**
 * Add the number of sermons to the Right Now on the Dashboard.
 * Used only on WP 3.6 and 3.7.
 *
 * @since 2014-01-08
 */
function wpfc_right_now() {
	$num_posts = wp_count_posts( 'wpfc_sermon' );
	$num       = number_format_i18n( $num_posts->publish );
	$text      = _n( 'Sermon', 'Sermons', intval( $num_posts->publish ) );
	if ( current_user_can( 'edit_posts' ) ) {
		$num  = "<a href='edit.php?post_type=wpfc_sermon'>$num</a>";
		$text = "<a href='edit.php?post_type=wpfc_sermon'>$text</a>";
	}
	echo '<td class="first b b-sermon">' . $num . '</td><td class="t sermons">' . $text . '</td></tr>';
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
	$text = _n( 'Sermon', 'Sermons', intval( $num_posts->publish ) );

	$count = '<li class="sermon-count">';

	if ( current_user_can( 'edit_posts' ) ) {
		$count .= '<a href="' . admin_url( 'edit.php?post_type=wpfc_sermon' ) . '">' . $num . ' ' . $text . '</a>';
	} else {
		$count .= $num . ' ' . $text;
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
		$short = mb_substr( $string, 0, $max_length, $encoding );

		/*
		 * A word has been cut in half during shortening.
		 * If the shortened string contains more than one word
		 * the last word in the string will be removed.
		 */
		if ( 0 !== mb_strpos( $string, $short . ' ', 0, $encoding ) ) {
			$pos = mb_strrpos( $short, ' ', $encoding );
			if ( false !== $pos ) {
				$short = mb_substr( $short, 0, $pos, $encoding );
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
