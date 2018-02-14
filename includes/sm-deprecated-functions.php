<?php
/**
 * Place where functions come to die.
 *
 * @since 2.4.9
 */

defined( 'ABSPATH' ) or die; // exit if accessed directly

// deprecated
define( 'SM___FILE__', __FILE__ );
define( 'SERMON_MANAGER_PATH', SM_PATH );
define( 'SERMON_MANAGER_URL', SM_URL );
define( 'SERMON_MANAGER_VERSION', SM_VERSION );


/**
 * Outputs Sermon date. Wrapper for sm_the_date()
 *
 * @see        sm_the_date()
 *
 * @param string $d      PHP date format. Defaults to the date_format option if not specified.
 * @param string $before Optional. Output before the date.
 * @param string $after  Optional. Output after the date.
 *
 * @deprecated deprecated since 2.6, use sm_the_date() instead
 */
function wpfc_sermon_date( $d, $before = '', $after = '' ) {
	sm_the_date( $d, $before = '', $after = '' );
}

/**
 * Saves service type
 *
 * Will be obsolete when we add new meta boxes code
 *
 * @param int $post_ID
 */
function set_service_type( $post_ID ) {
	if ( isset( $_POST['wpfc_service_type'] ) ) {
		if ( $term = get_term_by( 'id', $_POST['wpfc_service_type'], 'wpfc_service_type' ) ) {
			$service_type = $term->slug;
		}

		wp_set_object_terms( $post_ID, empty( $service_type ) ? null : $service_type, 'wpfc_service_type' );
	}
}

add_action( 'save_post', 'set_service_type', 99 );

add_action( 'sermon_media', 'wpfc_sermon_media', 5 );
add_action( 'sermon_audio', 'wpfc_sermon_audio', 5 );
add_action( 'sermon_single', 'wpfc_sermon_single' );
add_action( 'sermon_excerpt', 'wpfc_sermon_excerpt' );

/**
 * @deprecated - see wpfc_sermon_media()
 */
function wpfc_sermon_files() {
	do_action( 'sermon_media' );
}

/**
 * @deprecated - see wpfc_sermon_single() & wpfc_sermon_single_v2()
 */
function render_wpfc_sermon_single() {
	do_action( 'sermon_single' );
}

/**
 * @deprecated - see wpfc_sermon_excerpt() & wpfc_sermon_excerpt_v2()
 */
function render_wpfc_sermon_excerpt() {
	do_action( 'sermon_excerpt' );
}