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
	 * SM_Admin_Import_Export constructor.
	 */
	public function __construct() {
		$this->add_actions();
	}

	/**
	 * Adds actions.
	 */
	public function add_actions() {
		// Allows reimport after sermon deletion.
		add_action( 'before_delete_post', array( $this, 'remove_imported_post_from_list' ) );
		// Allow usage of remote URLs for attachments (used for images imported from SE).
		add_filter( 'wp_get_attachment_url', array( $this, 'allow_external_attachment_url' ), 10, 2 );
		// Temporary hook for import/export API.
		// @todo - We should do it via proper WordPress Ajax functions in future.
		add_action( 'admin_init', array( $this, 'decide_api_action' ) );
	}

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

	/**
	 * Used for executing the import/export actions.
	 */
	public function decide_api_action() {
		if ( isset( $_GET['page'] ) && 'sm-import-export' === $_GET['page'] ) {
			if ( isset( $_GET['doimport'] ) ) {
				$class = null;

				switch ( $_GET['doimport'] ) {
					case 'sb':
						$class = new SM_Import_SB();
						break;
					case 'se':
						$class = new SM_Import_SE();
						break;
					case 'sm':
						$class = new SM_Import_SM();
						break;
					case 'exsm':
						$class = new SM_Export_SM();
						$class->sermon_export_wp();
						die();
						break;
				}

				if ( null !== $class ) {
					$class->import();
					add_action( 'admin_notices', function () {
						if ( ! ! sm_get_option( 'debug_import' ) ) :
							?>
							<div class="notice notice-info">
								<p>Debug info:</p>
								<pre><?php echo get_option( 'sm_last_import_info' ) ?: 'No data available.'; ?></pre>
							</div>
						<?php endif; ?>

						<div class="notice notice-success">
							<p><?php _e( 'Import done!', 'sermon-manager-for-wordpress' ); ?></p>
						</div>
						<?php
					} );
				}
			}
		}
	}
}
