<?php
/*
Plugin Name: Sermon Manager for WordPress
Plugin URI: http://www.wpforchurch.com/products/sermon-manager-for-wordpress/
Description: Add audio and video sermons, manage speakers, series, and more. Visit <a href="http://wpforchurch.com" target="_blank">Wordpress for Church</a> for tutorials and support.
Version: 2.4.3
Author: WP for Church
Contributors: wpforchurch, jprummer, jamzth
Author URI: http://www.wpforchurch.com/
License: GPL2
Text Domain: sermon-manager
Domain Path: /languages/
*/

defined( 'ABSPATH' ) or die;

// All files must be PHP 5.2 compatible

class SermonManager {

	/**
	 * Refers to a single instance of this class.
	 */

	private static $instance = null;

	/**
	 * Construct
	 */

	public function __construct() {
		// Check the PHP version
		if ( version_compare( PHP_VERSION, '5.6.0', '<' ) ) {
			if ( is_admin() ) {
				add_action( 'admin_notices', array( $this, 'render_php_version_warning' ) );
			}
		}

		// Define constants (PATH and URL are with a trailing slash)
		define( 'SERMON_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
		define( 'SERMON_MANAGER_URL', plugin_dir_url( __FILE__ ) );
		define( 'SERMON_MANAGER_VERSION', preg_match( '/^.*Version: (.*)$/m', file_get_contents( __FILE__ ), $version ) ? trim( $version[1] ) : 'N/A' );

		// Include required items
		$this->includes();

		// Add defaults on activation
		register_activation_hook( __FILE__, array( $this, 'set_default_options' ) );

		// load translations
		add_action( 'init', array( $this, 'load_translations' ) );
		// enqueue scripts & styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );
		// Append custom classes to individual sermons
		add_filter( 'post_class', array( $this, 'add_additional_sermon_classes' ), 10, 3 );
		// Add Sermon Manager image sizes
		add_action( 'after_setup_theme', array( $this, 'add_image_sizes' ) );
		// Fix Sermon ordering
		add_action( 'pre_get_posts', array( $this, 'fix_sermons_ordering' ), 9999 );
		// no idea... better not touch it for now.
		add_filter( 'sermon-images-disable-public-css', '__return_true' );
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
			'/includes/helper-functions.php' // Global Helper Functions
		);

		/**
		 * Admin only includes
		 */
		$admin_includes = array(
			'/includes/admin-functions.php', // General Admin area functions
			'/includes/fix-dates.php', // Date fixing, explained in the script
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
		global $wp_query;

		// we will check all the posts in the query if they have sermons shortcode
		$has_shortcode = false;
		if ( ! empty( $wp_query->posts ) ) {
			foreach ( $wp_query->posts as $post ) {
				if ( ! empty( $post->post_content ) ) {
					$has_shortcode = has_shortcode( $post->post_content, 'sermons' ) ||
					                 has_shortcode( $post->post_content, 'list_sermons' ) ||
					                 has_shortcode( $post->post_content, 'sermon_images' ) ||
					                 has_shortcode( $post->post_content, 'latest_series' ) ||
					                 has_shortcode( $post->post_content, 'sermon_sort_fields' );

					if ( $has_shortcode === true ) {
						break;
					}
				}
			}
		}

		if ( 'wpfc_sermon' === get_post_type() || $has_shortcode ) {
			if ( ! \SermonManager::getOption( 'bibly' ) ) {
				wp_enqueue_script( 'bibly-script', SERMON_MANAGER_URL . 'js/bibly.min.js', array(), SERMON_MANAGER_VERSION );
				wp_enqueue_style( 'bibly-style', SERMON_MANAGER_URL . 'css/bibly.min.css', array(), SERMON_MANAGER_VERSION );

				// get options for JS
				$Bibleversion = \SermonManager::getOption( 'bibly_version' );
				wp_localize_script( 'bibly-script', 'bibly', array( // pass WP data into JS from this point on
					'linkVersion'  => $Bibleversion,
					'enablePopups' => true,
					'popupVersion' => $Bibleversion,
				) );
			}

			if ( ! \SermonManager::getOption( 'css' ) ) {
				wp_enqueue_style( 'sermon-styles', SERMON_MANAGER_URL . 'css/sermon.css', array(), SERMON_MANAGER_VERSION );

				if ( ! \SermonManager::getOption( 'use_old_player' ) ) {
					wp_enqueue_script( 'sermon-manager-plyr', SERMON_MANAGER_URL . 'js/plyr.js', array(), SERMON_MANAGER_VERSION );
					wp_enqueue_style( 'sermon-manager-plyr-css', SERMON_MANAGER_URL . 'css/plyr.css', array(), SERMON_MANAGER_VERSION );
					wp_add_inline_script( 'sermon-manager-plyr', 'window.onload=function(){plyr.setup(document.querySelectorAll(\'.wpfc-sermon-player\'));}' );
				}
			}
		}

		// enqueue dashicons on all pages
		wp_enqueue_style( 'dashicons' );
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
	 * Fixes Sermons ordering. Uses `sermon_date` meta instead of post's published date
	 *
	 * @param WP_Query $query
	 *
	 * @return void
	 */

	public static function fix_sermons_ordering( $query ) {
		if ( ! is_admin() && $query->is_main_query() ) {
			if ( is_post_type_archive( 'wpfc_sermon' ) || is_tax( 'wpfc_preacher' ) || is_tax( 'wpfc_sermon_topics' ) || is_tax( 'wpfc_sermon_series' ) || is_tax( 'wpfc_bible_book' ) ) {
				$query->set( 'meta_key', 'sermon_date' );
				$query->set( 'meta_value', time() );
				$query->set( 'meta_compare', '<=' );
				$query->set( 'orderby', 'meta_value_num' );
				$query->set( 'order', 'DESC' );
			}
		}
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
		if ( self::getOption( 'chk_default_options_db' ) == '1' || ! is_array( get_option( 'wpfc_options' ) ) ) {
			delete_option( 'wpfc_options' ); // just in case
			$arr = array(
				"bibly"            => "0",
				"bibly_version"    => "KJV",
				"archive_slug"     => "sermons",
				"archive_title"    => "Sermons",
				"common_base_slug" => "0"
			);

			update_option( 'wpfc_options', $arr );
		}
	}

	/**
	 * Renders the notice when the user is not using correct PHP version
	 */

	public static function render_php_version_warning() {
		echo '<div class="notice notice-warning is-dismissible"><p>';
		echo sprintf( "You are running <strong>PHP %s</strong>, but Sermon Manager recommends <strong>PHP %s</strong>. If you encounter issues, update PHP to a recommended version and check if they are still there.", PHP_VERSION, '5.6.0' );
		echo '</p></div>';
	}
}

// Initialize Sermon Manager
add_action( 'plugins_loaded', array( 'SermonManager', 'get_instance' ), 9 );
