<?php
/**
 * Core Functions.
 *
 * General core functions available on both the front-end and admin.
 *
 * @package SM/Core
 */

defined( 'ABSPATH' ) or die;

/**
 * Retrieve the date on which the sermon was preached.
 *
 * Unlike sm_the_date() this function will always return the date.
 * Modify output with the {@see 'sm_get_the_date'} filter.
 *
 * @param string  $d    Format.
 * @param WP_Post $post The sermon.
 *
 * @return false|string
 */
function sm_get_the_date( $d = '', $post = null ) {
	$the_date = SM_Dates::get( $d, $post );
	if ( ! $the_date ) {
		$post = get_post( $post );

		if ( ! $post ) {
			return false;
		}

		if ( '' == $d ) {
			$the_date = mysql2date( get_option( 'date_format' ), $post->post_date, true );
		} else {
			$the_date = mysql2date( $d, $post->post_date, true );
		}
	}

	/**
	 * Filters the date a sermon was preached.
	 *
	 * @param string      $the_date The formatted date.
	 * @param string      $d        PHP date format. Defaults to 'date_format' option
	 *                              if not specified.
	 * @param int|WP_Post $post     The post object or ID.
	 *
	 * @since 2.6
	 *
	 */
	return apply_filters( 'sm_get_the_date', $the_date, $d, $post );
}

/**
 * Display or Retrieve the date the current sermon was preached (once per date).
 *
 * Made to replace `wpfc_sermon_date()`.
 *
 * HTML output can be filtered with 'sm_the_date'.
 * Date string output can be filtered with 'sm_get_the_date'.
 *
 * @param string      $d      Optional. PHP date format. Defaults to the date_format option if not specified.
 * @param string      $before Optional. Output before the date.
 * @param string      $after  Optional. Output after the date.
 * @param int|WP_Post $post   Required if function is not used within The Loop; otherwise optional.
 *
 * @return void
 * @since 2.12 Added $post parameter
 *
 * @since 2.6
 */
function sm_the_date( $d = '', $before = '', $after = '', $post = null ) {
	$the_date = $before . sm_get_the_date( $d, $post ) . $after;

	/**
	 * Filters the date a post was preached
	 *
	 * @param string      $the_date The formatted date string.
	 * @param string      $d        PHP date format. Defaults to 'date_format' option
	 *                              if not specified.
	 * @param string      $before   HTML output before the date.
	 * @param string      $after    HTML output after the date.
	 * @param int|WP_Post $post     Sermon post object or ID.
	 *
	 * @since 2.6
	 *
	 */
	echo apply_filters( 'the_date', $the_date, $d, $before, $after, $post );
}

/**
 * Get permalink settings for Sermon Manager independent of the user locale.
 *
 * @return array
 * @since 2.12.3 added filter to easily modify slugs.
 *
 * @since 2.7
 */
