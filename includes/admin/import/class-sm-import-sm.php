<?php
/**
 * Imports data from Sermon Manager import file into Sermon Manager.
 *
 * @package SM/Core/Admin/Importing
 */

defined( 'ABSPATH' ) or die;

/**
 * Used to import data from XML File
 *
 * @since 2.12.0
 */
class SM_Import_SM {
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
	 * Max. supported WXR version.
	 *
	 * @var float
	 */
	var $max_wxr_version = 1.2;

	/**
	 * WXR attachment ID
	 *
	 * @var int
	 */
	var $id;

	/**
	 * Should it fetch attachments.
	 *
	 * @var bool
	 */
	var $fetch_attachments = true;

	/**
	 * URL re-mapping.
	 *
	 * @var array
	 */
	var $url_remap = array();

	/**
	 * Featured images.
	 *
	 * @var array
	 */
	var $featured_images = array();

	/**
	 * Taxonomy featured images.
	 *
	 * @var array
	 */
	var $taxonomy_featured_images = array();

	/**
	 * Import Status.
	 *
	 * @var bool
	 */
	var $import_status = false;

	/**
	 * Import Message.
	 *
	 * @var string
	 */
	var $import_message = '';

	/**
	 * Authors.
	 *
	 * @var array
	 */
	var $authors = array();

	/**
	 * Posts.
	 *
	 * @var array
	 */
	var $posts = array();

	/**
	 * Terms.
	 *
	 * @var array
	 */
	var $terms = array();

	/**
	 * Base URL.
	 *
	 * @var string
	 */
	var $base_url = '';

	// Mappings from old information to new.
	/**
	 * Processed authors.
	 *
	 * @var array
	 */
	var $processed_authors = array();

	/**
	 * Author mapping.
	 *
	 * @var array
	 */
	var $author_mapping = array();

	/**
	 * Processed terms.
	 *
	 * @var array
	 */
	var $processed_terms = array();

	/**
	 * Processed posts.
	 *
	 * @var array
	 */
	var $processed_posts = array();

	/**
	 * Post mapping.
	 *
	 * @var array
	 */
	var $post_orphans = array();

	/**
	 * Accepted WXR tags.
	 *
	 * @var array
	 */
	var $wp_tags = array(
		'wp:post_id',
		'wp:post_date',
		'wp:post_date_gmt',
		'wp:comment_status',
		'wp:ping_status',
		'wp:attachment_url',
		'wp:status',
		'wp:post_name',
		'wp:post_parent',
		'wp:menu_order',
		'wp:post_type',
		'wp:post_password',
		'wp:is_sticky',
		'wp:term_id',
		'wp:category_nicename',
		'wp:category_parent',
		'wp:cat_name',
		'wp:category_description',
		'wp:tag_slug',
		'wp:tag_name',
		'wp:tag_description',
		'wp:term_taxonomy',
		'wp:term_parent',
		'wp:term_name',
		'wp:term_description',
		'wp:author_id',
		'wp:author_login',
		'wp:author_email',
		'wp:author_display_name',
		'wp:author_first_name',
		'wp:author_last_name',
	);

	/**
	 * Accepted WXR subtags.
	 *
	 * @var array
	 */
	var $wp_sub_tags = array(
		'wp:comment_id',
		'wp:comment_author',
		'wp:comment_author_email',
		'wp:comment_author_url',
		'wp:comment_author_IP',
		'wp:comment_date',
		'wp:comment_date_gmt',
		'wp:comment_content',
		'wp:comment_approved',
		'wp:comment_type',
		'wp:comment_parent',
		'wp:comment_user_id',
	);

	/**
	 * SM_Import_SM constructor.
	 */
	public function __construct() {
		$this->is_debug   = ! ! \SermonManager::getOption( 'debug_import' );
		$this->start_time = microtime( true );
	}

	/**
	 * Decide if the given meta key maps to information we will want to import
	 *
	 * @param string $key The meta key to check.
	 *
	 * @return string|bool The key if we do want to import, false if not
	 */
	function is_valid_meta_key( $key ) {
		// skip attachment metadata since we'll regenerate it from scratch
		// skip _edit_lock as not relevant for import.
		if ( in_array( $key, array( '_wp_attached_file', '_wp_attachment_metadata', '_edit_lock' ) ) ) {
			return false;
		}

		return $key;
	}

	/**
	 * Added to http_request_timeout filter to force timeout at 60 seconds during import
	 *
	 * @param int $val Default timeout.
	 *
	 * @return int 60
	 */
	function bump_request_timeout( $val ) {
		return 60;
	}

	/**
	 * Update latest importing log.
	 */
	public function __destruct() {
		update_option( 'sm_last_import_info', $this->debug_data );
	}

