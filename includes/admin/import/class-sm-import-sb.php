<?php
defined( 'ABSPATH' ) or die;

/**
 * Used to import data from Sermon Browser
 *
 * @since 2.9
 */
class SM_Import_SB {
	/** @var array */
	private $_imported_books;

	/** @var array */
	private $_imported_preachers;

	/** @var array */
	private $_imported_series;

	/** @var array */
	private $_imported_service_types;

	/** @var array */
	private $_imported_tags;

	/**
	 * Checks if Sermon Browser databases exist
	 *
	 * @return bool
	 */
	public static function is_installed() {
		global $wpdb;

		return $wpdb->query( "SELECT id FROM {$wpdb->prefix}sb_sermons LIMIT 1 " ) !== false;
	}

	/**
	 * Do the import
	 */
	public function import() {
		if ( ! doing_action( 'admin_init' ) ) {
			add_action( 'admin_init', array( $this, __FUNCTION__ ) );

			return;
		}

		do_action( 'sm_import_before_sb' );

		$this->_import_books();
		$this->_import_preachers();
		$this->_import_series();
		$this->_import_service_types();
		$this->_import_sermon_tags();
		$this->_import_sermons();

		do_action( 'sm_import_after_sb' );
	}

	/**
	 * Imports Bible Books
	 */
	private function _import_books() {
		$used_books = $this->_get_used_books();

		foreach ( $used_books as $book ) {
			if ( ! $term_data = term_exists( $book->book_name, 'wpfc_bible_book' ) ) {
				$term_data = wp_insert_term( $book->book_name, 'wpfc_bible_book' );
			}

			$this->_imported_books[ $book->id ] = array(
				'new_id' => $term_data['term_id'],
			);
		}
	}

	/**
	 * Gets the names of all Bible Books that were used in Sermon Browser
	 *
	 * @return array
	 */
	private function _get_used_books() {
		global $wpdb;

		$used_books = array();
		$books      = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sb_books_sermons" );

		foreach ( $books as $book ) {
			foreach ( $used_books as $used_book ) {
				if ( $used_book->book_name === $book->book_name ) {
					continue 2;
				}
			}

			$used_books[] = $book;
		}

		/**
		 * Allows to filter books that will be imported
		 *
		 * @var array $used_books list of book names that will be imported
		 */
		return apply_filters( 'sm_import_sb_books', $used_books );
	}

