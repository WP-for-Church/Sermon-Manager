<?php
/**
 * Main admin file.
 *
 * @package SM/Core/Admin
 */

defined( 'ABSPATH' ) or die;

/**
 * Sermon Manager Admin
 *
 * @since 2.9
 */
class SM_Admin {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'current_screen', array( $this, 'conditional_includes' ) );
		add_action( 'admin_init', array( $this, 'buffer' ), 1 );
	}

	/**
	 * Output buffering allows admin screens to make redirects later on.
	 */
	public function buffer() {
		ob_start();
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		include_once 'sm-admin-functions.php';
		include_once 'class-sm-admin-post-types.php';
		include_once 'class-sm-admin-menus.php';
		include_once 'class-sm-admin-assets.php';
	}

	/**
	 * Include admin files conditionally.
	 */
	public function conditional_includes() {

	}
}

return new SM_Admin();
