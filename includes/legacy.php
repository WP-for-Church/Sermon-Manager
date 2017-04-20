<?php
/*
 * This file will contain all functions that will call rewritten classes/functions
 */

// Hack for old php versions to use boolval()
if ( ! function_exists( 'boolval' ) ) {
	function boolval( $val ) {
		return (bool) $val;
	}
}