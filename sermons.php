<?php
/**
 * Plugin Name: Sermon Manager for WordPress
 * Plugin URI: https://www.wpforchurch.com/products/sermon-manager-for-wordpress/
 * Description: Add audio and video sermons, manage speakers, series, and more.
 * Version: 2.12.5
 * Author: WP for Church
 * Author URI: https://www.wpforchurch.com/
 * Requires at least: 4.5
 * Tested up to: 4.9
 *
 * Text Domain: sermon-manager-for-wordpress
 * Domain Path: /languages/
 */

defined( 'ABSPATH' ) or die;

// All files must be PHP 5.3 compatible

// Check the PHP version
if ( version_compare( PHP_VERSION, '5.3.0', '<' ) ) {
	if ( is_admin() && ! get_option( 'dismissed-render_php_version_warning', 0 ) ) {
		add_action( 'admin_notices', 'sm_render_php_version_error' );
	}

	function sm_render_php_version_error() {
		?>
        <div class="notice notice-wpfc-php notice-error">
            <p>
				<?= // translators: %1$s current PHP version, see msgid "PHP %s", effectively <strong>PHP %s</strong>
				// translators: %2$s required PHP version, see msgid "PHP %s", effectively <strong>PHP %s</strong>
				wp_sprintf( esc_html__( 'You are running %1$s, but Sermon Manager requires at least %2$s.', 'sermon-manager-for-wordpress' ), '<strong>' . wp_sprintf( esc_html__( 'PHP %s', 'sermon-manager-for-wordpress' ), PHP_VERSION ) . '</strong>', '<strong>' . wp_sprintf( esc_html__( 'PHP %s', 'sermon-manager-for-wordpress' ), '5.3.0' ) . '</strong>' ); ?>
            </p>
        </div>
		<?php
	}

	return;
}

class SermonManager {

	/**
	 * Refers to a single instance of this class.
	 */

	private static $instance = null;