function sm_get_permalink_structure() {
	if ( did_action( 'admin_init' ) ) {
		sm_switch_to_site_locale();
	}

	$permalinks = wp_parse_args( (array) get_option( 'sm_permalinks', array() ), array(
		'wpfc_preacher'          => trim( sanitize_title( \SermonManager::getOption( 'preacher_label' ) ) ),
		'wpfc_sermon_series'     => '',
		'wpfc_sermon_topics'     => '',
		'wpfc_bible_book'        => '',
		'wpfc_service_type'      => trim( sanitize_title( \SermonManager::getOption( 'service_type_label' ) ) ),
		'wpfc_sermon'            => trim( \SermonManager::getOption( 'archive_slug' ) ),
		'use_verbose_page_rules' => false,
	) );

	// Ensure rewrite slugs are set.
	$permalinks['wpfc_preacher']      = untrailingslashit( empty( $permalinks['wpfc_preacher'] ) ? _x( 'preacher', 'slug', 'sermon-manager-for-wordpress' ) : $permalinks['wpfc_preacher'] );
	$permalinks['wpfc_sermon_series'] = untrailingslashit( empty( $permalinks['wpfc_sermon_series'] ) ? _x( 'series', 'slug', 'sermon-manager-for-wordpress' ) : $permalinks['wpfc_sermon_series'] );
	$permalinks['wpfc_sermon_topics'] = untrailingslashit( empty( $permalinks['wpfc_sermon_topics'] ) ? _x( 'topics', 'slug', 'sermon-manager-for-wordpress' ) : $permalinks['wpfc_sermon_topics'] );
	$permalinks['wpfc_bible_book']    = untrailingslashit( empty( $permalinks['wpfc_bible_book'] ) ? _x( 'book', 'slug', 'sermon-manager-for-wordpress' ) : $permalinks['wpfc_bible_book'] );
	$permalinks['wpfc_service_type']  = untrailingslashit( empty( $permalinks['wpfc_service_type'] ) ? _x( 'service-type', 'slug', 'sermon-manager-for-wordpress' ) : $permalinks['wpfc_service_type'] );
	$permalinks['wpfc_sermon']        = untrailingslashit( empty( $permalinks['wpfc_sermon'] ) ? _x( 'sermons', 'slug', 'sermon-manager-for-wordpress' ) : $permalinks['wpfc_sermon'] );

	if ( \SermonManager::getOption( 'common_base_slug' ) ) {
		foreach ( $permalinks as $name => &$permalink ) {
			if ( 'wpfc_sermon' === $name ) {
				continue;
			}

			$permalink = $permalinks['wpfc_sermon'] . '/' . $permalink;
		}
	}

	if ( did_action( 'admin_init' ) ) {
		sm_restore_locale();
	}

	/**
	 * Allows to easily modify the slugs of sermons and taxonomies.
	 *
	 * @param array $permalinks Existing permalinks structure.
	 *
	 * @since 2.12.3
	 */
	return apply_filters( 'wpfc_sm_permalink_structure', $permalinks );
}

/**
 * Switch Sermon Manager to site language.
 *
 * @since 2.7
 */
function sm_switch_to_site_locale() {
	if ( function_exists( 'switch_to_locale' ) ) {
		switch_to_locale( get_locale() );

		// Filter on plugin_locale so load_plugin_textdomain loads the correct locale.
		add_filter( 'plugin_locale', 'get_locale' );

		// Init Sermon Manager locale.
		SermonManager::load_translations();
	}
}

/**
 * Switch Sermon Manager language to original.
 *
 * @since 2.7
 */
function sm_restore_locale() {
	if ( function_exists( 'restore_previous_locale' ) ) {
		restore_previous_locale();

		// Remove filter.
		remove_filter( 'plugin_locale', 'get_locale' );

		// Init Sermon Manager locale.
		SermonManager::load_translations();
	}
}

/**
 * Display a Sermon Manager help tip.
 *
 * @param string $tip        Help tip text.
 * @param bool   $allow_html Allow sanitized HTML if true or escape.
 *
 * @return string
 * @since 2.9
 */
function sm_help_tip( $tip, $allow_html = false ) {
	if ( $allow_html ) {
		$tip = sm_sanitize_tooltip( $tip );
	} else {
		$tip = esc_attr( $tip );
	}

	return '<span class="sm-help-tip" data-tip="' . $tip . '"></span>';
}

/**
 * Get an image size in pixels, based on ID.
 *
 * Variable is filtered by sm_get_image_size_{image_size}.
 *
 * @param array|string $image_size The ID.
 *
 * @return array
 * @since 2.9
 */
