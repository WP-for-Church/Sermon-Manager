<?php
/**
 * Imports data from Series Engine into Sermon Manager.
 *
 * @package SM/Core/Admin/Importing
 */

defined( 'ABSPATH' ) or die;

/**
 * Used to import data from Series Engine
 *
 * @since 2.9
 */
class SM_Import_SE {
	/**
	 * Books that have been imported.
	 *
	 * @var array
	 */
	private $_imported_books;

	/**
	 * Speakers that have been imported.
	 *
	 * @var array
	 */
	private $_imported_speakers;

	/**
	 * Series that have been imported.
	 *
	 * @var array
	 */
	private $_imported_series;

	/**
	 * Topics that have been imported.
	 *
	 * @var array
	 */
	private $_imported_topics;

	/**
	 * Checks if Series Engine databases exist.
	 *
	 * @return bool
	 */
	public static function is_installed() {
		global $wpdb;

		return (bool) $wpdb->query( "SHOW TABLES LIKE '{$wpdb->prefix}se_messages'" );
	}

	/**
	 * Do the import.
	 */
	public function import() {
		if ( ! doing_action( 'admin_init' ) ) {
			add_action( 'admin_init', array( $this, __FUNCTION__ ) );

			return;
		}

		do_action( 'sm_import_before_se' );

		$this->_import_books();
		$this->_import_speakers();
		$this->_import_series();
		$this->_import_topics();
		$this->_import_messages();

		do_action( 'sm_import_after_se' );
	}

	/**
	 * Imports Bible Books.
	 */
	private function _import_books() {
		$used_books = $this->_get_used_books();

		foreach ( $used_books as $book ) {
			$term_data = term_exists( $book->book_name, 'wpfc_bible_book' );
			if ( ! $term_data ) {
				$term_data = wp_insert_term( $book->book_name, 'wpfc_bible_book' );
			}

			if ( ! $term_data instanceof WP_Error ) {
				$this->_imported_books[ $book->book_id ] = array(
					'new_id' => $term_data['term_id'],
				);
			}
		}
	}

	/**
	 * Gets the names of all Bible Books that were used in Series Engine.
	 *
	 * @return array
	 */
	private function _get_used_books() {
		global $wpdb;

		$used_books = array();
		$books      = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}se_books" );

		foreach ( $books as $book ) {
			if ( ! in_array( $book->book_name, $used_books ) ) {
				$used_books[] = $book;
			}
		}

