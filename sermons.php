<?php
/*
Plugin Name: Sermon Manager for WordPress
Plugin URI: http://www.wpforchurch.com/products/sermon-manager-for-wordpress/
Description: Add audio and video sermons, manage speakers, series, and more. Visit <a href="http://wpforchurch.com" target="_blank">Wordpress for Church</a> for tutorials and support.
Version: 2.4.11
Author: WP for Church
Contributors: wpforchurch, jprummer, jamzth
Author URI: http://www.wpforchurch.com/
License: GPL2
Text Domain: sermon-manager
Domain Path: /languages/
*/

defined( 'ABSPATH' ) or die;

// All files must be PHP 5.6 compatible

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
		define( 'SERMON_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
		define( 'SERMON_MANAGER_URL', plugin_dir_url( __FILE__ ) );
		define( 'SERMON_MANAGER_VERSION', preg_match( '/^.*Version: (.*)$/m', file_get_contents( __FILE__ ), $version ) ? trim( $version[1] ) : 'N/A' );

		// Check the PHP version
		if ( version_compare( PHP_VERSION, '5.6.0', '<' ) ) {
			if ( is_admin() && ! get_option( 'dismissed-render_php_version_warning', 0 ) ) {
				add_action( 'admin_notices', array( $this, 'render_php_version_warning' ) );
				add_action( 'admin_enqueue_scripts', function () {
					wp_enqueue_script( 'wpfc-php-notice-handler', SERMON_MANAGER_URL . 'js/dismiss-php.js', array(), SERMON_MANAGER_VERSION );
				} );
			}
		}

		// Include required items
		$this->includes();

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
		// no idea... better not touch it for now.
		add_filter( 'sermon-images-disable-public-css', '__return_true' );
		// Handler for dismissing PHP warning notice
		add_action( 'wp_ajax_wpfc_php_notice_handler', array( $this, 'php_notice_handler' ) );

		// do dates fixing
		$this->fix_dates();
	}

	/**
	 * Include Sermon Manager files
	 *
	 * @return void
	 */
	private function includes() {
		/**
		 * Files to include on frontend and backend
		 */
		$includes = array(
			'/includes/legacy-php.php', // Old PHP compatibility fixes
			'/includes/types-taxonomies.php', // Post Types and Taxonomies
			'/includes/taxonomy-images/taxonomy-images.php', // Images for Custom Taxonomies
			'/includes/entry-views.php', // Entry Views Tracking
			'/includes/shortcodes.php', // Shortcodes
			'/includes/widgets.php', // Widgets
			'/includes/template-tags.php', // Template Tags
			'/includes/podcast-functions.php', // Podcast Functions
			'/includes/helper-functions.php', // Global Helper Functions
			'/includes/sm-deprecated-functions.php', // Deprecated SM functions
		);

		/**
		 * Admin only includes
		 */
		$admin_includes = array(
			'/includes/admin-functions.php', // General Admin area functions
			'/includes/CMB2/init.php', // Metaboxes
			'/includes/options.php', // Options Page
		);

		// Load files
		foreach ( $includes as $file ) {
			if ( file_exists( SERMON_MANAGER_PATH . $file ) ) {
				require_once SERMON_MANAGER_PATH . $file;
			}
		}

		// Load admin files
		if ( is_admin() ) {
			foreach ( $admin_includes as $file ) {
				if ( file_exists( SERMON_MANAGER_PATH . $file ) ) {
					require_once SERMON_MANAGER_PATH . $file;
				}
			}
		}
	}

	private function fix_dates() {
		if ( ! isset( $_GET['sm_fix_dates'] ) ) {
			if ( get_option( 'wpfc_sm_dates_convert_done', 0 ) == 1 || ! is_admin() ) {
				return;
			}
		}

		try {
			global $wpdb;
			$posts_meta = array();

			// sermon date storage until now
			$sermon_dates = $wpdb->get_results( $wpdb->prepare( "SELECT meta_value, post_id FROM $wpdb->postmeta WHERE meta_key = %s", 'sermon_date' ) );
			// sermon date storage that was created by our fixing scripts
			$old_dates = $wpdb->get_results( $wpdb->prepare( "SELECT meta_value, post_id FROM $wpdb->postmeta WHERE meta_key = %s", 'sermon_date_old' ) );
			// WP sermon dates
			$wp_dates = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_date FROM $wpdb->posts WHERE post_type = %s", 'wpfc_sermon' ) );

			foreach ( $sermon_dates as $sermon_date ) {
				// reset variable
				$old = $post_date = false;

				// if for some reason, the date is blank or some other value,
				// try to get backup dates and if they are also blank or have some other value
				// then continue to the next sermon
				if ( empty( $sermon_date->meta_value ) ) {
					foreach ( $old_dates as $old_date ) {
						if ( $old_date->post_id == $sermon_date->post_id ) {
							$sermon_date = $old_date;
							break;
						}
					}

					$old = true;
				}

				// get post time
				foreach ( $wp_dates as $wp_date ) {
					if ( $wp_date->ID == $sermon_date->post_id ) {
						$post_date = $wp_date->post_date;
						break;
					}
				}

				$post_time = explode( ':', date( 'H:i:s', strtotime( $post_date ) ) );

				// add it to array for fixing
				$posts_meta[] = array(
					'date'    => intval( $sermon_date->meta_value ) + $post_time[0] * 60 * 60 + $post_time[1] * 60 + $post_time[2],
					'post_id' => (int) $sermon_date->post_id,
					'old'     => $old,
				);
			}

			$dates = $posts_meta;
			$fixed = 0;

			if ( ! empty( $dates ) ) {
				foreach ( $dates as $date ) {
					// convert it to mysql date
					$post_date = date( "Y-m-d H:i:s", $date['date'] );

					// save to the database
					$wpdb->update( $wpdb->posts, array(
						'post_date'     => $post_date,
						'post_date_gmt' => get_gmt_from_date( $post_date ),
					), array(
						'ID' => $date['post_id'],
					) );

					// add it to fixed dates
					$fixed ++;
				}
			}

			// clear all cached data
			wp_cache_flush();
		} catch ( Exception $exception ) {
			print_r( $exception );
			// failed :(
		}

		update_option( 'wpfc_sm_dates_convert_done', 1 );
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
		load_plugin_textdomain( 'sermon-manager', false, SERMON_MANAGER_PATH . 'languages' );
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
			wp_enqueue_style( 'wpfc-sm-styles', SERMON_MANAGER_URL . 'css/sermon.css', array(), SERMON_MANAGER_VERSION );
			wp_enqueue_style( 'dashicons' );

			if ( ! \SermonManager::getOption( 'use_old_player' ) ) {
				wp_enqueue_script( 'wpfc-sm-plyr', SERMON_MANAGER_URL . 'js/plyr.js', array(), SERMON_MANAGER_VERSION );
				wp_enqueue_style( 'wpfc-sm-plyr-css', SERMON_MANAGER_URL . 'css/plyr.css', array(), SERMON_MANAGER_VERSION );
				wp_add_inline_script( 'wpfc-sm-plyr', 'window.onload=function(){plyr.setup(document.querySelectorAll(\'.wpfc-sermon-player, #wpfc_sermon audio\'));}' );
			}
		}

		if ( ! \SermonManager::getOption( 'bibly' ) ) {
			wp_enqueue_script( 'wpfc-sm-bibly-script', SERMON_MANAGER_URL . 'js/bibly.min.js', array(), SERMON_MANAGER_VERSION );
			wp_enqueue_style( 'wpfc-sm-bibly-style', SERMON_MANAGER_URL . 'css/bibly.min.css', array(), SERMON_MANAGER_VERSION );

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

			// this also means that it's a first install, so date check is not needed:
			update_option( 'wpfc_sm_dates_all_fixed', '1' );
		}
	}

	/**
	 * Renders the notice when the user is not using correct PHP version
	 */
	public static function render_php_version_warning() {
		?>
        <div class="notice notice-wpfc-php notice-warning is-dismissible" data-notice="render_php_version_warning">
            <p>
				<?php echo sprintf( "You are running <strong>PHP %s</strong>, but Sermon Manager recommends <strong>PHP %s</strong>. If you encounter issues, update PHP to a recommended version and check if they are still there.", PHP_VERSION, '5.6.0' ); ?>
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

// Initialize Sermon Manager
add_action( 'plugins_loaded', array( 'SermonManager', 'get_instance' ), 9 );
