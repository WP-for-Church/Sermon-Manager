<?php
/**
 * Place where functions come to die.
 *
 * @package SM/Graveyard
 *
 * @since   2.4.9
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) or die;

// Deprecated.
define( 'SM___FILE__', __FILE__ );
define( 'SERMON_MANAGER_PATH', SM_PATH );
define( 'SERMON_MANAGER_URL', SM_URL );
define( 'SERMON_MANAGER_VERSION', SM_VERSION );

/**
 * Outputs Sermon date. Wrapper for sm_the_date().
 *
 * @see        sm_the_date()
 *
 * @param string $d      PHP date format. Defaults to the date_format option if not specified.
 * @param string $before Optional. Output before the date.
 * @param string $after  Optional. Output after the date.
 *
 * @deprecated deprecated since 2.6, use sm_the_date() instead.
 */
function wpfc_sermon_date( $d, $before = '', $after = '' ) {
	_deprecated_function( __FUNCTION__, '2.13.0', 'sm_the_date' );
	sm_the_date( $d, $before, $after );
}

add_action( 'sermon_media', 'wpfc_sermon_media', 5 );
add_action( 'sermon_audio', 'wpfc_sermon_audio', 5 );
add_action( 'sermon_single', 'wpfc_sermon_single' );
add_action( 'sermon_excerpt', 'wpfc_sermon_excerpt' );

/**
 * Output attachments.
 *
 * @deprecated 2.12.5 - see wpfc_sermon_media().
 */
function wpfc_sermon_files() {
	_deprecated_function( __FUNCTION__, '2.12.5', 'wpfc_sermon_attachments' );
	do_action( 'sermon_media' );
}

/**
 * Output single sermon.
 *
 * @deprecated 2.12.5 - see wpfc_sermon_single() & wpfc_sermon_single_v2().
 */
function render_wpfc_sermon_single() {
	_deprecated_function( __FUNCTION__, '2.12.5', 'wpfc_sermon_single_v2' );

	do_action( 'sermon_single' );
}

/**
 * Output archive sermon.
 *
 * @deprecated 2.12.5 - see wpfc_sermon_excerpt() & wpfc_sermon_excerpt_v2().
 */
function render_wpfc_sermon_excerpt() {
	_deprecated_function( __FUNCTION__, '2.12.5', 'wpfc_sermon_excerpt_v2' );

	do_action( 'sermon_excerpt' );
}

/**
 * Renders v0 archive the_content.
 *
 * @deprecated 2.12.0
 */
function render_wpfc_sermon_archive() {
	_deprecated_function( __FUNCTION__, '2.12.0', 'wpfc_sermon_excerpt_v2' );

	global $post;
	// translators: Sermon Title.
	$title = printf( esc_attr__( 'Permalink to %s', 'sermon-manager-for-wordpress' ), the_title_attribute( 'echo=0' ) );
	?>
	<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<h2 class="sermon-title"><a href="<?php the_permalink(); ?>"
					title="<?php echo $title; ?>"
					rel="bookmark"><?php the_title(); ?></a></h2>
		<div class="wpfc_sermon_image">
			<?php render_sermon_image( 'thumbnail' ); ?>
		</div>
		<div class="wpfc_sermon_meta cf">
			<p>
				<?php
				sm_the_date( '', '<span class="sermon_date">', '</span> ' );
				the_terms( $post->ID, 'wpfc_service_type', ' <span class="service_type">(', ' ', ')</span>' );
				?>
			</p>
			<p>
				<?php
				wpfc_sermon_meta( 'bible_passage', '<span class="bible_passage">' . __( 'Bible Text: ', 'sermon-manager-for-wordpress' ), '</span> | ' );
				the_terms( $post->ID, 'wpfc_preacher', '<span class="preacher_name">', ' ', '</span>' );
				the_terms( $post->ID, 'wpfc_sermon_series', '<p><span class="sermon_series">' . __( 'Series: ', 'sermon-manager-for-wordpress' ), ' ', '</span></p>' );
				?>
			</p>
		</div>
	</div>
	<?php
}

