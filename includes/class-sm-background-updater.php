<?php
/**
 * Background updater class loader.
 * Sets SM related stuff and fires it.
 *
 * @package SM/Core/Updating
 */

defined( 'ABSPATH' ) or die;

/*
 * Compatibility, if parent already exists
 */
if ( \SermonManager::getOption( 'in_house_background_update' ) ) {
	if ( ! class_exists( 'SM_WP_Async_Request', false ) ) {
		include_once 'vendor/wp-async-request.php';
	}

	if ( ! class_exists( 'SM_WP_Background_Process', false ) ) {
		include_once 'vendor/wp-background-process.php';
	}
} else {
	if ( ! class_exists( 'WP_Async_Request', false ) ) {
		include_once 'vendor/wp-async-request.php';
		class_alias( 'SM_WP_Async_Request', 'WP_Async_Request' );
	} else {
		/* @noinspection PhpIgnoredClassAliasDeclaration */
		class_alias( 'WP_Async_Request', 'SM_WP_Async_Request' );
	}

	if ( ! class_exists( 'WP_Background_Process', false ) ) {
		include_once 'vendor/wp-background-process.php';
		class_alias( 'SM_WP_Background_Process', 'WP_Background_Process' );
	} else {
		/* @noinspection PhpIgnoredClassAliasDeclaration */
		class_alias( 'WP_Background_Process', 'SM_WP_Background_Process' );
	}
}

/**
 * Adds SM options and fires it.
 *
 * @since 2.8
 */
class SM_Background_Updater extends SM_WP_Background_Process {

	/**
	 * Action name.
	 *
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
	 * Task.
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param string $callback Update callback function.
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
	 * Complete.
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		SM_Install::update_db_version();
		parent::complete();
	}
}
