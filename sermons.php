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

/**
 * The class that is used to initialize Sermon Manager.
 *
 * @author  WP For Church
 * @package SM/Core
 * @access  public
 */
class SermonManager {
	/**
	 * Construct.
	 */
	public function __construct() {
		// Execute specific update function on request.
		add_action( 'sm_admin_settings_sanitize_option_execute_specific_unexecuted_function', function ( $value ) {
			if ( '' !== $value ) {
				if ( ! function_exists( $value ) ) {
					require_once SM_PATH . 'includes/sm-update-functions.php';
				}

				call_user_func( $value );

				?>
				<div class="notice notice-success">
					<p><code><?php echo $value; ?></code> executed.</p>
				</div>
				<?php
			}

			return '';
		} );

		// Execute all non-executed update functions on request.
		add_action( 'sm_admin_settings_sanitize_option_execute_unexecuted_functions', function ( $value ) {
			if ( 'yes' === $value ) {
				foreach ( \SM_Install::$db_updates as $version => $functions ) {
					foreach ( $functions as $function ) {
						if ( ! get_option( 'wp_sm_updater_' . $function . '_done', 0 ) ) {
							$at_least_one = true;

							if ( ! function_exists( $function ) ) {
								require_once SM_PATH . 'includes/sm-update-functions.php';
							}

							call_user_func( $function );

							?>
							<div class="notice notice-success">
								<p><code><?php echo $function; ?></code> executed.</p>
							</div>
							<?php
						}
					}
				}

				if ( ! isset( $at_least_one ) ) {
					?>
					<div class="notice notice-success">
						<p>All update functions have already been executed.</p>
					</div>
					<?php
				}
			}

			return 'no';
		} );

		add_action( 'sm_admin_settings_sanitize_option_post_content_enabled', function ( $value ) {
			$value = intval( $value );

			if ( $value >= 10 ) {
				global $wpdb, $skip_content_check;

				$skip_content_check = true;

				$sm = SermonManager::get_instance();

				// All sermons.
				$sermons = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE `post_type` = %s", 'wpfc_sermon' ) );

				foreach ( $sermons as $sermon ) {
					$sermon_id = $sermon->ID;

					if ( 11 === $value ) {
						$sm->render_sermon_into_content( $sermon_id, null, true );
					} else {
						$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET `post_content` = '' WHERE `ID` = %d", $sermon_id ) );
					}
				}

				$skip_content_check = false;

				$value = intval( substr( $value, 1 ) );
			}

			return $value;
		} );

		// Remove audio ID if it's not needed.
		add_action( 'save_post_wpfc_sermon', function ( $post_ID, $post, $update ) {
			if ( ! isset( $_POST['sermon_audio_id'] ) && ! isset( $_POST['sermon_audio'] ) ) {
				return;
			}

			$audio_id  = &$_POST['sermon_audio_id'];
			$audio_url = $_POST['sermon_audio'];

			// Attempt to get remote file size.
			if ( $audio_url && ! $audio_id ) {
				// Put our options as default (sorry).
				stream_context_set_default( array(
					'http' => array(
						'method'  => 'HEAD',
						'timeout' => 2,
					),
				) );

				// Do the request.
				$head = array_change_key_case( get_headers( $audio_url, 1 ) );

				if ( $head && isset( $head['content-length'] ) ) {
					update_post_meta( $post_ID, '_wpfc_sermon_size', $head['content-length'] ?: 0 );
				}
			}

			if ( ! $audio_id ) {
				return;
			}

			$parsed_audio_url   = parse_url( $audio_url, PHP_URL_HOST );
			$parsed_website_url = parse_url( home_url(), PHP_URL_HOST );

			if ( $parsed_audio_url !== $parsed_website_url ) {
				$audio_id = '';
				update_post_meta( $post_ID, 'sermon_audio_id', $audio_id );
			}

			// Attempt to get audio file duration.
			if ( $audio_id ) {
				$the_file = wp_get_attachment_metadata( $audio_id );

				if ( $the_file ) {
					if ( isset( $the_file['length'] ) ) {
						$length                         = date( 'H:i:s', $the_file['length'] );
						$_POST['_wpfc_sermon_duration'] = $length;
						update_post_meta( $post_ID, '_wpfc_sermon_duration', $length );
					}

					if ( isset( $the_file['filesize'] ) ) {
						$_POST['_wpfc_sermon_size'] = $the_file['filesize'];
						update_post_meta( $post_ID, '_wpfc_sermon_size', $the_file['filesize'] );
					}
				}
			}
		}, 40, 3 );

		// Fix shortcode pagination.
		add_filter( 'redirect_canonical', function ( $redirect_url ) {
			global $wp_query;

			if ( get_query_var( 'paged' ) && $wp_query->post && false !== strpos( $wp_query->post->post_content, '[sermons' ) ) {
				return false;
			}

			return $redirect_url;
		} );

	}

	/**
	 * Creates or returns an instance of main plugin class.
	 *
	 * @deprecated 2.16.0 Use \SermonManager\Plugin::instance() instead.
	 *
	 * @return \SermonManager\Plugin A single instance of the main plugin class.
	 */
	public static function get_instance() {
		return \SermonManager\Plugin::instance();
	}

	/**
	 * Instead of loading options variable each time in every code snippet, let's have it in one place.
	 *
	 * @param string $name    Option name.
	 * @param string $default Default value to return if option is not set (defaults to empty string).
	 *
	 * @deprecated 2.16.0. Use settings manager to get an option, or `sm_get_option( $name, $default );`.
	 *
	 * @return mixed Returns option value or an empty string if it doesn't exist. Just like WP does.
	 */
	public static function getOption( $name = '', $default = '' ) { // phpcs:ignore
		return \SermonManager\Plugin::instance()->settings_manager->get_option( $name, $default );
	}
}
