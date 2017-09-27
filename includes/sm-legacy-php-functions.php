<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

/*
 * This file will contain all functions that older PHP versions don't have, or workarounds
 */

// Hack for old php versions to use boolval()
if ( ! function_exists( 'boolval' ) ) {
	function boolval( $val ) {
		return (bool) $val;
	}
}