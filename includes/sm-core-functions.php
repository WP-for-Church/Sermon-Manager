<?php
/**
 * Core Functions
 *
 * General core functions available on both the front-end and admin.
 */

defined( 'ABSPATH' ) or die;

/**
 * Retrieve the date on which the sermon was preached
 *
 * Unlike sm_the_date() this function will always return the date.
 * Modify output with the {@see 'sm_get_the_date'} filter.
 *
 * @param string $d
 * @param null   $post
 *
 * @return false|string
 */
function sm_get_the_date( $d = '', $post = null ) {
	if ( ! $the_date = SM_Dates::get( $d, $post ) ) {
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
	 * @since 2.6
	 *
	 * @param string      $the_date The formatted date.
	 * @param string      $d        PHP date format. Defaults to 'date_format' option
	 *                              if not specified.
	 * @param int|WP_Post $post     The post object or ID.
	 */
	return apply_filters( 'sm_get_the_date', $the_date, $d, $post );
}

/**
 * Display or Retrieve the date the current sermon was preached (once per date).
 *
 * Made to replace `wpfc_sermon_date()`
 *
 * HTML output can be filtered with 'sm_the_date'.
 * Date string output can be filtered with 'sm_get_the_date'.
 *
 * @since 2.6
 *
 * @param string $d      Optional. PHP date format. Defaults to the date_format option if not specified.
 * @param string $before Optional. Output before the date.
 * @param string $after  Optional. Output after the date.
 *
 * @return void
 */

function sm_the_date( $d = '', $before = '', $after = '' ) {
	$the_date = $before . sm_get_the_date( $d ) . $after;

	/**
	 * Filters the date a post was preached
	 *
	 * @since 2.6
	 *
	 * @param string $the_date The formatted date string.
	 * @param string $d        PHP date format. Defaults to 'date_format' option
	 *                         if not specified.
	 * @param string $before   HTML output before the date.
	 * @param string $after    HTML output after the date.
	 */
	echo apply_filters( 'the_date', $the_date, $d, $before, $after );
}

/**
 * Get permalink settings for Sermon Manager independent of the user locale.
 *
 * @since 2.7
 *
 * @return array
 */
function sm_get_permalink_structure() {
	if ( did_action( 'admin_init' ) ) {
		sm_switch_to_site_locale();
	}

	$permalinks = wp_parse_args( (array) get_option( 'sm_permalinks', array() ), array(
		'wpfc_preacher'          => '',
		'wpfc_sermon_series'     => '',
		'wpfc_sermon_topics'     => '',
		'wpfc_bible_book'        => '',
		'wpfc_service_type'      => '',
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
			if ( $name === 'wpfc_sermon' ) {
				continue;
			}

			$permalink = $permalinks['wpfc_sermon'] . '/' . $permalink;
		}
	}

	if ( did_action( 'admin_init' ) ) {
		sm_restore_locale();
	}

	return $permalinks;
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
 * @param  string $tip        Help tip text
 * @param  bool   $allow_html Allow sanitized HTML if true or escape
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
 * Get an image size.
 *
 * Variable is filtered by sm_get_image_size_{image_size}.
 *
 * @param array|string $image_size
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
		// reset variables
		$w = $h = $c = null;

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