function sm_get_image_size( $image_size ) {
	if ( is_array( $image_size ) ) {
		$width  = isset( $image_size[0] ) ? $image_size[0] : 300;
		$height = isset( $image_size[1] ) ? $image_size[1] : 200;
		$crop   = isset( $image_size[2] ) ? $image_size[2] : true;

		$size = array(
			'width'  => $width,
			'height' => $height,
			'crop'   => $crop,
		);

		$image_size = $width . '_' . $height;

	} elseif ( in_array( $image_size, array( 'sermon_small', 'sermon_medium', 'sermon_wide' ) ) ) {
		// Reset variables.
		$w = null;
		$h = null;
		$c = null;

		switch ( $image_size ) {
			case 'sermon_small':
				$w = 75;
				$h = 75;
				$c = true;

				break;
			case 'sermon_medium':
				$w = 300;
				$h = 200;
				$c = true;

				break;
			case 'sermon_wide':
				$w = 940;
				$h = 350;
				$c = true;

				break;
		}

		$size           = get_option( $image_size . '_image_size', array() );
		$size['width']  = isset( $size['width'] ) ? $size['width'] : $w;
		$size['height'] = isset( $size['height'] ) ? $size['height'] : $h;
		$size['crop']   = isset( $size['crop'] ) ? $size['crop'] : $c;

	} else {
		$size = array(
			'width'  => 300,
			'height' => 200,
			'crop'   => true,
		);
	}

	return apply_filters( 'sm_get_image_size_' . $image_size, $size );
}

/**
 * Checks the file extension and gets image size based on filetype.
 *
 * @param string $img_loc Image URL.
 *
 * @return array|false Array with first item as width and second as height of false if unable to get data.
 *
 * @since 2.10
 */
function sm_get_image_dimensions( $img_loc ) {
	// Check if url is set.
	if ( trim( $img_loc ) === '' ) {
		return false;
	}

	switch ( pathinfo( strtolower( $img_loc ), PATHINFO_EXTENSION ) ) {
		case 'jpg':
		case 'jpeg':
			return sm_get_jpeg_dimensions( $img_loc );
		case 'png':
			return sm_get_png_dimensions( $img_loc );
	}

	return false;
}

/**
 * Retrieve PNG width and height without downloading/reading entire image.
 * Adapted from http://php.net/manual/en/function.getimagesize.php#88793
 *
 * @param string $img_loc Image URL.
 *
 * @return array|false Array with first item as width and second as height of false if unable to get data
 *
 * @since 2.10
 */
function sm_get_png_dimensions( $img_loc ) {
	$handle = fopen( $img_loc, 'rb' );

	// Check if url is accessible or fail gracefully.
	if ( false === $handle ) {
		return false;
	}

	if ( ! feof( $handle ) ) {
		$new_block = fread( $handle, 24 );
		if ( "\x89" == $new_block[0] && "\x50" == $new_block[1] && "\x4E" == $new_block[2] && "\x47" == $new_block[3] && "\x0D" == $new_block[4] && "\x0A" == $new_block[5] && "\x1A" == $new_block[6] && "\x0A" == $new_block[7] ) {
			if ( "\x49\x48\x44\x52" === $new_block[12] . $new_block[13] . $new_block[14] . $new_block[15] ) {
				$width = unpack( 'H*', $new_block[16] . $new_block[17] . $new_block[18] . $new_block[19] );
				$width = hexdec( $width[1] );

				$height = unpack( 'H*', $new_block[20] . $new_block[21] . $new_block[22] . $new_block[23] );
				$height = hexdec( $height[1] );

				return array( $width, $height );
			}
		}
	}

	return false;
}

/**
 * Retrieve JPEG width and height without downloading/reading entire image.
 *
 * @param string $img_loc Image URL.
 *
 * @return array|false
 * @since 2.9
 *
 * @see   http://php.net/manual/en/function.getimagesize.php#88793
 */
