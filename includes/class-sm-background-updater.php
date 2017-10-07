<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

if ( ! class_exists( 'WP_Async_Request', false ) ) {
	include_once 'libraries/wp-async-request.php';
}

if ( ! class_exists( 'WP_Background_Process', false ) ) {
	include_once 'libraries/wp-background-process.php';
}

/**
 * @since 2.8
 */
class SM_Background_Updater extends WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'sm_updater';

	/**
	 * Is the updater running?
	 *
	 * @return boolean
	 */
	public function is_updating() {
		return false === $this->is_queue_empty();
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param string $callback Update callback function
	 *
	 * @return mixed
	 */
	protected function task( $callback ) {
		if ( ! defined( 'SM_UPDATING' ) ) {
			define( 'SM_UPDATING', true );
		}

		include_once 'sm-update-functions.php';

		if ( is_callable( $callback ) ) {
			call_user_func( $callback );
		}

		return false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		SM_Install::update_db_version();
		parent::complete();
	}
}
