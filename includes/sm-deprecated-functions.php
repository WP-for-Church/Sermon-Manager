<?php
/**
 * Place where functions come to die.
 *
 * @since 2.4.9
 */


/**
 * Searches WP_Query for sermon_date meta sort and removes it.
 * `sermon_date` meta has been removed in 2.4.7.
 *
 * @param WP_Query $data Query instance
 *
 * @return WP_Query
 *
 * @since 2.4.9
 */
function sm_modify_wp_query( $data ) {
	// If it's not a sermon, bail out
	if ( empty( $data->query_vars['post_type'] ) || $data->query_vars['post_type'] !== 'wpfc_sermon' ) {
		return $data;
	}

	foreach ( array( 'query' => $data->query, 'query_vars' => $data->query_vars ) as $type => $vars ) {
		// Modify ordering
		if ( ! empty( $vars['orderby'] ) && in_array( $vars['orderby'], array(
				'meta_value',
				'meta_value_num'
			) ) && $vars['meta_key'] === 'sermon_date' ) {
			$vars['orderby'] = 'date';
			unset( $vars['meta_key'], $vars['meta_value_num'], $vars['meta_compare'] );

			// save modified data to original query
			$data->{$type} = $vars;
		}
	}

	return $data;
}

add_filter( 'pre_get_posts', 'sm_modify_wp_query' );