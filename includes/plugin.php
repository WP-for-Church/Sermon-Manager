<?php // phpcs:ignore

/**
 * Main Sermon Manager file.
 *
 * @since   2.16.0
 * @package SermonManager\Core
 */

namespace SermonManager;

/**
 * Main Plugin Class
 *
 * @since 2.16.0 All methods as well, unless otherwise states.
 */
class Plugin {
	/**
	 * Instance.
	 *
	 * Holds the plugin instance.
	 *
	 * @access public
	 * @static
	 *
	 * @var Plugin
	 */
	public static $instance = null;

	/**
	 * Plugin constructor.
	 *
	 * @access private
	 */
	private function __construct() {
		// Register autoloader.
		$this->_register_autoloader();

		add_action( 'init', array( $this, 'init' ), 0 );
	}

	/**
	 * Register autoloader.
	 *
	 * @access private
	 */
	private function _register_autoloader() {
		require SM_PATH . '/includes/autoloader.php';

		Autoloader::run();
	}

	/**
	 * Instance.
	 *
	 * Ensures only one instance of the plugin class is loaded or can be loaded.
	 *
	 * @access public
	 * @static
	 *
	 * @return Plugin An instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			/**
			 * Sermon Manager loaded.
			 *
			 * Fires when plugin class has been loaded, but before everything else.
			 *
			 * @since 2.16.0
			 */
			do_action( 'sm_loaded' );

			/**
			 * Sermon Manager loaded.
			 *
			 * Fires when plugin class has been loaded, but before everything else.
			 *
			 * @since      2.12.5
			 * @deprecated 2.16.0
			 */
			do_action( 'sm_before_plugin_load' );
		}

		return self::$instance;
	}

	/**
	 * Clone.
	 *
	 * Disable class cloning and throw an error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object. Therefore, we don't want the object to be cloned.
	 *
	 * @access public
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Something went wrong.', 'sermon-manager-for-wordpress' ), '2.16.0' );
	}

	/**
	 * Wakeup.
	 *
	 * Disable unserializing of the class.
	 *
	 * @access public
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Something went wrong.', 'sermon-manager-for-wordpress' ), '2.16.0' );
	}

	/**
	 * Init.
	 *
	 * Initialize Sermon Manager Plugin.
	 *
	 * @access public
	 */
	public function init() {
		$this->_include_files();
		$this->_init_components();
		$this->_add_actions();
		$this->_add_filters();

		/**
		 * Sermon Manager init.
		 *
		 * Fires when plugin has been fully loaded and instantiated.
		 *
		 * @since 2.16.0
		 */
		do_action( 'sm_init' );

		/**
		 * Sermon Manager init.
		 *
		 * Fires when plugin has been fully loaded and instantiated.
		 *
		 * @since      2.12.5
		 * @deprecated 2.16.0
		 */
		do_action( 'sm_after_plugin_load' );
	}

	/**
	 * Includes required files.
	 *
	 * @access private
	 */
	private function _include_files() {
		/**
		 * General includes.
		 */
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
	 * Init components.
	 *
	 * Initialize Sermon Manager components.
	 *
	 * @access private
	 */
	private function _init_components() {

	}

	/**
	 * Hooks into the required actions.
	 *
	 * @access private
	 */
	private function _add_actions() {
		// Load translations.
		add_action( 'after_setup_theme', array( $this, 'load_translations' ) );
		// Enqueue scripts & styles.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );
		add_action( 'wp_footer', array( $this, 'enqueue_scripts_styles' ) );
		// Append custom classes to individual sermons.
		add_filter( 'post_class', array( $this, 'add_additional_sermon_classes' ), 10, 3 );
		// Add Sermon Manager image sizes.
		add_action( 'after_setup_theme', array( $this, 'add_image_sizes' ) );
		// No idea... better not touch it for now.
		add_filter( 'sermon-images-disable-public-css', '__return_true' );
		// Remove SB Help from SM pages, since it messes up the formatting.
		add_action( 'contextual_help', function () {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			if ( in_array( $screen_id, sm_get_screen_ids() ) ) {
				remove_action( 'contextual_help', 'sb_add_contextual_help' );
			}
		}, 0 );
	}

	/**
	 * Hooks into the required filters.
	 *
	 * @access private
	 */
	private function _add_filters() {

	}

	/**
	 * Load plugin translations.
	 *
	 * @return void
	 */
	public function load_translations() {
		load_plugin_textdomain( 'sermon-manager-for-wordpress', false, SM_PATH . '/languages' );
	}

	/**
	 * Add images sizes.
	 *
	 * @return void
	 */
	public function add_image_sizes() {
		if ( function_exists( 'add_image_size' ) ) {
			add_image_size( 'sermon_small', 75, 75, true );
			add_image_size( 'sermon_medium', 300, 200, true );
			add_image_size( 'sermon_wide', 940, 350, true );
		}
	}
}

return Plugin::instance();
