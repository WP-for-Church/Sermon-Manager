<?php // phpcs:ignore
/**
 * Plugin Name: Sermon Manager for WordPress
 * Plugin URI: https://www.wpforchurch.com/products/sermon-manager-for-wordpress/
 * Description: Add audio and video sermons, manage speakers, series, and more.
 * Version: 2.16.8
 * Author: WP for Church
 * Author URI: https://www.wpforchurch.com/
 * Requires at least: 4.5
 * Tested up to: 5.7.1
 *
 * Text Domain: sermon-manager-for-wordpress
 * Domain Path: /languages/
 *
 * @package SM/Core
 */

// All files must be PHP 5.3 compatible!
defined( 'ABSPATH' ) or die;

// Check the PHP version.
if ( version_compare( PHP_VERSION, '5.3.0', '<' ) ) {
	add_action( 'admin_notices', 'sm_render_php_version_error' );

	/**
	 * Renders the error notice when PHP is less than 5.3
	 *
	 * @since 2.8
	 */
	function sm_render_php_version_error() {
		?>
		<div class="notice notice-wpfc-php notice-error">
			<p>
				<?php
				// translators: %1$s current PHP version, see msgid "PHP %s", effectively <strong>PHP %s</strong>.
				// translators: %2$s required PHP version, see msgid "PHP %s", effectively <strong>PHP %s</strong>.
				echo wp_sprintf( esc_html__( 'You are running %1$s, but Sermon Manager requires at least %2$s.', 'sermon-manager-for-wordpress' ), '<strong>' . wp_sprintf( esc_html__( 'PHP %s', 'sermon-manager-for-wordpress' ), PHP_VERSION ) . '</strong>', '<strong>' . wp_sprintf( esc_html__( 'PHP %s', 'sermon-manager-for-wordpress' ), '5.3.0' ) . '</strong>' );
				?>
			</p>
		</div>
		<?php
	}

	return;
}

/**
 * Get all Sermon Manager screen ids.
 *
 * @return array Screen IDs
 * @since 2.9
 */
function sm_get_screen_ids() {
	$screen_ids = array(
		'wpfc_sermon',
		'edit-wpfc_sermon',
		'edit-wpfc_preacher',
		'edit-wpfc_sermon_series',
		'edit-wpfc_sermon_topics',
		'edit-wpfc_bible_book',
		'edit-wpfc_service_type',
		'wpfc_sermon_page_sm-settings',
		'wpfc_sermon_page_sm-import-export',
	);

	return apply_filters( 'sm_screen_ids', $screen_ids );
}

/**
 * The class that is used to initialize Sermon Manager.
 *
 * @author  WP For Church
 * @package SM/Core
 * @access  public
 */
class SermonManager { // phpcs:ignore

	/**
	 * Refers to a single instance of this class.
	 *
	 * @var $instance null|SermonManager The class instance.
	 */
	private static $instance = null;
	public static $image;
	public static $title;
	public static $description;

	/**
	 * Construct.
	 */
	public function __construct() {
		// Define constants (PATH and URL are with a trailing slash).
		define( 'SM_PLUGIN_FILE', __FILE__ );
		define( 'SM_PATH', dirname( SM_PLUGIN_FILE ) . '/' );
		define( 'SM_BASENAME', plugin_basename( __FILE__ ) );
		define( 'SM_URL', plugin_dir_url( __FILE__ ) );
		define( 'SM_VERSION', preg_match( '/^.*Version: (.*)$/m', file_get_contents( __FILE__ ), $version ) ? trim( $version[1] ) : 'N/A' );

		// Easy way to get if output buffering is enabled. @todo - fix it, causes issues to many users.
		define( 'SM_OB_ENABLED', true );

		do_action( 'sm_before_plugin_load' );

		// Include required items.
		$this->_includes();

		// Attach actions.
		$this->_init_actions();

		// Exec stuff after load.
		do_action( 'sm_after_plugin_load' );
	}


	public function fetchOptionalValue($args){

	}

