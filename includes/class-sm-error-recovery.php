<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

/**
 * After leaving many websites temporarily unusable (mostly because of usage on old and outdated PHP
 * versions), we needed to make a script that would allow users to recover their websites after a
 * fatal error caused by Sermon Manager.
 *
 * This script is the solution for it.
 *
 * It will hook into PHP shutdown action (like WordPress actions, just has no relation to WordPress)
 * and listen for errors. Errors that are being caught are E_ERROR, E_RECOVERABLE_ERROR, E_CORE_ERROR,
 * E_COMPILE_ERROR, E_COMPILE_ERROR.
 *
 * It will show an admin error message if error is caught, with an button to show stacktrace, to submit
 * an anonymous report and to re-enable plugin.
 *
 * Absolute stacktrace file paths will be converted to relative, for user privacy.
 *
 * Goal of this script is to allow frontend access even if Sermon Manager errors out.
 *
 * @since   2.7
 * @version 1.0
 */
class SM_Error_Recovery {
	/**
	 * @var string Name of constant that has "__FILE__" magic constant of main plugin file
	 * @access private
	 */
	private static $_plugin_main_file = 'SM_PLUGIN_FILE';

	/**
	 * @var array Errors to catch
	 * @access private
	 */
	private static $_catch_errors = array(
		E_ERROR,
		E_RECOVERABLE_ERROR,
		E_CORE_ERROR,
		E_COMPILE_ERROR,
		E_COMPILE_ERROR
	);

	/**
	 * @var array An associative array describing the PHP error with keys "type", "message",
	 *            "file" and "line"
	 * @access private
	 */
	private static $_error;

	/**
	 * @var null|self The instance of this class
	 */
	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Gets last PHP error and executes actions on catch
	 */
	public static function do_catch() {
		global $table_prefix;
		$mysqli = new mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );

		// enable recovery if user disabled
		if ( isset( $_GET['sm_enable_recovery'] ) ) {
			$sql    = "SELECT option_value FROM {$table_prefix}options WHERE option_name = '_sm_recovery_do_not_catch'";
			$result = $mysqli->query( $sql );
			if ( $result->num_rows === 0 ) {
				$sql = "INSERT INTO {$table_prefix}options (option_name, option_value, autoload) VALUES ('_sm_recovery_do_not_catch', '0', 'yes')";
			} else {
				$sql = "UPDATE {$table_prefix}options SET option_value = '0' WHERE option_name = '_sm_recovery_do_not_catch'";
			}
			$mysqli->query( $sql );
		}

		$sql    = "SELECT option_value FROM {$table_prefix}options WHERE option_name = '_sm_recovery_do_not_catch'";
		$result = $mysqli->query( $sql );
		if ( $result->num_rows === 0 ) {
			$does_not_exist  = true;
			$sm_do_not_catch = false;
		} else {
			$result          = $result->fetch_assoc();
			$sm_do_not_catch = $result['option_value'] == 1;
		}

		if ( $sm_do_not_catch ) {
			return;
		}

		self::$_error = error_get_last();

