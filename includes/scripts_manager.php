<?php // phpcs:ignore

/**
 * The scripts manager.
 *
 * @since   2.16.0
 *
 * @package SermonManager\Core
 */

namespace SermonManager;

defined( 'ABSPATH' ) or die;

/**
 * The scripts manager.
 *
 * Handles all registering/loading of scripts and styles.
 *
 * @since   2.16.0
 */
class Scripts_Manager {
	/**
	 * Hooks into WordPress to call other enqueueing methods from this class.
	 */
	public function __construct() {
		$this->register_scripts();

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_backend' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_backend_all' ) );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend' ) );
			add_action( 'wp_footer', array( $this, 'enqueue_frontend' ) );
		}
	}

	/**
	 * Registers the scripts.
	 */
	public function register_scripts() {
		// Main admin styles.
		wp_register_style( 'sm_admin_styles', SM_URL . 'assets/css/admin.min.css', array(), SM_VERSION );
		// Import/Export page.
		wp_register_script( 'import-export-js', SM_URL . 'assets/js/admin/import-export' . ( ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) ? '' : '.min' ) . '.js', array(), SM_VERSION );
		// Fix for menu icon.
		wp_register_style( 'sm-icon', SM_URL . 'assets/css/admin-icon.css', array(), SM_VERSION );
		// Settings.
		wp_register_script( 'sm_settings', SM_URL . 'assets/js/admin/settings' . ( ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) ? '' : '.min' ) . '.js', array(
			'jquery',
			'jquery-ui-datepicker',
			'jquery-ui-sortable',
		), SM_VERSION, true );

		// Settings podcast helper.
		wp_register_script( 'sm_settings_podcast', SM_URL . 'assets/js/admin/settings/podcast' . ( ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) ? '' : '.min' ) . '.js', 'sm_settings', SM_VERSION, true );
		// Settings verse helper.
		wp_register_script( 'sm_settings_verse', SM_URL . 'assets/js/admin/settings/verse' . ( ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) ? '' : '.min' ) . '.js', 'sm_settings', SM_VERSION, true );

		// Facebook player.
		wp_register_script( 'wpfc-sm-fb-player', SM_URL . 'assets/vendor/js/facebook-video.js', array(), SM_VERSION );
		// Main Plyr JS.
		wp_register_script( 'wpfc-sm-plyr', SM_URL . 'assets/vendor/js/plyr.polyfilled' . ( ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) ? '' : '.min' ) . '.js', array(), '3.4.3', \SermonManager::getOption( 'player_js_footer' ) );
		// Plyr loader.
		wp_register_script( 'wpfc-sm-plyr-loader', SM_URL . 'assets/js/plyr' . ( ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) ? '' : '.min' ) . '.js', array( 'wpfc-sm-plyr' ), SM_VERSION );
		// Verses.
		wp_register_script( 'wpfc-sm-verse-script', SM_URL . 'assets/vendor/js/verse.js', array(), SM_VERSION );
		// Main styles.
		wp_register_style( 'wpfc-sm-styles', SM_URL . 'assets/css/sermon.min.css', array(), SM_VERSION );
		// Main Plyr CSS.
		wp_register_style( 'wpfc-sm-plyr-css', SM_URL . 'assets/vendor/css/plyr.min.css', array(), '3.4.3' );

		// Register top theme-specific styling, if there are any.
		if ( file_exists( get_stylesheet_directory() . '/sermon.css' ) ) {
			wp_register_style( 'wpfc-sm-style-theme', get_stylesheet_directory_uri() . '/sermon.css', array(), SM_VERSION );
		}

		// Register theme-specific styling for main views, if there are any.
		if ( file_exists( SM_PATH . 'assets/css/theme-specific/' . get_option( 'template' ) . '.css' ) ) {
			wp_register_style( 'wpfc-sm-style-' . get_option( 'template' ), SM_URL . 'assets/css/theme-specific/' . get_option( 'template' ) . '.css', array( 'wpfc-sm-styles' ), SM_VERSION );
		}

		/**
		 * Triggers after scripts have been registered.
		 *
		 * You can register additional scripts here or deregister existing ones.
		 *
		 * @since 2.16.0
		 */
		do_action( 'sm/scripts/register' );
	}

	/**
	 * Enqueues the scripts on frontend.
	 */
	public function enqueue_frontend() {
		if ( defined( 'SM_SCRIPTS_STYLES_ENQUEUED' ) ) {
			return;
		}

		if ( ! ( defined( 'SM_ENQUEUE_SCRIPTS_STYLES' ) || 'wpfc_sermon' === get_post_type() || is_post_type_archive( 'wpfc_sermon' ) )
		) {
			return;
		}

		// Do not enqueue main styles if they are disabled by user.
		if ( ! \SermonManager::getOption( 'css' ) ) {
			wp_enqueue_style( 'wpfc-sm-styles' );
			wp_enqueue_style( 'dashicons' );

			if ( wp_style_is( 'wpfc-sm-style-' . get_option( 'template' ), 'registered' ) ) {
				wp_enqueue_style( 'wpfc-sm-style-' . get_option( 'template' ) );
			}

			/**
			 * Triggers only if user hasn't disabled Sermon Manager styles.
			 *
			 * @since 2.13.2
			 */
			do_action( 'sm_enqueue_css' );

			/**
			 * Triggers only if user hasn't disabled Sermon Manager styles.
			 *
			 * @since 2.13.2
			 */
			do_action( 'sm_enqueue_js' );
		}

		if ( wp_style_is( 'wpfc-sm-style-theme', 'registered' ) ) {
			wp_enqueue_style( 'wpfc-sm-style-theme' );
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

		// Only if verse popups are enabled.
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

		/**
		 * Triggers after scripts have been enqueued for frontend.
		 *
		 * You can enqueue additional scripts here.
		 *
		 * @since 2.16.0
		 */
		do_action( 'sm/scripts/enqueue_frontend' );

		// Do not enqueue twice.
		define( 'SM_SCRIPTS_STYLES_ENQUEUED', true );
	}

	/**
	 * Enqueues the scripts on Sermon Manager backend/admin screens.
	 */
	public function enqueue_backend() {
		// Check if we are at right screen.
		$screen    = \get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( ! in_array( $screen_id, sm_get_screen_ids() ) ) {
			return; // Bail. Wrong screen.
		}

		wp_enqueue_style( 'sm_admin_styles' );

		$screen    = \get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		// @todo - get the correct screen ids.
		switch ( $screen_id ) {
			case '': // @todo Settings.
				wp_enqueue_script( 'sm_settings' );
				wp_localize_script( 'sm_settings', 'sm_settings_params', array(
					'i18n_nav_warning'        => __( 'The changes you made will be lost if you navigate away from this page.', 'sermon-manager-for-wordpress' ),
					'i18n_bible_spanish_note' => __( 'Note: WordPress is not set to any Spanish variant. Reverted to ESV.', 'sermon-manager-for-wordpress' ),
					'is_wp_spanish'           => strpos( get_locale(), 'es_' ) !== false,
				) );
				break;
			case '': // @todo Importing.
				wp_enqueue_script( 'import-export-js' );
				break;
		}

		/**
		 * Enqueues scripts only on Sermon Manager admin screens.
		 *
		 * @since      2.13.0
		 *
		 * @deprecated 2.16.0 in favor of "sm/scripts/enqueue_backend".
		 */
		do_action( 'sm_enqueue_admin_css' );

		/**
		 * Enqueues styles only on Sermon Manager admin screens.
		 *
		 * @since      2.13.0
		 *
		 * @deprecated 2.16.0 in favor of "sm/scripts/enqueue_backend".
		 */
		do_action( 'sm_enqueue_admin_js' );

		/**
		 * Triggers after scripts have been enqueued for Sermon Manager backend/admin.
		 *
		 * You can enqueue additional scripts here.
		 *
		 * @since 2.16.0
		 */
		do_action( 'sm/scripts/enqueue_backend' );
	}

	/**
	 * Enqueues the scripts on all backend/admin pages.
	 */
	public function enqueue_backend_all() {
		wp_enqueue_style( 'sm-icon' );

		/**
		 * Triggers after scripts have been enqueued for all admin screens.
		 *
		 * You can enqueue additional scripts here.
		 *
		 * @since 2.16.0
		 */
		do_action( 'sm/scripts/enqueue_backend_all' );
	}

	/**
	 * Enqueues the scripts on both frontend and all backend screens.
	 */
	public function enqueue_all() {
		/**
		 * Triggers after scripts have been enqueued for both frontend and backend/admin.
		 *
		 * You can enqueue additional scripts here.
		 *
		 * @since 2.16.0
		 */
		do_action( 'sm/scripts/enqueue_all' );
	}
}