	/**
	 * Imports Preachers
	 */
	private function _import_preachers() {
		global $wpdb;

		/**
		 * Filter preachers that will be imported
		 *
		 * @var array Raw database data
		 */
		$preachers = apply_filters( 'sm_import_sb_preachers', $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sb_preachers" ) );

		foreach ( $preachers as $preacher ) {
			if ( ! $term_data = term_exists( $preacher->name, 'wpfc_preacher' ) ) {
				$term_data = wp_insert_term( $preacher->name, 'wpfc_preacher', array(
					'desc' => apply_filters( 'sm_import_sb_preacher_description', $preacher->description ?: '' )
				) );
			}

			if ( $preacher->image !== '' ) {
				// Set image
				$media         = wp_get_upload_dir();
				$attachment_id = sm_import_and_set_post_thumbnail( $media['baseurl'] . '/sermons/images/' . $preacher->image, 0 );
				if ( is_int( $attachment_id ) ) {
					$assigned_images                          = get_option( 'sermon_image_plugin' );
					$assigned_images[ $term_data['term_id'] ] = $attachment_id;
					update_option( 'sermon_image_plugin', $assigned_images );
				}
			}

			$this->_imported_preachers[ $preacher->id ] = array(
				'new_id' => $term_data['term_id'],
			);
		}
	}

	/**
	 * Imports Series
	 */
	private function _import_series() {
		global $wpdb;

		/**
		 * Filter series that will be imported
		 *
		 * @var array Raw database data
		 */
		$series = apply_filters( 'sm_import_sb_series', $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sb_series" ) );

		foreach ( $series as $item ) {
			if ( trim( $item->name ) === '' ) {
				continue;
			}

			if ( ! $term_data = term_exists( $item->name, 'wpfc_sermon_series' ) ) {
				$term_data = wp_insert_term( $item->name, 'wpfc_sermon_series' );
			}

			$this->_imported_series[ $item->id ] = array(
				'new_id' => $term_data['term_id'],
			);
		}
	}

	/**
	 * Imports Service Types
	 */
	private function _import_service_types() {
		global $wpdb;

		/**
		 * Filter service types that will be imported
		 *
		 * @var array Raw database data
		 */
		$services = apply_filters( 'sm_import_sb_service_types', $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sb_services" ) );

		foreach ( $services as $service ) {
			if ( ! $term_data = term_exists( $service->name, 'wpfc_sermon_series' ) ) {
				$term_data = wp_insert_term( $service->name, 'wpfc_sermon_series' );
			}

			$this->_imported_service_types[ $service->id ] = array(
				'new_id' => $term_data['term_id'],
			);
		}
	}

	/**
	 * Sermon tags are not working in SB, so we can't know how to import them
	 */
	private function _import_sermon_tags() {
		return null;
	}

	/**
	 * Imports Sermons
	 */
	private function _import_sermons() {
		global $wpdb;

		// Imported sermons
		$imported = get_option( '_sm_import_sb_messages', array() );

		// media upload directory
		$media = wp_get_upload_dir();

		/**
		 * Filter sermons that will be imported
		 *
		 * @var array $sermons Raw database data
		 */
		$sermons = apply_filters( 'sm_import_sb_messages', $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sb_sermons" ) );


		foreach ( $sermons as $sermon ) {
			if ( ! isset( $imported[ $sermon->id ] ) ) {
				$id = wp_insert_post( apply_filters( 'sm_import_sb_message', array(
					'post_date'    => $sermon->datetime,
					'post_content' => '%todo_render%',
					'post_title'   => $sermon->title,
					'post_type'    => 'wpfc_sermon',
					'post_status'  => 'publish',
				) ) );

				if ( $id === 0 ) {
					// silently skip if error
					continue;
				}

				$imported[ $sermon->id ] = array(
					'new_id' => $id
				);

				/**
				 * we write it after each insert in case that we get fatal error - we don't want to
				 * import sermons twice, it would be a mess
				 */
				update_option( '_sm_import_sb_messages', $imported );
			} else {
				$id = $imported[ $sermon->id ]['new_id'];
			}

			/**
			 * Filter stuff that will be imported
			 *
			 * @var array $stuff Raw database data
			 */
			$stuff = apply_filters( 'sm_import_sb_message_stuff', $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sb_stuff WHERE `sermon_id` = '{$sermon->id}'" ) );

			// set files
			update_post_meta( $id, 'sm_files', $stuff );

			// set mp3
			foreach ( $stuff as $item ) {
				$url = $item->name;

				if ( in_array( pathinfo( $url, PATHINFO_EXTENSION ), array( 'mp3', 'wav', 'ogg' ) ) ) {
					if ( parse_url( $url, PHP_URL_SCHEME ) === null ) {
						$url = $media['baseurl'] . '/media/audio/' . rawurlencode( $url );
					}

					update_post_meta( $id, 'sermon_audio', $url );
					break;
				}
			}

			// set speaker
			wp_set_object_terms( $id, intval( $this->_imported_preachers[ intval( $sermon->preacher_id ) ]['new_id'] ), 'wpfc_preacher' );

			// set service type
			wp_set_object_terms( $id, intval( $this->_imported_service_types[ intval( $sermon->service_id ) ]['new_id'] ), 'wpfc_service_type' );

			// set series
			wp_set_object_terms( $id, intval( $this->_imported_series[ intval( $sermon->series_id ) ]['new_id'] ), 'wpfc_sermon_series' );

			// set description
			update_post_meta( $id, 'sermon_description', $sermon->description );

			// set passage
			update_post_meta( $id, 'bible_passages_start', $sermon->start );
			update_post_meta( $id, 'bible_passages_end', $sermon->end );

			// set date
			update_post_meta( $id, 'sermon_date', strtotime( $sermon->datetime ) );
			update_post_meta( $id, 'sermon_date_auto', '1' );
		}

		// update term counts
		foreach (
			array(
				'_imported_preachers'     => 'wpfc_preacher',
				'_imported_service_types' => 'wpfc_service_type',
				'_imported_series'        => 'wpfc_sermon_series',
				'_imported_books'         => 'wpfc_bible_book',
			) as $terms_array => $taxonomy
		) {
			$terms = array();

			foreach ( $this->{$terms_array} as $item ) {
				$terms[] = intval( $item['new_id'] );
			}

			_update_generic_term_count( $terms, (object) array( 'name' => $taxonomy ) );
		}
	}
}