function sm_get_jpeg_dimensions( $img_loc ) {
	$handle = fopen( $img_loc, 'rb' );

	// Check if url is accessible or fail gracefully.
	if ( false === $handle ) {
		return false;
	}

	$new_block = null;
	if ( ! feof( $handle ) ) {
		$new_block = fread( $handle, 32 );
		$i         = 0;
		if ( "\xFF" == $new_block[ $i ] && "\xD8" == $new_block[ $i + 1 ] && "\xFF" == $new_block[ $i + 2 ] && "\xE0" == $new_block[ $i + 3 ] ) {
			$i += 4;
			if ( "\x4A" == $new_block[ $i + 2 ] && "\x46" == $new_block[ $i + 3 ] && "\x49" == $new_block[ $i + 4 ] && "\x46" == $new_block[ $i + 5 ] && "\x00" == $new_block[ $i + 6 ] ) {
				// Read block size and skip ahead to begin cycling through blocks in search of SOF marker.
				$block_size = unpack( 'H*', $new_block[ $i ] . $new_block[ $i + 1 ] );
				$block_size = hexdec( $block_size[1] );
				while ( ! feof( $handle ) ) {
					$i         += $block_size;
					$new_block .= fread( $handle, $block_size );
					if ( "\xFF" == $new_block[ $i ] ) {
						// New block detected, check for SOF marker.
						$sof_marker = array(
							"\xC0",
							"\xC1",
							"\xC2",
							"\xC3",
							"\xC5",
							"\xC6",
							"\xC7",
							"\xC8",
							"\xC9",
							"\xCA",
							"\xCB",
							"\xCD",
							"\xCE",
							"\xCF",
						);
						if ( in_array( $new_block[ $i + 1 ], $sof_marker ) ) {
							// SOF marker detected. Width and height information is contained in bytes 4-7 after this byte.
							$size_data = $new_block[ $i + 2 ] . $new_block[ $i + 3 ] . $new_block[ $i + 4 ] . $new_block[ $i + 5 ] . $new_block[ $i + 6 ] . $new_block[ $i + 7 ] . $new_block[ $i + 8 ];
							$unpacked  = unpack( 'H*', $size_data );
							$unpacked  = $unpacked[1];
							$height    = hexdec( $unpacked[6] . $unpacked[7] . $unpacked[8] . $unpacked[9] );
							$width     = hexdec( $unpacked[10] . $unpacked[11] . $unpacked[12] . $unpacked[13] );

							return array( $width, $height );
						} else {
							// Skip block marker and read block size.
							$i += 2;

							$block_size = unpack( 'H*', $new_block[ $i ] . $new_block[ $i + 1 ] );
							$block_size = hexdec( $block_size[1] );
						}
					} else {
						return false;
					}
				}
			}
		}
	}

	return false;
}

/**
 * Import and assign thumbnail to sermon (or any other post/CPT).
 *
 * Accepts remote or local image URL.
 *
 * - If it is a local URL and it's pointing to a path under WP uploads directory, it will check for
 *   database attachment existence - if it exists, it will use it.
 *   If database attachment does not exist - it will create it without moving the image, and will use it.
 * - If it is a local URL and is not under WP uploads directory, it will act like it is a remote URL.
 * - If it is a remote URL, it will save it as such.
 *
 * (remote URLs are not supported by default in WP, but we have a piece of code that makes them usable)
 *
 * @param string $image_url The URL of the image to use (local or remote).
 * @param int    $post_id   Sermon/post to attach the image to; Passing 0 won't assign the image, it will
 *                          just import it and return the ID of the attachment.
 *
 * @return bool|int If $post_id is set to 0 - returns attachment ID; True|false otherwise, depending on success.
 * @since 2.9
 */
