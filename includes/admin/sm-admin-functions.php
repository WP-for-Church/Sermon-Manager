<?php
defined( 'ABSPATH' ) or die;

/**
 * Get all Sermon Manager screen ids.
 *
 * @return array Screen IDs
 * @since 2.9
 */
function sm_get_screen_ids() {
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
}

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

	return $options;
}