		if ( self::_is_fatal() ) {
			// check if it's caused by SM
			self::_update_db();

			if ( ! empty( $does_not_exist ) ) {
				$sql = "INSERT INTO {$table_prefix}options (option_name, option_value, autoload) VALUES ('_sm_recovery_do_not_catch', '1', 'yes')";
			} else {
				$sql = "UPDATE {$table_prefix}options SET option_value = '1' WHERE option_name = '_sm_recovery_do_not_catch'";
			}
			$mysqli->query( $sql );

			if ( strpos( $_SERVER['REQUEST_URI'], 'wp-admin' ) === false ) {
				$content = file_get_contents( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
				$headers = get_headers( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
				if ( ! ( strpos( strtolower( $content ), 'fatal error' ) === false &&
				         $content !== '' &&
				         substr( $headers[0], 9, 3 ) != 500 ) ) {
					self::reset_db();
				}
			} else {
				if ( strpos( self::$_error['message'], 'sermon' ) === false &&
				     strpos( $_SERVER['REQUEST_URI'], 'sermon' ) === false ) {
					self::reset_db();
				}
			}

			$mysqli->query( "UPDATE {$table_prefix}options SET option_value = '0' WHERE option_name = '_sm_recovery_do_not_catch'" );
		}
	}

	/**
	 * Checks if PHP error is fatal
	 *
	 * @access private
	 *
	 * @return bool True if it is, false otherwise
	 */
	private static function _is_fatal() {
		return in_array( self::$_error['type'], self::$_catch_errors );
	}

	/**
	 * Prevents Sermon Manager from running and saves error message for displaying
	 *
	 * @access private
	 */
	private static function _update_db() {
		global $table_prefix;
		$mysqli = new mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );

		// check if set
		$sql    = "SELECT option_id FROM {$table_prefix}options WHERE option_name = '_sm_recovery_disable'";
		$result = $mysqli->query( $sql );
		if ( $result->num_rows === 0 ) {
			$sql = "INSERT INTO {$table_prefix}options (option_name, option_value, autoload) VALUES ('_sm_recovery_disable', '1', 'yes')";
		} else {
			$sql = "UPDATE {$table_prefix}options SET option_value = '1' WHERE option_name = '_sm_recovery_disable'";
		}
		$mysqli->query( $sql );

		// check if set
		$sql    = "SELECT option_id FROM {$table_prefix}options WHERE option_name = '_sm_recovery_last_fatal_error'";
		$result = $mysqli->query( $sql );
		if ( $result->num_rows === 0 ) {
			$sql = "INSERT INTO {$table_prefix}options (option_name, option_value, autoload) VALUES ('_sm_recovery_last_fatal_error', '" . $mysqli->real_escape_string( self::_get_message() ) . "', 'yes')";
		} else {
			$sql = "UPDATE {$table_prefix}options SET option_value = '" . $mysqli->real_escape_string( self::_get_message() ) . "' WHERE option_name = '_sm_recovery_last_fatal_error'";
		}
		$mysqli->query( $sql );
	}

	/**
	 * Gets PHP error message
	 *
	 * @access private
	 *
	 * @return string
	 */
	private static function _get_message() {
		return self::$_error['message'];
	}

	/**
	 * Allows Sermon Manager to run again, called on plugin update or by user
	 */
	public static function reset_db() {
		global $table_prefix;
		$mysqli = new mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );

		// check if set
		$sql    = "SELECT option_id FROM {$table_prefix}options WHERE option_name = '_sm_recovery_disable'";
		$result = $mysqli->query( $sql );
		if ( $result->num_rows === 0 ) {
			$sql = "INSERT INTO {$table_prefix}options (option_name, option_value, autoload) VALUES ('_sm_recovery_disable', '0', 'yes')";
		} else {
			$sql = "UPDATE {$table_prefix}options SET option_value = '0' WHERE option_name = '_sm_recovery_disable'";
		}