function sm_import_and_set_post_thumbnail( $image_url, $post_id = 0 ) {
	global $wpdb;

	if ( empty( $image_url ) || trim( $image_url ) === '' ) {
		return false;
	}

	// Check if local file.
	if ( strpos( $image_url, '/' ) === 0 && strpos( $image_url, '//' ) !== 0 ) {
		if ( ! file_exists( $image_url ) ) {
			return false;
		}
	}

	$attachment_id = attachment_url_to_postid( $image_url );
	$upload        = wp_upload_dir();

	if ( $attachment_id && 0 !== $attachment_id ) {
		// Continue with execution.
	} elseif ( $upload && false !== strpos( $image_url, $upload['baseurl'] ) ) {
		global $doing_sm_upload;

		$doing_sm_upload = true;

		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once( ABSPATH . 'wp-admin' . '/includes/image.php' );
			require_once( ABSPATH . 'wp-admin' . '/includes/file.php' );
			require_once( ABSPATH . 'wp-admin' . '/includes/media.php' );
		}

		$url = str_replace( $upload['baseurl'], $upload['basedir'], $image_url );
		preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $url, $matches );

		$attachment_id = media_handle_sideload( array(
			'name'     => basename( $matches[0] ),
			'tmp_name' => $url,
		), 0 );

		$doing_sm_upload = false;
	} else {
		$file = wp_check_filetype( $image_url );

		preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $image_url, $matches );

		$wpdb->insert( $wpdb->prefix . 'posts', array(
			'post_author'       => get_current_user_id(),
			'post_date'         => current_time( 'mysql' ),
			'post_date_gmt'     => get_gmt_from_date( current_time( 'mysql' ) ),
			'post_title'        => pathinfo( $matches[0], PATHINFO_FILENAME ),
			'post_status'       => 'inherit',
			'comment_status'    => get_default_comment_status( 'attachment' ),
			'ping_status'       => get_default_comment_status( 'attachment', 'pingback' ),
			'post_name'         => sanitize_title( pathinfo( $matches[0], PATHINFO_FILENAME ) ),
			'post_modified'     => current_time( 'mysql' ),
			'post_modified_gmt' => get_gmt_from_date( current_time( 'mysql' ) ),
			'post_parent'       => 0,
			'guid'              => $image_url,
			'menu_order'        => 0,
			'post_type'         => 'attachment',
			'post_mime_type'    => $file['type'],
			'comment_count'     => 0,
		), array(
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%d',
			'%s',
			'%d',
			'%s',
			'%s',
			'%d',
		) );

		$attachment_id = $wpdb->insert_id;

		update_post_meta( $attachment_id, '_wp_attached_file', $image_url );

		$size = sm_get_image_dimensions( $image_url );
		if ( is_array( $size ) ) {
			update_post_meta( $attachment_id, '_wp_attachment_metadata', array(
				'width'  => $size[0],
				'height' => $size[1],
			) );
		}
	}

	if ( 0 === $post_id ) {
		return $attachment_id;

	}

	return set_post_thumbnail( $post_id, $attachment_id );
}

/**
 * Get real image path in upload directory before it's overwritten.
 * And disable image moving.
 *
 * @since 2.9
 */
add_filter( 'pre_move_uploaded_file', function ( $null, $file ) {
	global $upload_dir_file_path, $doing_sm_upload;

	if ( true === $doing_sm_upload ) {
		$uploads              = wp_get_upload_dir();
		$upload_dir_file_path = str_replace( $uploads['basedir'], '', $file['tmp_name'] );

		return false;
	}

	return $null;
}, 10, 2 );

/**
 * Update image upload URL and path to the real image path location,
 * only if executed by sm_import_and_set_post_thumbnail().
 *
 * @since 2.9
 */
add_filter( 'wp_handle_upload', function ( $data ) {
	global $upload_dir_file_path, $doing_sm_upload;

	if ( true === $doing_sm_upload ) {
		$uploads = wp_get_upload_dir();
		$data    = array(
			'file' => $uploads['basedir'] . $upload_dir_file_path,
			'url'  => $uploads['baseurl'] . $upload_dir_file_path,
			'type' => $data['type'],
		);
	}

	return $data;
} );

