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

	/**
	 * Adds actions.
	 */
	public function add_actions() {
		// Allows reimport after sermon deletion.
		add_action( 'before_delete_post', array( $this, 'remove_imported_post_from_list' ) );
	}

	/**
	 * Removes imported post from the list of imported posts, so it can be imported again during next import.
	 *
	 * @param int $id The deleted post ID.
	 */
	public function remove_imported_post_from_list( $id ) {
		global $post_type;

		if ( 'wpfc_sermon' !== $post_type ) {
			return;
		}

		$sermons_se = get_option( '_sm_import_se_messages' );
		$sermons_sb = get_option( '_sm_import_sb_messages' );

		$sermon_messages = array( $sermons_se, $sermons_sb );

		foreach ( $sermon_messages as $offset0 => &$sermons_array ) {
			foreach ( $sermons_array as $offset1 => $value ) {
				if ( $value['new_id'] == $id ) {
					unset( $sermons_array[ $offset1 ] );
					update_option( 0 === $offset0 ? '_sm_import_se_messages' : '_sm_import_sb_messages', $sermons_array );

					return;
				}
			}
		}
	}
}