	/**
	 * Include Sermon Manager files.
	 *
	 * @return void
	 */
	private function _includes() {
		/**
		 * General includes.
		 */
		include SM_PATH . 'includes/class-sm-autoloader.php'; // Autoloader.
		include SM_PATH . 'includes/sm-core-functions.php'; // Core Sermon Manager functions.
		include SM_PATH . 'includes/class-sm-dates.php'; // Dates operations.
		include SM_PATH . 'includes/class-sm-dates-wp.php'; // Attach to WP filters.
		include SM_PATH . 'includes/class-sm-api.php'; // API.
		include SM_PATH . 'includes/class-sm-post-types.php'; // Register post type, taxonomies, etc.
		include SM_PATH . 'includes/class-sm-install.php'; // Install and update functions.
		include SM_PATH . 'includes/class-sm-roles.php'; // Adds roles support.
		include SM_PATH . 'includes/sm-deprecated-functions.php'; // Deprecated SM functions.
		include SM_PATH . 'includes/sm-formatting-functions.php'; // Data formatting.
		include SM_PATH . 'includes/vendor/taxonomy-images/taxonomy-images.php'; // Images for Custom Taxonomies.
		include SM_PATH . 'includes/vendor/entry-views.php'; // Entry Views Tracking.
		include SM_PATH . 'includes/class-sm-shortcodes.php'; // Shortcodes.
		include SM_PATH . 'includes/class-sm-widget-recent-sermons.php'; // Recent sermons widget.
		include SM_PATH . 'includes/sm-template-functions.php'; // Template functions.
		include SM_PATH . 'includes/sm-podcast-functions.php'; // Podcast Functions.

		/**
		 * Admin only includes.
		 */
		if ( is_admin() ) {
			include SM_PATH . 'includes/admin/class-sm-admin.php'; // Admin init class.
			include SM_PATH . 'includes/admin/sm-cmb-functions.php'; // CMB2 Meta Fields functions.
			include SM_PATH . 'includes/vendor/CMB2/init.php'; // Metaboxes.
		}
	}