/**
 * Gets sermon series image URL.
 *
 * @param int          $series_id  ID of the series.
 * @param string|array $image_size The image size to get. Either a valid image size or array with width and height in
 *                                 pixels.
 *
 * @return string|null Image URL; null if image not set or invalid/not set series ID.
 *
 * @since 2.11.0
 */
function get_sermon_series_image_url( $series_id = 0, $image_size = 'thumbnail' ) {
	if ( ! ( is_int( $series_id ) && 0 !== $series_id ) ) {
		return null;
	}

	$associations = sermon_image_plugin_get_associations();

	return ! empty( $associations[ $series_id ] ) ? wp_get_attachment_image_url( $associations[ $series_id ], $image_size ) : null;
}

/**
 * Gets dropdown options for a setting in "Debug" tab of Sermon Manager Settings.
 *
 * @return array
 *
 * @since 2.11.0
 */
function sm_debug_get_update_functions() {
	$options = array(
		'' => '---',
	);

	foreach ( \SM_Install::$db_updates as $version => $functions ) {
		foreach ( $functions as $function ) {
			if ( get_option( 'wp_sm_updater_' . $function . '_done', 0 ) ) {
				$name = '[AE]';
			} else {
				$name = '[NE]';
			}

			$name .= ' ' . $function . ' ';
			$name .= "($version)";

			$options[ $function ] = $name;
		}
	}

	/**
	 * Allows to modify the update functions list.
	 *
	 * @param array $options The update functions.
	 *
	 * @since 2.15.14
	 */
	return apply_filters( 'sm_get_update_functions', $options );
}

/**
 * Returns sermon/series image URL.
 *
 * @param bool         $fallback             If set to true, it will try to fallback to the secondary option. If series
 *                                           is primary, it will fallback to sermon image, else if sermon image is
 *                                           primary, it will fallback to series image - if they exist, of course.
 * @param string|array $image_size           The image size. Defaults to "post-thumbnail".
 * @param bool         $series_image_primary Set series image as primary.
 * @param WP_Post      $post                 The sermon object, unless it's defined via global $post.
 *
 * @return string Image URL or empty string.
 *
 * @since 2.12.0
 */
function get_sermon_image_url( $fallback = true, $image_size = 'post-thumbnail', $series_image_primary = false, $post = null ) {
	if ( null === $post ) {
		global $post;
	}

	/**
	 * Allows to filter the image size.
	 *
	 * @param string|array $image_size           The image size. Default: "post-thumbnail".
	 * @param bool         $fallback             If set to true, it will try to fallback to the secondary option. If series
	 *                                           is primary, it will fallback to sermon image, else if sermon image is
	 *                                           primary, it will fallback to series image - if they exist, of course.
	 * @param bool         $series_image_primary Set series image as primary.
	 * @param WP_Post      $post                 The sermon object.
	 *
	 * @since 2.13.0
	 */
	$image_size = apply_filters( 'get_sermon_image_url_image_size', $image_size, $fallback, $series_image_primary, $post );

	// Get the sermon image.
	$sermon_image = get_the_post_thumbnail_url( $post, $image_size ) ?: null;
	$series_image = null;

	// Get the series image.
	foreach (
		apply_filters( 'sermon-images-get-the-terms', '', array( // phpcs:ignore
			'post_id'    => $post->ID,
			'image_size' => $image_size,
		) ) as $term
	) {
		if ( isset( $term->image_id ) && 0 !== $term->image_id ) {
			$series_image = wp_get_attachment_image_url( $term->image_id, $image_size );

			if ( $series_image ) {
				break;
			}
		}
	}

	// Assign the image, based on function parameters.
	if ( $series_image_primary ) {
		$image = $series_image ?: ( $fallback ? $sermon_image : null );
	} else {
		$image = $sermon_image ?: ( $fallback ? $series_image : null );
	}

	// Use the image, or default image set in options, if nothing found.
	$image = $image ?: \SermonManager::getOption( 'default_image' );

	/**
	 * Allows to filter the image URL.
	 *
	 * @param string       $image                The image URL.
	 * @param bool         $fallback             If set to true, it will try to fallback to the secondary option. If series
	 *                                           is primary, it will fallback to sermon image, else if sermon image is
	 *                                           primary, it will fallback to series image - if they exist, of course.
	 * @param bool         $series_image_primary Set series image as primary.
	 * @param WP_Post      $post                 The sermon object.
	 * @param string|array $image_size           The image size. Default: "post-thumbnail".
	 *
	 * @since 2.13.0
	 * @since 2.15.2 - Added missing $image_size argument, and re-labelled $image to correct description.
	 */
	return apply_filters( 'get_sermon_image_url', $image, $fallback, $series_image_primary, $post, $image_size );
}

