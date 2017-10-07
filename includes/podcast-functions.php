<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

/**
 * Pre-hook for adding podcast data to the XML file.
 */
add_action( 'pre_get_posts', 'wpfc_podcast_add_hooks', 9999 );

/**
 * User can also use a custom action for echoing the whole feed.
 * Example: `do_action( 'do_feed_podcast' );`
 */
add_action( 'do_feed_podcast', 'wpfc_podcast_render', 10, 1 );

/**
 * Add podcast data to the WordPress default XML feed
 *
 * @param WP_Query $query The query
 *
 * @return void
 */
function wpfc_podcast_add_hooks( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_feed() ) {
		if ( is_post_type_archive( 'wpfc_sermon' ) || is_tax( 'wpfc_preacher' ) || is_tax( 'wpfc_sermon_topics' ) || is_tax( 'wpfc_service_type' ) || is_tax( 'wpfc_sermon_series' ) || is_tax( 'wpfc_bible_book' ) ) {
			add_filter( 'get_post_time', 'wpfc_podcast_item_date', 10, 3 );
			add_filter( 'bloginfo_rss', 'wpfc_bloginfo_rss_filter', 10, 2 );
			add_filter( 'wp_title_rss', 'wpfc_modify_podcast_title', 99, 3 );
			add_action( 'rss_ns', 'wpfc_podcast_add_namespace' );
			add_action( 'rss2_ns', 'wpfc_podcast_add_namespace' );
			add_action( 'rss_head', 'wpfc_podcast_add_head' );
			add_action( 'rss2_head', 'wpfc_podcast_add_head' );
			add_action( 'rss_item', 'wpfc_podcast_add_item' );
			add_action( 'rss2_item', 'wpfc_podcast_add_item' );
			add_filter( 'the_content_feed', 'wpfc_podcast_summary', 10, 3 );
			add_filter( 'the_excerpt_rss', 'wpfc_podcast_summary' );
			add_filter( 'rss_enclosure', '__return_empty_string' );

			if ( \SermonManager::getOption( 'enable_podcast_html_description' ) ) {
				add_filter( 'the_excerpt_rss', 'wpautop' );
			}

			// remove sermons that don't have audio
			$query->set( 'meta_query', array(
					'relation' => 'AND',
					array(
						'key'     => 'sermon_audio',
						'compare' => 'EXISTS'
					),
					array(
						'key'     => 'sermon_audio',
						'value'   => '',
						'compare' => '!='
					)
				)
			);

			if ( intval( \SermonManager::getOption( 'podcasts_per_page' ) ) !== 0 ) {
				$query->set( 'posts_per_rss', intval( \SermonManager::getOption( 'podcasts_per_page' ) ) );
			}
		}
	}
}

/**
 * Load the template used for podcast XML.
 *
 * It can be overridden by putting the `wpfc-podcast-feed.php` file in the root of your active theme.
 *
 * @since 2.3.5 Added ability to override the default template
 * @return void
 */
function wpfc_podcast_render() {
	if ( $overridden_template = locate_template( 'wpfc-podcast-feed.php' ) ) {
		load_template( $overridden_template );
	} else {
		load_template( SM_PATH . 'views/wpfc-podcast-feed.php' );
	}
}

/**
 * Add iTunes XML Namespace to the XML head
 *
 * @return void
 */
function wpfc_podcast_add_namespace() {
	echo 'xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"';
}

/**
 * Add iTunes header data
 *
 * @return void
 */
function wpfc_podcast_add_head() {
	remove_filter( 'the_content', 'add_wpfc_sermon_content' );
	?>
    <copyright><?php echo html_entity_decode( esc_html( \SermonManager::getOption( 'copyright' ) ), ENT_COMPAT, 'UTF-8' ) ?></copyright>
    <itunes:subtitle><?php echo esc_html( \SermonManager::getOption( 'itunes_subtitle' ) ) ?></itunes:subtitle>
    <itunes:author><?php echo esc_html( \SermonManager::getOption( 'itunes_author' ) ) ?></itunes:author>
	<?php if ( trim( category_description() ) !== '' ) : ?>
        <itunes:summary><?php echo str_replace( '&nbsp;', '',
				\SermonManager::getOption( 'enable_podcast_html_description' ) ?
					stripslashes( wpautop( wp_filter_kses( category_description() ) ), true ) :
					stripslashes( wp_filter_nohtml_kses( category_description() ) ) ); ?></itunes:summary>
	<?php else: ?>
        <itunes:summary><?php echo str_replace( '&nbsp;', '',
				\SermonManager::getOption( 'enable_podcast_html_description' ) ?
					stripslashes( wpautop( wp_filter_kses( \SermonManager::getOption( 'itunes_summary' ) ) ) ) :
					stripslashes( wp_filter_nohtml_kses( \SermonManager::getOption( 'itunes_summary' ) ) ) ); ?></itunes:summary>
	<?php endif; ?>
    <itunes:owner>
        <itunes:name><?php echo esc_html( \SermonManager::getOption( 'itunes_owner_name' ) ) ?></itunes:name>
        <itunes:email><?php echo esc_html( \SermonManager::getOption( 'itunes_owner_email' ) ) ?></itunes:email>
    </itunes:owner>
    <itunes:explicit>no</itunes:explicit>
	<?php if ( \SermonManager::getOption( 'itunes_cover_image' ) ) : ?>
        <itunes:image href="<?php echo esc_url( \SermonManager::getOption( 'itunes_cover_image' ) ) ?>"/>
	<?php endif; ?>
    <itunes:category text="<?php echo esc_attr( \SermonManager::getOption( 'itunes_top_category' ) ) ?>">
        <itunes:category text="<?php echo esc_attr( \SermonManager::getOption( 'itunes_sub_category' ) ) ?>"/>
    </itunes:category>
	<?php
}

