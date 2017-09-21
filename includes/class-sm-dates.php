<?php

/**
 * Class used to get/set custom sermon dates
 *
 * It will try to be compatible with all Sermon Manager date implementations from previous versions
 *
 * @since 2.6
 */
class SM_Dates {
	/**
	 * Retrieve the date on which the sermon was preached.
	 *
	 * Modify output with the {@see 'sm_dates_get'} filter.
	 *
	 * @since 2.6
	 *
	 * @param string      $format                Optional. PHP date format defaults to the date_format option if not
	 *                                           specified. (or Unix timestamp if date_format option is not set)
	 * @param int|WP_Post $post                  Optional. Post ID or WP_Post object. Default current post.
	 * @param bool        $force_unix_sanitation Optional. Sanitation is done only if Sermon Manager is older than 2.6,
	 *                                           we are assuming that newer (2.6>) Sermon Manager versions will save
	 *                                           the date as Unix timestamp so sanitation is not required
	 *
	 * @return string|false Date when sermon was preached. False on failure
	 */
	public static function get( $format = '', $post = null, $force_unix_sanitation = false ) {
		// Reset the variable
		$has_time = $sanitized = false;

		// Get the sermon
		$post = get_post( $post );

		// If we are working on right post type
		if ( ! $post || $post->post_type !== 'wpfc_sermon' ) {
			return false;
		}

		// Check if date is set
		if ( ! $date = get_post_meta( $post->ID, 'sermon_date', true ) ) {
			return false;
		}

		// Save original date to a variable to allow later filtering
		$orig_date = $date;

		// If it's already an Unix timestamp, don't convert it
		if ( is_numeric( $date ) && $date = intval( trim( $date ) ) ) {
			$dt = DateTime::createFromFormat( 'U', $date );
			if ( $dt->format( 'H' ) !== '00' && $dt->format( 'i' ) !== '00' ) {
				$has_time = true;
			}
		} else {
			$date      = self::sanitize( $date );
			$sanitized = true;
			update_post_meta( $post->ID, 'sermon_date', $date );
		}

		// Check if we need to force it
		if ( $sanitized === false && $force_unix_sanitation === true ) {
			$date = self::sanitize( $date );
		}

		// Add the time if time is not set. The way this is done is that it checks for post time, takes it, converts to
		// seconds and adds to Unix timestamp. It's so we don't have 00:00 time set for all sermons with old date format.
		if ( ! $has_time ) {
			$dt = DateTime::createFromFormat( 'U', mysql2date( 'U', $post->post_date_gmt ) );

			$time = array(
				$dt->format( 'H' ),
				$dt->format( 'm' ),
				$dt->format( 's' )
			);

			// convert all to ints
			$time = array_map( 'intval', $time );

			list( $hours, $minutes, $seconds ) = $time;

			$date += $hours * HOUR_IN_SECONDS + $minutes * MINUTE_IN_SECONDS + $seconds;
		}

		// Check if format is set. If not, set to WP defined, or in super rare cases
		// when WP format is not defined, set it to Unix timestamp
		if ( empty( $format ) ) {
			$format = get_option( 'date_format', 'U' );
		}

		// Format it
		$date = date( $format, $date );

		/**
		 * Filters the date a post was preached
		 *
		 * @since 2.6
		 *
		 * @param string $date                  Modified and sanitized date
		 * @param string $orig_date             Original date from the database
		 * @param string $format                Date format
		 * @param bool   $force_unix_sanitation If the sanitation is forced
		 */
		return apply_filters( 'sm_dates_get', $date, $orig_date, $format, $force_unix_sanitation );
	}

	/**
	 * Tries to convert the textual date to Unix timestamp
	 *
	 * @param string $date
	 *
	 * @return int Unix timestamp
	 */
	protected static function sanitize( $date ) {
		$sanitized_date = strtotime( $date );

		/**
		 * Allow modification of the sanitized date
		 *
		 * @since 2.6
		 *
		 * @param int    $sanitized_date Unix timestamp
		 * @param string $date           The raw date, usually in "mm/dd/YYYY" format, but could be "dd/mm/YYYY" as well.
		 *                               Unfortunately, this function could return wrong time. But, we assume that all
		 *                               dates have been saved to the database in "mm/dd/YYYY" format.
		 *                               Warning: there could be other formats that we are not aware of yet.
		 */
		return apply_filters( 'sm_sanitize_date', $sanitized_date, $date );
	}

	public static function set( $date, $post ) {

	}
}