/**
 * Renders v1 archive the_content.
 *
 * @param bool $return True to return, false to echo (default).
 *
 * @return string
 *
 * @deprecated 2.12.2
 */
function wpfc_sermon_excerpt( $return = false ) {
	_deprecated_function( __FUNCTION__, '2.12.2', 'wpfc_sermon_excerpt_v2' );

	global $post;

	if ( SM_OB_ENABLED ) {
		ob_start();
		?>
		<div class="wpfc_sermon_wrap cf">
			<div class="wpfc_sermon_image">
				<?php render_sermon_image( apply_filters( 'wpfc_sermon_excerpt_sermon_image_size', 'sermon_small' ) ); ?>
			</div>
			<div class="wpfc_sermon_meta cf">
				<p>
					<?php
					sm_the_date( '', '<span class="sermon_date">', '</span> ' );
					the_terms( $post->ID, 'wpfc_service_type', ' <span class="service_type">(', ' ', ')</span>' );
					?>
				</p>
				<p>
					<?php
					wpfc_sermon_meta( 'bible_passage', '<span class="bible_passage">' . __( 'Bible Text: ', 'sermon-manager-for-wordpress' ), '</span> | ' );
					the_terms( $post->ID, 'wpfc_preacher', '<span class="preacher_name">', ', ', '</span>' );
					?>
				</p>
				<p>
					<?php the_terms( $post->ID, 'wpfc_sermon_series', '<span class="sermon_series">' . __( 'Series: ', 'sermon-manager-for-wordpress' ), ' ', '</span>' ); ?>
				</p>
			</div>
			<?php if ( \SermonManager::getOption( 'archive_player' ) || \SermonManager::getOption( 'archive_meta' ) ) : ?>
				<div class="wpfc_sermon cf">
					<?php if ( \SermonManager::getOption( 'archive_player' ) ) : ?>
						<?php echo wpfc_sermon_media(); ?>
					<?php endif; ?>
					<?php if ( \SermonManager::getOption( 'archive_meta' ) ) : ?>
						<?php echo wpfc_sermon_attachments(); ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php

		$output = ob_get_clean();
	} else {
		$output = '';
	}

	/**
	 * Allows you to modify the sermon HTML on archive pages
	 *
	 * @param string  $output The HTML that will be outputted
	 * @param WP_Post $post   The sermon
	 *
	 * @since 2.10.1
	 */
	$output = apply_filters( 'wpfc_sermon_excerpt', $output, $post );

	if ( ! $return ) {
		echo $output;
	}

	return $output;
}

/**
 * Renders v1 single the_content
 *
 * @deprecated 2.12.0
 *
 * @param bool    $return Should it echo or return. Default - echo.
 * @param WP_Post $post   The sermon WP_Post instance.
 *
 * @return string The output
 */
function wpfc_sermon_single( $return = false, $post = null ) {
	_deprecated_function( __FUNCTION__, '2.12.0', 'wpfc_sermon_single_v2' );

	if ( null === $post || '' === $post ) {
		global $post;
	}

	if ( SM_OB_ENABLED ) {
		ob_start();
		?>
		<div class="wpfc_sermon_wrap cf">
			<div class="wpfc_sermon_image">
				<?php render_sermon_image( 'sermon_small' ); ?>
			</div>
			<div class="wpfc_sermon_meta cf">
				<p>
					<?php
					sm_the_date( '', '<span class="sermon_date">', '</span> ' );
					the_terms( $post->ID, 'wpfc_service_type', ' <span class="service_type">(', ' ', ')</span>' );
					?>
				</p>
				<p>
					<?php
					wpfc_sermon_meta( 'bible_passage', '<span class="bible_passage">' . __( 'Bible Text: ', 'sermon-manager-for-wordpress' ), '</span> | ' );
					the_terms( $post->ID, 'wpfc_preacher', '<span class="preacher_name">', ', ', '</span>' );
					the_terms( $post->ID, 'wpfc_sermon_series', '<p><span class="sermon_series">' . __( 'Series: ', 'sermon-manager-for-wordpress' ), ' ', '</span></p>' );
					?>
				</p>
			</div>
		</div>
		<div class="wpfc_sermon cf">

			<?php echo wpfc_sermon_media(); ?>

			<?php wpfc_sermon_description(); ?>

			<?php echo wpfc_sermon_attachments(); ?>

			<?php the_terms( $post->ID, 'wpfc_sermon_topics', '<p class="sermon_topics">' . __( 'Sermon Topics: ', 'sermon-manager-for-wordpress' ), ',', '</p>' ); ?>

		</div>
		<?php
		$output = ob_get_clean();
	} else {
		$output = false;
	}

	/**
	 * Allows you to modify the sermon HTML on single sermon pages
	 *
	 * @param string  $output The HTML that will be outputted
	 * @param WP_Post $post   The sermon
	 *
	 * @since 2.12.0
	 */
	$output = apply_filters( 'wpfc_sermon_single', $output, $post );

	if ( ! $return ) {
		echo $output;
	}

	return $output;
}