	/**
	 * Instead of loading options variable each time in every code snippet, let's have it in one place.
	 *
	 * @param string $name    Option name.
	 * @param string $default Default value to return if option is not set (defaults to empty string).
	 *
	 * @return mixed Returns option value or an empty string if it doesn't exist. Just like WP does.
	 */
	public static function getOption( $name = '', $default = '' ) { // phpcs:ignore
		if ( ! class_exists( 'SM_Admin_Settings' ) ) {
			include_once SM_PATH . 'includes/admin/class-sm-admin-settings.php';
		}

		return SM_Admin_Settings::get_option( $name, $default );
	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return SermonManager A single instance of this class.
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Saves whole Sermon HTML markup into post content for better search compatibility.
	 *
	 * @param int     $post_ID    Post ID.
	 * @param WP_Post $post       Post object.
	 * @param bool    $skip_check Disables check of "SM_SAVING_POST" constant.
	 *
	 * @since 2.8
	 */
	public function render_sermon_into_content( $post_ID = 0, $post = null, $skip_check = false ) {
		global $wpdb, $sm_skip_content_check;

		if ( null === $post ) {
			$post = get_post( $post_ID );
		}

		if ( 'wpfc_sermon' !== $post->post_type ) {
			return;
		}

		if ( ! $skip_check ) {
			if ( defined( 'SM_SAVING_POST' ) ) {
				return;
			} else {
				define( 'SM_SAVING_POST', 1 );
			}
		}

		$content       = '';
		// $bible_passage = get_post_meta( $post_ID, 'bible_passage', true );
		// $has_preachers = has_term( '', 'wpfc_preacher', $post );
		// $has_series    = has_term( '', 'wpfc_sermon_series', $post );

		// if ( $bible_passage ) {
		// 	$content .= __( 'Bible Text:', 'sermon-manager-for-wordpress' ) . ' ' . $bible_passage;
		// }

		// if ( $has_preachers ) {
		// 	if ( $bible_passage ) {
		// 		$content .= ' | ';
		// 	}

		// 	$content .= sm_get_taxonomy_field( 'wpfc_preacher', 'singular_name' ) . ': ';
		// 	$content .= strip_tags( get_the_term_list( $post->ID, 'wpfc_preacher', '', ', ', '' ) );
		// }

		// if ( $has_series ) {
		// 	if ( $has_preachers ) {
		// 		$content .= ' | ';
		// 	}
		// 	$content .= strip_tags( get_the_term_list( $post->ID, 'wpfc_sermon_series', __( 'Series:', 'sermon-manager-for-wordpress' ) . ' ', ', ', '' ) );
		// }

		$description = strip_tags( trim( get_post_meta( $post->ID, 'sermon_description', true ) ) );

		if ( '' !== $description ) {
			$content .=  $description;
		}

		/**
		 * Allows to modify sermon content that will be saved as "post_content".
		 *
		 * @param string  $content    Textual content (no HTML).
		 * @param int     $post_ID    ID of the sermon.
		 * @param WP_Post $post       Sermon post object.
		 * @param bool    $skip_check Basically, a way to identify if the function is being executed from the update function or not.
		 *
		 * @since 2.11.0
		 */
		$content = apply_filters( 'sm_sermon_post_content', $content, $post_ID, $post, $skip_check );
		$content = apply_filters( "sm_sermon_post_content_$post_ID", $content, $post_ID, $post, $skip_check );

		if ( ! $sm_skip_content_check ) {
			if ( ! SermonManager::getOption( 'post_content_enabled', 1 ) ) {
				$content = '';
			}
		}

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $wpdb->posts SET `post_content` = %s WHERE `ID` = %s",
				array(
					$content,
					$post_ID,
				)
			)
		);
	}

	/**
	 * Fixes Sermons ordering. Uses `sermon_date` meta instead of sermon's published date.
	 *
	 * @param WP_Query $query The query.
	 *
	 * @return void
	 */
	public static function fix_sermons_ordering( $query ) {
		if ( ! is_admin() && ( $query->is_main_query() ) ) {
			if ( is_post_type_archive( 'wpfc_sermon' ) || is_tax( sm_get_taxonomies() ) ) {
				$orderby = SermonManager::getOption( 'archive_orderby' );
				$order   = SermonManager::getOption( 'archive_order' );

				switch ( $orderby ) {
					case 'date_preached':
						$query->set( 'meta_key', 'sermon_date' );
						$query->set( 'meta_value_num', time() );
						$query->set( 'meta_compare', '<=' );
						$query->set( 'orderby', 'meta_value_num' );
						break;
					case 'date_published':
						$query->set( 'orderby', 'date' );
						break;
					case 'title':
					case 'random':
					case 'id':
						$query->set( 'orderby', $orderby );
						break;
				}

				$query->set( 'order', strtoupper( $order ) );

				$query->set( 'posts_per_page', SermonManager::getOption( 'sermon_count', get_option( 'posts_per_page' ) ) );

				/**
				 * Allows to filter the sermon query.
				 *
				 * @param WP_Query $query The query.
				 *
				 * @since 2.13.5
				 */
				do_action( 'sm_query', $query );
			}
		}
	}

	/**
	 * Load plugin translations
	 *
	 * @return void
	 */
	public static function load_translations() {
		load_plugin_textdomain( 'sermon-manager-for-wordpress', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Enqueue Sermon Manager scripts and styles
	 *
	 * @return void
	 */
	public static function enqueue_scripts_styles() {
		if ( defined( 'SM_SCRIPTS_STYLES_ENQUEUED' ) ) {
			return;
		}

		if ( ! ( defined( 'SM_ENQUEUE_SCRIPTS_STYLES' ) || 'wpfc_sermon' === get_post_type() || is_post_type_archive( 'wpfc_sermon' ) )
		) {
			return;
		}

		if ( ! SermonManager::getOption( 'css' ) ) {
			wp_enqueue_style( 'wpfc-sm-styles' );
			wp_enqueue_style( 'dashicons' );

			// Load theme-specific styling, if there's any.
			wp_enqueue_style( 'wpfc-sm-style-' . get_option( 'template' ) );

			do_action( 'sm_enqueue_css' );
			do_action( 'sm_enqueue_js' );
		}

		// Load top theme-specific styling, if there's any.
		wp_enqueue_style( 'wpfc-sm-style-theme' );

		switch ( SermonManager::getOption( 'player' ) ) {
			case 'mediaelement':
				wp_enqueue_style( 'wp-mediaelement' );
				wp_enqueue_script( 'wp-mediaelement' );

				break;
			case 'plyr':
				wp_localize_script(
					'wpfc-sm-plyr-loader',
					'sm_data',
					array(
						'debug'                    => defined( 'WP_DEBUG' ) && WP_DEBUG === true ? 1 : 0,
						'use_native_player_safari' => SermonManager::getOption( 'use_native_player_safari', false ) ? 1 : 0,
					)
				);

				if ( SermonManager::getOption( 'disable_cloudflare_plyr' ) ) {
					global $wp_scripts;

					$GLOBALS['sm_plyr_scripts'] = array(
						'wpfc-sm-plyr-loader' => $wp_scripts->registered['wpfc-sm-plyr-loader'],
						'wpfc-sm-plyr'        => $wp_scripts->registered['wpfc-sm-plyr'],
					);

					add_action( 'wp_print_scripts', array( __CLASS__, 'maybe_print_cloudflare_plyr' ) );
					add_action( 'wp_print_footer_scripts', array( __CLASS__, 'maybe_print_cloudflare_plyr' ) );
				} else {
					wp_enqueue_script( 'wpfc-sm-plyr' );
					wp_enqueue_script( 'wpfc-sm-plyr-loader' );
				}

				wp_enqueue_style( 'wpfc-sm-plyr-css' );

				break;
		}

		if ( ! apply_filters( 'verse_popup_disable', SermonManager::getOption( 'verse_popup' ) ) ) { // phpcs:ignore
			wp_enqueue_script( 'wpfc-sm-verse-script' );

			// Get options for JS.
			$bible_version  = SermonManager::getOption( 'verse_bible_version' );
			$bible_versions = array(
				'LBLA95',
				'NBLH',
				'NVI',
				'RVR60',
				'RVA',
			);

			if ( strpos( get_locale(), 'es_' ) === false && in_array( $bible_version, $bible_versions ) ) {
				$bible_version = 'ESV';
			}

			$verse_popup_data = array(
				'bible_version' => $bible_version,
				'language'      => strpos( get_locale(), 'es_' ) !== false ? 'es_ES' : 'en_US',
			);

			/**
			 * Allows you to filter the variables passed to the verse script.
			 *
			 * @since 2.15.9
			 */
			$verse_popup_data = apply_filters( 'sm_verse_popup_data', $verse_popup_data );

			wp_localize_script( 'wpfc-sm-verse-script', 'verse', $verse_popup_data );
		}

		// Do not enqueue twice.
		define( 'SM_SCRIPTS_STYLES_ENQUEUED', true );
	}

	/**
	 * Append the terms of Sermon Manager taxonomies to the list
	 * of sermon (post) classes generated by post_class().
	 *
	 * @param array $classes An array of existing post classes.
	 * @param array $class   An array of additional classes added to the post (not needed).
	 * @param int   $post_id The post ID.
	 *
	 * @return array Modified class list.
	 */
	public static function add_additional_sermon_classes( $classes, $class, $post_id ) {
		if ( 'wpfc_sermon' !== get_post_type( $post_id ) ) {
			return $classes;
		}

		// Disable PHPCS warning.
		unset( $class );

		$additional_classes = array();

		$taxonomies = array(
			'wpfc_preacher',
			'wpfc_sermon_series',
			'wpfc_bible_book',
			'wpfc_sermon_topics',
		);

		foreach ( $taxonomies as $taxonomy ) {
			foreach ( (array) get_the_terms( $post_id, $taxonomy ) as $term ) {
				if ( empty( $term->slug ) ) {
					continue;
				}

				if ( ! in_array( $term->slug, $classes ) ) {
					$term_class = sanitize_html_class( $term->slug, $term->term_id );

					if ( is_numeric( $term_class ) || ! trim( $term_class, '-' ) ) {
						$term_class = $term->term_id;
					}

					$additional_classes[] = esc_attr( sanitize_html_class( $taxonomy . '-' . $term_class, $taxonomy . '-' . $term->term_id ) );
				}
			}
		}

		if ( is_archive() ) {
			$additional_classes[] = 'wpfc-sermon';
		} else {
			$additional_classes[] = 'wpfc-sermon-single';
		}

		/**
		 * Allows filtering of additional Sermon Manager classes
		 *
		 * @param array $classes The array of added classes
		 *
		 * @since 2.12.0
		 */
		$additional_classes = apply_filters( 'wpfc_sermon_classes', $additional_classes, $classes, $post_id );

		return array_merge( $additional_classes, $classes );
	}

	/**
	 * Add images sizes for Series and Speakers
	 *
	 * @return void
	 */
	public static function add_image_sizes() {
		if ( function_exists( 'add_image_size' ) ) {
			add_image_size( 'sermon_small', 75, 75, true );
			add_image_size( 'sermon_medium', 300, 200, true );
			add_image_size( 'sermon_wide', 940, 350, true );
		}
	}

	/**
	 * Workaround for Cloudflare caching.
	 *
	 * @since 2.15.2
	 */
	public static function maybe_print_cloudflare_plyr() {
		if ( defined( 'SM_CLOUDFLARE_DONE' ) ) {
			return;
		}

		if ( ! isset( $GLOBALS['sm_plyr_scripts'] ) ) {
			return;
		}

		foreach ( $GLOBALS['sm_plyr_scripts'] as $script ) {
			echo '<script type="text/javascript" data-cfasync="false" src="' . $script->src . '"></script>';

			if ( ! empty( $script->extra ) ) {
				/* @noinspection BadExpressionStatementJS */
				printf( "<script type='text/javascript'>\n%s\n</script>\n", $script->extra['data'] );
			}
		}

		define( 'SM_CLOUDFLARE_DONE', true );
	}

	/**
	 * Registers all of the scripts and styles, without enqueueing them.
	 *
	 * It will be removed in future in favor of Script_Manager class.
	 *
	 * @since 2.15.7
	 */
	public static function register_scripts_styles() {
		wp_register_script( 'wpfc-sm-fb-player', SM_URL . 'assets/vendor/js/facebook-video.js', array(), SM_VERSION );
		wp_register_script( 'wpfc-sm-plyr', SM_URL . 'assets/vendor/js/plyr.polyfilled' . ( ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) ? '' : '.min' ) . '.js', array(), '3.4.7', SermonManager::getOption( 'player_js_footer' ) );
		wp_register_script( 'wpfc-sm-plyr-loader', SM_URL . 'assets/js/plyr' . ( ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) ? '' : '.min' ) . '.js', array( 'wpfc-sm-plyr' ), SM_VERSION );
		wp_register_script( 'wpfc-sm-verse-script', SM_URL . 'assets/vendor/js/verse.js', array(), SM_VERSION );
		wp_register_style( 'wpfc-sm-styles', SM_URL . 'assets/css/sermon.min.css', array(), SM_VERSION );
		wp_register_style( 'wpfc-sm-plyr-css', SM_URL . 'assets/vendor/css/plyr.min.css', array(), '3.4.7' );

		// Register theme-specific styling, if there's any.
		if ( file_exists( SM_PATH . 'assets/css/theme-specific/' . get_option( 'template' ) . '.css' ) ) {
			wp_register_style( 'wpfc-sm-style-' . get_option( 'template' ), SM_URL . 'assets/css/theme-specific/' . get_option( 'template' ) . '.css', array( 'wpfc-sm-styles' ), SM_VERSION );
		}

		// Register top theme-specific styling, if there's any.
		if ( file_exists( get_stylesheet_directory() . '/sermon.css' ) ) {
			wp_register_style( 'wpfc-sm-style-theme', get_stylesheet_directory_uri() . '/sermon.css', array( 'wpfc-sm-styles' ), SM_VERSION );
		}
		
	}

	/**
	 * Executes required actions.
	 *
	 * @since 2.15.13
	 */
	protected function _init_actions() {
		// Load translations.
		add_action( 'after_setup_theme', array( $this, 'load_translations' ) );
		// Register & enqueue scripts & styles.
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );
		add_action( 'wp_footer', array( $this, 'register_scripts_styles' ) );
		add_action( 'wp_footer', array( $this, 'enqueue_scripts_styles' ) );
		// Append custom classes to individual sermons.
		add_filter( 'post_class', array( $this, 'add_additional_sermon_classes' ), 10, 3 );
		// Add Sermon Manager image sizes.
		add_action( 'after_setup_theme', array( $this, 'add_image_sizes' ) );
		// Fix Sermon ordering.
		add_action( 'pre_get_posts', array( $this, 'fix_sermons_ordering' ), 90 );
		// No idea... better not touch it for now.
		add_filter( 'sermon-images-disable-public-css', '__return_true' );
		// Attach to fix WP dates.
		SM_Dates_WP::hook();
		// Render sermon HTML for search compatibility.
		add_action( 'wp_insert_post', array( $this, 'render_sermon_into_content' ), 10, 2 );
		// Remove SB Help from SM pages, since it messes up the formatting.
		add_action(
			'current_screen',
			function () {
				$screen    = get_current_screen();
				$screen_id = $screen ? $screen->id : '';

				if ( in_array( $screen_id, sm_get_screen_ids() ) ) {
					remove_action( 'current_screen', 'sb_add_contextual_help' );
				}
			},
			0
		);
		// Allow usage of remote URLs for attachments (used for images imported from SE).
		add_filter(
			'wp_get_attachment_url',
			function ( $url, $attachment_id ) {
				$db_url = get_post_meta( $attachment_id, '_wp_attached_file', true );

				if ( $db_url && parse_url( $db_url, PHP_URL_SCHEME ) !== null ) {
					return $db_url;
				}

				return $url;
			},
			10,
			2
		);
		// Allows reimport after sermon deletion.
		add_action(
			'before_delete_post',
			function ( $id ) {
				global $post_type;

				if ( 'wpfc_sermon' !== $post_type ) {
					return;
				}

				$sermons_se = get_option( '_sm_import_se_messages' );
				$sermons_sb = get_option( '_sm_import_sb_messages' );

				$sermon_messages = array( $sermons_se, $sermons_sb );

				foreach ( $sermon_messages as $offset0 => $sermons_array ) {
					if(count($sermons_array)>0){
						foreach ( $sermons_array as $offset1 => $value ) {
							if ( $value['new_id'] == $id ) {
								unset( $sermons_array[ $offset1 ] );
								update_option( 0 === $offset0 ? '_sm_import_se_messages' : '_sm_import_sb_messages', $sermons_array );

								return;
							}
						}
					}
					
				}
			}
		);

		// Temporary hook for importing until API is properly done.
		add_action(
			'admin_init',
			function () {
				if ( isset( $_GET['page'] ) && 'sm-import-export' === $_GET['page'] ) {
					if ( isset( $_GET['doimport'] ) ) {
						$class = null;

						switch ( $_GET['doimport'] ) {
							case 'sb':
								$class = new SM_Import_SB();
								break;
							case 'se':
								$class = new SM_Import_SE();
								break;
							case 'exsm':
								$class = new SM_Export_SM();
								$class->sermon_export_wp();
								die();
								break;
							case 'sm':
								$class = new SM_Import_SM();
								break;
						}

						if ( null !== $class ) {
							$class->import();
							add_action(
								'admin_notices',
								function () {
									if ( ! ! SermonManager::getOption( 'debug_import' ) ) :
										?>
										<div class="notice notice-info">
											<p>Debug info:</p>
											<pre><?php echo get_option( 'sm_last_import_info' ) ?: 'No data available.'; ?></pre>
										</div>
									<?php endif; ?>

									<div class="notice notice-success">
										<p><?php _e( 'Import done!', 'sermon-manager-for-wordpress' ); ?></p>
									</div>
									<?php
								}
							);
						}
					}
				}
			}
		);

		// Execute specific update function on request.
		add_action(
			'sm_admin_settings_sanitize_option_execute_specific_unexecuted_function',
			function ( $value ) {
				if ( '' !== $value ) {
					if ( ! function_exists( $value ) ) {
						require_once SM_PATH . 'includes/sm-update-functions.php';
					}

					call_user_func( $value );

					?>
					<div class="notice notice-success">
						<p><code><?php echo $value; ?></code> executed.</p>
					</div>
					<?php
				}

				return '';
			}
		);

		// Clear all transients.
		add_action(
			'sm_admin_settings_sanitize_option_clear_transients',
			function ( $value ) {
				if ( '' !== $value ) {
					global $wpdb;

					$sql = 'DELETE FROM ' . $wpdb->options . ' WHERE ( `option_name` LIKE "_transient_%" OR `option_name` LIKE "transient_%")';
					$wpdb->query( $sql );

					?>
					<div class="notice notice-success">
						<p>Removed <?php echo $wpdb->rows_affected; ?> transient fields.</p>
					</div>
					<?php
				}

				return '';
			}
		);

		// Execute all non-executed update functions on request.
		add_action(
			'sm_admin_settings_sanitize_option_execute_unexecuted_functions',
			function ( $value ) {
				if ( 'yes' === $value ) {
					foreach ( \SM_Install::$db_updates as $version => $functions ) {
						foreach ( $functions as $function ) {
							if ( ! get_option( 'wp_sm_updater_' . $function . '_done', 0 ) ) {
								$at_least_one = true;

								if ( ! function_exists( $function ) ) {
									require_once SM_PATH . 'includes/sm-update-functions.php';
								}

								call_user_func( $function );

								?>
								<div class="notice notice-success">
									<p><code><?php echo $function; ?></code> executed.</p>
								</div>
								<?php
							}
						}
					}

					if ( ! isset( $at_least_one ) ) {
						?>
						<div class="notice notice-success">
							<p>All update functions have already been executed.</p>
						</div>
						<?php
					}
				}

				return 'no';
			}
		);

		add_action(
			'sm_admin_settings_sanitize_option_post_content_enabled',
			function ( $value ) {
				$value = intval( $value );

				if ( $value >= 10 ) {
					global $wpdb, $sm_skip_content_check;

					$sm_skip_content_check = true;

					$sm = SermonManager::get_instance();

					// All sermons.
					$sermons = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE `post_type` = %s", 'wpfc_sermon' ) );

					foreach ( $sermons as $sermon ) {
						$sermon_id = $sermon->ID;

						if ( 11 === $value ) {
							$sm->render_sermon_into_content( $sermon_id, null, true );
						} else {
							$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET `post_content` = '' WHERE `ID` = %d", $sermon_id ) );
						}
					}

					$sm_skip_content_check = false;

					$value = intval( substr( $value, 1 ) );
				}

				return $value;
			}
		);

		// Remove audio ID if it's not needed.
		add_action(
			'save_post_wpfc_sermon',
			function ( $post_ID, $post, $update ) {
				error_log("1888");			
				error_log(print_r($_POST,true));			
				// error_log(print_r($_POST),true);
				if ( ! isset( $_POST['sermon_audio_id'] ) && ! isset( $_POST['sermon_audio'] ) ) {
					return;
				}

				$audio_id  = sanitize_text_field($_POST['sermon_audio_id']);
				$audio_url = sanitize_text_field($_POST['sermon_audio']);

				// Attempt to get remote file size.
				if ( $audio_url && ! $audio_id ) {
					// Put our options as default (sorry).
					stream_context_set_default(
						array(
							'http' => array(
								'method'  => 'HEAD',
								'timeout' => 2,
							),
						)
					);

					// Do the request.
					$head = array_change_key_case( get_headers( $audio_url, 1 ) );

					if ( $head && isset( $head['content-length'] ) ) {
						update_post_meta( $post_ID, '_wpfc_sermon_size', $head['content-length'] ?: 0 );
					}
				}

				if ( ! $audio_id ) {
					return;
				}

				$parsed_audio_url   = parse_url( $audio_url, PHP_URL_HOST );
				$parsed_website_url = parse_url( home_url(), PHP_URL_HOST );

				if ( $parsed_audio_url !== $parsed_website_url ) {
					$audio_id = '';
					update_post_meta( $post_ID, 'sermon_audio_id', $audio_id );
				}

				// Attempt to get audio file duration.
				if ( $audio_id ) {
					$the_file = wp_get_attachment_metadata( $audio_id );

					if ( $the_file ) {
						if ( isset( $the_file['length'] ) ) {
							$length                         = date( 'H:i:s', $the_file['length'] );
							$_POST['_wpfc_sermon_duration'] = $length;
							update_post_meta( $post_ID, '_wpfc_sermon_duration', $length );
						}

						if ( isset( $the_file['filesize'] ) ) {
							$_POST['_wpfc_sermon_size'] = $the_file['filesize'];
							update_post_meta( $post_ID, '_wpfc_sermon_size', $the_file['filesize'] );
						}
					}
				}
			},
			40,
			3
		);

		// Allows user to not include themselves into views count.
		add_filter(
			'sm_views_add_view',
			function () {
				if ( ! SermonManager::getOption( 'enable_views_count_logged_in', true ) ) {
					if ( is_user_logged_in() && ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) ) {
						return false;
					}
				}

				return true;
			}
		);

		// Add a notice if output buffering is disabled.
		add_action(
			'admin_notices',
			function () {
				if ( ! SM_OB_ENABLED ) {
					?>
					<div class="notice notice-wpfc-php notice-warning">
						<p>
							<?php
							// translators: %s: The plugin name. Effectively "<strong>Sermon Manager</strong>".
							echo wp_sprintf( __( '%s requires output buffering to be turned on to display content. It is currently off. Please enable it or contact your hosting provider for help. Most of plugin functionality will be disabled until output buffering is enabled.', 'sermon-manager-for-wordpress' ), '<strong>' . __( 'Sermon Manager', 'sermon-manager-for-wordpress' ) . '</strong>' );
							?>
						</p>
					</div>
					<?php
				}
			}
		);

		add_action(
			'wp_ajax_sm_settings_get_select_data',
			function () {
				echo json_encode( apply_filters( 'sm_settings_get_select_data', array(), sanitize_text_field($_POST['category']), sanitize_text_field($_POST['podcast_id']), sanitize_text_field($_POST['option_id']) ) );

				wp_die();
			}
		);
	}
}

