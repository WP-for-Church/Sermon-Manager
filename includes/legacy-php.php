<?php
/*
 * This file will contain all functions that older PHP versions don't have
 */

// Hack for old php versions to use boolval()
if ( ! function_exists( 'boolval' ) ) {
	function boolval( $val ) {
		return (bool) $val;
	}
}