/**
 * Add iTunes data to each sermon
 *
 * @return void
 */
function wpfc_podcast_add_item() {
	global $post;
	$audio_raw       = str_ireplace( 'https://', 'http://', get_post_meta( $post->ID, 'sermon_audio', true ) );
	$audio_p         = strrpos( $audio_raw, '/' ) + 1;
	$audio_raw       = urldecode( $audio_raw );
	$audio           = substr( $audio_raw, 0, $audio_p ) . rawurlencode( substr( $audio_raw, $audio_p ) );
	$speaker         = strip_tags( get_the_term_list( $post->ID, 'wpfc_preacher', '', ' &amp; ', '' ) );
	$series          = strip_tags( get_the_term_list( $post->ID, 'wpfc_sermon_series', '', ', ', '' ) );
	$topics          = strip_tags( get_the_term_list( $post->ID, 'wpfc_sermon_topics', '', ', ', '' ) );
	$post_image      = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );
	$post_image      = str_ireplace( 'https://', 'http://', ! empty( $post_image['0'] ) ? $post_image['0'] : '' );
	$audio_duration  = get_post_meta( $post->ID, '_wpfc_sermon_duration', true ) ?: '0:00';
	$audio_file_size = get_post_meta( $post->ID, '_wpfc_sermon_size', 'true' ) ?: 0;
	?>
    <itunes:author><?php echo esc_html( $speaker ); ?></itunes:author>
    <itunes:subtitle><?php echo esc_html( $series ); ?></itunes:subtitle>
    <itunes:summary><?php echo preg_replace( '/&nbsp;/', '',
			\SermonManager::getOption( 'enable_podcast_html_description' ) ?
				stripslashes( wpautop( wp_filter_kses( get_wpfc_sermon_meta( 'sermon_description' ) ) ) ) :
				stripslashes( wp_filter_nohtml_kses( get_wpfc_sermon_meta( 'sermon_description' ) ) ) ); ?></itunes:summary>
	<?php if ( $post_image ) : ?>
        <itunes:image href="<?php echo esc_url( $post_image ); ?>"/>
	<?php endif; ?>
	<?php if ( \SermonManager::getOption( 'podtrac' ) ) : ?>
        <enclosure
                url="http://dts.podtrac.com/redirect.mp3/<?php echo esc_url( preg_replace( '#^https?://#', '', $audio ) ); ?>"
                length="<?php echo esc_attr( $audio_file_size ); ?>"
                type="audio/mpeg"/>
	<?php else: ?>
        <enclosure url="<?php echo esc_url( $audio ); ?>" length="<?php echo esc_attr( $audio_file_size ); ?>"
                   type="audio/mpeg"/>
	<?php endif; ?>
    <itunes:duration><?php echo esc_html( $audio_duration ); ?></itunes:duration>
	<?php if ( $topics ): ?>
        <itunes:keywords><?php echo esc_html( $topics ); ?></itunes:keywords>
	<?php endif; ?>

	<?php
}

/**
 * Replace feed item content and excerpt with Sermon description
 *
 * @param string $content Original content
 *
 * @return string Modified content
 */
function wpfc_podcast_summary( $content ) {
	if ( \SermonManager::getOption( 'enable_podcast_html_description' ) ) {
		$content = stripslashes( wpautop( wp_filter_kses( get_wpfc_sermon_meta( 'sermon_description' ) ) ) );
	} else {
		$content = stripslashes( wp_filter_nohtml_kses( get_wpfc_sermon_meta( 'sermon_description' ) ) );
	}

	return $content;
}

/**
 * Replace feed item published date with Sermon date
 *
 * @param string $time The formatted time.
 * @param string $d    Format to use for retrieving the time the post was written.
 *                     Accepts 'G', 'U', or php date format. Default 'U'.
 * @param bool   $gmt  Whether to retrieve the GMT time. Default false.
 *
 * @return string Modified date
 */
function wpfc_podcast_item_date( $time, $d = 'U', $gmt = false ) {
	return sm_get_the_date( $d );
}

/**
 * Replace feed title with the one defined in Sermon Manager settings
 *
 * @param string $title Default title
 *
 * @return string Modified title
 */
function wpfc_modify_podcast_title( $title ) {
	$podcast_title = esc_html( \SermonManager::getOption( 'title' ) );

	if ( $podcast_title !== '' ) {
		return $podcast_title;
	}

	return $title;
}

/**
 * Modifies get_bloginfo output and injects Sermon Manager data
 *
 * @param string $info Default data
 * @param string $show Requested data
 *
 * @return string Modified data
 */
function wpfc_bloginfo_rss_filter( $info, $show ) {
	$new_info = '';

	switch ( $show ) {
		case 'name':
			$new_info = esc_html( \SermonManager::getOption( 'title' ) );
			break;
		case 'description':
			if ( \SermonManager::getOption( 'enable_podcast_html_description' ) ) {
				$new_info = stripslashes( wpautop( wp_filter_kses( \SermonManager::getOption( 'itunes_summary' ) ) ) );
			} else {
				$new_info = stripslashes( wp_filter_nohtml_kses( \SermonManager::getOption( 'itunes_summary' ) ) );
			}

			break;
	}

	if ( $new_info !== '' ) {
		return $new_info;
	}

	return $info;
}
