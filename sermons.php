<?php
/**
 * Plugin Name: Sermon Manager for WordPress
 * Plugin URI: https://www.wpforchurch.com/products/sermon-manager-for-wordpress/
 * Description: Add audio and video sermons, manage speakers, series, and more.
 * Version: 2.14.0
 * Author: WP for Church
 * Author URI: https://www.wpforchurch.com/
 * Requires at least: 4.5
 * Tested up to: 4.9
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
 * The class that is used to initialize Sermon Manager.
 *
 * @author  WP For Church
 * @package SM/Core
 * @access  public
 */
class SermonManager {

	/**
	 * Refers to a single instance of this class.
	 *
	 * @var $instance null|SermonManager The class instance.
	 */
	private static $instance = null;

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

		do_action( 'sm_before_plugin_load' );

		// Include required items.
		$this->_includes();

		// Load translations.
		add_action( 'after_setup_theme', array( $this, 'load_translations' ) );
		// Enqueue scripts & styles.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );
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
		add_action( 'contextual_help', function () {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			if ( in_array( $screen_id, sm_get_screen_ids() ) ) {
				remove_action( 'contextual_help', 'sb_add_contextual_help' );
			}
		}, 0 );
		// Allow usage of remote URLs for attachments (used for images imported from SE).
		add_filter( 'wp_get_attachment_url', function ( $url, $attachment_id ) {
			$db_url = get_post_meta( $attachment_id, '_wp_attached_file', true );

			if ( $db_url && parse_url( $db_url, PHP_URL_SCHEME ) !== null ) {
				return $db_url;
			}

			return $url;
		}, 10, 2 );
		// Allows reimport after sermon deletion.
		add_action( 'before_delete_post', function ( $id ) {
			global $post_type;

			if ( 'wpfc_sermon' !== $post_type ) {
				return;
			}

			$sermons_se = get_option( '_sm_import_se_messages' );
			$sermons_sb = get_option( '_sm_import_sb_messages' );

			$sermon_messages = array( $sermons_se, $sermons_sb );

			foreach ( $sermon_messages as $offset0 => &$sermons_array ) {
				foreach ( $sermons_array as $offset1 => $value ) {
					if ( $value['new_id'] == $id ) {
						unset( $sermons_array[ $offset1 ] );
						update_option( 0 === $offset0 ? '_sm_import_se_messages' : '_sm_import_sb_messages', $sermons_array );

						return;
					}
				}
			}
		} );