	/**
	 * Do the import
	 */
	public function import() {
		$this->log( 'Init info:' . PHP_EOL . 'Sermon Manager ' . SM_VERSION . PHP_EOL . 'Release Date: ' . date( 'Y-m-d', filemtime( SM_PLUGIN_FILE ) ), 255 );
		if ( ! doing_action( 'admin_init' ) ) {
			$this->log( 'Scheduling for `admin_init` action.', 0 );
			add_action( 'admin_init', array( $this, __FUNCTION__ ) );

			return;
		}

		$this->log( 'Including import files.', 0 );
		require_once( ABSPATH . 'wp-admin/includes/import.php' );

		if ( ! class_exists( 'WP_Importer' ) ) {
			$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
			if ( file_exists( $class_wp_importer ) ) {
				/* @noinspection PhpIncludeInspection */
				require $class_wp_importer;
			}
		}
		$this->log( 'Files included.', 0 );

		add_filter( 'import_post_meta_key', array( $this, 'is_valid_meta_key' ) );
		add_filter( 'http_request_timeout', array( $this, 'bump_request_timeout' ) );

		$this->log( 'Doing `sm_import_before_sm` action.', 0 );
		do_action( 'sm_import_before_sm' );
		$this->log( 'Done.', 0 );

		$this->log( 'Handling uploaded file.', 0 );
		$upload = $this->handle_upload();
		if ( $upload['status'] ) {
			$this->log( 'File successfully loaded.', 0 );
			$this->log( 'Starting content import.', 0 );
			$this->importContent( $upload['file'] );
			$this->log( 'Content import ended.', 0 );
		} else {
			/* Notify about failed file upload */
			$this->log( 'Error while loading export file', 0 );
		}

		$this->log( 'Doing `sm_import_after_sm` action.', 0 );
		do_action( 'sm_import_after_sm' );
		$this->log( 'Done.', 0 );
	}

	/**
	 * Logs a message to show in debug
	 *
	 * @param string $message  The message.
	 * @param int    $severity The severity.
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
	 * Handles the WXR upload and initial parsing of the file to prepare for
	 * displaying author import options
	 *
	 * @return array ['status'] false if error uploading or invalid file, true otherwise
	 */
	function handle_upload() {
		$file     = wp_import_handle_upload();
		$response = array();
		if ( isset( $file['error'] ) ) {
			$response['status'] = false;
			$this->log( 'Error message: ' . $file['error'], 0 );
		} elseif ( ! file_exists( $file['file'] ) ) {
			$response['status'] = false;
			$this->log( 'The export file could not be found. It is likely that this was caused by a permissions problem.' . $file['error'], 0 );
		}

		$this->id = (int) $file['id'];
		$this->log( 'Starting XML File parsing.', 0 );
		$import_data = $this->XMLparse( $file['file'] );
		if ( is_wp_error( $import_data ) ) {
			$response['status'] = false;
			$this->log( 'Parsing error: ' . $import_data->get_error_message(), 0 );
		}

		$this->version = $import_data['version'];
		if ( $this->version > $this->max_wxr_version ) {
			$response['status'] = false;
			$this->log( 'This WXR file version may not be supported by this version of the importer. Please consider updating.', 0 );
		}

		$response['status'] = true;
		$response['file']   = $file['file'];
		$this->log( 'XML parsed with success.', 0 );

		return $response;
	}

	/**
	 * Parses the import file.
	 *
	 * @param string $file Import file path.
	 *
	 * @return array|WP_Error
	 */
	function XMLparse( $file ) {
		$this->wxr_version = $this->in_post = $this->cdata = $this->data = $this->sub_data = $this->in_tag = $this->in_sub_tag = false;
		$this->authors     = $this->posts = $this->term = $this->category = $this->tag = array();

		$this->log( 'XML parser setup.', 0 );
		$xml = xml_parser_create( 'UTF-8' );
		xml_parser_set_option( $xml, XML_OPTION_SKIP_WHITE, 1 );
		xml_parser_set_option( $xml, XML_OPTION_CASE_FOLDING, 0 );
		xml_set_object( $xml, $this );
		xml_set_character_data_handler( $xml, 'cdata' );
		xml_set_element_handler( $xml, 'tag_open', 'tag_close' );

		$this->log( 'Parsing content.', 0 );
		if ( ! xml_parse( $xml, file_get_contents( $file ), true ) ) {
			$current_line   = xml_get_current_line_number( $xml );
			$current_column = xml_get_current_column_number( $xml );
			$error_code     = xml_get_error_code( $xml );
			$error_string   = xml_error_string( $error_code );
			$this->log( 'There was an error when reading this WXR file.', 0 );

			return new WP_Error( 'XML_parse_error', 'There was an error when reading this WXR file', array(
				$current_line,
				$current_column,
				$error_string,
			) );
		}
		xml_parser_free( $xml );

		if ( ! preg_match( '/^\d+\.\d+$/', $this->wxr_version ) ) {
			$this->log( 'This does not appear to be a WXR file, missing/invalid WXR version number.', 0 );

			return new WP_Error( 'WXR_parse_error', __( 'This does not appear to be a WXR file, missing/invalid WXR version number', 'wordpress-importer' ) );
		}

		$this->log( 'Setting content parameters.', 0 );

		return array(
			'authors'    => $this->authors,
			'posts'      => $this->posts,
			'categories' => $this->category,
			'tags'       => $this->tag,
			'terms'      => $this->term,
			'base_url'   => $this->base_url,
			'version'    => $this->wxr_version,
		);
	}

