<?php
/**
 * Import/Export related functionality
 *
 * @package SM/Core/Admin/Importing
 */

defined( 'ABSPATH' ) or die;

/**
 * Import/export functions
 *
 * @since 2.9
 */
class SM_Admin_Import_Export {
	/**
	 * Import/export page.
	 *
	 * Handles the display of the Sermon Manager import/export page in admin.
	 */
	public static function output() {
		do_action( 'sm_import_export_start' );
		include 'views/html-admin-import-export.php';
	}
}
