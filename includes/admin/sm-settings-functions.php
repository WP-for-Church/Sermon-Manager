<?php
/**
 * Contains the functions that are used for settings callbacks.
 *
 * Return anything else than null to save that value instead.
 *
 * @package SermonManager\Admin
 */

/**
 * Execute specific update function.
 *
 * @param mixed  $value  The option value.
 * @param string $option The option name.
 *
 * @return string
 */
function sm_execute_specific_unexecuted_function( $value, $option ) {
	if ( '' !== $value ) {
		if ( ! function_exists( $value ) ) {
			require_once SM_PATH . 'includes/sm-update-functions.php';
		}

		if ( function_exists( $value ) ) {
			call_user_func( $value );

			// translators: %s: Function name in code tags. Effectively "<code>$function</code>".
			\SermonManager\Plugin::instance()->notices_manager->add_success( sprintf( __( 'Function %s executed.', 'sermon-manager-for-wordpress' ), "<code>${value}</code>" ) );
		} else {
			// translators: %s: Function name in code tags. Effectively "<code>$function</code>".
			\SermonManager\Plugin::instance()->notices_manager->add_warning( sprintf( __( 'Function %s not executed. Could not find the function. Typo?', 'sermon-manager-for-wordpress' ), "<code>${value}</code>" ) );
		}
	}

	return '';
}

/**
 * Execute all non-executed update functions.
 *
 * @param mixed  $value  The option value.
 * @param string $option The option name.
 *
 * @return string
 */
function sm_execute_all_unexecuted_functions( $value, $option ) {
	if ( 'yes' === $value ) {
		foreach ( \SM_Install::$db_updates as $version => $functions ) {
			foreach ( $functions as $function ) {
				if ( ! get_option( 'wp_sm_updater_' . $function . '_done', 0 ) ) {
					$at_least_one = true;

					if ( ! function_exists( $function ) ) {
						require_once SM_PATH . 'includes/sm-update-functions.php';
					}

					if ( function_exists( $function ) ) {
						call_user_func( $function );

						// translators: %s: Function name in code tags. Effectively "<code>$function</code>".
						\SermonManager\Plugin::instance()->notices_manager->add_success( sprintf( __( 'Function %s executed.', 'sermon-manager-for-wordpress' ), "<code>${value}</code>" ) );
					} else {
						// translators: %s: Function name in code tags. Effectively "<code>$function</code>".
						\SermonManager\Plugin::instance()->notices_manager->add_warning( sprintf( __( 'Function %s not executed. Could not find the function. Typo?', 'sermon-manager-for-wordpress' ), "<code>${value}</code>" ) );
					}
				}
			}
		}

		if ( ! isset( $at_least_one ) ) {
			\SermonManager\Plugin::instance()->notices_manager->add_success( __( 'All update functions have been executed.', 'sermon-manager-for-wordpress' ) );
		}
	}

	return 'no';
}

/**
 * Maybe enables post content rendering.
 *
 * @param mixed  $value  The option value.
 * @param string $option The option name.
 *
 * @return string
 */
function sm_maybe_render_post_content( $value, $option ) {
	$value = intval( $value );

	if ( $value >= 10 ) {
		global $wpdb, $skip_content_check;

		$skip_content_check = true;

		// All sermons.
		$sermons = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE `post_type` = %s", 'wpfc_sermon' ) );

		foreach ( $sermons as $sermon ) {
			$sermon_id = $sermon->ID;

			if ( 11 === $value ) {
				sm_render_sermon_into_content( $sermon_id, null, true );
			} else {
				$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET `post_content` = '' WHERE `ID` = %d", $sermon_id ) );
			}
		}

		$skip_content_check = false;

		$value = intval( substr( $value, 1 ) );
	}

	return $value;
}

