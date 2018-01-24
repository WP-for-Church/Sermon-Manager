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