	/**
	 * The main controller for the actual import stage.
	 *
	 * @param string $file Path to the WXR file for importing.
	 */
	function importContent( $file ) {

		$this->log( 'Import Content function started.', 0 );
		$this->import_start( $file );

		$this->get_author_mapping();

		wp_suspend_cache_invalidation( true );

		$this->log( 'Process terms start.', 0 );
		$this->process_terms();
		$this->log( 'Process terms end.', 0 );

		$this->log( 'Process posts start.', 0 );
		$this->process_posts();
		$this->log( 'Process posts end.', 0 );

		wp_suspend_cache_invalidation( false );

		// update incorrect/missing information in the DB.
		$this->log( 'Update incorrect/missing information in the DB.', 0 );

		$this->log( 'Update parent/child relations start.', 0 );
		$this->backfill_parents();
		$this->log( 'Update parent/child relations end.', 0 );

		$this->log( 'Update attachment urls start.', 0 );
		$this->backfill_attachment_urls();
		$this->log( 'Update attachment urls end.', 0 );

		$this->log( 'Update featured image ids in posts start.', 0 );
		$this->remap_featured_images();
		$this->log( 'Update featured image ids in posts end.', 0 );

		$this->log( 'Update term image ids in terms start.', 0 );
		$this->remap_term_images();
		$this->log( 'Update term image ids in terms end.', 0 );

		$this->import_end();
		$this->log( 'Import Content function ended.', 0 );
	}

	/**
	 * Parses the WXR file and prepares us for the task of processing parsed data
	 *
	 * @param string $file Path to the WXR file for importing.
	 */
	function import_start( $file ) {
		if ( ! is_file( $file ) ) {
			$this->import_status = false;
			$this->log( 'The file does not exist, please try again.', 0 );

			return;
		}

		$import_data = $this->XMLparse( $file );

		if ( is_wp_error( $import_data ) ) {
			$this->import_status = false;
			$this->log( 'Import start error: ' . $import_data->get_error_message(), 0 );

			return;
		}

		$this->log( 'Setup version.', 0 );
		$this->version = $import_data['version'];
		$this->log( 'Setup authors.', 0 );
		$this->get_authors_from_import( $import_data );
		$this->log( 'Setup posts.', 0 );
		$this->posts = $import_data['posts'];
		$this->log( 'Setup terms.', 0 );
		$this->terms = $import_data['terms'];
		$this->log( 'Setup base url.', 0 );
		$this->base_url = esc_url( $import_data['base_url'] );

		wp_defer_term_counting( true );
		wp_defer_comment_counting( true );

		do_action( 'import_start' );
	}

	/**
	 * Retrieve authors from parsed WXR data
	 *
	 * Uses the provided author information from WXR 1.1 files
	 * or extracts info from each post for WXR 1.0 files
	 *
	 * @param array $import_data Data returned by a WXR parser.
	 */
	function get_authors_from_import( $import_data ) {
		$this->log( 'Importing authors start.', 0 );
		if ( ! empty( $import_data['authors'] ) ) {
			$this->log( 'Authors exists, setting them.', 0 );
			$this->authors = $import_data['authors'];
			// no author information, grab it from the posts.
		} else {
			$this->log( 'Authors does not exist, getting them from posts.', 0 );
			foreach ( $import_data['posts'] as $post ) {
				$login = sanitize_user( $post['post_author'], true );
				if ( empty( $login ) ) {
					continue;
				}

				if ( ! isset( $this->authors[ $login ] ) ) {
					$this->authors[ $login ] = array(
						'author_login'        => $login,
						'author_display_name' => $post['post_author'],
					);
				}
			}
		}
	}

	/**
	 * Map old author logins to local user IDs. Can map to an existing user, create a new user
	 * or falls back to the current user in case of error with either of the previous
	 */
	function get_author_mapping() {

		$create_users = $this->allow_create_users();

		if ( $create_users && '1.0' != $this->version ) {
			$this->log( 'Users creation enabled.', 0 );
			foreach ( $this->authors as $i => $data ) {

				$santized_old_login = sanitize_user( $i, true );
				$old_id             = isset( $this->authors[ $i ]['author_id'] ) ? intval( $this->authors[ $i ]['author_id'] ) : false;
				$this->log( 'Creating user.', 0 );
				$user_data = array(
					'user_login'   => $i,
					'user_pass'    => wp_generate_password(),
					'user_email'   => isset( $this->authors[ $i ]['author_email'] ) ? $this->authors[ $i ]['author_email'] : '',
					'display_name' => $this->authors[ $i ]['author_display_name'],
					'first_name'   => isset( $this->authors[ $i ]['author_first_name'] ) ? $this->authors[ $i ]['author_first_name'] : '',
					'last_name'    => isset( $this->authors[ $i ]['author_last_name'] ) ? $this->authors[ $i ]['author_last_name'] : '',
				);
				$user_id   = wp_insert_user( $user_data );

				if ( ! is_wp_error( $user_id ) ) {
					if ( $old_id ) {
						$this->processed_authors[ $old_id ] = $user_id;
					}
					$this->author_mapping[ $santized_old_login ] = $user_id;
				}

				// failsafe: if the user_id was invalid, default to the current user.
				if ( ! isset( $this->author_mapping[ $santized_old_login ] ) ) {
					$this->log( 'Some post does not have user assigned, use current user.', 0 );
					if ( $old_id ) {
						$this->processed_authors[ $old_id ] = (int) get_current_user_id();
					}
					$this->author_mapping[ $santized_old_login ] = (int) get_current_user_id();
				}
			}
		}
	}

