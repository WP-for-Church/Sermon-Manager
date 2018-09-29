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
		// Allow usage of remote URLs for attachments (used for images imported from SE).
		add_filter( 'wp_get_attachment_url', array( $this, 'allow_external_attachment_url' ), 10, 2 );
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

	/**
	 * Allows importing of attachments with external URL.
	 *
	 * @param string $url           The URL to check.
	 * @param int    $attachment_id The attachment ID.
	 *
	 * @return string The modified or original URL.
	 */
	public function allow_external_attachment_url( $url, $attachment_id ) {
		$db_url = get_post_meta( $attachment_id, '_wp_attached_file', true );

		if ( $db_url && parse_url( $db_url, PHP_URL_SCHEME ) !== null ) {
			return $db_url;
		}

		return $url;
	}
}
