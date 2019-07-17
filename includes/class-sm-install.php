<?php
/**
 * Installation functionality.
 *
 * @package SM/Core/Updating
 */

defined( 'ABSPATH' ) or die;

/**
 * Used on installation/update
 *
 * @since 2.8
 */
class SM_Install {
	/**
	 * DB updates and callbacks that need to be run per version
	 *
	 * @var array
	 */
	public static $db_updates = array(
		'2.8'     => array(
			'sm_update_28_revert_old_dates',
			'sm_update_28_convert_dates_to_unix',
			'sm_update_28_fill_out_empty_dates',
			'sm_update_28_fill_out_series_dates',
			'sm_update_28_save_sermon_render_into_post_content',
		),
		'2.8.4'   => array(
			'sm_update_284_resave_sermons',
		),
		'2.9'     => array(
			'sm_update_29_fill_out_series_dates',
			'sm_update_29_convert_settings',
		),
		'2.9.3'   => array(
			'sm_update_293_fix_import_dates',
		),
		'2.10'    => array(
			'sm_update_210_update_options',
		),
		'2.11'    => array(
			'sm_update_211_render_content',
			'sm_update_211_update_date_time',
		),
		'2.12.3'  => array(
			'sm_update_2123_fix_preacher_permalink',
		),
		'2.13.0'  => array(
			'sm_update_2130_fill_out_sermon_term_dates',
			'sm_update_2130_remove_excerpts',
		),
		'2.14.0'  => array(
			'sm_update_2140_convert_bible_verse',
		),
		'2.15.0'  => array(
			'sm_update_2150_audio_file_ids',
			'sm_update_2150_audio_duration_and_size',
		),
		'2.15.2'  => array(
			'sm_update_2152_remove_default_image',
		),
		'2.15.11' => array(
			'sm_update_21511_update_term_dates',
		),
		'2.15.16' => array(
			'sm_update_21516_update_term_dates',
		),
	);

	/**
	 * Background update class
	 *
	 * @var object
	 */
	private static $background_updater;

	/**
	 * Initialize the updater.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'init_background_updater' ), 3 );
		add_action( 'init', array( __CLASS__, 'check_version' ), 8 );
		add_filter( 'plugin_action_links_' . SM_BASENAME, array( __CLASS__, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );
		add_filter( 'cron_schedules', array( __CLASS__, 'cron_schedules' ) );
	}

	/**
	 * Check Sermon Manager version and run the updater is required.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 */
	public static function check_version() {
		global $pagenow;

		if ( ! defined( 'IFRAME_REQUEST' ) && ( ( 'plugins.php' === $pagenow && isset( $_GET['activate'] ) && 'true' === $_GET['activate'] ) || SM_VERSION !== get_option( 'sm_version' ) ) ) {
			self::_install();
			do_action( 'sm_updated' );
		}
	}

