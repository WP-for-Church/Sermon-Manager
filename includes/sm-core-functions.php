<?php
/**
 * Core Functions
 *
 * General core functions available on both the front-end and admin.
 */

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
			$the_date = mysql2date( get_option( 'date_format' ), $post->post_date );
		} else {
			$the_date = mysql2date( $d, $post->post_date );
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