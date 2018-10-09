<?php
/**
 * Imports data from Sermon Browser into Sermon Manager.
 *
 * @package SM/Core/Admin/Importing
 */

defined( 'ABSPATH' ) or die;

/**
 * Used to import data from Sermon Browser
 *
 * @since 2.9
 */
class SM_Import_SB {

	/**
	 * If import debug is enabled.
	 *
	 * @var bool
	 */
	public $is_debug = false;

	/**
	 * Debug log.
	 *
	 * @var string
	 */
	public $debug_data = '';

	/**
	 * Time when importing started, for calculating elapsed seconds.
	 *
	 * @var int
	 */
	public $start_time = 0;

	/**
	 * Books that have been imported.
	 *
	 * @var array
	 */
	private $_imported_books;

	/**
	 * Preachers that have been imported.
	 *
	 * @var array
	 */
	private $_imported_preachers;

	/**
	 * Series that have been imported.
	 *
	 * @var array
	 */
	private $_imported_series;

	/**
	 * Service Types that have been imported.
	 *
	 * @var array
	 */
	private $_imported_service_types;

	/**
	 * SM_Import_SB constructor.
	 */
	public function __construct() {
		$this->is_debug   = ! ! \SermonManager::getOption( 'debug_import' );
		$this->start_time = microtime( true );
	}

	/**
	 * Checks if Sermon Browser databases exist
	 *
	 * @return bool
	 */
	public static function is_installed() {
		global $wpdb;

		return (bool) $wpdb->query( "SHOW TABLES LIKE '{$wpdb->prefix}sb_sermons'" );
	}

	/**
	 * Update latest importing log.
	 */
	public function __destruct() {
		update_option( 'sm_last_import_info', $this->debug_data );
	}

	/**
	 * Do the import.
	 */
	public function import() {
		$this->log( 'Init info:' . PHP_EOL . 'Sermon Manager ' . SM_VERSION . PHP_EOL . 'Release Date: ' . date( 'Y-m-d', filemtime( SM_PLUGIN_FILE ) ), 255 );
		if ( ! doing_action( 'admin_init' ) ) {
			$this->log( 'Scheduling for `admin_init` action.', 0 );
			add_action( 'admin_init', array( $this, __FUNCTION__ ) );

			return;
		}

		$this->log( 'Doing `sm_import_before_sb` action.', 0 );
		do_action( 'sm_import_before_sb' );
		$this->log( 'Done.', 254 );

		$this->log( 'Starting book import.', 0 );
		$this->_import_books();
		$this->log( 'Finished book import.' );
		$this->log( 'Starting preachers import.', 0 );
		$this->_import_preachers();
		$this->log( 'Finished preachers import.' );
		$this->log( 'Starting series import.', 0 );
		$this->_import_series();
		$this->log( 'Finished series import.' );
		$this->log( 'Starting service type import.', 0 );
		$this->_import_service_types();
		$this->log( 'Finished service type import.' );
		$this->log( 'Starting sermon tags import.', 0 );
		$this->_import_sermon_tags();
		$this->log( 'Finished sermon tags import.' );
		$this->log( 'Starting sermons import.', 0 );
		$this->_import_sermons();
		$this->log( 'Finished sermons import.' );

		$this->log( 'Doing `sm_import_after_sb` action.', 0 );
		do_action( 'sm_import_after_sb' );
		$this->log( 'Done.', 254 );
	}

	/**
	 * Logs a message to show in debug.
	 *
	 * @param string $message  The message.
	 * @param int    $severity Message severity.
	 * @param bool   $no_time  To hide time or not.
	 *
	 * @since 2.11.0
	 */
	public function log( $message = '', $severity = 254, $no_time = false ) {
		$diff = microtime( true ) - $this->start_time;
		$sec  = sprintf( '%0' . ( 4 - strlen( intval( $diff ) ) ) . 'd', intval( $diff ) );
		$time = $sec . str_replace( '0.', '.', sprintf( '%.3f', $diff - intval( $diff ) ) );
		$line = '';

		if ( ! $no_time ) {
			$line .= "[${time}]";
		}

		switch ( $severity ) {
			case 0:
				$line .= ' (II)';
				break;
			case 1:
				$line .= ' (EE)';
				break;
			case 2:
				$line .= ' (WW)';
				break;
			case 253:
				$line .= '   ';
				break;
			case 254:
				$line .= '     ';
				break;
			case 255:
				$line .= '';
		}

		$this->debug_data .= $line . ' ' . $message . PHP_EOL;
	}

