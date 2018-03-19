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

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
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

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
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

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
}

/**
 * For enabling sorting by series date
 *
 * @see SM_Dates_WP::update_series_date()
 */
function sm_update_28_fill_out_series_dates() {
	SM_Dates_WP::update_series_date();

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
}

/**
 * Renders sermon text and saves as "post_content", for better search compatibility
 *
 * @since 2.11.0 updated to render text and not HTML
 */
function sm_update_28_save_sermon_render_into_post_content() {
	sm_update_211_render_content();

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
}

/**
 * We had a bug from 2.8 to 2.8.3, so we will do it again
 */
function sm_update_284_resave_sermons() {
	sm_update_28_save_sermon_render_into_post_content();

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
}

/**
 * There was a bug in function for 2.8, so we will do it again
 */
function sm_update_29_fill_out_series_dates() {
	sm_update_28_fill_out_series_dates();

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
}

/**
 * Settings storage has been changed in 2.9
 */
function sm_update_29_convert_settings() {
	$original_settings = get_option( 'wpfc_options', array() );

	foreach ( $original_settings as $key => $value ) {
		add_option( 'sermonmanager_' . $key, $value );
	}

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
}

/**
 * SB and SE import did not import dates correctly. This function imports them for those who did import
 */
function sm_update_293_fix_import_dates() {
	sm_update_28_fill_out_empty_dates();

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
}

/**
 * Removed Bibly so we will change option names
 */
function sm_update_210_update_options() {
	if ( is_bool( SermonManager::getOption( 'bibly' ) ) ) {
		add_option( 'sermonmanager_verse_popup', SermonManager::getOption( 'bibly' ) ? 'yes' : 'no' );
	}

	if ( $bible_version = SermonManager::getOption( 'bibly_version' ) ) {
		add_option( 'sermonmanager_verse_bible_version', $bible_version );
	}

	if ( is_bool( SermonManager::getOption( 'use_old_player' ) ) ) {
		add_option( 'sermonmanager_player', SermonManager::getOption( 'use_old_player' ) ? 'wordpress' : 'plyr' );
	}

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
}

/**
 * Re-renders all sermon content into database as text; for better compatibility with search engines, etc...
 */
function sm_update_211_render_content() {
	global $wpdb;

	// All sermons
	$sermons = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s", 'wpfc_sermon' ) );

	$sermon_manager = \SermonManager::get_instance();

	foreach ( $sermons as $sermon ) {
		$sermon_manager->render_sermon_into_content( $sermon->ID, get_post( $sermon->ID ), true );
	}

	// clear all cached data
	wp_cache_flush();

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
}

/**
 * Adds time alongside date in sermon date option
 */
function sm_update_211_update_date_time() {
	global $wpdb;

	// All sermons
	$sermons = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_date FROM $wpdb->posts WHERE post_type = %s", 'wpfc_sermon' ) );

	foreach ( $sermons as $sermon ) {
		if ( $sermon_date = get_post_meta( $sermon->ID, 'sermon_date', true ) ) {
			$dt      = DateTime::createFromFormat( 'U', $sermon_date );
			$dt_post = DateTime::createFromFormat( 'U', mysql2date( 'U', $sermon->post_date ) );

			$time = array(
				$dt_post->format( 'H' ),
				$dt_post->format( 'i' ),
				$dt_post->format( 's' )
			);

			// convert all to ints
			$time = array_map( 'intval', $time );

			list( $hours, $minutes, $seconds ) = $time;

			if ( $dt instanceof DateTime && $dt->format( 'U' ) != $GLOBALS['sm_original_sermon_date'] ) {
				$dt->setTime( $hours, $minutes, $seconds );

				update_post_meta( $sermon->ID, 'sermon_date', $dt->format( 'U' ) );
				update_post_meta( $sermon->ID, 'sermon_date_auto', 0 );
			}
		}
	}

	// clear all cached data
	wp_cache_flush();

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
}

/**
 * There was a bug that prevented preacher slug to be used as a permalink as well
 *
 * Fixed in 2.12.3
 */
function sm_update_2123_fix_preacher_permalink() {
	flush_rewrite_rules();

	// mark it as done, backup way
	update_option( 'wp_sm_updater_' . __FUNCTION__ . '_done', 1 );
}