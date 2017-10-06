<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

/**
 * Functions used by database updater go here
 */

/**
 * Renames all "sermon_date_old" fields to "sermon_date" if "sermon_date" is not set
 */
function sm_update_28_revert_old_dates() {
	if ( get_option( 'wpfc_sm_dates_restore_done' ) ) {
		return;
	}

	global $wpdb;

	foreach ( $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_date FROM $wpdb->posts WHERE post_type = %s AND post_status NOT IN ('auto-draft', 'inherit')", 'wpfc_sermon' ) ) as $sermon ) {
		if ( get_post_meta( $sermon->ID, 'sermon_date', true ) === '' &&
		     $date = get_post_meta( $sermon->ID, 'sermon_date_old', true ) !== '' ) {
			update_post_meta( $sermon->ID, 'sermon_date', is_numeric( $date ) ?: strtotime( $date ) );
			delete_post_meta( $sermon->ID, 'sermon_date_old' );
		}
	}

	// clear all cached data
	wp_cache_flush();
}

/**
 * Final dates conversion for users who skipped converters in previous SM versions
 *
 * Basically, converts "sermon_date" value to Unix time if it's not numeric
 */
function sm_update_28_convert_dates_to_unix() {
	global $wpdb;

	// All sermons
	$sermons = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_date FROM $wpdb->posts WHERE post_type = %s AND post_status NOT IN ('auto-draft', 'inherit')", 'wpfc_sermon' ) );

	foreach ( $sermons as $sermon ) {
		if ( $date = get_post_meta( $sermon->ID, 'sermon_date', true ) ) {
			if ( ! is_numeric( $date ) ) {
				update_post_meta( $sermon->ID, 'sermon_date', strtotime( $date ) );
			}
		}
	}

	// clear all cached data
	wp_cache_flush();
}

/**
 * Fills out dates of sermons that don't have `sermon_date` set. Takes "Published" date for them and marks
 * them as auto-filled, so they get updated when Published date gets updated
 */
function sm_update_28_fill_out_empty_dates() {
	global $wpdb;

	// All sermons
	$sermons = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_date FROM $wpdb->posts WHERE post_type = %s AND post_status NOT IN ('auto-draft', 'inherit')", 'wpfc_sermon' ) );

	foreach ( $sermons as $sermon ) {
		if ( get_post_meta( $sermon->ID, 'sermon_date', true ) === '' ) {
			update_post_meta( $sermon->ID, 'sermon_date', strtotime( $sermon->post_date ) );
			update_post_meta( $sermon->ID, 'sermon_date_auto', '1' );
		}
	}

	// clear all cached data
	wp_cache_flush();
}

/**
 * For enabling sorting by series date
 *
 * @see SM_Dates_WP::update_series_date()
 */
function sm_update_28_fill_out_series_dates() {
	SM_Dates_WP::update_series_date();
}

/**
 * Renders sermon HTML and saves as "post_content", for better search compatibility
 */
function sm_update_28_save_sermon_render_into_post_content() {
	global $wpdb;

	// All sermons
	$sermons = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_date FROM $wpdb->posts WHERE post_type = %s", 'wpfc_sermon' ) );

	foreach ( $sermons as $sermon ) {
		wp_update_post( array(
			'ID'           => $sermon->ID,
			'post_content' => wpfc_sermon_single( true, $sermon )
		) );
	}

	// clear all cached data
	wp_cache_flush();
}

/**
 * <source> element was not included in 2.8 save. We allowed it and it will work now
 */
function sm_update_283_resave_sermons(){
	sm_update_28_save_sermon_render_into_post_content();
}
