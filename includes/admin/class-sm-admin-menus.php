<?php
defined( 'ABSPATH' ) or die;

/**
 * Setup menus in WP admin
 *
 * @since 2.9
 */
class SM_Admin_Menus {
	public function __construct() {
		// Add menus
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 60 );
		add_action( 'admin_menu', array( $this, 'import_export_menu' ), 70 );

		add_action( 'admin_enqueue_scripts', array( $this, 'fix_icon' ) );
	}

	/**
	 * Add menu item
	 */
	public function settings_menu() {
		add_submenu_page( 'edit.php?post_type=wpfc_sermon', __( 'Sermon Manager Settings', 'sermon-manager-for-wordpress' ), __( 'Settings', 'sermon-manager-for-wordpress' ), 'manage_options', 'sm-settings', array(
			$this,
			'settings_page'
		) );
	}

	/**
	 * Add menu item.
	 */
	public function import_export_menu() {
		add_submenu_page( 'edit.php?post_type=wpfc_sermon', __( 'Sermon Manager Import/Export', 'sermon-manager-for-wordpress' ), __( 'Import/Export', 'sermon-manager-for-wordpress' ), 'manage_options', 'sm-import-export', array(
			$this,
			'import_export_page'
		) );
	}

	/**
	 * Init the settings page
	 */
	public function settings_page() {
		SM_Admin_Settings::output();
	}

	/**
	 * Init the settings page
	 */
	public function import_export_page() {
		SM_Admin_Import_Export::output();
	}

	/**
	 * Fixes Sermon Manager top-level icon
	 */
	public function fix_icon() {
		wp_enqueue_style( 'sm-icon', SM_URL . 'assets/css/admin-icon.css', array(), SM_VERSION );
	}
}

return new SM_Admin_Menus();
