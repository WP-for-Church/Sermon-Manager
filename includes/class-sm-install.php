<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

/**
 * Used on installation/update
 *
 * @since 2.8
 */
class SM_Install {
	/** @var array DB updates and callbacks that need to be run per version */
	private static $db_updates = array(
		'2.8' => array(
			'sm_update_28_revert_old_dates',
			'sm_update_28_convert_dates_to_unix',
			'sm_update_28_fill_out_empty_dates',
			'sm_update_28_fill_out_series_dates',
			'sm_update_28_save_sermon_render_into_post_content',
			'sm_update_28_reset_recovery',
		),
		'2.8.3' => array(
			'sm_update_283_resave_sermons'
		)
	);

	/** @var object Background update class */
	private static $background_updater;

	public static function init() {
		add_action( 'init', array( __CLASS__, 'init_background_updater' ), 3 );
		add_action( 'init', array( __CLASS__, 'check_version' ), 8 );
		add_filter( 'plugin_action_links_' . SM_BASENAME, array( __CLASS__, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );
		add_filter( 'cron_schedules', array( __CLASS__, 'cron_schedules' ) );
	}

	/**
	 * Check Sermon Manager version and run the updater is required
	 *
	 * This check is done on all requests and runs if the versions do not match
	 */
	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'sm_version' ) !== SM_VERSION ) {
			self::_install();
			do_action( 'sm_updated' );
		}
	}

	/**
	 * Install Sermon Manager
	 */
	private static function _install() {
		global $wpdb;

		if ( ! is_blog_installed() ) {
			return;
		}

		if ( ! defined( 'SM_INSTALLING' ) ) {
			define( 'SM_INSTALLING', true );
		}

		//self::_create_options(); todo: will be done in future versions, every option will have its own field in the database, so we will just use `add_option()` - it won't overwrite the field
		//self::_create_roles(); todo: will be done in future versions

		// Register post types
		SM_Post_types::register_post_types();
		SM_Post_types::register_taxonomies();

		// do update
		self::_update();

		// Update version just in case
		self::update_db_version();

		// Flush rules after install
		do_action( 'sm_flush_rewrite_rules' );

		/*
		 * Deletes all expired transients. The multi-table delete syntax is used
		 * to delete the transient record from table a, and the corresponding
		 * transient_timeout record from table b.
		 *
		 * Based on code inside core's upgrade_network() function.
		 */
		/** @noinspection SqlNoDataSourceInspection */
		$sql = "DELETE a, b FROM $wpdb->options a, $wpdb->options b
			WHERE a.option_name LIKE %s
			AND a.option_name NOT LIKE %s
			AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
			AND b.option_value < %d";
		$wpdb->query( $wpdb->prepare( $sql, $wpdb->esc_like( '_transient_' ) . '%', $wpdb->esc_like( '_transient_timeout_' ) . '%', time() ) );

		// Trigger action
		do_action( 'sm_installed' );
	}

	/**
	 * Push all needed DB updates to the queue for processing.
	 */
	private static function _update() {
		$current_db_version = get_option( 'sm_version' );
		$update_queued      = false;

		foreach ( self::_get_db_update_callbacks() as $version => $update_callbacks ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
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
	 * Init background updates
	 */
	public static function init_background_updater() {
		include_once 'class-sm-background-updater.php';
		self::$background_updater = new SM_Background_Updater();
	}

	/**
	 * Update DB version to current.
	 *
	 * @param string $version (optional)
	 */
	public static function update_db_version( $version = null ) {
		delete_option( 'sm_version' );
		add_option( 'sm_version', is_null( $version ) ? SM_VERSION : $version );
	}

	/**
	 * Add more cron schedules
	 *
	 * @param  array $schedules
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
	 * @param    mixed $links Plugin Action links
	 *
	 * @return    array
	 */
	public static function plugin_action_links( $links ) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'edit.php?post_type=wpfc_sermon&page=Sermon-Manager%2Fincludes%2Foptions.php' ) . '" aria-label="' . esc_attr__( 'View Sermon Manager settings', 'sermon-manager-for-wordpress' ) . '">' . esc_html__( 'Settings' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param    mixed $links Plugin Row Meta
	 * @param    mixed $file  Plugin Base file
	 *
	 * @return    array
	 */
	public static function plugin_row_meta( $links, $file ) {
		/** @noinspection PhpUndefinedConstantInspection */
		if ( SM_BASENAME == $file ) {
			$row_meta = array(
				'support' => '<a href="' . esc_url( 'https://wpforchurch.com/my/submitticket.php' ) . '" aria-label="' . esc_attr__( 'Visit premium customer support', 'sermon-manager-for-wordpress' ) . '">' . esc_html__( 'Premium support', 'sermon-manager-for-wordpress' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}
}

SM_Install::init();
