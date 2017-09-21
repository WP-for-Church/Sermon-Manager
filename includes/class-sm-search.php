<?php

defined( 'ABSPATH' ) or die; // exit if accessed directly

/**
 * Extends WP Search to include custom Sermon Manager datapoints during search
 *
 * @since 2.7
 */
class SM_Search {

	/**
	 * Hook into actions
	 */
	public function hook() {
		add_filter( 'posts_where', array( $this, 'where' ) );
		add_filter( 'posts_join', array( $this, 'join' ) );
		add_filter( 'posts_groupby', array( $this, 'groupby' ) );
	}

	/**
	 *
	 * @global $wpdb
	 *
	 * @param  string $where
	 *
	 * @return string
	 */
	public function where( $where ) {
		global $wpdb;

		if ( is_search() ) {
			$where .= "OR (t.name LIKE '%" . get_search_query() . "%' AND {$wpdb->posts}.post_status = 'publish')";
		}

		return $where;
	}

	/**
	 * Include taxonomies to default search
	 *
	 * @global $wpdb
	 *
	 * @param  string $join
	 *
	 * @return string
	 */
	public function join( $join ) {
		global $wpdb;

		if ( is_search() ) {
			$join .= "LEFT JOIN {$wpdb->term_relationships} tr ON {$wpdb->posts}.ID = tr.object_id INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id=tr.term_taxonomy_id INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id";
		}

		return $join;
	}

	/**
	 *
	 * @global $wpdb
	 *
	 * @param  int    $groupby
	 *
	 * @return string
	 */
	public function groupby( $groupby ) {
		global $wpdb;

		// we need to group on post ID
		$groupby_id = "{$wpdb->posts}.ID";
		if ( ! is_search() || strpos( $groupby, $groupby_id ) !== false ) {
			return $groupby;
		}
		// groupby was empty, use ours
		if ( ! strlen( trim( $groupby ) ) ) {
			return $groupby_id;
		}

		// wasn't empty, append ours
		return $groupby . ", " . $groupby_id;
	}
}

$search = new SM_Search;
$search->hook();