		/**
		 * Filter books that will be imported.
		 *
		 * @var array $books list of book data that will be imported.
		 */
		return apply_filters( 'sm_import_se_books', $used_books );
	}

	/**
	 * Imports Speakers.
	 */
	private function _import_speakers() {
		global $wpdb;

		/**
		 * Filter speakers that will be imported.
		 *
		 * @var array $speakers Raw database data.
		 */
		$speakers = apply_filters( 'sm_import_se_speakers', $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}se_speakers" ) );

		foreach ( $speakers as $speaker ) {
			foreach (
				array(
					$speaker->first_name,
					$speaker->last_name,
					$speaker->first_name . ' ' . $speaker->last_name,
				) as $name
			) {
				$term_data = term_exists( $name, 'wpfc_preacher' );
				if ( $term_data ) {
					break;
				}
			}

			if ( empty( $term_data ) ) {
				$term_data = wp_insert_term( trim( $speaker->first_name . ' ' . $speaker->last_name ), 'wpfc_preacher' );
			}

			if ( ! $term_data instanceof WP_Error ) {
				$this->_imported_speakers[ $speaker->speaker_id ] = array(
					'new_id' => $term_data['term_id'],
				);
			}
		}
	}

	/**
	 * Imports Series.
	 */
	private function _import_series() {
		global $wpdb;

		/**
		 * Filter series that will be imported.
		 *
		 * @var array $series Raw database data.
		 */
		$series = apply_filters( 'sm_import_se_series', $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}se_series" ) );

		foreach ( $series as $item ) {
			$term_data = term_exists( $item->s_title, 'wpfc_sermon_series' );
			if ( ! $term_data ) {
				$term_data = wp_insert_term( $item->s_title, 'wpfc_sermon_series', array(
					'description' => apply_filters( 'sm_import_se_series_description', $item->s_description ?: '' ),
				) );
			}

			if ( ! $term_data instanceof WP_Error ) {
				// Set image.
				$attachment_id = sm_import_and_set_post_thumbnail( $item->thumbnail_url, 0 );
				if ( is_int( $attachment_id ) ) {
					$assigned_images                          = get_option( 'sermon_image_plugin' );
					$assigned_images[ $term_data['term_id'] ] = $attachment_id;
					update_option( 'sermon_image_plugin', $assigned_images );
				}

				$this->_imported_series[ $item->series_id ] = array(
					'new_id' => $term_data['term_id'],
				);
			}
		}
	}

	/**
	 * Imports Topics.
	 */
	private function _import_topics() {
		global $wpdb;

		/**
		 * Filter topics that will be imported.
		 *
		 * @var array $topics Raw database data.
		 */
		$topics = apply_filters( 'sm_import_se_topics', $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}se_topics" ) );

		foreach ( $topics as $topic ) {
			$term_data = term_exists( $topic->name, 'wpfc_sermon_topics' );
			if ( ! $term_data ) {
				$term_data = wp_insert_term( $topic->name, 'wpfc_sermon_topics' );
			}

			if ( ! $term_data instanceof WP_Error ) {
				$this->_imported_topics[ $topic->topic_id ] = array(
					'new_id' => $term_data['term_id'],
				);
			}
		}
	}

	/**
	 * Import messages.
	 */
	private function _import_messages() {
		global $wpdb;

		// Imported messages.
		$imported = get_option( '_sm_import_se_messages', array() );

		/**
		 * Filter messages that will be imported.
		 *
		 * @var array Raw database data.
		 */
		$messages = apply_filters( 'sm_import_se_messages', $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}se_messages" ) );

		/**
		 * Filter speaker association table that will be imported.
		 *
		 * @var array Raw database data.
		 */
		$messages_speakers = apply_filters( 'sm_import_se_speaker_association', $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}se_message_speaker_matches" ) );

		/**
		 * Filter topics association table that will be imported.
		 *
		 * @var array Raw database data.
		 */
		$messages_topics = apply_filters( 'sm_import_se_topics_association', $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}se_message_topic_matches" ) );

		/**
		 * Filter books association table that will be imported.
		 *
		 * @var array Raw database data.
		 */
		$messages_books = apply_filters( 'sm_import_se_books_association', $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}se_book_message_matches" ) );

		/**
		 * Filter series association table that will be imported.
		 *
		 * @var array Raw database data.
		 */
		$messages_series = apply_filters( 'sm_import_se_series_association', $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}se_series_message_matches" ) );

		// Start the import.
		foreach ( $messages as $message ) {
			$post_id  = $message->wp_post_id;
			$the_post = null;
			if ( null !== $post_id ) {
				$the_post = get_post( $message->wp_post_id );
			} else {
				$post_id = $message->message_id;
			}

			if ( ! isset( $imported[ $post_id ] ) ) {
				if ( null === $the_post ) {
					$id = wp_insert_post( apply_filters( 'sm_import_se_message', array(
						'post_date'      => $message->date . ' 12:00:00',
						'post_content'   => '%todo_render%',
						'post_title'     => $message->title,
						'post_type'      => 'wpfc_sermon',
						'post_status'    => 'publish',
						'comment_status' => SermonManager::getOption( 'import_disallow_comments' ) ? 'closed' : 'open',
					) ) );
				} else {
					$id = wp_insert_post( apply_filters( 'sm_import_se_message', array(
						'post_author'       => $the_post->post_author,
						'post_date'         => $the_post->post_date,
						'post_date_gmt'     => $the_post->post_date_gmt,
						'post_content'      => '%todo_render%',
						'post_title'        => $message->title,
						'post_status'       => $the_post->post_status,
						'post_type'         => 'wpfc_sermon',
						'post_modified'     => $the_post->post_modified,
						'post_modified_gmt' => $the_post->post_modified_gmt,
						'comment_status'    => SermonManager::getOption( 'import_disallow_comments' ) ? 'closed' : 'open',
					) ) );
				}

				if ( 0 === $id || $id instanceof WP_Error ) {
					// Silently skip if error.
					continue;
				}

				$imported[ $post_id ] = array(
					'new_id' => $id,
				);

				/**
				 * We write it after each insert in case that we get fatal error - we don't want to
				 * import messages twice, it would be a mess.
				 */
				update_option( '_sm_import_se_messages', $imported );
			} else {
				$id = $imported[ $post_id ]['new_id'];
			}

			// Set speakers.
			$keys = array_keys( array_map( function ( $element ) {
				return $element->message_id;
			}, $messages_speakers ), $message->message_id );
			if ( $keys ) {
				$terms = array();
				foreach ( $keys as $key ) {
					$terms[] = intval( $this->_imported_speakers[ intval( $messages_speakers[ $key ]->speaker_id ) ]['new_id'] );
				}

				if ( ! empty( $terms ) ) {
					wp_set_object_terms( $id, $terms, 'wpfc_preacher' );
				}
			}

			// Set books.
			$keys = array_keys( array_map( function ( $element ) {
				return $element->message_id;
			}, $messages_books ), $message->message_id );
			if ( $keys ) {
				$terms = array();
				foreach ( $keys as $key ) {
					$terms[] = intval( $this->_imported_books[ intval( $messages_books[ $key ]->book_id ) ]['new_id'] );
				}

				if ( ! empty( $terms ) ) {
					wp_set_object_terms( $id, $terms, 'wpfc_bible_book' );
				}
			}

			// Set topics.
			$keys = array_keys( array_keys( array_map( function ( $element ) {
				return $element->message_id;
			}, $messages_topics ), $message->message_id ) );
			if ( $keys ) {
				$terms = array();
				foreach ( $keys as $key ) {
					$terms[] = intval( $this->_imported_topics[ intval( $messages_topics[ $key ]->topic_id ) ]['new_id'] );
				}

				if ( ! empty( $terms ) ) {
					wp_set_object_terms( $id, $terms, 'wpfc_sermon_topics' );
				}
			}

			// Set series.
			$keys = array_keys( array_map( function ( $element ) {
				return $element->message_id;
			}, $messages_series ), $message->message_id );
			if ( $keys ) {
				$terms = array();
				foreach ( $keys as $key ) {
					$terms[] = intval( $this->_imported_series[ intval( $messages_series[ $key ]->series_id ) ]['new_id'] );
				}

				if ( ! empty( $terms ) ) {
					wp_set_object_terms( $id, $terms, 'wpfc_sermon_series' );
				}
			}

			// Set scripture.
			if ( ! empty( $message->focus_scripture ) ) {
				update_post_meta( $id, 'bible_passage', $message->focus_scripture );
			}

			// Set description.
			if ( ! empty( $message->description ) ) {
				update_post_meta( $id, 'sermon_description', $message->description );
			}

			// Set sermon date.
			if ( ! empty( $message->date ) && '0000-00-00' !== $message->date ) {
				update_post_meta( $id, 'sermon_date', strtotime( $message->date ) );
			} else {
				if ( null !== $the_post ) {
					update_post_meta( $id, 'sermon_date', strtotime( $the_post->post_date ) );
				} else {
					update_post_meta( $id, 'sermon_date', strtotime( $message->date ) );
				}

				update_post_meta( $id, 'sermon_date_auto', SermonManager::getOption( 'import_disable_auto_dates' ) ? '0' : '1' );
			}

			// Set audio length.
			if ( ! empty( $message->message_length ) ) {
				update_post_meta( $id, '_wpfc_sermon_duration', substr_count( $message->message_length, ':' ) === 1 ? '00:' . $message->message_length : $message->message_length );
			}

			// Set audio size (bytes).
			if ( ! empty( $message->audio_file_size ) ) {
				update_post_meta( $id, '_wpfc_sermon_size', $message->audio_file_size );
			}

			// Set audio file.
			if ( ! empty( $message->audio_url ) ) {
				update_post_meta( $id, 'sermon_audio', $message->audio_url );
			}

			// Set video url.
			if ( ! empty( $message->video_url ) ) {
				update_post_meta( $id, 'sermon_video_link', $message->video_url );
			}

			// Set video embed.
			if ( ! empty( $message->embed_code ) ) {
				update_post_meta( $id, 'sermon_video', $message->embed_code );
			}

			// Set views.
			if ( ! empty( $message->audio_count ) ) {
				update_post_meta( $id, 'Views', $message->audio_count );
			}

			// Update main file.
			if ( ! empty( $message->file_url ) ) {
				update_post_meta( $id, 'sermon_notes', $message->file_url );
			}

			// Set image.
			sm_import_and_set_post_thumbnail( $message->message_thumbnail, $id );
		}

		// Update term counts.
		foreach (
			array(
				'_imported_speakers' => 'wpfc_preacher',
				'_imported_books'    => 'wpfc_bible_book',
				'_imported_series'   => 'wpfc_sermon_series',
				'_imported_topics'   => 'wpfc_sermon_topics',
			) as $terms_array => $taxonomy
		) {
			$terms = array();

			if ( empty( $this->{$terms_array} ) ) {
				continue;
			}

			foreach ( $this->{$terms_array} as $item ) {
				$terms[] = intval( $item['new_id'] );
			}

			_update_generic_term_count( $terms, (object) array( 'name' => $taxonomy ) );
		}
	}
}