/**
 * Removed.
 *
 * @param string $feed_type Removed.
 *
 * @deprecated 2.12.0
 */
function wpfc_podcast_url( $feed_type = 'deprecated' ) {
	_deprecated_function( __FUNCTION__, '2.12.0', null );
}

/**
 * Removed.
 *
 * @deprecated 2.12.0
 */
function wpfc_footer_series() {
	_deprecated_function( __FUNCTION__, '2.12.0', null );
}

/**
 * Removed.
 *
 * @deprecated 2.12.0
 */
function wpfc_footer_preacher() {
	_deprecated_function( __FUNCTION__, '2.12.0', null );
}

/**
 * Render sermon audio HTML.
 *
 * @return string
 *
 * @deprecated 2.12.0
 */
function wpfc_sermon_audio() {
	_deprecated_function( __FUNCTION__, '2.12.0', 'wpfc_render_audio' );

	$html = '';

	$html .= '<div class="wpfc_sermon-audio cf">';
	$html .= wpfc_render_audio( get_wpfc_sermon_meta( 'sermon_audio' ) );
	$html .= '</div>';

	return $html;
}

/**
 * Render sermon image, if not set try series image, if not set too - try preacher.
 *
 * @param string $size Image size, supports WP image size.
 *
 * @see        get_sermon_image_url()
 *
 * @deprecated 2.12.0
 */
function render_sermon_image( $size ) {
	_deprecated_function( __FUNCTION__, '2.12.0', 'get_sermon_image_url' );

	// $size = any defined image size in WordPress.
	if ( has_post_thumbnail() ) :
		the_post_thumbnail( $size );
	elseif ( apply_filters( 'sermon-images-list-the-terms', '', array( 'taxonomy' => 'wpfc_sermon_series' ) ) ) :
		// Get series image.
		print apply_filters( 'sermon-images-list-the-terms', '', array(
			'image_size'   => $size,
			'taxonomy'     => 'wpfc_sermon_series',
			'after'        => '',
			'after_image'  => '',
			'before'       => '',
			'before_image' => '',
		) );
	elseif ( ! has_post_thumbnail() && ! apply_filters( 'sermon-images-list-the-terms', '', array( 'taxonomy' => 'wpfc_sermon_series' ) ) ) :
		// Get speaker image.
		print apply_filters( 'sermon-images-list-the-terms', '', array(
			'image_size'   => $size,
			'taxonomy'     => 'wpfc_preacher',
			'after'        => '',
			'after_image'  => '',
			'before'       => '',
			'before_image' => '',
		) );
	endif;
}

/**
 * Renders video and/or audio with wrapper HTML.
 *
 * @return string The HTML.
 *
 * @deprecated 2.12.0
 */
