<?php
/**
 * This is the handler for notices, errors, warnings, etc...
 *
 * Notice is universal term for any type. Types are: Error, Warning, Info.
 *
 * @since   2.16.0
 *
 * @package SermonManager\Admin
 */

namespace SermonManager\Admin;

/**
 * Class Notice_Manager
 *
 * @package SermonManagerPro
 */
class Notices_Manager {

	/**
	 * The errors.
	 *
	 * @var array
	 */
	protected $error = array();

	/**
	 * The warnings.
	 *
	 * @var array
	 */
	protected $warning = array();

	/**
	 * The information notices.
	 *
	 * @var array
	 */
	protected $info = array();

	/**
	 * The success notices.
	 *
	 * @var array
	 */
	protected $success = array();

	/**
	 * Should we save after adding each error or no. Defaults to false, unless we don't have action to hook to at the
	 * end of WordPress execution.
	 *
	 * @var bool
	 */
	protected $save_instantly = true; // Temporarily true.

	/**
	 * Notice_Manager constructor.
	 */
	public function __construct() {
		// Define notice levels.
		define( 'SM_NOTICE_SUCCESS', 0 );
		define( 'SM_NOTICE_ERROR', 1 );
		define( 'SM_NOTICE_WARN', 2 );
		define( 'SM_NOTICE_INFO', 3 );

		// Load notices into the manager.
		$this->_load_notices();

		// Save notices before shutdown.
		if ( has_action( 'shutdown' && true !== $this->save_instantly ) ) {
			add_action( 'shutdown', array( $this, '_save_notices' ) );
		} else {
			$this->save_instantly = true;
		}
	}

	/**
	 * Loads the notices into class.
	 */
	protected function _load_notices() {
		// Get notices from the db.
		$notices = get_option( 'sm_notices', array() );

		// Load them into the manager.
		foreach ( $notices as $type => $notice ) {
			if ( property_exists( $this, $type ) ) {
				$this->$type = $notice;
			}
		}
	}

	/**
	 * Adds an error to stack.
	 *
	 * @param string $message  The notice message (required).
	 * @param string $context  The notice context.
	 * @param bool   $preserve Should it stay after reload or not.
	 */
	public function add_error( $message = '', $context = '', $preserve = true ) {
		$this->_add_notice( $message, $context, $preserve, SM_NOTICE_ERROR );
	}

	/**
	 * Adds an success notice to stack.
	 *
	 * @param string $message  The notice message (required).
	 * @param string $context  The notice context.
	 * @param bool   $preserve Should it stay after reload or not.
	 * @param int    $type     The notice type.
	 */
	protected function _add_notice( $message = '', $context = '', $preserve = true, $type = null ) {
		// Fill out missing data/sanitize.
		$notice = $this->_fill_out_missing_data( array(
			'message'  => $message,
			'context'  => $context,
			'preserve' => $preserve,
			'type'     => $type,
		) );

		// Do not add the notice if message doesn't exist.
		if ( ! $notice ) {
			return;
		}

		switch ( $type ) {
			case SM_NOTICE_ERROR:
				$this->error[] = $notice;
				break;
			case SM_NOTICE_INFO:
				$this->info[] = $notice;
				break;
			case SM_NOTICE_WARN:
				$this->warning[] = $notice;
				break;
			case SM_NOTICE_SUCCESS:
				$this->success[] = $notice;
				break;
		}

		if ( $this->save_instantly ) {
			$this->_save_notices();
		}
	}

