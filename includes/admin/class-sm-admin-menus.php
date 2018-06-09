<?php
/**
 * Registers SM related menus.
 *
 * @package SM/Core/Admin/Menus
 */

defined( 'ABSPATH' ) or die;

/**
 * Setup menus in WP admin.
 *
 * @since 2.9
 */
class SM_Admin_Menus {
	/**
	 * SM_Admin_Menus constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 60 );
		add_action( 'admin_menu', array( $this, 'import_export_menu' ), 70 );

		add_action( 'admin_enqueue_scripts', array( $this, 'fix_icon' ) );

		// Fix first submenu menu name (Sermons => All Sermons).
		add_action( 'admin_menu', array( $this, 'fix_sermons_title' ), 100 );
	}

	/**
	 * Add menu item.
	 */
	public function settings_menu() {
		add_submenu_page( 'edit.php?post_type=wpfc_sermon', __( 'Sermon Manager Settings', 'sermon-manager-for-wordpress' ), __( 'Settings', 'sermon-manager-for-wordpress' ), 'manage_wpfc_sm_settings', 'sm-settings', array(
			$this,
			'settings_page',
		) );
	}

	/**
	 * Add menu item.
	 */
	public function import_export_menu() {
		add_submenu_page( 'edit.php?post_type=wpfc_sermon', __( 'Sermon Manager Import/Export', 'sermon-manager-for-wordpress' ), __( 'Import/Export', 'sermon-manager-for-wordpress' ), 'manage_wpfc_sm_settings', 'sm-import-export', array(
			$this,
			'import_export_page',
		) );
	}

	/**
	 * Init the settings page.
	 */
	public function settings_page() {
		SM_Admin_Settings::output();
	}

	/**
	 * Init the settings page.
	 */
	public function import_export_page() {
		wp_enqueue_script( 'import-export-js', SM_URL . 'assets/js/admin/import-export' . ( ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) ? '' : '.min' ) . '.js', array(), SM_VERSION );
		SM_Admin_Import_Export::output();
	}

	/**
	 * Fixes Sermon Manager top-level icon.
	 */
	public function fix_icon() {
		wp_enqueue_style( 'sm-icon', SM_URL . 'assets/css/admin-icon.css', array(), SM_VERSION );
	}

	/**
	 * Changes child menu item name to All Sermons.
	 */
	public function fix_sermons_title() {
		global $submenu;

		if ( ! isset( $submenu['edit.php?post_type=wpfc_sermon'] ) ) {
			return;
		}

		foreach ( $submenu['edit.php?post_type=wpfc_sermon'] as &$sermon_item ) {
			if ( 'edit.php?post_type=wpfc_sermon' === $sermon_item[2] ) {
				$sermon_item[0] = __( 'All Sermons', 'sermon-manager-for-wordpress' );
				return;
			}
		}
	}
}

return new SM_Admin_Menus();