	/**
	 * Imports Bible Books.
	 */
	private function _import_books() {
		$used_books = $this->_get_used_books();

		foreach ( $used_books as $book ) {
			$term_data = term_exists( $book->book_name, 'wpfc_bible_book' );
			if ( $term_data ) {
				$this->log( 'Term "' . $book->book_name . '" already exists. (ID: ' . $term_data['term_id'] . ')' );
			} else {
				$term_data = wp_insert_term( $book->book_name, 'wpfc_bible_book' );
				if ( ! $term_data instanceof WP_Error ) {
					$this->log( 'Term "' . $book->book_name . '" imported. (ID: ' . $term_data['term_id'] . ')' );
				} else {
					$this->log( 'Term "' . $book->book_name . '" <strong>not</strong> imported. (' . $term_data->get_error_code() . ': ' . $term_data->get_error_message() . ')' );
					continue;
				}
			}

			$this->_imported_books[ $book->id ] = array(
				'new_id' => $term_data['term_id'],
			);
		}
	}

	/**
	 * Gets the names of all Bible Books that were used in Sermon Browser.
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
		 * Allows to filter books that will be imported.
		 *
		 * @var array $used_books list of book names that will be imported.
		 */
		return apply_filters( 'sm_import_sb_books', $used_books );
	}

	/**
	 * Imports Preachers.
	 */
	private function _import_preachers() {
		global $wpdb;

		/**
		 * Filter preachers that will be imported.
		 *
		 * @var array Raw database data
		 */
		$preachers = apply_filters( 'sm_import_sb_preachers', $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sb_preachers" ) );

		foreach ( $preachers as $preacher ) {
			$term_data = term_exists( $preacher->name, 'wpfc_preacher' );
			if ( $term_data ) {
				$this->log( 'Term "' . $preacher->name . '" already exists. (ID: ' . $term_data['term_id'] . ')' );
			} else {
				$term_data = wp_insert_term( $preacher->name, 'wpfc_preacher', array(
					'desc' => apply_filters( 'sm_import_sb_preacher_description', $preacher->description ?: '' ),
				) );
				if ( ! $term_data instanceof WP_Error ) {
					$this->log( 'Term "' . $preacher->name . '" imported. (ID: ' . $term_data['term_id'] . ')' );
				} else {
					$this->log( 'Term "' . $preacher->name . '" <strong>not</strong> imported. (' . $term_data->get_error_code() . ': ' . $term_data->get_error_message() . ')' );
					continue;
				}
			}

			if ( '' !== $preacher->image ) {
				// Set image.
				$media = wp_get_upload_dir();

				if ( file_exists( $media['basedir'] . '/sermons/images/' . $preacher->image ) ) {
					$attachment_id = sm_import_and_set_post_thumbnail( $media['baseurl'] . '/sermons/images/' . $preacher->image, 0 );
					if ( is_int( $attachment_id ) ) {
						$assigned_images                          = get_option( 'sermon_image_plugin' );
						$assigned_images[ $term_data['term_id'] ] = $attachment_id;
						update_option( 'sermon_image_plugin', $assigned_images );
					}
				}
			}

			$this->_imported_preachers[ $preacher->id ] = array(
				'new_id' => $term_data['term_id'],
			);
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
		 * @var array Raw database data
		 */
		$series = apply_filters( 'sm_import_sb_series', $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sb_series" ) );

		foreach ( $series as $item ) {
			if ( trim( $item->name ) === '' ) {
				continue;
			}

			$term_data = term_exists( $item->name, 'wpfc_sermon_series' );
			if ( $term_data ) {
				$this->log( 'Term "' . $item->name . '" already exists. (ID: ' . $term_data['term_id'] . ')' );
			} else {
				$term_data = wp_insert_term( $item->name, 'wpfc_sermon_series' );
				if ( ! $term_data instanceof WP_Error ) {
					$this->log( 'Term "' . $item->name . '" imported. (ID: ' . $term_data['term_id'] . ')' );
				} else {
					$this->log( 'Term "' . $item->name . '" <strong>not</strong> imported. (' . $term_data->get_error_code() . ': ' . $term_data->get_error_message() . ')' );
					continue;
				}
			}

			$this->_imported_series[ $item->id ] = array(
				'new_id' => $term_data['term_id'],
			);
		}
	}

	/**
	 * Imports Service Types.
	 */
	private function _import_service_types() {
		global $wpdb;

		/**
		 * Filter service types that will be imported.
		 *
		 * @var array Raw database data.
		 */
		$services = apply_filters( 'sm_import_sb_service_types', $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sb_services" ) );

		foreach ( $services as $service ) {
			$term_data = term_exists( $service->name, 'wpfc_service_type' );
			if ( $term_data ) {
				$this->log( 'Term "' . $service->name . '" already exists. (ID: ' . $term_data['term_id'] . ')' );
			} else {
				$term_data = wp_insert_term( $service->name, 'wpfc_service_type' );
				if ( ! $term_data instanceof WP_Error ) {
					$this->log( 'Term "' . $service->name . '" imported. (ID: ' . $term_data['term_id'] . ')' );
				} else {
					$this->log( 'Term "' . $service->name . '" <strong>not</strong> imported. (' . $term_data->get_error_code() . ': ' . $term_data->get_error_message() . ')' );
					continue;
				}
			}

			$this->_imported_service_types[ $service->id ] = array(
				'new_id' => $term_data['term_id'],
			);
		}
	}

	/**
	 * Sermon tags are not working in SB, so we can't know how to import them.
	 */
	private function _import_sermon_tags() {
		$this->log( 'Not implemented.', 2 );

		return null;
	}

	/**
	 * Imports Sermons.
	 */
	private function _import_sermons() {
		global $wpdb;

		// Imported sermons.
		$imported = get_option( '_sm_import_sb_messages', array() );

		// SB options.
		$options = get_option( 'sermonbrowser_options', array(
			'upload_dir' => 'wp-content/uploads/sermons/',
		) );
		$options = is_array( $options ) ? $options : unserialize( base64_decode( $options ) );

		if ( SM_OB_ENABLED ) {
			ob_start();
			print_r( $options );

			$this->log( 'Sermon Browser plugin options: <a onclick="jQuery(\'#sb-options\').toggle();" style="cursor:pointer;">Show data</a><div id="sb-options" style="background: #f1f1f1; padding: .5rem; border: 1px solid #ccc;display:none">' . ob_get_clean() . '</div>', 0 );
		}

		/**
		 * Filter sermons that will be imported.
		 *
		 * @var array $sermons Raw database data.
		 */
		$sermons = apply_filters( 'sm_import_sb_messages', $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}sb_sermons" ) );

		if ( SM_OB_ENABLED ) {
			ob_start();
			print_r( $sermons );

			$this->log( 'Raw sermons data: <a onclick="jQuery(\'#sermon-data\').toggle();" style="cursor:pointer;">Show data</a><div id="sermon-data" style="background: #f1f1f1; padding: .5rem; border: 1px solid #ccc;display:none">' . ob_get_clean() . '</div>', 0 );
		}

		foreach ( $sermons as $sermon ) {
			if ( ! isset( $imported[ $sermon->id ] ) ) {
				import: // phpcs:ignore
				$id = wp_insert_post( apply_filters( 'sm_import_sb_message', array( // phpcs:ignore
					'post_date'      => $sermon->datetime,
					'post_content'   => '%todo_render%',
					'post_title'     => $sermon->title,
					'post_type'      => 'wpfc_sermon',
					'post_status'    => 'publish',
					'comment_status' => SermonManager::getOption( 'import_disallow_comments' ) ? 'closed' : 'open',
				) ) );

				$imported[ $sermon->id ] = array(
					'new_id' => $id,
				);

				if ( 0 === $id || $id instanceof WP_Error ) {
					// Skip if error.
					$this->log( 'Sermon "' . $sermon->title . '" could not be imported. (error data: ' . serialize( $id ) . ')', 2 );
					continue;
				} else {
					$this->log( ' • Sermon "' . $sermon->title . '" imported. (ID: ' . $imported[ $sermon->id ]['new_id'] . ')', 255 );
				}

				/**
				 * We write it after each insert in case that we get fatal error later.
				 * We don't want to import sermons twice, it would be a mess.
				 */
				update_option( '_sm_import_sb_messages', $imported );
			} else {
				if ( ! post_exists( $sermon->title ) ) {
					goto import; // phpcs:ignore
				} else {
					$this->log( ' • Sermon "' . $sermon->title . '" is already imported. (ID: ' . $imported[ $sermon->id ]['new_id'] . ')', 255 );
					$id = $imported[ $sermon->id ]['new_id'];
				}
			}

			/**
			 * Filter stuff that will be imported.
			 *
			 * @var array $stuff Raw database data.
			 */
			$stuff = apply_filters( 'sm_import_sb_message_stuff', $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}sb_stuff WHERE `sermon_id` = %d", $sermon->id ) ) );

			if ( SM_OB_ENABLED ) {
				ob_start();
				print_r( $stuff );

				$this->log( 'Raw files data: <a onclick="jQuery(\'#files-data-' . $id . '\').toggle();" style="cursor:pointer;">Show data</a><div id="files-data-' . $id . '" style="background: #f1f1f1; padding: .5rem; border: 1px solid #ccc;display:none">' . ob_get_clean() . '</div>', 253 );
			}

			// Set files.
			update_post_meta( $id, 'sm_files', $stuff );

			// Set mp3.
			foreach ( $stuff as $item ) {
				$url = $item->name;

				if ( 'file' === $item->type || 'url' === $item->type ) {
					if ( parse_url( $url, PHP_URL_SCHEME ) === null ) {
						$url = site_url( ( ! empty( $options['upload_dir'] ) ? $options['upload_dir'] : 'wp-content/uploads/sermons/' ) . rawurlencode( $url ) );
						$this->log( 'File URL is local, created a full URL. ("' . $url . '")', 253 );
					}

					switch ( pathinfo( $url, PATHINFO_EXTENSION ) ) {
						case 'mp3':
						case 'wav':
						case 'ogg':
						case 'wma':
							$this->log( 'Found an audio file! ("' . $url . '")', 253 );

							update_post_meta( $id, 'sermon_audio', $url );
							break;
						case 'mp4':
						case 'avi':
						case 'wmv':
						case 'mov':
						case 'divx':
							$this->log( 'Found an video file! ("' . $url . '")', 253 );

							update_post_meta( $id, 'sermon_video_link', $url );
							break;
						case 'doc':
						case 'docx':
						case 'rtf':
						case 'txt':
							$this->log( 'Found sermon notes! ("' . $url . '")', 253 );

							update_post_meta( $id, 'sermon_notes', $url );
							break;
						case 'ppt':
						case 'pptx':
						case 'pdf':
						case 'xls':
						case 'xlsx':
							$this->log( 'Found sermon bulletin! ("' . $url . '")', 253 );

							update_post_meta( $id, 'sermon_bulletin', $url );
							break;
						default:
							if ( strpos( $url, 'vimeo.com' ) !== false ) {
								$this->log( 'Found an video URL! ("' . $url . '")', 253 );

								update_post_meta( $id, 'sermon_video_link', $url );
								break;
							}
					}
				} elseif ( 'code' === $item->type ) {
					$this->log( 'Found video embed!', 253 );

					update_post_meta( $id, 'sermon_video', base64_decode( $item->name ) );
				}
			}

			if ( ! empty( $this->_imported_preachers[ intval( $sermon->preacher_id ) ] ) ) {
				// Set speaker.
				wp_set_object_terms( $id, intval( $this->_imported_preachers[ intval( $sermon->preacher_id ) ]['new_id'] ), 'wpfc_preacher' );
				$this->log( 'Assigned preacher with ID ' . intval( $this->_imported_preachers[ intval( $sermon->preacher_id ) ]['new_id'] ), 253 );
			}

			if ( ! empty( $this->_imported_service_types[ intval( $sermon->service_id ) ] ) ) {
				// Set service type.
				wp_set_object_terms( $id, intval( $this->_imported_service_types[ intval( $sermon->service_id ) ]['new_id'] ), 'wpfc_service_type' );
				update_post_meta( $id, 'wpfc_service_type', intval( $this->_imported_service_types[ intval( $sermon->service_id ) ]['new_id'] ) );
				$this->log( 'Assigned service type with ID ' . intval( $this->_imported_service_types[ intval( $sermon->service_id ) ]['new_id'] ), 253 );
			}

			if ( ! empty( $this->_imported_series[ intval( $sermon->series_id ) ] ) ) {
				// Set series.
				wp_set_object_terms( $id, intval( $this->_imported_series[ intval( $sermon->series_id ) ]['new_id'] ), 'wpfc_sermon_series' );
				$this->log( 'Assigned series with ID ' . intval( $this->_imported_series[ intval( $sermon->series_id ) ]['new_id'] ), 253 );
			}

			// Set description.
			update_post_meta( $id, 'sermon_description', $sermon->description );

			// Set passage.
			update_post_meta( $id, 'bible_passages_start', $sermon->start );
			update_post_meta( $id, 'bible_passages_end', $sermon->end );

			// Set date.
			update_post_meta( $id, 'sermon_date', strtotime( $sermon->datetime ) );
			$this->log( 'Set sermon_date to ' . date( 'c', strtotime( $sermon->datetime ) ), 253 );
			update_post_meta( $id, 'sermon_date_auto', SermonManager::getOption( 'import_disable_auto_dates' ) ? '0' : '1' );

			// Set views.
			/* @noinspection SqlResolve */
			update_post_meta( $id, 'Views', $wpdb->get_var( $wpdb->prepare( "SELECT SUM(`count`) FROM {$wpdb->prefix}sb_stuff WHERE `sermon_id` = %d", $sermon->id ) ) );
		}

		// Convert passages to Sermon Manager format.
		if ( ! function_exists( 'sm_update_2140_convert_bible_verse' ) ) {
			include_once SM_PATH . 'includes/sm-update-functions.php';
		}
		sm_update_2140_convert_bible_verse();

		// Update term counts.
		foreach (
			array(
				'_imported_preachers'     => 'wpfc_preacher',
				'_imported_service_types' => 'wpfc_service_type',
				'_imported_series'        => 'wpfc_sermon_series',
				'_imported_books'         => 'wpfc_bible_book',
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