	/**
	 * Adds missing data, and sanitizes it.
	 *
	 * @param array|string $notice The notice.
	 *
	 * @return array|false Cleaned notice or false if there is no message field.
	 *
	 * @throws \InvalidArgumentException If the notice is not array or string.
	 */
	protected function _fill_out_missing_data( $notice ) {
		// If it's a string, let's assume that we want to create an error without context.
		if ( is_string( $notice ) ) {
			$notice = array(
				'message' => sanitize_text_field( trim( $notice ) ),
			);
		}

		// Add the missing fields.
		$notice += array(
			'message'  => '',
			'context'  => null,
			'preserve' => true,
			'seen'     => false,
			'type'     => SM_NOTICE_ERROR,
		);

		// Sanitize the array.
		if ( is_array( $notice ) ) {
			foreach ( $notice as $variable => $value ) {
				switch ( $variable ) {
					case 'message':
						$notice[ $variable ] = isset( $value ) ? sanitize_text_field( $value ) : '';
						break;
					case 'context':
						$notice[ $variable ] = isset( $value ) ? sanitize_text_field( $value ) : null;
						break;
					case 'preserve':
						$notice[ $variable ] = isset( $value ) ? boolval( $value ) : true;
						break;
					case 'seen':
						$notice[ $variable ] = isset( $value ) ? boolval( $value ) : false;
						break;
					case 'type':
						$notice[ $variable ] = isset( $value ) ? intval( $value ) : SM_NOTICE_ERROR;
						break;
				}
			}
		} else {
			throw new \InvalidArgumentException( __( 'Notice is not array nor string. Unacceptable.', 'sermon-manager-for-wordpress' ) );
		}

		// Do not return the notice if there is no message.
		if ( empty( $notice['message'] ) || ! $notice['message'] ) {
			return false;
		}

		return $notice;
	}

	/**
	 * Saves notices into the database.
	 */
	public function _save_notices() {
		$notices = array(
			'error'   => $this->get_errors(),
			'warning' => $this->get_warnings(),
			'info'    => $this->get_infos(),
			'success' => $this->get_success(),
		);

		foreach ( $notices as $type => $notice_group ) {
			foreach ( $notice_group as $id => $notice ) {
				if ( ( isset( $notice['seen'] ) && isset( $notice['preserve'] ) ) &&
				     ( true === $notice['seen'] && false === $notice['preserve'] ) ) { // phpcs:ignore
					unset( $notices[ $type ][ $id ] );
				}
			}
		}

		update_option( 'sm_notices', $notices );
	}

	/**
	 * Gets current errors.
	 *
	 * @return array Errors.
	 */
	public function get_errors() {
		return $this->error;
	}

	/**
	 * Gets current warnings.
	 *
	 * @return array Warnings.
	 */
	public function get_warnings() {
		return $this->warning;
	}

	/**
	 * Gets current information notices.
	 *
	 * @return array Information notices.
	 */
	public function get_infos() {
		return $this->info;
	}

	/**
	 * Gets current success notices.
	 *
	 * @return array Success notices.
	 */
	public function get_success() {
		return $this->success;
	}

	/**
	 * Adds an information to stack.
	 *
	 * @param string $message  The notice message (required).
	 * @param string $context  The notice context.
	 * @param bool   $preserve Should it stay after reload or not.
	 */
	public function add_info( $message = '', $context = '', $preserve = true ) {
		$this->_add_notice( $message, $context, $preserve, SM_NOTICE_INFO );
	}

	/**
	 * Adds an warning to stack.
	 *
	 * @param string $message  The notice message (required).
	 * @param string $context  The notice context.
	 * @param bool   $preserve Should it stay after reload or not.
	 */
	public function add_warning( $message = '', $context = '', $preserve = true ) {
		$this->_add_notice( $message, $context, $preserve, SM_NOTICE_WARN );
	}

	/**
	 * Adds an success notice to stack.
	 *
	 * @param string $message  The notice message (required).
	 * @param string $context  The notice context.
	 * @param bool   $preserve Should it stay after reload or not.
	 */
	public function add_success( $message = '', $context = '', $preserve = true ) {
		$this->_add_notice( $message, $context, $preserve, SM_NOTICE_SUCCESS );
	}

	/**
	 * Sets notice as "seen".
	 *
	 * @param string $message The notice to find, by message.
	 * @param bool   $seen    If it's seen or not.
	 */
	public function set_seen( $message = '', $seen = true ) {
		foreach ( $this->get_notices() as $type => $notice_group ) {
			foreach ( $notice_group as $id => $notice ) {
				if ( $message === $notice['message'] ) {
					$this->$type[ $id ]['seen'] = $seen;
				}
			}
		}

		if ( $this->save_instantly ) {
			$this->_save_notices();
		}
	}

	/**
	 * Gets current notices.
	 *
	 * @return array The notices.
	 */
	public function get_notices() {
		return array(
			'error'   => $this->get_errors(),
			'warning' => $this->get_warnings(),
			'info'    => $this->get_infos(),
			'success' => $this->get_success(),
		);
	}
}