function wpfc_sermon_media() {
	_deprecated_function( __FUNCTION__, '2.12.0', null );

	$html = '';

	if ( get_wpfc_sermon_meta( 'sermon_video_link' ) ) {
		$html .= '<div class="wpfc_sermon-video-link cf">';
		$html .= wpfc_render_video( get_wpfc_sermon_meta( 'sermon_video_link' ) );
		$html .= '</div>';
	} else {
		$html .= '<div class="wpfc_sermon-video cf">';
		$html .= do_shortcode( get_wpfc_sermon_meta( 'sermon_video' ) );
		$html .= '</div>';
	}

	if ( get_wpfc_sermon_meta( 'sermon_audio' ) ) {
		$html .= '<div class="wpfc_sermon-audio cf">';
		$html .= wpfc_render_audio( get_wpfc_sermon_meta( 'sermon_audio' ) );
		$html .= '</div>';
	}

	return $html;
}

/**
 * Removed.
 *
 * @deprecated 2.12.0
 */
function wpfc_sermon_author_filter() {
	_deprecated_function( __FUNCTION__, '2.12.0', null );
}

/**
 * Add podcast data to the WordPress default XML feed.
 *
 * @param WP_Query $query The query.
 *
 * @return void
 *
 * @deprecated 2.13.0
 */
function wpfc_podcast_add_hooks( $query ) {
	_deprecated_function( __FUNCTION__, '2.13.0', null );
}

/**
 * Add iTunes XML Namespace to the XML head.
 *
 * @return void
 * @deprecated 2.13.0
 */
function wpfc_podcast_add_namespace() {
	_deprecated_function( __FUNCTION__, '2.13.0', null );
}

/**
 * Add iTunes header data.
 *
 * @return void
 * @deprecated 2.13.0
 */
function wpfc_podcast_add_head() {
	_deprecated_function( __FUNCTION__, '2.13.0', null );
}

/**
 * Add iTunes data to each sermon.
 *
 * @deprecated 2.13.0
 */
function wpfc_podcast_add_item() {
	_deprecated_function( __FUNCTION__, '2.13.0', null );
}

/**
 * Replace feed item content and excerpt with Sermon description.
 *
 * @param string $content Original content.
 *
 * @return string Modified content.
 *
 * @deprecated 2.13.0
 */
function wpfc_podcast_summary( $content ) {
	_deprecated_function( __FUNCTION__, '2.13.0', null );

	return '';
}

/**
 * Replace feed item published date with Sermon date.
 *
 * @param string $time The formatted time.
 * @param string $d    Format to use for retrieving the time the post was written.
 *                     Accepts 'G', 'U', or php date format. Default 'U'.
 * @param bool   $gmt  Whether to retrieve the GMT time. Default false.
 *
 * @return string Modified date
 *
 * @deprecated 2.13.0
 */
function wpfc_podcast_item_date( $time, $d = 'U', $gmt = false ) {
	_deprecated_function( __FUNCTION__, '2.13.0', null );

	return '';
}

/**
 * Replace feed title with the one defined in Sermon Manager settings.
 *
 * @param string $title Default title.
 *
 * @return string Modified title.
 *
 * @deprecated 2.13.0
 */
function wpfc_modify_podcast_title( $title ) {
	_deprecated_function( __FUNCTION__, '2.13.0', null );

	return '';
}

/**
 * Modifies get_bloginfo output and injects Sermon Manager data.
 *
 * @param string $info Default data.
 * @param string $show Requested data.
 *
 * @return string Modified data
 *
 * @deprecated 2.13.0
 */
function wpfc_bloginfo_rss_filter( $info, $show ) {
	_deprecated_function( __FUNCTION__, '2.13.0', null );

	return '';
}

/**
 * Note: Unfinished feature.
 * Take a look at comment at `views/wpfc-podcast-feed.php`.
 *
 * Load the template used for podcast XML.
 *
 * It can be overridden by putting the `wpfc-podcast-feed.php` file in the root of your active theme.
 *
 * @since 2.3.5 Added ability to override the default template
 * @return void
 */
function wpfc_podcast_render() {
	_deprecated_function( __FUNCTION__, '2.13.0', null );
}