	/**
	 * Decide whether or not the importer is allowed to create users.
	 * Default is true, can be filtered via import_allow_create_users
	 *
	 * @return bool True if creating users is allowed
	 */
	function allow_create_users() {
		return true;
	}

	/**
	 * Create new terms based on import information
	 *
	 * Doesn't create a term its slug already exists
	 */
	function process_terms() {
		$this->log( 'Start terms processing.', 0 );
		if ( empty( $this->terms ) ) {
			return;
		}

		foreach ( $this->terms as $term ) {
			$this->log( 'Checking terms.', 0 );
			// if the term already exists in the correct taxonomy leave it alone.
			$term_id = term_exists( $term['slug'], $term['term_taxonomy'] );
			if ( $term_id ) {
				if ( is_array( $term_id ) ) {
					$term_id = $term_id['term_id'];
				}
				if ( isset( $term['term_id'] ) ) {
					$this->processed_terms[ intval( $term['term_id'] ) ] = (int) $term_id;
				}
				continue;
			}

			if ( empty( $term['term_parent'] ) ) {
				$parent = 0;
			} else {
				$parent = term_exists( $term['term_parent'], $term['term_taxonomy'] );
				if ( is_array( $parent ) ) {
					$parent = $parent['term_id'];
				}
			}
			$term        = wp_slash( $term );
			$description = isset( $term['term_description'] ) ? $term['term_description'] : '';
			$termarr     = array(
				'slug'        => $term['slug'],
				'description' => $description,
				'parent'      => intval( $parent ),
			);

			$this->log( 'Inserting terms.', 0 );
			$id = wp_insert_term( $term['term_name'], $term['term_taxonomy'], $termarr );
			if ( ! is_wp_error( $id ) ) {
				if ( isset( $term['term_id'] ) ) {
					$this->processed_terms[ intval( $term['term_id'] ) ] = $id['term_id'];
				}
			} else {
				$this->log( 'Inserting error: ' . $id->get_error_message(), 0 );
				continue;
			}

			$this->log( 'Processing term meta start.', 0 );
			$this->process_termmeta( $term, $id['term_id'] );
			$this->log( 'Processing term meta end.', 0 );
		}

		unset( $this->terms );
	}

	/**
	 * Add metadata to imported term.
	 *
	 * @since 0.6.2
	 *
	 * @param array $term    Term data from WXR import.
	 * @param int   $term_id ID of the newly created term.
	 */
	protected function process_termmeta( $term, $term_id ) {
		if ( ! isset( $term['termmeta'] ) ) {
			$term['termmeta'] = array();
		}

		if ( empty( $term['termmeta'] ) ) {
			return;
		}

		$this->log( 'Going over term meta.', 0 );
		foreach ( $term['termmeta'] as $meta ) {
			$key = $meta['key'];
			if ( ! $key ) {
				continue;
			}

			// Export gets meta straight from the DB so could have a serialized string.
			$value = maybe_unserialize( $meta['value'] );
			add_term_meta( $term_id, $key, $value );

			if ( 'sm_term_image_id' == $key ) {
				$this->log( 'Term has image id set.', 0 );
				$assigned_term_images = get_option( 'sermon_image_plugin' );
				if ( empty( $assigned_term_images ) ) {
					$assigned_term_images = array();
				}
				$assigned_term_images[ $term_id ] = $value;
				update_option( 'sermon_image_plugin', $assigned_term_images );

				$this->taxonomy_featured_images[ $term_id ] = (int) $value;
			}
		}
	}