/**
 * Converts different video URL time formats to seconds. Examples:
 * "?t=2m12s" => 132
 * "?t=1h2s" => 3602
 * "#t=1m" => 60
 * "#t=25s" => 25
 * "?t=56" => 56
 * "?t=10:45" => 645
 * "?t=01:00:01" => 3601
 *
 * @param string $url The URL to the video file.
 *
 * @return false|int|null Seconds if successful, null if it couldn't decode the format, and false if the parameter is
 *                        not set.
 *
 * @since 2.12.3
 */
function wpfc_get_media_url_seconds( $url ) {
	$seconds = 0;

	if ( strpos( $url, '?t=' ) === false && strpos( $url, '#t=' ) === false && strpos( $url, '&t=' ) === false ) {
		return false;
	}

	// Try out hms format first (example: 1h2m3s).
	preg_match( '/t=(\d+h)?(\d+m)?(\d+s)+?/', $url, $hms );
	if ( ! empty( $hms ) ) {
		for ( $i = 1; $i <= 3; $i ++ ) {
			if ( '' === $hms[ $i ] ) {
				continue;
			}

			switch ( $i ) {
				case 1:
					$multiplication = HOUR_IN_SECONDS;
					break;
				case 2:
					$multiplication = MINUTE_IN_SECONDS;
					break;
				default:
					$multiplication = 1;
			}

			$seconds += intval( substr( $hms[ $i ], 0, - 1 ) ) * $multiplication;
		}

		return $seconds;
	}

	// Try out colon format (example: 25:12).
	preg_match( '/t=(\d+:)?(\d+:)?(\d+)+?/', $url, $colons );
	if ( ! empty( $colons ) ) {
		// Fix hours and minutes if needed.
		if ( empty( $colons[2] ) && ! empty( $colons[1] ) ) {
			$colons[2] = $colons[1];
			$colons[1] = '';
		}

		for ( $i = 1; $i <= 3; $i ++ ) {
			if ( '' === $colons[ $i ] ) {
				continue;
			}

			switch ( $i ) {
				case 1:
					$multiplication = HOUR_IN_SECONDS;
					$colons[ $i ]   = substr( $colons[ $i ], 0, - 1 );
					break;
				case 2:
					$multiplication = MINUTE_IN_SECONDS;
					$colons[ $i ]   = substr( $colons[ $i ], 0, - 1 );
					break;
				default:
					$multiplication = 1;
			}

			$seconds += intval( $colons[ $i ] ) * $multiplication;
		}

		return $seconds;
	}

	// Try out seconds (example: 12 (or 12s)).
	preg_match( '/t=(\d+)/', $url, $seconds );
	if ( ! empty( $seconds ) && ! empty( $seconds[1] ) ) {
		return intval( $seconds[1] );
	}

	return null;
}

/**
 * Gets previous latest sermon. I.e. orders sermons by meta and finds the previous one.
 *
 * @param WP_Post $post The current sermon, will use global if not defined.
 *
 * @return WP_Post|null The sermon if found, null otherwise.
 * @since 2.12.5
 *
 */
