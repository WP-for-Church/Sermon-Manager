<?php
/**
 * Place where functions come to die.
 *
 * @since 2.4.9
 */

defined( 'ABSPATH' ) or die; // exit if accessed directly

// deprecated
define( 'SM___FILE__', __FILE__ );
define( 'SERMON_MANAGER_PATH', SM_PATH );
define( 'SERMON_MANAGER_URL', SM_URL );
define( 'SERMON_MANAGER_VERSION', SM_VERSION );


/**
 * Outputs Sermon date. Wrapper for sm_the_date()
 *
 * @see        sm_the_date()
 *
 * @param string $d      PHP date format. Defaults to the date_format option if not specified.
 * @param string $before Optional. Output before the date.
 * @param string $after  Optional. Output after the date.
 *
 * @deprecated deprecated since 2.6, use sm_the_date() instead
 */
function wpfc_sermon_date( $d, $before = '', $after = '' ) {
	sm_the_date( $d, $before = '', $after = '' );
}

/**
 * Saves service type
 *
 * Will be obsolete when we add new meta boxes code
 *
 * @param int $post_ID
 */
function set_service_type( $post_ID ) {
	if ( isset( $_POST['wpfc_service_type'] ) ) {
		if ( $term = get_term_by( 'id', $_POST['wpfc_service_type'], 'wpfc_service_type' ) ) {
			$service_type = $term->slug;
		}

		wp_set_object_terms( $post_ID, empty( $service_type ) ? null : $service_type, 'wpfc_service_type' );
	}
}

add_action( 'save_post', 'set_service_type', 99 );

add_action( 'sermon_media', 'wpfc_sermon_media', 5 );
add_action( 'sermon_audio', 'wpfc_sermon_audio', 5 );
add_action( 'sermon_single', 'wpfc_sermon_single' );
add_action( 'sermon_excerpt', 'wpfc_sermon_excerpt' );

/**
 * @deprecated - see wpfc_sermon_media()
 */
function wpfc_sermon_files() {
	do_action( 'sermon_media' );
}

/**
 * @deprecated - see wpfc_sermon_single() & wpfc_sermon_single_v2()
 */
function render_wpfc_sermon_single() {
	do_action( 'sermon_single' );
}

/**
 * @deprecated - see wpfc_sermon_excerpt() & wpfc_sermon_excerpt_v2()
 */
function render_wpfc_sermon_excerpt() {
	do_action( 'sermon_excerpt' );
}

/**
 * Renders v0 archive the_content
 *
 * @deprecated 2.12.0
 */
function render_wpfc_sermon_archive() {
	global $post; ?>
	<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<h2 class="sermon-title"><a href="<?php the_permalink(); ?>"
		                            title="<?php printf( esc_attr__( 'Permalink to %s', 'sermon-manager-for-wordpress' ), the_title_attribute( 'echo=0' ) ); ?>"
		                            rel="bookmark"><?php the_title(); ?></a></h2>
		<div class="wpfc_sermon_image">
			<?php render_sermon_image( 'thumbnail' ); ?>
		</div>
		<div class="wpfc_sermon_meta cf">
			<p>
				<?php
				sm_the_date( '', '<span class="sermon_date">', '</span> ' );
				the_terms( $post->ID, 'wpfc_service_type', ' <span class="service_type">(', ' ', ')</span>' );
				?></p>
			<p><?php

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
 * Renders v1 archive the_content
 *
 * @param bool $return True to return, false to echo (default)
 *
 * @return string
 *
 * @deprecated 2.12.2
 */
function wpfc_sermon_excerpt( $return = false ) {
	global $post;

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
		<?php if ( \SermonManager::getOption( 'archive_player' ) || \SermonManager::getOption( 'archive_meta' ) ): ?>
			<div class="wpfc_sermon cf">
				<?php if ( \SermonManager::getOption( 'archive_player' ) ): ?>
					<?php echo wpfc_sermon_media(); ?>
				<?php endif; ?>
				<?php if ( \SermonManager::getOption( 'archive_meta' ) ): ?>
					<?php echo wpfc_sermon_attachments(); ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php

	$output = ob_get_clean();

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
 */
function wpfc_sermon_single( $return = false, $post = '' ) {
	if ( $post === '' ) {
		global $post;
	}

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
				?></p>
			<p><?php
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
 * @deprecated 2.12.0
 */
function wpfc_podcast_url( $feed_type = 'deprecated' ) {
	_deprecated_function( __FUNCTION__, '2.12.0', null );
}

/**
 * @deprecated 2.12.0
 */
function wpfc_footer_series() {
	_deprecated_function( __FUNCTION__, '2.12.0', null );
}

/**
 * @deprecated 2.12.0
 */
function wpfc_footer_preacher() {
	_deprecated_function( __FUNCTION__, '2.12.0', null );
}

/**
 * Render sermon audio HTML
 *
 * @return string
 *
 * @deprecated 2.12.0
 */
function wpfc_sermon_audio() {
	$html = '<div class="wpfc_sermon-audio cf">';
	$html .= wpfc_render_audio( get_wpfc_sermon_meta( 'sermon_audio' ) );
	$html .= '</div>';

	return $html;
}

/**
 * Render sermon image, if not set try series image, if not set too - try preacher
 *
 * @param string $size Image size, supports WP image size
 *
 * @deprecated 2.12.0
 */
function render_sermon_image( $size ) {
	//$size = any defined image size in WordPress
	if ( has_post_thumbnail() ) :
		the_post_thumbnail( $size );
	elseif ( apply_filters( 'sermon-images-list-the-terms', '', array( 'taxonomy' => 'wpfc_sermon_series', ) ) ) :
		// get series image
		print apply_filters( 'sermon-images-list-the-terms', '', array(
			'image_size'   => $size,
			'taxonomy'     => 'wpfc_sermon_series',
			'after'        => '',
			'after_image'  => '',
			'before'       => '',
			'before_image' => ''
		) );
	elseif ( ! has_post_thumbnail() && ! apply_filters( 'sermon-images-list-the-terms', '', array( 'taxonomy' => 'wpfc_sermon_series', ) ) ) :
		// get speaker image
		print apply_filters( 'sermon-images-list-the-terms', '', array(
			'image_size'   => $size,
			'taxonomy'     => 'wpfc_preacher',
			'after'        => '',
			'after_image'  => '',
			'before'       => '',
			'before_image' => ''
		) );
	endif;
}

/**
 * Renders video and/or audio with wrapper HTML
 *
 * @return string The HTML
 *
 * @deprecated 2.12.0
 */
function wpfc_sermon_media() {
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
 * @deprecated 2.12.0
 */
function wpfc_sermon_author_filter() {
	_deprecated_function( __FUNCTION__, '2.12.0', null );
}
