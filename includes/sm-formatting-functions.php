<?php
/**
 * Functions for formatting data.
 *
 * @package SM/Core/Formatting
 */

defined( 'ABSPATH' ) or die;

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param string|array $var The variable to clean.
 *
 * @return string|array
 * @since 2.7
 */
function sm_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'sm_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}

/**
 * Sanitize a string destined to be a tooltip.
 *
 * @param string $var The variable to sanitize.
 *
 * @return string
 * @since 2.9
 */
function sm_sanitize_tooltip( $var ) {
	return htmlspecialchars( wp_kses( html_entity_decode( $var ), array(
		'br'     => array(),
		'em'     => array(),
		'strong' => array(),
		'small'  => array(),
		'span'   => array(),
		'ul'     => array(),
		'li'     => array(),
		'ol'     => array(),
		'p'      => array(),
	) ) );
}
