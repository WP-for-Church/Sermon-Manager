<?php
/**
 * Hooks for WordPress date getters and setters.
 *
 * @package SM/Core/Dates
 */

defined( 'ABSPATH' ) or die;

/**
 * Class used to hook into WordPress and make it use Sermon Manager dates, instead of core dates.
 *
 * Can be disabled by `add_filter('sm_dates_wp', '__return_false');`.
 *
 * @since 2.6
 */
class SM_Dates_WP extends SM_Dates {
	/**
	 * Filters WordPress internal function `get_the_date()`.
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

		return false === $sm_date ? $the_date : $sm_date;
	}

	/**
	 * Hooks into WordPress filtering functions.
	 *
	 * @since 2.6
	 *
	 * @return void
	 */
	public static function hook() {
		add_action( 'save_post_wpfc_sermon', array( get_class(), 'maybe_update_date' ), 10, 3 );
		add_action( 'save_post_wpfc_sermon', array( get_class(), 'save_series_date' ), 20, 3 );
		add_action( 'save_post_wpfc_sermon', array( get_class(), 'update_series_date' ), 30 );
		add_action( 'pre_post_update', array( get_class(), 'get_original_series' ) );
		add_action( 'pre_post_update', array( get_class(), 'get_original_date' ) );
		add_filter( 'cmb2_override_sermon_date_meta_remove', '__return_true' );
		add_filter( 'cmb2_override_sermon_date_meta_save', '__return_true' );

		/**
		 * Exit if disabled.
		 */
		if ( apply_filters( 'sm_dates_wp', true ) === false ) {
			return;
		}

		add_filter( 'get_the_date', array( get_class(), 'get_the_date' ), 10, 3 );
	}

	/**
	 * Used to save series that were there before sermon update, for later comparison.
	 *
	 * @param int $post_ID Post ID.
	 *
	 * @since 2.8
	 */
	public static function get_original_series( $post_ID ) {
		if ( get_post_type( $post_ID ) === 'wpfc_sermon' ) {
			$GLOBALS['sm_original_series'] = wp_get_object_terms( $post_ID, 'wpfc_sermon_series' );
		}
	}

	/**
	 * Saves sermon date as term meta (for ordering).
	 *
	 * @param int     $post_ID Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an existing post being updated or not.
	 *
	 * @since 2.8
	 */
	public static function save_series_date( $post_ID, $post, $update ) {
		if ( ! isset( $_POST['tax_input'] ) ) {
			return;
		}

		$series      = $_POST['tax_input']['wpfc_sermon_series'];
		$orig_series = $GLOBALS['sm_original_series'];

		if ( $update ) {
			foreach ( $orig_series as $term ) {
				delete_term_meta( $term->term_id, 'sermon_date' );
			}
		}

		if ( ! empty( $series ) ) {
			foreach ( $orig_series as $term_id ) {
				update_term_meta( $term_id, 'sermon_date_' . $post_ID, get_post_meta( $post_ID, 'sermon_date', true ) );
			}
		}
	}

	/**
	 * Left here for backwards-compatibility reasons.
	 * Does exactly the same as - self::update_term_dates();
	 *
	 * @since 2.8
	 * @deprecated
	 */
	public static function update_series_date() {
		self::update_term_dates();
	}

