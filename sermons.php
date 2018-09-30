<?php
/**
 * Plugin Name: Sermon Manager for WordPress
 * Plugin URI: https://www.wpforchurch.com/products/sermon-manager-for-wordpress/
 * Description: Add audio and video sermons, manage speakers, series, and more.
 * Version: 2.15.0
 * Author: WP for Church
 * Author URI: https://www.wpforchurch.com/
 * Requires at least: 4.5
 * Tested up to: 4.9
 *
 * Text Domain: sermon-manager-for-wordpress
 * Domain Path: /languages/
 *
 * @package SermonManager\Core
 */

// All files must be PHP 5.3 compatible!
defined( 'ABSPATH' ) or die;

/**
 * Loads Sermon Manager.
 */
function sm_load() {
	// Define constants (PATH and URL are with a trailing slash).
	define( 'SM_PLUGIN_FILE', __FILE__ );
	define( 'SM_PATH', dirname( SM_PLUGIN_FILE ) . '/' );
	define( 'SM_BASENAME', plugin_basename( __FILE__ ) );
	define( 'SM_URL', plugin_dir_url( __FILE__ ) );
	define( 'SM_VERSION', preg_match( '/^.*Version: (.*)$/m', file_get_contents( __FILE__ ), $version ) ? trim( $version[1] ) : 'N/A' );

	if ( ! version_compare( PHP_VERSION, '5.3', '>=' ) ) {
		add_action( 'admin_notices', 'sm_fail_php' );
	} else {
		require_once SM_PATH . 'includes/plugin.php';
	}

	/**
	 * Renders the error notice when PHP is less than 5.3
	 *
	 * @since 2.16.0
	 */
	function sm_fail_php() {
		?>
		<div class="notice notice-wpfc-php notice-error">
			<p>
				<?php
				// translators: %1$s current PHP version, see msgid "PHP %s", effectively <strong>PHP %s</strong>.
				// translators: %2$s required PHP version, see msgid "PHP %s", effectively <strong>PHP %s</strong>.
				echo wp_sprintf( esc_html__( 'You are running %1$s, but Sermon Manager requires at least %2$s.', 'sermon-manager-for-wordpress' ), '<strong>' . wp_sprintf( esc_html__( 'PHP %s', 'sermon-manager-for-wordpress' ), PHP_VERSION ) . '</strong>', '<strong>' . wp_sprintf( esc_html__( 'PHP %s', 'sermon-manager-for-wordpress' ), '5.3.0' ) . '</strong>' );
				?>
			</p>
		</div>
		<?php
	}
}

// Do the init.
sm_load();