	/**
	 * Construct
	 */
	public function __construct() {
		// Define constants (PATH and URL are with a trailing slash)
		define( 'SM_PLUGIN_FILE', __FILE__ );
		define( 'SM_BASENAME', plugin_basename( __FILE__ ) );
		define( 'SM_PATH', plugin_dir_path( __FILE__ ) );
		define( 'SM_URL', plugin_dir_url( __FILE__ ) );
		define( 'SM_VERSION', preg_match( '/^.*Version: (.*)$/m', file_get_contents( __FILE__ ), $version ) ? trim( $version[1] ) : 'N/A' );

		do_action( 'sm_before_plugin_load' );

		if ( version_compare( PHP_VERSION, '5.6.0', '<' ) ) {
			if ( is_admin() && ! get_option( 'dismissed-render_php_version_warning', 0 ) ) {
				add_action( 'admin_notices', array( $this, 'render_php_version_warning' ) );
				add_action( 'admin_enqueue_scripts', function () {
					wp_enqueue_script( 'wpfc-php-notice-handler', SM_URL . 'assets/js/admin/dismiss-php.js', array(), SM_VERSION );
				} );
			}
		}

		// Include required items
		$this->_includes();

		// load translations
		add_action( 'after_setup_theme', array( $this, 'load_translations' ) );
		// enqueue scripts & styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );
		add_action( 'wp_footer', array( $this, 'enqueue_scripts_styles' ) );
		// Append custom classes to individual sermons
		add_filter( 'post_class', array( $this, 'add_additional_sermon_classes' ), 10, 3 );
		// Add Sermon Manager image sizes
		add_action( 'after_setup_theme', array( $this, 'add_image_sizes' ) );
		// Fix Sermon ordering
		add_action( 'pre_get_posts', array( $this, 'fix_sermons_ordering' ), 90 );
		// no idea... better not touch it for now.
		add_filter( 'sermon-images-disable-public-css', '__return_true' );
		// Handler for dismissing PHP warning notice
		add_action( 'wp_ajax_wpfc_php_notice_handler', array( $this, 'php_notice_handler' ) );
		// Attach to fix WP dates
		SM_Dates_WP::hook();
		// Render sermon HTML for search compatibility
		add_action( 'wp_insert_post', array( $this, 'render_sermon_into_content' ), 10, 2 );
		// Remove SB Help from SM pages, since it messes up the formatting
		add_action( 'contextual_help', function () {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			if ( in_array( $screen_id, sm_get_screen_ids() ) ) {
				remove_action( 'contextual_help', 'sb_add_contextual_help' );
			}
		}, 0 );
		// Allow usage of remote URLs for attachments (used for images imported from SE)
		add_filter( 'wp_get_attachment_url', function ( $url, $attachment_id ) {
			if ( ( $db_url = get_post_meta( $attachment_id, '_wp_attached_file', true ) ) && parse_url( $db_url, PHP_URL_SCHEME ) !== null ) {
				return $db_url;
			}

			return $url;
		}, 10, 2 );
		// Allows reimport after sermon deletion
		add_action( 'before_delete_post', function ( $id ) {
			if ( $GLOBALS['post_type'] !== 'wpfc_sermon' ) {
				return;
			}

			$sermons_se = get_option( '_sm_import_se_messages' );
			$sermons_sb = get_option( '_sm_import_sb_messages' );

			$sermon_messages = array( $sermons_se, $sermons_sb );

			foreach ( $sermon_messages as $offset0 => &$sermons_array ) {
				foreach ( $sermons_array as $offset1 => $value ) {
					if ( $value['new_id'] == $id ) {
						unset( $sermons_array[ $offset1 ] );
						update_option( $offset0 === 0 ? '_sm_import_se_messages' : '_sm_import_sb_messages', $sermons_array );

						return;
					}
				}
			}
		} );


		// temporary hook for importing until API is properly done
		add_action( 'admin_init', function () {
			if ( isset( $_GET['page'] ) && $_GET['page'] === 'sm-import-export' ) {
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

					if ( $class !== null ) {
						$class->import();
						add_action( 'admin_notices', function () {
							if ( ! ! \SermonManager::getOption( 'debug_import' ) ) : ?>
                                <div class="notice notice-info">
                                    <p>Debug info:</p>
                                    <pre><?= get_option( 'sm_last_import_info' ) ?: 'No data available.'; ?></pre>
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

		// execute specific update function on request
		add_action( 'sm_admin_settings_sanitize_option_execute_specific_unexecuted_function', function ( $value ) {
			if ( $value !== '' ) {
				if ( ! function_exists( $value ) ) {
					require_once SM_PATH . 'includes/sm-update-functions.php';
				}

				call_user_func( $value );

				?>
                <div class="notice notice-success">
                    <p><code><?= $value ?></code> executed.</p>
                </div>
				<?php
			}

			return '';
		} );

		// execute all non-executed update functions on request
		add_action( 'sm_admin_settings_sanitize_option_execute_unexecuted_functions', function ( $value ) {
			if ( $value === 'yes' ) {
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
                                <p><code><?= $function ?></code> executed.</p>
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

		do_action('sm_after_plugin_load');

		add_action( 'sm_admin_settings_sanitize_option_post_content_enabled', function ( $value ) {
			$value = intval( $value );

			if ( $value >= 10 ) {
				global $wpdb, $skip_content_check;

				$skip_content_check = true;

				$sm = SermonManager::get_instance();

				// All sermons
				$sermons = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s", 'wpfc_sermon' ) );

				foreach ( $sermons as $sermon ) {
					$sermon_ID = $sermon->ID;

					if ( $value === 11 ) {
						$sm->render_sermon_into_content( $sermon_ID, null, true );
					} else {
						$wpdb->query( "UPDATE $wpdb->posts SET `post_content` = '' WHERE `ID` = $sermon_ID" );
					}
				}

				$skip_content_check = false;

				$value = intval( substr( $value, 1 ) );
			}

			return $value;
		} );

		add_action( 'sm_admin_settings_sanitize_option_post_excerpt_enabled', function ( $value ) {
			$value = intval( $value );

			if ( $value >= 10 ) {
				global $wpdb, $skip_excerpt_check;

				$skip_excerpt_check = true;

				$sm = SermonManager::get_instance();

				// All sermons
				$sermons = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s", 'wpfc_sermon' ) );

				foreach ( $sermons as $sermon ) {
					$sermon_ID = $sermon->ID;

					if ( $value === 11 ) {
						$sm->render_sermon_into_content( $sermon_ID, null, true );
					} else {
						$wpdb->query( "UPDATE $wpdb->posts SET `post_excerpt` = '' WHERE `ID` = $sermon_ID" );
					}
				}

				$skip_excerpt_check = false;

				$value = intval( substr( $value, 1 ) );
			}

			return $value;
		} );
	}

	/**
	 * Include Sermon Manager files
	 *
	 * @return void
	 */
	private function _includes() {
		/**
		 * Files to include on frontend and backend
		 */
		$includes = array(
			'includes/class-sm-autoloader.php', // Autoloader
			'includes/sm-core-functions.php', // Core Sermon Manager functions
			'includes/class-sm-dates.php', // Dates operations
			'includes/class-sm-dates-wp.php', // Attach to WP filters
			'includes/class-sm-api.php', // API
			'includes/class-sm-post-types.php', // Register post type, taxonomies, etc
			'includes/class-sm-install.php', // Install and update functions
			'includes/sm-deprecated-functions.php', // Deprecated SM functions
			'includes/sm-formatting-functions.php', // Data formatting
			'includes/sm-cmb-functions.php', // CMB2 Meta Fields functions
			'includes/taxonomy-images/taxonomy-images.php', // Images for Custom Taxonomies
			'includes/entry-views.php', // Entry Views Tracking
			'includes/shortcodes.php', // Shortcodes
			'includes/widgets.php', // Widgets
			'includes/sm-template-functions.php', // Template functions
			'includes/podcast-functions.php', // Podcast Functions
			'includes/helper-functions.php', // Global Helper Functions
		);

		/**
		 * Admin only includes
		 */
		$admin_includes = array(
			'includes/admin/class-sm-admin.php', // Admin init class
			'includes/admin-functions.php', // General Admin area functions - todo: refactor before 2.9
			'includes/CMB2/init.php', // Metaboxes
		);

		// Load files
		foreach ( $includes as $file ) {
			if ( file_exists( SM_PATH . $file ) ) {
				require_once SM_PATH . $file;
			}
		}

		// Load admin files
		if ( is_admin() ) {
			foreach ( $admin_includes as $file ) {
				if ( file_exists( SM_PATH . $file ) ) {
					require_once SM_PATH . $file;
				}
			}
		}
	}

	/**
	 * Instead of loading options variable each time in every code snippet, let's have it in one place.
	 *
	 * @param string $name    Option name
	 * @param string $default Default value to return if option is not set (defaults to empty string)
	 *
	 * @return mixed Returns option value or an empty string if it doesn't exist. Just like WP does.
	 */
	public static function getOption( $name = '', $default = '' ) {
		if ( ! class_exists( 'SM_Admin_Settings' ) ) {
			include_once 'includes/admin/class-sm-admin-settings.php';
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
	 * Saves whole Sermon HTML markup into post content for better search compatibility
	 *
	 * @param int     $post_ID
	 * @param WP_Post $post       Post object
	 * @param bool    $skip_check Disables check of "SM_SAVING_POST" constant
	 *
	 * @since 2.8
	 */
	public function render_sermon_into_content( $post_ID = 0, $post = null, $skip_check = false ) {
		global $wpdb, $skip_excerpt_check, $skip_content_check;

		if ( $post === null ) {
			$post = get_post( $post_ID );
		}

		if ( $post->post_type !== 'wpfc_sermon' ) {
			return;
		}

		if ( ! $skip_check ) {
			if ( defined( 'SM_SAVING_POST' ) ) {
				return;
			} else {
				define( 'SM_SAVING_POST', 1 );
			}
		}

		$content = '';

		if ( $bible_passage = get_post_meta( $post_ID, 'bible_passage', true ) ) {
			$content .= __( 'Bible Text:', 'sermon-manager-for-wordpress' ) . ' ' . $bible_passage;
		}

		if ( $has_preachers = has_term( '', 'wpfc_preacher', $post ) ) {
			if ( $bible_passage ) {
				$content .= ' | ';
			}

			$content .= ( \SermonManager::getOption( 'preacher_label', '' ) ? \SermonManager::getOption( 'preacher_label', 'Preacher' ) . ':' : __( 'Preacher:', 'sermon-manager-for-wordpress' ) ) . ' ';
			$content .= strip_tags( get_the_term_list( $post->ID, 'wpfc_preacher', '', ', ', '' ) );
		}

		if ( $has_series = has_term( '', 'wpfc_sermon_series', $post ) ) {
			if ( $has_preachers ) {
				$content .= ' | ';
			}
			$content .= strip_tags( get_the_term_list( $post->ID, 'wpfc_sermon_series', __( 'Series:', 'sermon-manager-for-wordpress' ) . ' ', ', ', '' ) );
		}

		$description = strip_tags( trim( get_post_meta( $post->ID, 'sermon_description', true ) ) );

		if ( $description !== '' ) {
			$content .= PHP_EOL . PHP_EOL;
			$content .= $description;
		}

		/**
		 * Allows to modify sermon content that will be saved as "post_content"
		 *
		 * @param string  $content    Textual content (no HTML)
		 * @param int     $post_ID    ID of the sermon
		 * @param WP_Post $post       Sermon post object
		 * @param bool    $skip_check Basically, a way to identify if the function is being
		 *                            executed from the update function or not
		 *
		 * @since 2.11.0
		 */
		$content = apply_filters( "sm_sermon_post_content", $content, $post_ID, $post, $skip_check );
		$content = apply_filters( "sm_sermon_post_content_$post_ID", $content, $post_ID, $post, $skip_check );

		$excerpt = ! $content ? '' : wp_trim_excerpt( $content );

		/**
		 * Allows to modify sermon content that will be saved as "post_excerpt"
		 *
		 * @param string  $excerpt    Textual content (no HTML), limited to 55 words by default
		 * @param int     $post_ID    ID of the sermon
		 * @param WP_Post $post       Sermon post object
		 * @param bool    $skip_check Basically, a way to identify if the function is being
		 *                            executed from the update function or not
		 *
		 * @since 2.11.0
		 */
		$excerpt = apply_filters( "sm_sermon_post_excerpt", $excerpt, $post_ID, $post, $skip_check );
		$excerpt = apply_filters( "sm_sermon_post_excerpt_$post_ID", $excerpt, $post_ID, $post, $skip_check );

		if ( ! $skip_content_check ) {
			if ( ! \SermonManager::getOption( 'post_content_enabled', 1 ) ) {
				$content = '';
			}
		}

		if ( ! $skip_excerpt_check ) {
			if ( ! \SermonManager::getOption( 'post_excerpt_enabled', 1 ) ) {
				$excerpt = '';
			}
		}

		$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET `post_content` = '%s', `post_excerpt` = '%s' WHERE `ID` = $post_ID", array(
			$content,
			$excerpt,
		) ) );
	}

	/**
	 * Fixes Sermons ordering. Uses `sermon_date` meta instead of sermon's published date
	 *
	 * @param WP_Query $query
	 *
	 * @return void
	 */
	public static function fix_sermons_ordering( $query ) {
		if ( ! is_admin() && ( $query->is_main_query() ) ) {
			if ( is_post_type_archive( 'wpfc_sermon' ) ||
			     is_tax( 'wpfc_preacher' ) ||
			     is_tax( 'wpfc_sermon_topics' ) ||
			     is_tax( 'wpfc_sermon_series' ) ||
			     is_tax( 'wpfc_bible_book' )
			) {
				$query->set( 'meta_key', 'sermon_date' );
				$query->set( 'meta_value_num', time() );
				$query->set( 'meta_compare', '<=' );
				$query->set( 'orderby', 'meta_value_num' );
				$query->set( 'order', 'DESC' );
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

		if ( ! ( defined( 'SM_ENQUEUE_SCRIPTS_STYLES' ) ||
		         'wpfc_sermon' === get_post_type() ||
		         is_post_type_archive( 'wpfc_sermon' ) )
		) {
			return;
		}

		if ( ! \SermonManager::getOption( 'css' ) ) {
			wp_enqueue_style( 'wpfc-sm-styles', SM_URL . 'assets/css/sermon.min.css', array(), SM_VERSION );
			wp_enqueue_style( 'dashicons' );

			wp_enqueue_script( 'wpfc-sm-additional_classes', SM_URL . 'assets/js/additional_classes.js', array(), SM_VERSION, true );

			// load theme-specific styling, if there's any
			if ( file_exists( SM_PATH . 'assets/css/theme-specific/' . get_option( 'template' ) . '.css' ) ) {
				wp_enqueue_style( 'wpfc-sm-style-' . get_option( 'template' ), SM_URL . 'assets/css/theme-specific/' . get_option( 'template' ) . '.css', array( 'wpfc-sm-styles' ), SM_VERSION );
			}
		}

		wp_register_script( 'wpfc-sm-fb-player', SM_URL . 'assets/js/facebook-video.js', array(), SM_VERSION );

		switch ( \SermonManager::getOption( 'player' ) ) {
			case 'mediaelement':
				wp_enqueue_style( 'wp-mediaelement' );
				wp_enqueue_script( 'wp-mediaelement' );

				break;
			case 'plyr':
				wp_enqueue_script( 'wpfc-sm-plyr', SM_URL . 'assets/js/plyr' . ( ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) ? '' : '.min' ) . '.js', array(), SM_VERSION, \SermonManager::getOption( 'player_js_footer' ) );
				wp_enqueue_style( 'wpfc-sm-plyr-css', SM_URL . 'assets/css/plyr.min.css', array(), SM_VERSION );
				wp_add_inline_script( 'wpfc-sm-plyr', "window.addEventListener('DOMContentLoaded',function(){var players=plyr.setup(document.querySelectorAll('.wpfc-sermon-player,.wpfc-sermon-video-player'),{\"debug\": " . ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ? 'true' : 'false' ) . "});for(var p in players){if(players.hasOwnProperty(p)){players[p].on('loadedmetadata ready',function(event){if(typeof this.firstChild.dataset.plyr_seek !== 'undefined'){var instance=event.detail.plyr;instance.seek(parseInt(this.firstChild.dataset.plyr_seek));}});}}})" );

				break;
		}

		if ( ! \SermonManager::getOption( 'verse_popup' ) ) {
			wp_enqueue_script( 'wpfc-sm-verse-script', SM_URL . 'assets/js/verse.js', array(), SM_VERSION );

			// get options for JS
			$bible_version = \SermonManager::getOption( 'verse_bible_version' );

			if ( strpos( get_locale(), 'es_' ) === false &&
			     in_array( $bible_version, array(
				     'LBLA95',
				     'NBLH',
				     'NVI',
				     'RVR60',
				     'RVA',
			     ) ) ) {
				$bible_version = 'ESV';
			}

			wp_localize_script( 'wpfc-sm-verse-script', 'verse', array(
				'bible_version' => $bible_version,
				'language'      => strpos( get_locale(), 'es_' ) !== false ? 'es_ES' : 'en_US',
			) );
		}

		// do not enqueue twice
		define( 'SM_SCRIPTS_STYLES_ENQUEUED', true );
	}

	/**
	 * Append the terms of Sermon Manager taxonomies to the list
	 * of sermon (post) classes generated by post_class().
	 *
	 * @param array $classes An array of existing post classes
	 * @param array $class   An array of additional classes added to the post (not needed)
	 * @param int   $post_id The post ID
	 *
	 * @return array Modified class list
	 */
	public static function add_additional_sermon_classes( $classes, $class, $post_id ) {
		if ( get_post_type( $post_id ) !== 'wpfc_sermon' ) {
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

	/**
	 * Renders the notice when the user is not using correct PHP version
	 */
	public static function render_php_version_warning() {
		?>
        <div class="notice notice-wpfc-php notice-warning is-dismissible" data-notice="render_php_version_warning">
            <p>
				<?php echo sprintf( "You are running <strong>PHP %s</strong>, but Sermon Manager recommends at least <strong>PHP %s</strong>. If you encounter issues, update PHP to a recommended version and check if they are still there.", PHP_VERSION, '5.6.0' ); ?>
            </p>
        </div>
		<?php
	}

	/**
	 * AJAX handler to store the state of dismissible notices.
	 */
	function php_notice_handler() {
		update_option( 'dismissed-' . $_POST['type'], 1 );
	}
}

if ( SermonManager::getOption( 'sm_debug' ) || ( defined( 'SM_DEBUG' ) && SM_DEBUG === true ) ) {
	error_reporting( E_ALL );
	ini_set( 'display_errors', 'On' );
}

// Initialize Sermon Manager
SermonManager::get_instance();

// Fix shortcode pagination
add_filter( 'redirect_canonical', function ( $redirect_url ) {
	global $wp_query;

	if ( get_query_var( 'paged' ) && $wp_query->post &&
	     false !== strpos( $wp_query->post->post_content, '[sermons' ) ) {
		return false;
	}

	return $redirect_url;
} );