	/**
	 * Install Sermon Manager.
	 */
	private static function _install() {
		global $wpdb;

		if ( ! is_blog_installed() ) {
			return;
		}

		if ( ! defined( 'SM_INSTALLING' ) ) {
			define( 'SM_INSTALLING', true );
		}

		// self::_create_roles(); @todo: will be done in future versions (move it below options).
		self::_create_options();

		// Register post types.
		SM_Post_types::register_post_types();
		SM_Post_types::register_taxonomies();

		// Do update.
		self::_update();

		// Update version just in case.
		self::update_db_version();

		// Flush 1.
		do_action( 'sm_flush_rewrite_rules' );

		// Flush 2.
		add_action( 'init', function () {
			do_action( 'sm_flush_rewrite_rules' );
		} );

		/*
		 * Deletes all expired transients. The multi-table delete syntax is used
		 * to delete the transient record from table a, and the corresponding
		 * transient_timeout record from table b.
		 *
		 * Based on code inside core's upgrade_network() function.
		 */
		$wpdb->query( $wpdb->prepare( "DELETE a, b FROM $wpdb->options a, $wpdb->options b
			WHERE a.option_name LIKE %s
			AND a.option_name NOT LIKE %s
			AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
			AND b.option_value < %d", $wpdb->esc_like( '_transient_' ) . '%', $wpdb->esc_like( '_transient_timeout_' ) . '%', time() ) );

		// Trigger action.
		do_action( 'sm_installed' );
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 *
	 * @since 2.10
	 */
	private static function _create_options() {
		// Include settings so that we can run through defaults.
		include_once 'admin/class-sm-admin-settings.php';

		$settings = SM_Admin_Settings::get_settings_pages();

		foreach ( $settings as $section ) {
			if ( ! method_exists( $section, 'get_settings' ) ) {
				continue;
			}

			foreach ( $section->get_settings() as $value ) {
				if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
					$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
					add_option( 'sermonmanager_' . $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
				}
			}
		}
	}

	/**
	 * Push all needed DB updates to the queue for processing.
	 */
	private static function _update() {
		if ( self::$background_updater->is_updating() ) {
			return;
		}

		$update_queued = false;

		foreach ( self::_get_db_update_callbacks() as $version => $update_callbacks ) {
			foreach ( $update_callbacks as $update_callback ) {
				if ( ! get_option( 'wp_sm_updater_' . $update_callback . '_done' ) ) {
					self::$background_updater->push_to_queue( $update_callback );
					$update_queued = true;
				}
			}
		}

		if ( $update_queued ) {
			self::$background_updater->save()->dispatch();
		}
	}

	/**
	 * Get list of DB update callbacks.
	 *
	 * @return array
	 */
	private static function _get_db_update_callbacks() {
		return self::$db_updates;
	}

	/**
	 * Update DB version to current.
	 *
	 * @param string $version (optional).
	 */
	public static function update_db_version( $version = null ) {
		delete_option( 'sm_version' );
		add_option( 'sm_version', is_null( $version ) ? SM_VERSION : $version );
	}

	/**
	 * Init background updates.
	 */
	public static function init_background_updater() {
		include_once 'class-sm-background-updater.php';
		self::$background_updater = new SM_Background_Updater();
	}

	/**
	 * Add more cron schedules.
	 *
	 * @param  array $schedules The existing array of schedule data.
	 *
	 * @return array
	 */
	public static function cron_schedules( $schedules ) {
		$schedules['monthly'] = array(
			'interval' => 2635200,
			'display'  => __( 'Monthly', 'sermon-manager-for-wordpress' ),
		);

		return $schedules;
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param    mixed $links Plugin Action links.
	 *
	 * @return    array
	 */
	public static function plugin_action_links( $links ) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'edit.php?post_type=wpfc_sermon&page=sm-settings' ) . '" aria-label="' . esc_attr__( 'View Sermon Manager settings', 'sermon-manager-for-wordpress' ) . '">' . esc_html__( 'Settings' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param    mixed $links Plugin Row Meta.
	 * @param    mixed $file  Plugin Base file.
	 *
	 * @return    array
	 */
	public static function plugin_row_meta( $links, $file ) {
		if ( SM_BASENAME == $file ) {
			$row_meta = array(
				'support' => '<a href="' . esc_url( 'https://wpforchurch.com/my/submitticket.php?utm_source=sermon-manager&utm_medium=wordpress' ) . '" aria-label="' . esc_attr__( 'Visit premium customer support', 'sermon-manager-for-wordpress' ) . '">' . esc_html__( 'Premium support', 'sermon-manager-for-wordpress' ) . '</a>',
				'smp'     => '<a href="https://sermonmanager.pro/?utm_source=sermon-manager&amp;utm_medium=wordpress" aria-label="' . esc_attr( __( 'Get Sermon Manager Pro', 'sermon-manager-pro' ) ) . '" target="_blank" style="color:#ff0000;">' . __( 'Get Sermon Manager Pro', 'sermon-manager-pro' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}
}

SM_Install::init();