	/**
	 * Loops through all terms and sets latest available sermon date.
	 *
	 * @since 2.13.0 - extended to all terms
	 */
	public static function update_term_dates() {
		foreach (
			get_terms( array(
				'taxonomy'   => array(
					'wpfc_sermon_series',
					'wpfc_preacher',
					'wpfc_sermon_topics',
					'wpfc_bible_book',
					'wpfc_service_type',
				),
				'hide_empty' => true,
			) ) as $term
		) {
			$term_meta = get_term_meta( $term->term_id );

			if ( empty( $term_meta['sermon_date'] ) ) {
				$dates = array();
				foreach ( $term_meta as $name => $value ) {
					if ( strpos( $name, 'sermon_date_' ) !== false ) {
						$dates[] = $value[0];
					}
				}

				if ( ! empty( $dates ) ) {
					arsort( $dates );
					$date = $dates[0];
				} else {
					$query = new WP_Query( array(
						'post_type'      => 'wpfc_sermon',
						'posts_per_page' => 1,
						'meta_key'       => 'sermon_date',
						'meta_value_num' => time(),
						'meta_compare'   => '<=',
						'orderby'        => 'meta_value_num',
						'tax_query'      => array(
							array(
								'taxonomy' => $term->taxonomy,
								'field'    => 'term_id',
								'terms'    => $term->term_id,
							),
						),
					) );
					if ( $query->have_posts() ) {
						$date = get_post_meta( $query->posts[0]->ID, 'sermon_date', true );
					} else {
						$date = 0;
					}
				}

				update_term_meta( $term->term_id, 'sermon_date', $date );
			}
		}
	}

	/**
	 * Used to save date that was there before sermon update, for later comparison.
	 *
	 * @param int $post_ID Post ID.
	 *
	 * @since 2.7
	 */
	public static function get_original_date( $post_ID ) {
		if ( get_post_type( $post_ID ) === 'wpfc_sermon' ) {
			$post                                  = get_post( $post_ID );
			$GLOBALS['sm_original_published_date'] = $post->post_date;
			$GLOBALS['sm_original_sermon_date']    = get_post_meta( $post_ID, 'sermon_date', true );
		}
	}

	/**
	 * Sets/updates date for posts if they are not user-defined.
	 *
	 * @param int     $post_ID Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an existing post being updated or not.
	 *
	 * @since 2.7
	 */
	public static function maybe_update_date( $post_ID, $post, $update ) {
		$update_date = false;
		$auto        = false;

		if ( $update ) {
			// Compare sermon date and if user changed it update sermon date and disable auto update.
			if ( ! empty( $_POST['sermon_date'] ) ) {
				switch ( \SermonManager::getOption( 'date_format' ) ) {
					case '0':
						$date_format = 'm/d/Y';
						break;
					case '1':
						$date_format = 'd/m/Y';
						break;
					case '2':
						$date_format = 'Y/m/d';
						break;
					case '3':
						$date_format = 'Y/d/m';
						break;
					default:
						$date_format = 'm/d/Y';
						break;
				}

				$dt      = DateTime::createFromFormat( $date_format, $_POST['sermon_date'] );
				$dt_post = DateTime::createFromFormat( 'U', mysql2date( 'U', $post->post_date ) );

				$time = array(
					$dt_post->format( 'H' ),
					$dt_post->format( 'i' ),
					$dt_post->format( 's' ),
				);

				// Convert all to ints.
				$time = array_map( 'intval', $time );

				list( $hours, $minutes, $seconds ) = $time;

				if ( $dt instanceof DateTime && $dt->format( 'U' ) != $GLOBALS['sm_original_sermon_date'] ) {
					$dt->setTime( $hours, $minutes, $seconds );

					update_post_meta( $post_ID, 'sermon_date', $dt->format( 'U' ) );
					update_post_meta( $post_ID, 'sermon_date_auto', 0 );
				}
			}

			// Compare published date and if user changed it update sermon date if auto update is set.
			if ( ! empty( $GLOBALS['sm_original_published_date'] ) ) {
				if ( $post->post_date !== $GLOBALS['sm_original_published_date'] && 1 == get_post_meta( $post_ID, 'sermon_date_auto', true ) ) {
					$update_date = true;
				}
			}
		}

		/*
		 * If sermon date is blank (not set on sermon create or removed later on update), mark
		 * this post for auto updating and update date now.
		 */
		if ( isset( $_POST['sermon_date'] ) && '' == $_POST['sermon_date'] ) {
			$update_date = true;
			$auto        = true;
		}

		// If marked for date updating.
		if ( $update_date ) {
			update_post_meta( $post_ID, 'sermon_date', mysql2date( 'U', $post->post_date ) );
		}

		// If we should set it for auto date updating.
		if ( $auto ) {
			update_post_meta( $post_ID, 'sermon_date_auto', '1' );
		}
	}
}
