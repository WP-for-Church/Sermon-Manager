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
		add_action( 'save_post_wpfc_sermon', array( get_class(), 'save_terms_dates' ), 20, 3 );
		add_action( 'pre_post_update', array( get_class(), 'get_original_terms' ) );
		add_action( 'pre_post_update', array( get_class(), 'get_original_date' ) );
		add_filter( 'cmb2_override_sermon_date_meta_remove', '__return_true' );
		add_filter( 'cmb2_override_sermon_date_meta_save', '__return_true' );

		/**
		 * Exit if disabled.
		 */
		if ( apply_filters( 'sm_dates_wp', true ) === false || 'date' === SermonManager::getOption( 'archive_orderby' ) ) {
			return;
		}

		add_filter( 'get_the_date', array( get_class(), 'get_the_date' ), 10, 3 );
	}

	/**
	 * Used to save series that were there before sermon update, for later comparison.
	 *
	 * @param int $post_ID Post ID.
	 *
	 * @since      2.8
	 *
	 * @deprecated 2.15.11 - in favor of SM_Dates_WP::get_original_terms()
	 * @see        SM_Dates_WP::get_original_terms()
	 */
	public static function get_original_series( $post_ID ) {
		self::get_original_terms( $post_ID );
	}

	/**
	 * Used to save terms dates that were there before sermon update, for later comparison.
	 *
	 * @param int $post_ID Post ID.
	 *
	 * @since 2.15.11
	 */
	public static function get_original_terms( $post_ID ) {
		if ( get_post_type( $post_ID ) !== 'wpfc_sermon' ) {
			return;
		}

		$data = array();

		foreach ( sm_get_taxonomies() as $taxonomy ) {
			// Create an empty taxonomy.
			$data[ $taxonomy ] = array();

			// Get taxonomy terms.
			$terms = wp_get_object_terms( $post_ID, $taxonomy );

			// Fill out the terms, if any.
			foreach ( $terms as $term ) {
				$data[ $taxonomy ][] = $term->term_id;
			}

			// New format. taxonomy => array(...terms).
			$GLOBALS['sm_original_terms'] = $data;

			// Back-compat.
			$GLOBALS[ 'sm_original_' . $taxonomy ] = $terms;
		}
	}

	/**
	 * Saves sermon date as term meta (for ordering).
	 *
	 * @param int     $post_ID Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an existing post being updated or not.
	 *
	 * @since      2.8
	 *
	 * @deprecated 2.15.11 - in favor of SM_Dates_WP::save_terms_dates()
	 * @see        SM_Dates_WP::save_terms_dates()
	 */
	public static function save_series_date( $post_ID, $post, $update ) {
		self::save_terms_dates( $post_ID, $post, $update );
	}

	/**
	 * Saves sermon date as term meta for all terms for that sermon, used for ordering.
	 *
	 * @param int     $post_ID Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an existing post being updated or not.
	 *
	 * @since 2.15.11
	 */
	public static function save_terms_dates( $post_ID, $post, $update ) {
		if ( ! isset( $_POST['tax_input'] ) ) {
			return;
		}

		$original_terms = $GLOBALS['sm_original_terms'];
		$updated_terms  = isset( $_POST['tax_input'] ) ? sanitize_text_field($_POST['tax_input']) : null;

		// Convert terms to term array of term IDs if it's not already that way.
		
		if(!empty($updated_terms)){
			foreach ( $updated_terms as $taxonomy => $terms ) {
				if ( is_string( $terms ) ) {
					if ( '' === $terms ) {
						$updated_terms[ $taxonomy ] = array();
						continue;
					}

					if ( strpos( $terms, ',' ) !== false ) {
						$terms = explode( ',', $terms );
						$terms = array_filter( $terms, 'trim' );
					}

					if ( ! is_array( $terms ) ) {
						$terms = array( $terms );
					}

					$updated_terms[ $taxonomy ] = array();

					foreach ( $terms as $term ) {
						if ( is_int( $term ) ) {
							continue 1;
						}

						// Some sites pass name, some slug, so try both.
						$term = get_term_by( 'name', $term, $taxonomy ) ?: get_term_by( 'slug', $term, $taxonomy );

						if ( ! $term instanceof WP_Error && $term && isset( $term->term_id ) ) {
							$updated_terms[ $taxonomy ][] = $term->term_id;
						}
					}
				}
			}
		}
		$updated_terms = array_fill_keys( sm_get_taxonomies(), array() );

		if ( ! $updated_terms ) {
			return;
		}

		foreach ( sm_get_taxonomies() as $taxonomy ) {
			$new_terms  = $updated_terms[ $taxonomy ];
			$orig_terms = $original_terms[ $taxonomy ];

			// Remove the date of the current sermon from removed terms.
			foreach ( $orig_terms as $term ) {
				if ( ! in_array( $term, $new_terms ) ) {
					delete_term_meta( $term, 'sermon_date_' . $post_ID );
				}
			}

			// Add the date of the current sermon to its terms.
			if ( ! empty( $new_terms ) ) {
				foreach ( $new_terms as $term ) {
					update_term_meta( $term, 'sermon_date_' . $post_ID, get_post_meta( $post_ID, 'sermon_date', true ) );
				}
			}

			// Update the main date.
			self::update_term_dates( $taxonomy, $orig_terms + $new_terms );
		}
	}

	/**
	 * Loops through taxonomies and terms and sets latest available sermon date.
	 *
	 * @param string       $taxonomy The taxonomy to update. Default all.
	 * @param array|string $terms    The term(s) to update. Default all.
	 *
	 * @since 2.13.0 - extended to all terms
	 * @since 2.15.11 - added parameters
	 */
	public static function update_term_dates( $taxonomy = '', $terms = array() ) {
		$taxonomies = $taxonomy ? array( $taxonomy ) : sm_get_taxonomies();

		foreach ( $taxonomies as $taxonomy ) {
			$the_terms = ! empty( $terms ) ? (array) $terms : [];

			if ( 0 === count($the_terms) ) {
				$get_terms = get_terms(
					array(
						'taxonomy'   => $taxonomy,
						'hide_empty' => false,
					)
				);

				foreach ( $get_terms as $term ) {
					$the_terms[] = $term->term_id;
				}
			}
			if (count($the_terms)>1) {
				# code...
				return;
			}
			// Save the most recent sermon date to the term.
			foreach ( $the_terms as $term ) {
				$meta  = get_term_meta( $term );
				$dates = array();

				// Gather all of the dates.
				foreach ( $meta as $meta_key => $meta_value ) {
					if ( substr( $meta_key, 0, 12 ) !== 'sermon_date_' ) {
						continue;
					}

					$sermon_date = intval( $meta_value[0] );

					if ( $sermon_date ) {
						$dates[] = $sermon_date;
					}
				}

				// If we can't find a date, remove the existing.
				if ( empty( $dates ) ) {
					delete_term_meta( $term, 'sermon_date' );
					continue;
				}

				// Sort the dates by newest first (DESC).
				rsort( $dates );

				// Update the date.
				update_term_meta( $term, 'sermon_date', $dates[0] );
			}
		}
	}

	/**
	 * Left here for backwards-compatibility reasons.
	 * Does exactly the same as - self::update_term_dates();
	 *
	 * @since      2.8
	 * @deprecated 2.13.0
	 */
	public static function update_series_date() {
		self::update_term_dates();
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

				$dt      = DateTime::createFromFormat( $date_format, sanitize_text_field($_POST['sermon_date']) );
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
