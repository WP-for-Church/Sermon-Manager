<?php
/**
 * Class used to hook into WordPress and make it use Sermon Manager dates, instead of core dates
 *
 * Can be disabled by `add_action('sm_dates_wp', '__return_false');`
 *
 * @since 2.6
 */

class SM_Dates_WP extends SM_Dates {
	public static function get_the_date() {

	}

	public static function get_the_time() {

	}

	private function hook() {

	}
}