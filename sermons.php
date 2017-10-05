<?php
/**
 * Plugin Name: Sermon Manager for WordPress
 * Plugin URI: https://www.wpforchurch.com/products/sermon-manager-for-wordpress/
 * Description: Add audio and video sermons, manage speakers, series, and more.
 * Version: 2.8.2
 * Author: WP for Church
 * Author URI: https://www.wpforchurch.com/
 * Requires at least: 4.5
 * Tested up to: 4.8.2
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
				<?php echo sprintf( __( "You are running <strong>PHP %s</strong>, but Sermon Manager requires at least <strong>PHP %s</strong>.", 'sermon-manager-for-wordpress' ), PHP_VERSION, '5.3.0' ); ?>
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

		// Register error handlers before continuing
		/*include_once 'includes/class-sm-error-recovery.php';
		$error_recovery = new SM_Error_Recovery();
		$error_recovery->init();*/

		// Break if fatal error detected
		if ( defined( 'sm_break' ) && sm_break === true ) {
			return;
		}

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

		// Add defaults on activation
		register_activation_hook( __FILE__, array( $this, 'set_default_options' ) );

		// load translations
		add_action( 'init', array( $this, 'load_translations' ) );
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
		add_action( 'save_post_wpfc_sermon', array( $this, 'render_sermon_into_content' ), 10 );

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', function () {
				wp_enqueue_style( 'sm-icon', SM_URL . 'assets/css/admin-icon.css', array(), SM_VERSION );
			} );
		}
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
			'includes/class-sm-dates.php', // Dates operations
			'includes/class-sm-dates-wp.php', // Attach to WP filters
			'includes/class-sm-api.php', // API
			'includes/class-sm-post-types.php', // Register post type, taxonomies, etc
            'includes/class-sm-install.php', // Install and update functions
			'includes/sm-deprecated-functions.php', // Deprecated SM functions
			'includes/sm-core-functions.php', // Deprecated SM functions
			'includes/sm-legacy-php-functions.php', // Old PHP compatibility fixes
			'includes/sm-cmb-functions.php', // CMB2 Meta Fields functions
			'includes/taxonomy-images/taxonomy-images.php', // Images for Custom Taxonomies
			'includes/entry-views.php', // Entry Views Tracking
			'includes/shortcodes.php', // Shortcodes
			'includes/widgets.php', // Widgets
			'includes/template-tags.php', // Template Tags
			'includes/podcast-functions.php', // Podcast Functions
			'includes/helper-functions.php', // Global Helper Functions
		);

		/**
		 * Admin only includes
		 */
		$admin_includes = array(
			'includes/admin-functions.php', // General Admin area functions
			'includes/CMB2/init.php', // Metaboxes
			'includes/options.php', // Options Page
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
			}
		}
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
			wp_enqueue_style( 'wpfc-sm-styles', SM_URL . 'assets/css/sermon.css', array(), SM_VERSION );
			wp_enqueue_style( 'dashicons' );

			if ( ! \SermonManager::getOption( 'use_old_player' ) ) {
				wp_enqueue_script( 'wpfc-sm-plyr', SM_URL . 'assets/js/plyr.js', array(), SM_VERSION );
				wp_enqueue_style( 'wpfc-sm-plyr-css', SM_URL . 'assets/css/plyr.css', array(), SM_VERSION );
				wp_add_inline_script( 'wpfc-sm-plyr', 'window.onload=function(){plyr.setup(document.querySelectorAll(\'.wpfc-sermon-player, #wpfc_sermon audio\'));}' );
			}
		}

		if ( ! \SermonManager::getOption( 'bibly' ) ) {
			wp_enqueue_script( 'wpfc-sm-bibly-script', SM_URL . 'assets/js/bibly.min.js', array(), SM_VERSION );
			wp_enqueue_style( 'wpfc-sm-bibly-style', SM_URL . 'assets/css/bibly.min.css', array(), SM_VERSION );

			// get options for JS
			$bible_version = \SermonManager::getOption( 'bibly_version' );
			wp_localize_script( 'wpfc-sm-bibly-script', 'bibly', array( // pass WP data into JS from this point on
				'linkVersion'  => $bible_version,
				'enablePopups' => true,
				'popupVersion' => $bible_version,
			) );
		}

		// do not enqueue twice
		define( 'SM_SCRIPTS_STYLES_ENQUEUED', true );
	}

	/**
	 * Instead of loading options variable each time in every code snippet, let's have it in one place.
	 *
	 * @param string $name Option name
	 *
	 * @return mixed Returns option value or an empty string if it doesn't exist. Just like WP does.
	 */
	public static function getOption( $name = '' ) {
		$options = get_option( 'wpfc_options' );

		if ( ! empty( $options[ $name ] ) ) {
			return $options[ $name ];
		}

		return '';
	}

	/**
	 * Append the terms of Sermon Manager taxonomies to the list
	 * of sermon (post) classes generated by post_class().
	 *
	 * @param array $classes An array of existing post classes
	 * @param array $class   An array of additional classes added to the post (not needed)
	 * @param int   $ID      The post ID
	 *
	 * @return array Modified class list
	 */
	public static function add_additional_sermon_classes( $classes, $class, $ID ) {
		$taxonomies = array(
			'wpfc_preacher',
			'wpfc_sermon_series',
			'wpfc_bible_book',
			'wpfc_sermon_topics',
		);

		foreach ( $taxonomies as $taxonomy ) {
			foreach ( (array) get_the_terms( $ID, $taxonomy ) as $term ) {
				if ( empty( $term->slug ) ) {
					continue;
				}

				if ( ! in_array( $term->slug, $classes ) ) {
					$term_class = sanitize_html_class( $term->slug, $term->term_id );

					if ( is_numeric( $term_class ) || ! trim( $term_class, '-' ) ) {
						$term_class = $term->term_id;
					}

					$classes[] = esc_attr( sanitize_html_class( $taxonomy . '-' . $term_class, $taxonomy . '-' . $term->term_id ) );
				}
			}
		}

		return $classes;
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
	 * Checks if the plugin options have been set, and if they haven't, sets defaults.
	 *
	 * @return void
	 */
	public static function set_default_options() {
		if ( ! is_array( get_option( 'wpfc_options' ) ) ) {
			delete_option( 'wpfc_options' ); // just in case
			$arr = array(
				'bibly'            => '0',
				'bibly_version'    => 'KJV',
				'archive_slug'     => 'sermons',
				'archive_title'    => 'Sermons',
				'common_base_slug' => '0'
			);

			update_option( 'wpfc_options', $arr );
		}

		// add image support to taxonomies if it's not initialized
		if ( ! get_option( 'sermon_image_plugin_settings' ) ) {
			update_option( 'sermon_image_plugin_settings', array(
				'taxonomies' => array( 'wpfc_sermon_series', 'wpfc_preacher', 'wpfc_sermon_topics' )
			) );
		}

		// Enable error recovery on plugin re-activation
		update_option( '_sm_recovery_do_not_catch', 0 );

		// Flush rewrite cache
		flush_rewrite_rules( true );
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
	 * Saves whole Sermon HTML markup into post content for better search compatibility
	 *
	 * @param int $post_ID
	 *
	 * @since 2.8
	 */
	public function render_sermon_into_content( $post_ID ) {
		if ( defined( 'SM_SAVING_POST' ) ) {
			return;
		} else {
			define( 'SM_SAVING_POST', 1 );
		}

		wp_update_post( array(
			'ID'           => $post_ID,
			'post_content' => wp_filter_post_kses( wpfc_sermon_single( true ) )
		) );
	}

	/**
	 * AJAX handler to store the state of dismissible notices.
	 */
	function php_notice_handler() {
		update_option( 'dismissed-' . $_POST['type'], 1 );
	}
}

// Initialize Sermon Manager
SermonManager::get_instance();