function sm_get_previous_sermon( $post = null ) {
	if ( null === $post ) {
		global $post;
	}

	if ( ! $post instanceof WP_Post || 'wpfc_sermon' !== $post->post_type ) {
		_doing_it_wrong( __FUNCTION__, '$post must be an instance of WP_Post.', '2.12.5' );
	}

	$the_post = get_previous_post();

	/**
	 * Allows to filter the return value.
	 *
	 * @param $the_post WP_Post|null The post if found.
	 */
	return apply_filters( 'sm_get_previous_sermon', empty( $the_post ) ? null : $the_post );
}

/**
 * Gets next latest sermon. I.e. orders sermons by meta and finds the next one.
 *
 * @param WP_Post $post The current sermon, will use global if not defined.
 *
 * @return WP_Post|null The sermon if found, null otherwise.
 * @since 2.12.5
 *
 */
function sm_get_next_sermon( $post = null ) {
	if ( null === $post ) {
		global $post;
	}

	if ( ! $post instanceof WP_Post || 'wpfc_sermon' !== $post->post_type ) {
		_doing_it_wrong( __FUNCTION__, '$post must be an instance of WP_Post.', '2.12.5' );
	}

	$the_post = get_next_post();

	/**
	 * Allows to filter the return value.
	 *
	 * @param $the_post WP_Post|null The post if found.
	 */
	return apply_filters( 'sm_get_next_sermon', empty( $the_post ) ? null : $the_post );
}

/**
 * Saves service type.
 *
 * Will be obsolete when we add new meta boxes code.
 *
 * @param int $post_ID The sermon ID.
 */
function sm_set_service_type( $post_ID ) {
	if ( isset( $_POST['wpfc_service_type'] ) ) {
		$term = get_term_by( 'id', sanitize_text_field($_POST['wpfc_service_type']), 'wpfc_service_type' );

		if ( $term ) {
			$service_type = $term->slug;
		}

		wp_set_object_terms( $post_ID, empty( $service_type ) ? null : $service_type, 'wpfc_service_type' );

		return;
	}

	$get  = isset( $_GET['tax_input'] ) && isset( $_GET['tax_input']['wpfc_service_type'] ) && $_GET['tax_input']['wpfc_service_type'];
	$post = isset( $_POST['tax_input'] ) && isset( $_POST['tax_input']['wpfc_service_type'] ) && $_POST['tax_input']['wpfc_service_type'];

	if ( $get || $post ) {
		$field = $get ? sanitize_text_field($_GET['tax_input']['wpfc_service_type']) : sanitize_text_field($_POST['tax_input']['wpfc_service_type']);
		$terms = explode( ',', $field );

		if ( $terms ) {
			$term = get_term_by( 'name', $terms[0], 'wpfc_service_type' );

			if ( $term ) {
				update_post_meta( $post_ID, 'wpfc_service_type', $term->term_id );
			}
		}
	}
}

add_action( 'save_post', 'sm_set_service_type' );

/**
 * Returns registered Sermon Manager's taxonomies.
 *
 * @return array Array of taxonomy names.
 *
 * @since 2.13.5
 */
function sm_get_taxonomies() {
	return get_object_taxonomies( 'wpfc_sermon' );
}

/**
 * Gets the taxonomy field.
 *
 * @param string|int|WP_Taxonomy $taxonomy   The taxonomy.
 * @param string                 $field_name The field to get.
 *
 * @return mixed The field value or null.
 *
 * @since 2.15.16
 */
function sm_get_taxonomy_field( $taxonomy, $field_name ) {
	$taxonomy = get_taxonomy( $taxonomy );

	if ( ! $taxonomy instanceof WP_Taxonomy ) {
		return null;
	}

	if ( isset( $taxonomy->$field_name ) ) {
		return $taxonomy->$field_name;
	}

	if ( isset( $taxonomy->labels->$field_name ) ) {
		return $taxonomy->labels->$field_name;
	}


	return null;
}
