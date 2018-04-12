<?php
/**
 * Admin assets loading
 *
 * @package SM/Core/Admin
 */

defined( 'ABSPATH' ) or die;

/**
 * SM_Admin_Assets Class.
 */
class SM_Admin_Assets {
	/**
	 * SM_Admin_Assets constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Enqueue styles.
	 */
	public function admin_styles() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		// Register admin styles.
		wp_register_style( 'sm_admin_styles', SM_URL . 'assets/css/admin.min.css', array(), SM_VERSION );

		// Enqueue styles for Sermon Manager pages only.
		if ( in_array( $screen_id, sm_get_screen_ids() ) ) {
			wp_enqueue_style( 'sm_admin_styles' );

			do_action( 'sm_enqueue_admin_css' );
		}
	}

	/**
	 * Enqueue scripts.
	 */
	public function admin_scripts() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		// Enqueue scripts for Sermon Manager pages only.
		if ( in_array( $screen_id, sm_get_screen_ids() ) ) {
			do_action( 'sm_enqueue_admin_js' );
		}
	}
}

return new SM_Admin_Assets();