	/**
	 * Create new posts based on import information
	 *
	 * Posts marked as having a parent which doesn't exist will become top level items.
	 * Doesn't create a new post if: the post type doesn't exist, the given post ID
	 * is already noted as imported or a post with the same title and date already exists.
	 * Note that new/updated terms, comments and meta are imported for the last of the above.
	 */
	function process_posts() {

		foreach ( $this->posts as $post ) {
			$this->log( 'Iterating over posts (sermon and attachment).', 0 );
			if ( ! post_type_exists( $post['post_type'] ) && ( 'wpfc_sermon' != $post['post_type'] || 'attachment' != $post['post_type'] ) ) {
				continue;
			}

			if ( isset( $this->processed_posts[ $post['post_id'] ] ) && ! empty( $post['post_id'] ) ) {
				continue;
			}

			if ( 'auto-draft' == $post['status'] ) {
				continue;
			}

			$post_type_object = get_post_type_object( $post['post_type'] );

			$post_exists = post_exists( $post['post_title'], '', $post['post_date'] );

			if ( $post_exists && get_post_type( $post_exists ) == $post['post_type'] ) {
				$comment_post_ID                                     = $post_id = $post_exists;
				$this->processed_posts[ intval( $post['post_id'] ) ] = intval( $post_exists );
			} else {
				$post_parent = (int) $post['post_parent'];
				if ( $post_parent ) {
					// if we already know the parent, map it to the new local ID.
					if ( isset( $this->processed_posts[ $post_parent ] ) ) {
						$post_parent = $this->processed_posts[ $post_parent ];
						// otherwise record the parent for later.
					} else {
						$this->post_orphans[ intval( $post['post_id'] ) ] = $post_parent;
						$post_parent                                      = 0;
					}
				}

				// map the post author.
				$author = sanitize_user( $post['post_author'], true );
				if ( isset( $this->author_mapping[ $author ] ) ) {
					$author = $this->author_mapping[ $author ];
				} else {
					$author = (int) get_current_user_id();
				}

				$postdata = array(
					'import_id'      => $post['post_id'],
					'post_author'    => $author,
					'post_date'      => $post['post_date'],
					'post_date_gmt'  => $post['post_date_gmt'],
					'post_content'   => $post['post_content'],
					'post_excerpt'   => $post['post_excerpt'],
					'post_title'     => $post['post_title'],
					'post_status'    => $post['status'],
					'post_name'      => $post['post_name'],
					'comment_status' => $post['comment_status'],
					'ping_status'    => $post['ping_status'],
					'guid'           => $post['guid'],
					'post_parent'    => $post_parent,
					'menu_order'     => $post['menu_order'],
					'post_type'      => $post['post_type'],
					'post_password'  => $post['post_password'],
				);

				$postdata = wp_slash( $postdata );

				if ( 'attachment' == $postdata['post_type'] ) {
					$remote_url = ! empty( $post['attachment_url'] ) ? $post['attachment_url'] : $post['guid'];

					// try to use _wp_attached file for upload folder placement to ensure the same location as the export site
					// e.g. location is 2003/05/image.jpg but the attachment post_date is 2010/09, see media_handle_upload().
					$postdata['upload_date'] = $post['post_date'];
					if ( isset( $post['postmeta'] ) ) {
						foreach ( $post['postmeta'] as $meta ) {
							if ( '_wp_attached_file' == $meta['key'] ) {
								if ( preg_match( '%^[0-9]{4}/[0-9]{2}%', $meta['value'], $matches ) ) {
									$postdata['upload_date'] = $matches[0];
								}
								break;
							}
						}
					}

					$comment_post_ID = $post_id = $this->process_attachment( $postdata, $remote_url );
				} else {
					$comment_post_ID = $post_id = wp_insert_post( $postdata, true );
				}

				if ( is_wp_error( $post_id ) ) {
					continue;
				}

				if ( 1 == $post['is_sticky'] ) {
					stick_post( $post_id );
				}
			}

			// map pre-import ID to local ID.
			$this->processed_posts[ intval( $post['post_id'] ) ] = (int) $post_id;

			if ( ! isset( $post['terms'] ) ) {
				$post['terms'] = array();
			}

			// add categories, tags and other terms.
			if ( ! empty( $post['terms'] ) ) {
				$terms_to_set = array();
				foreach ( $post['terms'] as $term ) {
					// back compat with WXR 1.0 map 'tag' to 'post_tag'.
					$taxonomy    = ( 'tag' == $term['domain'] ) ? 'post_tag' : $term['domain'];
					$term_exists = term_exists( $term['slug'], $taxonomy );
					$term_id     = is_array( $term_exists ) ? $term_exists['term_id'] : $term_exists;
					if ( ! $term_id ) {
						$t = wp_insert_term( $term['name'], $taxonomy, array( 'slug' => $term['slug'] ) );
						if ( ! is_wp_error( $t ) ) {
							$term_id = $t['term_id'];
						} else {
							continue;
						}
					}
					$terms_to_set[ $taxonomy ][] = intval( $term_id );
				}

				foreach ( $terms_to_set as $tax => $ids ) {
					$tt_ids = wp_set_post_terms( $post_id, $ids, $tax );
				}
				unset( $post['terms'], $terms_to_set );
			}

			if ( ! isset( $post['comments'] ) ) {
				$post['comments'] = array();
			}

			// add/update comments.
			if ( ! empty( $post['comments'] ) ) {
				$num_comments      = 0;
				$inserted_comments = array();
				foreach ( $post['comments'] as $comment ) {
					$comment_id                                         = $comment['comment_id'];
					$newcomments[ $comment_id ]['comment_post_ID']      = $comment_post_ID;
					$newcomments[ $comment_id ]['comment_author']       = $comment['comment_author'];
					$newcomments[ $comment_id ]['comment_author_email'] = $comment['comment_author_email'];
					$newcomments[ $comment_id ]['comment_author_IP']    = $comment['comment_author_IP'];
					$newcomments[ $comment_id ]['comment_author_url']   = $comment['comment_author_url'];
					$newcomments[ $comment_id ]['comment_date']         = $comment['comment_date'];
					$newcomments[ $comment_id ]['comment_date_gmt']     = $comment['comment_date_gmt'];
					$newcomments[ $comment_id ]['comment_content']      = $comment['comment_content'];
					$newcomments[ $comment_id ]['comment_approved']     = $comment['comment_approved'];
					$newcomments[ $comment_id ]['comment_type']         = $comment['comment_type'];
					$newcomments[ $comment_id ]['comment_parent']       = $comment['comment_parent'];
					$newcomments[ $comment_id ]['commentmeta']          = isset( $comment['commentmeta'] ) ? $comment['commentmeta'] : array();
					if ( isset( $this->processed_authors[ $comment['comment_user_id'] ] ) ) {
						$newcomments[ $comment_id ]['user_id'] = $this->processed_authors[ $comment['comment_user_id'] ];
					}
				}
				ksort( $newcomments );

				foreach ( $newcomments as $key => $comment ) {
					// if this is a new post we can skip the comment_exists() check.
					if ( ! $post_exists || ! comment_exists( $comment['comment_author'], $comment['comment_date'] ) ) {
						if ( isset( $inserted_comments[ $comment['comment_parent'] ] ) ) {
							$comment['comment_parent'] = $inserted_comments[ $comment['comment_parent'] ];
						}
						$comment                   = wp_filter_comment( $comment );
						$inserted_comments[ $key ] = wp_insert_comment( $comment );

						foreach ( $comment['commentmeta'] as $meta ) {
							$value = maybe_unserialize( $meta['value'] );
							add_comment_meta( $inserted_comments[ $key ], $meta['key'], $value );
						}

						$num_comments ++;
					}
				}
				unset( $newcomments, $inserted_comments, $post['comments'] );
			}

			if ( ! isset( $post['postmeta'] ) ) {
				$post['postmeta'] = array();
			}

			// add/update post meta.
			if ( ! empty( $post['postmeta'] ) ) {
				foreach ( $post['postmeta'] as $meta ) {
					$key   = $meta['key'];
					$value = false;

					if ( '_edit_last' == $key ) {
						if ( isset( $this->processed_authors[ intval( $meta['value'] ) ] ) ) {
							$value = $this->processed_authors[ intval( $meta['value'] ) ];
						} else {
							$key = false;
						}
					}

					if ( $key ) {
						// export gets meta straight from the DB so could have a serialized string.
						if ( ! $value ) {
							$value = maybe_unserialize( $meta['value'] );
						}
						/* echo "post meta called";
						die(); */
						add_post_meta( $post_id, $key, $value );

						// if the post has a featured image, take note of this in case of remap.
						if ( '_thumbnail_id' == $key ) {
							$this->featured_images[ $post_id ] = (int) $value;
						}
					}
				}
			}
		}

		unset( $this->posts );
	}