// Initialize Sermon Manager.
SermonManager::get_instance();

// Fix shortcode pagination.
add_filter(
	'redirect_canonical',
	function ( $redirect_url ) {
		global $wp_query;

		if ( get_query_var( 'paged' ) && $wp_query->post && false !== strpos( $wp_query->post->post_content, '[sermons' ) ) {
			return false;
		}

		return $redirect_url;
	}
);

add_action("edit_post","update_multiple_sermon_meta_data");
function update_multiple_sermon_meta_data($post_ID){
	$notes = get_wpfc_sermon_meta( 'sermon_notes' );	
	if(is_array($notes)){
		if(count($notes)>0){
			update_post_meta($post_ID, 'sermon_notes_multiple', $notes );
			update_post_meta($post_ID, 'sermon_notes', '' );
		}
	}
	$bulletin = get_wpfc_sermon_meta( 'sermon_bulletin' );	
	if(is_array($bulletin)){
		if(count($bulletin)>0){
			update_post_meta($post_ID, 'sermon_bulletin_multiple', $bulletin );
			update_post_meta($post_ID, 'sermon_bulletin', '' );			
		}
	}
	return;
}
add_action( 'wp', 'on_post_view_update_multiple_sermon_meta_data' );
function on_post_view_update_multiple_sermon_meta_data()
{
    if ('wpfc_sermon' === get_post_type() && is_singular()){
    	update_multiple_sermon_meta_data(get_the_ID());
    	
    }
}