		// Temporary hook for importing until API is properly done.
		add_action( 'admin_init', function () {
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
						add_action( 'admin_notices', function () {
							if ( ! ! \SermonManager::getOption( 'debug_import' ) ) :
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
						} );
					}
				}
			}
		} );

		// Execute specific update function on request.
		add_action( 'sm_admin_settings_sanitize_option_execute_specific_unexecuted_function', function ( $value ) {
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
		} );

		// Execute all non-executed update functions on request.
		add_action( 'sm_admin_settings_sanitize_option_execute_unexecuted_functions', function ( $value ) {
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
		} );

		add_action( 'sm_admin_settings_sanitize_option_post_content_enabled', function ( $value ) {
			$value = intval( $value );

			if ( $value >= 10 ) {
				global $wpdb, $skip_content_check;

				$skip_content_check = true;

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

				$skip_content_check = false;

				$value = intval( substr( $value, 1 ) );
			}

			return $value;
		} );

		do_action( 'sm_after_plugin_load' );
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
	public static function getOption( $name = '', $default = '' ) {
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
		global $wpdb, $skip_content_check;

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
		$bible_passage = get_post_meta( $post_ID, 'bible_passage', true );
		$has_preachers = has_term( '', 'wpfc_preacher', $post );
		$has_series    = has_term( '', 'wpfc_sermon_series', $post );

		if ( $bible_passage ) {
			$content .= __( 'Bible Text:', 'sermon-manager-for-wordpress' ) . ' ' . $bible_passage;
		}

		if ( $has_preachers ) {
			if ( $bible_passage ) {
				$content .= ' | ';
			}

			$content .= ( \SermonManager::getOption( 'preacher_label', '' ) ? \SermonManager::getOption( 'preacher_label', 'Preacher' ) . ':' : __( 'Preacher:', 'sermon-manager-for-wordpress' ) ) . ' ';
			$content .= strip_tags( get_the_term_list( $post->ID, 'wpfc_preacher', '', ', ', '' ) );
		}

		if ( $has_series ) {
			if ( $has_preachers ) {
				$content .= ' | ';
			}
			$content .= strip_tags( get_the_term_list( $post->ID, 'wpfc_sermon_series', __( 'Series:', 'sermon-manager-for-wordpress' ) . ' ', ', ', '' ) );
		}

		$description = strip_tags( trim( get_post_meta( $post->ID, 'sermon_description', true ) ) );

		if ( '' !== $description ) {
			$content .= PHP_EOL . PHP_EOL;
			$content .= $description;
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

		if ( ! $skip_content_check ) {
			if ( ! \SermonManager::getOption( 'post_content_enabled', 1 ) ) {
				$content = '';
			}
		}

		$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET `post_content` = %s WHERE `ID` = %s", array(
			$content,
			$post_ID,
		) ) );
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
			if ( is_post_type_archive( array(
				'wpfc_sermon',
				'wpfc_preacher',
				'wpfc_sermon_topics',
				'wpfc_sermon_series',
				'wpfc_bible_book',
			) ) ) {
				$query->set( 'meta_key', 'sermon_date' );
				$query->set( 'meta_value_num', time() );
				$query->set( 'meta_compare', '<=' );
				$query->set( 'orderby', 'meta_value_num' );
				$query->set( 'order', 'DESC' );

				/**
				 * Allows to filter the sermon query.
				 *
				 * @since 2.13.5
				 *
				 * @param WP_Query $query The query.
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

		wp_register_script( 'wpfc-sm-fb-player', SM_URL . 'assets/vendor/js/facebook-video.js', array(), SM_VERSION );
		wp_register_script( 'wpfc-sm-plyr', SM_URL . 'assets/vendor/js/plyr.polyfilled' . ( ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) ? '' : '.min' ) . '.js', array(), SM_VERSION, \SermonManager::getOption( 'player_js_footer' ) );
		wp_register_script( 'wpfc-sm-plyr-loader', SM_URL . 'assets/js/plyr' . ( ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) ? '' : '.min' ) . '.js', array( 'wpfc-sm-plyr' ), SM_VERSION );
		wp_register_script( 'wpfc-sm-verse-script', SM_URL . 'assets/vendor/js/verse.js', array(), SM_VERSION );
		wp_register_style( 'wpfc-sm-styles', SM_URL . 'assets/css/sermon.min.css', array(), SM_VERSION );
		wp_register_style( 'wpfc-sm-plyr-css', SM_URL . 'assets/vendor/css/plyr.min.css', array(), SM_VERSION );

		if ( ! ( defined( 'SM_ENQUEUE_SCRIPTS_STYLES' ) || 'wpfc_sermon' === get_post_type() || is_post_type_archive( 'wpfc_sermon' ) )
		) {
			return;
		}

		if ( ! \SermonManager::getOption( 'css' ) ) {
			wp_enqueue_style( 'wpfc-sm-styles' );
			wp_enqueue_style( 'dashicons' );

			// Load theme-specific styling, if there's any.
			if ( file_exists( SM_PATH . 'assets/css/theme-specific/' . get_option( 'template' ) . '.css' ) ) {
				wp_enqueue_style( 'wpfc-sm-style-' . get_option( 'template' ), SM_URL . 'assets/css/theme-specific/' . get_option( 'template' ) . '.css', array( 'wpfc-sm-styles' ), SM_VERSION );
			}

			do_action( 'sm_enqueue_css' );
			do_action( 'sm_enqueue_js' );
		}

		switch ( \SermonManager::getOption( 'player' ) ) {
			case 'mediaelement':
				wp_enqueue_style( 'wp-mediaelement' );
				wp_enqueue_script( 'wp-mediaelement' );

				break;
			case 'plyr':
				wp_localize_script( 'wpfc-sm-plyr-loader', 'sm_data', array(
					'debug'                    => defined( 'WP_DEBUG' ) && WP_DEBUG === true ? 1 : 0,
					'use_native_player_safari' => \SermonManager::getOption( 'use_native_player_safari', false ) ? 1 : 0,
				) );

				wp_enqueue_script( 'wpfc-sm-plyr' );
				wp_enqueue_script( 'wpfc-sm-plyr-loader' );

				wp_enqueue_style( 'wpfc-sm-plyr-css' );

				break;
		}

		if ( ! \SermonManager::getOption( 'verse_popup' ) ) {
			wp_enqueue_script( 'wpfc-sm-verse-script' );

			// Get options for JS.
			$bible_version  = \SermonManager::getOption( 'verse_bible_version' );
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

			wp_localize_script( 'wpfc-sm-verse-script', 'verse', array(
				'bible_version' => $bible_version,
				'language'      => strpos( get_locale(), 'es_' ) !== false ? 'es_ES' : 'en_US',
			) );
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
}

// Initialize Sermon Manager.
SermonManager::get_instance();

// Fix shortcode pagination.
add_filter( 'redirect_canonical', function ( $redirect_url ) {
	global $wp_query;

	if ( get_query_var( 'paged' ) && $wp_query->post && false !== strpos( $wp_query->post->post_content, '[sermons' ) ) {
		return false;
	}

	return $redirect_url;
} );