	/**
	 * If fetching attachments is enabled then attempt to create a new attachment
	 *
	 * @param array  $post Attachment post details from WXR.
	 * @param string $url  URL to fetch attachment from.
	 *
	 * @return int|WP_Error Post ID on success, WP_Error otherwise
	 */
	function process_attachment( $post, $url ) {
		if ( ! $this->fetch_attachments ) {
			return new WP_Error( 'attachment_processing_error', __( 'Fetching attachments is not enabled', 'wordpress-importer' ) );
		}

		// if the URL is absolute, but does not contain address, then upload it assuming base_site_url.
		if ( preg_match( '|^/[\w\W]+$|', $url ) ) {
			$url = rtrim( $this->base_url, '/' ) . $url;
		}

		$upload = $this->fetch_remote_file( $url, $post );
		if ( is_wp_error( $upload ) ) {
			return $upload;
		}

		$info = wp_check_filetype( $upload['file'] );
		if ( $info ) {
			$post['post_mime_type'] = $info['type'];
		} else {
			return new WP_Error( 'attachment_processing_error', __( 'Invalid file type', 'wordpress-importer' ) );
		}

		$post['guid'] = $upload['url'];

		// as per `wp-admin/includes/upload.php`.
		$post_id = wp_insert_attachment( $post, $upload['file'] );
		wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $upload['file'] ) );

		// remap resized image URLs, works by stripping the extension and remapping the URL stub.
		if ( preg_match( '!^image/!', $info['type'] ) ) {
			$parts = pathinfo( $url );
			$name  = basename( $parts['basename'], ".{$parts['extension']}" ); // PATHINFO_FILENAME in PHP 5.2.

			$parts_new = pathinfo( $upload['url'] );
			$name_new  = basename( $parts_new['basename'], ".{$parts_new['extension']}" );

			$this->url_remap[ $parts['dirname'] . '/' . $name ] = $parts_new['dirname'] . '/' . $name_new;
		}

		return $post_id;
	}

	/**
	 * Attempt to download a remote file attachment
	 *
	 * @param string $url  URL of item to fetch.
	 * @param array  $post Attachment details.
	 *
	 * @return array|WP_Error Local file location details on success, WP_Error otherwise
	 */
	function fetch_remote_file( $url, $post ) {
		// extract the file name and extension from the url.
		$file_name = basename( $url );

		// get placeholder file in the upload dir with a unique, sanitized filename.
		$upload = wp_upload_bits( $file_name, 0, '', $post['upload_date'] );
		if ( $upload['error'] ) {
			return new WP_Error( 'upload_dir_error', $upload['error'] );
		}

		// fetch the remote url and write it to the placeholder file.
		$headers = wp_get_http( $url, $upload['file'] );

		// request failed.
		if ( ! $headers ) {
			@unlink( $upload['file'] );

			return new WP_Error( 'import_file_error', __( 'Remote server did not respond', 'wordpress-importer' ) );
		}

		// make sure the fetch was successful.
		if ( $headers['response'] != '200' ) {
			@unlink( $upload['file'] );

			return new WP_Error( 'import_file_error', sprintf( __( 'Remote server returned error response %1$d %2$s', 'wordpress-importer' ), esc_html( $headers['response'] ), get_status_header_desc( $headers['response'] ) ) );
		}

		$filesize = filesize( $upload['file'] );

		if ( isset( $headers['content-length'] ) && $filesize != $headers['content-length'] ) {
			@unlink( $upload['file'] );

			return new WP_Error( 'import_file_error', __( 'Remote file is incorrect size', 'wordpress-importer' ) );
		}

		if ( 0 == $filesize ) {
			@unlink( $upload['file'] );

			return new WP_Error( 'import_file_error', __( 'Zero size file downloaded', 'wordpress-importer' ) );
		}

		$max_size = (int) $this->max_attachment_size();
		if ( ! empty( $max_size ) && $filesize > $max_size ) {
			@unlink( $upload['file'] );

			return new WP_Error( 'import_file_error', sprintf( __( 'Remote file is too large, limit is %s', 'wordpress-importer' ), size_format( $max_size ) ) );
		}

		// keep track of the old and new urls so we can substitute them later.
		$this->url_remap[ $url ]          = $upload['url'];
		$this->url_remap[ $post['guid'] ] = $upload['url']; // r13735, really needed?
		// keep track of the destination if the remote url is redirected somewhere else.
		if ( isset( $headers['x-final-location'] ) && $headers['x-final-location'] != $url ) {
			$this->url_remap[ $headers['x-final-location'] ] = $upload['url'];
		}

		return $upload;
	}

	/**
	 * Decide what the maximum file size for downloaded attachments is.
	 * Default is 0 (unlimited), can be filtered via import_attachment_size_limit
	 *
	 * @return int Maximum attachment file size to import
	 */
	function max_attachment_size() {
		return 0;
	}

	/**
	 * Attempt to associate posts with previously missing parents
	 *
	 * An imported post's parent may not have been imported when it was first created
	 * so try again.
	 */
	function backfill_parents() {
		global $wpdb;

		// find parents for post orphans.
		foreach ( $this->post_orphans as $child_id => $parent_id ) {
			$local_child_id = $local_parent_id = false;
			if ( isset( $this->processed_posts[ $child_id ] ) ) {
				$local_child_id = $this->processed_posts[ $child_id ];
			}
			if ( isset( $this->processed_posts[ $parent_id ] ) ) {
				$local_parent_id = $this->processed_posts[ $parent_id ];
			}

			if ( $local_child_id && $local_parent_id ) {
				$wpdb->update( $wpdb->posts, array( 'post_parent' => $local_parent_id ), array( 'ID' => $local_child_id ), '%d', '%d' );
			}
		}
	}

	// return the difference in length between two strings.
	/**
	 * Use stored mapping information to update old attachment URLs
	 */
	function backfill_attachment_urls() {
		global $wpdb;
		// make sure we do the longest urls first, in case one is a substring of another.
		uksort( $this->url_remap, array( &$this, 'cmpr_strlen' ) );

		foreach ( $this->url_remap as $from_url => $to_url ) {
			// remap urls in post_content.
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s)", $from_url, $to_url ) );
			// remap enclosure urls.
			$result = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key='enclosure'", $from_url, $to_url ) );
		}
	}

	/**
	 * Update _thumbnail_id meta to new, imported attachment IDs
	 */
	function remap_featured_images() {
		// cycle through posts that have a featured image.
		foreach ( $this->featured_images as $post_id => $value ) {
			if ( isset( $this->processed_posts[ $value ] ) ) {
				$new_id = $this->processed_posts[ $value ];
				// only update if there's a difference.
				if ( $new_id != $value ) {
					update_post_meta( $post_id, '_thumbnail_id', $new_id );
				}
			}
		}
	}

	/**
	 * Update sm_term_image_id meta to new, imported attachment IDs
	 */
	function remap_term_images() {
		// cycle through posts that have a featured image.
		foreach ( $this->taxonomy_featured_images as $term_id => $value ) {
			if ( isset( $this->processed_posts[ $value ] ) ) {
				$new_id = $this->processed_posts[ $value ];
				// only update if there's a difference.
				if ( $new_id != $value ) {
					$assigned_term_images             = get_option( 'sermon_image_plugin' );
					$assigned_term_images[ $term_id ] = $new_id;
					update_option( 'sermon_image_plugin', $assigned_term_images );
				}
			}
		}
	}

	/**
	 * Performs post-import cleanup of files and the cache
	 */
	function import_end() {
		wp_import_cleanup( $this->id );

		wp_cache_flush();
		foreach ( get_taxonomies() as $tax ) {
			delete_option( "{$tax}_children" );
			_get_term_hierarchy( $tax );
		}

		wp_defer_term_counting( false );
		wp_defer_comment_counting( false );

		do_action( 'import_end' );
	}

	/* WXR Functions - Start */
	function cmpr_strlen( $a, $b ) {
		return strlen( $b ) - strlen( $a );
	}

	function tag_open( $parse, $tag, $attr ) {
		$this->log( 'Opening tags.', 0 );
		if ( in_array( $tag, $this->wp_tags ) ) {
			$this->in_tag = substr( $tag, 3 );

			return;
		}

		if ( in_array( $tag, $this->wp_sub_tags ) ) {
			$this->in_sub_tag = substr( $tag, 3 );

			return;
		}

		switch ( $tag ) {
			case 'category':
				if ( isset( $attr['domain'], $attr['nicename'] ) ) {
					$this->sub_data['domain'] = $attr['domain'];
					$this->sub_data['slug']   = $attr['nicename'];
				}
				break;
			case 'item':
				$this->in_post = true;
			case 'title':
				if ( $this->in_post ) {
					$this->in_tag = 'post_title';
				}
				break;
			case 'guid':
				$this->in_tag = 'guid';
				break;
			case 'dc:creator':
				$this->in_tag = 'post_author';
				break;
			case 'content:encoded':
				$this->in_tag = 'post_content';
				break;
			case 'excerpt:encoded':
				$this->in_tag = 'post_excerpt';
				break;

			case 'wp:term_slug':
				$this->in_tag = 'slug';
				break;
			case 'wp:meta_key':
				$this->in_sub_tag = 'key';
				break;
			case 'wp:meta_value':
				$this->in_sub_tag = 'value';
				break;
		}
	}

	function cdata( $parser, $cdata ) {
		$this->log( 'Handling data.', 0 );
		if ( ! trim( $cdata ) ) {
			return;
		}

		if ( false !== $this->in_tag || false !== $this->in_sub_tag ) {
			$this->cdata .= $cdata;
		} else {
			$this->cdata .= trim( $cdata );
		}
	}

	function tag_close( $parser, $tag ) {
		$this->log( 'Closing tag.', 0 );
		switch ( $tag ) {
			case 'wp:comment':
				unset( $this->sub_data['key'], $this->sub_data['value'] ); // remove meta sub_data
				if ( ! empty( $this->sub_data ) ) {
					$this->data['comments'][] = $this->sub_data;
				}
				$this->sub_data = false;
				break;
			case 'wp:commentmeta':
				$this->sub_data['commentmeta'][] = array(
					'key'   => $this->sub_data['key'],
					'value' => $this->sub_data['value']
				);
				break;
			case 'category':
				if ( ! empty( $this->sub_data ) ) {
					$this->sub_data['name'] = $this->cdata;
					$this->data['terms'][]  = $this->sub_data;
				}
				$this->sub_data = false;
				break;
			case 'wp:postmeta':
				if ( ! empty( $this->sub_data ) ) {
					$this->data['postmeta'][] = $this->sub_data;
				}
				$this->sub_data = false;
				break;
			case 'wp:termmeta':
				if ( ! empty( $this->sub_data ) ) {
					$this->data['termmeta'][] = $this->sub_data;
				}
				$this->sub_data = false;
				break;
			case 'item':
				$this->posts[] = $this->data;
				$this->data    = false;
				break;
			case 'wp:category':
			case 'wp:tag':
			case 'wp:term':
				$n = substr( $tag, 3 );
				array_push( $this->$n, $this->data );
				$this->data = false;
				break;
			case 'wp:author':
				if ( ! empty( $this->data['author_login'] ) ) {
					$this->authors[ $this->data['author_login'] ] = $this->data;
				}
				$this->data = false;
				break;
			case 'wp:base_site_url':
				$this->base_url = $this->cdata;
				break;
			case 'wp:wxr_version':
				$this->wxr_version = $this->cdata;
				break;

			default:
				if ( $this->in_sub_tag ) {
					$this->sub_data[ $this->in_sub_tag ] = ! empty( $this->cdata ) ? $this->cdata : '';
					$this->in_sub_tag                    = false;
				} elseif ( $this->in_tag ) {
					$this->data[ $this->in_tag ] = ! empty( $this->cdata ) ? $this->cdata : '';
					$this->in_tag                = false;
				}
		}

		$this->cdata = false;
	}

	/* WXR Functions - End */
}