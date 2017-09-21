<?php
/**
 * Class used to hook into WordPress and make it use Sermon Manager dates, instead of core dates
 *
 * Can be disabled by `add_filter('sm_dates_wp', '__return_false');`
 *
 * @since 2.6
 */

class SM_Dates_WP extends SM_Dates {
	/**
	 * Filters WordPress internal function `get_the_date()`
	 *
	 * @param string      $the_date The formatted date.
	 * @param string      $d        PHP date format. Defaults to 'date_format' option
	 *                              if not specified.
	 * @param int|WP_Post $post     The post object or ID.
	 *
	 * @return string Preached date
	 */
	public static function get_the_date( $the_date = '', $d = '', $post = null ) {
		$sm_date = SM_Dates::get( $d, $post );

		return $sm_date === false ? $the_date : $sm_date;
	}

	/**
	 * Hooks into WordPress filtering functions
	 *
	 * @since 2.6
	 *
	 * @return void
	 */
	public static function hook() {
		/**
		 * Exit if disabled
		 */
		if ( apply_filters( 'sm_dates_wp', true ) === false ) {
			return;
		}

		add_filter( 'get_the_date', array( get_class( new SM_Dates_WP ), 'get_the_date' ), 10, 3 );
	}
}