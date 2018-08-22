<?php
/**
 * Functions used for podcast rendering.
 *
 * @package SM/Core/Podcasting
 */

defined( 'ABSPATH' ) or die;

/**
 * User can also use a custom action for echoing the whole feed.
 * Example: `do_action( 'do_feed_podcast' );`
 */
add_action( 'do_feed_podcast', 'wpfc_podcast_render', 10, 1 );

/**
 * Redirection, if enabled in settings.
 */
add_action( 'parse_request', function () {
	if ( SermonManager::getOption( 'enable_podcast_redirection' ) ) {
		$old_url     = wp_make_link_relative( preg_replace( '{/$}', '', SermonManager::getOption( 'podcast_redirection_old_url' ) ) );
		$current_url = preg_replace( '{/$}', '', $_SERVER['REQUEST_URI'] );

		if ( strpos( $current_url, $old_url ) !== false ) {
			wp_redirect( SermonManager::getOption( 'podcast_redirection_new_url' ), 301 );
			exit;
		}
	}
} );

/**
 * Render the feed.
 *
 * The view can be overridden by placing a file named "wpfc-podcast-feed.php" in your (child) theme.
 */
add_action( 'rss_tag_pre', function () {
	global $post_type, $taxonomy;

	if ( 'wpfc_sermon' === $post_type || in_array( $taxonomy, sm_get_taxonomies() ) ) {
		$overridden_template = locate_template( 'wpfc-podcast-feed.php' );
		if ( $overridden_template ) {
			load_template( $overridden_template );
		} else {
			load_template( SM_PATH . 'views/wpfc-podcast-feed.php' );
		}

		exit;
	}
} );
