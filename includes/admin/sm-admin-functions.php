<?php
defined( 'ABSPATH' ) or die;

/**
 * Get all Sermon Manager screen ids.
 *
 * @return array Screen IDs
 * @since 2.9
 */
function sm_get_screen_ids() {
	$sm_screen_id = 'wpfc_sermon';
	$screen_ids   = array(
		'toplevel_page_' . $sm_screen_id,
		$sm_screen_id . '_page_sm-sermons',
		$sm_screen_id . '_page_sm-settings',
		$sm_screen_id . '_page_sm-status',
		$sm_screen_id . '_page_sm-addons',
		'toplevel_page_sm-stats',
		'edit-wpfc_sermon',
		'wpfc_sermon',
	);

	return apply_filters( 'sm_screen_ids', $screen_ids );
}