		$mysqli->query( $sql );
	}

	/**
	 * Displays WordPress admin error message
	 */
	public static function render_admin_message() {
		$plugin_data = get_plugin_data( constant( self::$_plugin_main_file ) );
		$plugin_name = $plugin_data['Name'];
		$old_error   = get_option( '_sm_recovery_last_fatal_error_hash' ) === md5( get_option( '_sm_recovery_last_fatal_error' ) );

		?>
        <div class="sm notice notice-error" id="sm-fatal-error-notice">
            <p id="notice-message">
				<?php /* Translators: %s: Plugin name */ ?>
					<?= wp_sprintf( esc_html__( '%s encountered a fatal error and recovered successfully.', 'sermon-manager-for-wordpress' ), '<strong>' . esc_html( $plugin_name . '</strong>' ) ) ?>

				<?php if ( $old_error ): ?>
					<?= esc_html__( 'The issue has already been submitted.', 'sermon-manager-for-wordpress' ) ?>
				<?php endif; ?></p>
            <p class="sm-actions">
				<?php if ( ! $old_error ): ?>
                    <a name="send-report" id="send-report" class="button button-primary">
						<?= esc_html_x( 'Send an anonymous report', 'Button', 'sermon-manager-for-wordpress' ) ?>
                    </a>
				<?php endif; ?>
                <a name="view-error" id="view-error" class="button">
					<?= esc_html_x( 'Show error message', 'Button', 'sermon-manager-for-wordpress' ) ?>
                </a>
                <a name="reactivate-plugin" id="reactivate-plugin" class="button">
					<?= esc_html_x( 'Reactivate Plugin', 'Button', 'sermon-manager-for-wordpress' ) ?>
                </a>
            </p>
            <pre id="sm-error"
                 style="display:none"><?php echo str_replace( ABSPATH, '~/', get_option( '_sm_recovery_last_fatal_error' ) ); ?></pre>
            <span class="spinner is-active" id="sm-spinner"></span>
            <div id="sm-curtain"></div>
            <div id="reactivate-dialog" title="<?= esc_attr_x( 'Are you sure?', 'Title', 'sermon-manager-for-wordpress' ) ?>"
                 style="display: none">
                <p><?= esc_html__( 'If the issue is not fixed, website will crash. (but we will recover it again)', 'sermon-manager-for-wordpress' ) ?></p>
            </div>
            <div id="send-report-dialog" title="<?= esc_attr_x( 'Optional info', 'title', 'sermon-manager-for-wordpress' ) ?>"
                 style="display: none">
                <p><?= esc_html__( 'If you have more information about the issue, please type it here (optional):', 'sermon-manager-for-wordpress' ) ?></p>
                <textarea aria-multiline="true"
                          title="<?= esc_attr_x( 'Issue details', 'Label', 'sermon-manager-for-wordpress' ) ?>" id="issue-info"
                          rows="5"
                          placeholder="<?= esc_attr_x( 'Steps for how to reproduce, etc&hellip;', 'Placeholder', 'sermon-manager-for-wordpress' ) ?>"></textarea>
                <p><?= esc_html__( 'Email for further contact (optional)', 'sermon-manager-for-wordpress' ) ?></p>
                <input type="email" placeholder="<?= esc_attr__( 'name@example.com', 'sermon-manager-for-wordpress' ); ?>"
                       title="<?= esc_attr_x( 'Email', 'Label', 'sermon-manager-for-wordpress' ) ?>" id="issue-email">
            </div>
        </div>
		<?php
	}

	/**
	 * Enqueue required scripts for displaying in admin area
	 */
	public static function enqueue_scripts_styles() {
		$plugin_data = get_plugin_data( constant( self::$_plugin_main_file ) );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'sm-error-recovery', SM_URL . 'assets/js/admin/error-recovery.js', array(), SM_VERSION );
		wp_localize_script( 'sm-error-recovery', 'sm_error_recovery_data', array(
			'stacktrace'       => urlencode( str_replace( ABSPATH, '~/', get_option( '_sm_recovery_last_fatal_error' ) ) ),
			'environment_info' => 'WordPress: ' . $GLOBALS['wp_version'] . '; Server: ' . ( function_exists( 'apache_get_version' ) ? apache_get_version() : 'N/A' ) . '; PHP: ' . PHP_VERSION . '; Sermon Manager:' . SM_VERSION . ';',
			'plugin_name'      => $plugin_data['Name'],

		) );
		wp_enqueue_style( 'sm-error-recovery', SM_URL . 'assets/css/error-recovery.css', array(), SM_VERSION );
	}

	/**
	 * Disables send report button.
	 */
	public static function disable_send_report_button() {
		update_option( '_sm_recovery_last_fatal_error_hash', md5( get_option( '_sm_recovery_last_fatal_error' ) ) );
		update_option( '_sm_recovery_disable_send', '1' );

		return true;
	}

	/**
	 * Re-allow recovery to work on update
	 */
	public static function upgrade_check() {
		$db_version = get_option( 'sm_version' );
		if ( empty( $db_version ) || $db_version != SM_VERSION ) {
			update_option( '_sm_recovery_do_not_catch', 0 );
			update_option( '_sm_recovery_disable', 0 );
		}
	}

	public function init() {
		$this->_hook();
	}

	/**
	 * Hooks into PHP error handing function and WordPress if plugin detected an error
	 *
	 * @access private
	 */
	private function _hook() {
		register_shutdown_function( array( get_class(), 'do_catch' ) );
		add_action( 'plugins_loaded', array( get_class(), 'upgrade_check' ) );

		if ( get_option( '_sm_recovery_disable' ) ) {
			$this->_register_wp_hooks();
			define( 'sm_break', true );
		}
	}

	/**
	 * Hooks into WP
	 *
	 * @access private
	 */
	private function _register_wp_hooks() {
		add_action( 'admin_enqueue_scripts', array( get_class(), 'enqueue_scripts_styles' ) );
		add_action( 'admin_notices', array( get_class(), 'render_admin_message' ), 0 );
		add_action( 'wp_ajax_sm_clear_fatal_error', array( get_class(), 'reset_db' ) );
		add_action( 'wp_ajax_sm_recovery_disable_send_report', array( get_class(), 'disable_send_report_button' ) );
	}
}
