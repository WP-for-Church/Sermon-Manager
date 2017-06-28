<?php

/*
 * Template selection
 */
// Check plugin options to decide what to do
if ( \SermonManager::getOption( 'template' ) ) {
	add_filter( 'template_include', 'sermon_template_include' );
	add_filter( 'template_include', 'preacher_template_include' );
	add_filter( 'template_include', 'series_template_include' );
	add_filter( 'template_include', 'service_type_template_include' );
	add_filter( 'template_include', 'bible_book_template_include' );
	add_filter( 'template_include', 'sermon_topics_template_include' );
}
add_action( 'sermon_media', 'wpfc_sermon_media', 5 );
add_action( 'sermon_audio', 'wpfc_sermon_audio', 5 );
add_action( 'sermon_single', 'wpfc_sermon_single' );
add_action( 'sermon_excerpt', 'wpfc_sermon_excerpt' );
// Add sermon content
add_filter( 'the_content', 'add_wpfc_sermon_content' );

// Include template for displaying sermons
function sermon_template_include( $template ) {
	if ( get_query_var( 'post_type' ) == 'wpfc_sermon' ) {
		if ( is_archive() || is_search() ) :
			if ( file_exists( get_stylesheet_directory() . '/archive-wpfc_sermon.php' ) ) {
				return get_stylesheet_directory() . '/archive-wpfc_sermon.php';
			}

			return SERMON_MANAGER_PATH . 'views/archive-wpfc_sermon.php';
		else :
			if ( file_exists( get_stylesheet_directory() . '/single-wpfc_sermon.php' ) ) {
				return get_stylesheet_directory() . '/single-wpfc_sermon.php';
			}

			return SERMON_MANAGER_PATH . 'views/single-wpfc_sermon.php';
		endif;
	}

	return $template;
}

// Include template for displaying sermon topics
function sermon_topics_template_include( $template ) {
	if ( get_query_var( 'taxonomy' ) == 'wpfc_sermon_topics' ) {
		if ( file_exists( get_stylesheet_directory() . '/taxonomy-wpfc_sermon_topics.php' ) ) {
			return get_stylesheet_directory() . '/taxonomy-wpfc_sermon_topics.php';
		}

		return SERMON_MANAGER_PATH . 'views/taxonomy-wpfc_sermon_topics.php';
	}

	return $template;
}

// Include template for displaying sermons by Preacher
function preacher_template_include( $template ) {
	if ( get_query_var( 'taxonomy' ) == 'wpfc_preacher' ) {
		if ( file_exists( get_stylesheet_directory() . '/taxonomy-wpfc_preacher.php' ) ) {
			return get_stylesheet_directory() . '/taxonomy-wpfc_preacher.php';
		}

		return SERMON_MANAGER_PATH . 'views/taxonomy-wpfc_preacher.php';
	}

	return $template;
}

// Include template for displaying sermon series
function series_template_include( $template ) {
	if ( get_query_var( 'taxonomy' ) == 'wpfc_sermon_series' ) {
		if ( file_exists( get_stylesheet_directory() . '/taxonomy-wpfc_sermon_series.php' ) ) {
			return get_stylesheet_directory() . '/taxonomy-wpfc_sermon_series.php';
		}

		return SERMON_MANAGER_PATH . 'views/taxonomy-wpfc_sermon_series.php';
	}

	return $template;
}

// Include template for displaying service types
function service_type_template_include( $template ) {
	if ( get_query_var( 'taxonomy' ) == 'wpfc_service_type' ) {
		if ( file_exists( get_stylesheet_directory() . '/taxonomy-wpfc_service_type.php' ) ) {
			return get_stylesheet_directory() . '/taxonomy-wpfc_service_type.php';
		}

		return SERMON_MANAGER_PATH . 'views/taxonomy-wpfc_service_type.php';
	}

	return $template;
}

// Include template for displaying sermons by book
function bible_book_template_include( $template ) {
	if ( get_query_var( 'taxonomy' ) == 'wpfc_bible_book' ) {
		if ( file_exists( get_stylesheet_directory() . '/taxonomy-wpfc_bible_book.php' ) ) {
			return get_stylesheet_directory() . '/taxonomy-wpfc_bible_book.php';
		}

		return SERMON_MANAGER_PATH . 'views/taxonomy-wpfc_bible_book.php';
	}

	return $template;
}

// render archive entry; depreciated - use render_wpfc_sermon_excerpt() instead
function render_wpfc_sermon_archive() {
	global $post; ?>
    <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <h2 class="sermon-title"><a href="<?php the_permalink(); ?>"
                                    title="<?php printf( esc_attr__( 'Permalink to %s', 'sermon-manager' ), the_title_attribute( 'echo=0' ) ); ?>"
                                    rel="bookmark"><?php the_title(); ?></a></h2>
        <div class="wpfc_sermon_image">
			<?php render_sermon_image( 'thumbnail' ); ?>
        </div>
        <div class="wpfc_sermon_meta cf">
            <p>
				<?php
				wpfc_sermon_date( get_option( 'date_format' ), '<span class="sermon_date">', '</span> ' );
				echo the_terms( $post->ID, 'wpfc_service_type', ' <span class="service_type">(', ' ', ')</span>' );
				?></p>
            <p><?php

				wpfc_sermon_meta( 'bible_passage', '<span class="bible_passage">' . __( 'Bible Text: ', 'sermon-manager' ), '</span> | ' );
				echo the_terms( $post->ID, 'wpfc_preacher', '<span class="preacher_name">', ' ', '</span>' );
				echo the_terms( $post->ID, 'wpfc_sermon_series', '<p><span class="sermon_series">' . __( 'Series: ', 'sermon-manager' ), ' ', '</span></p>' );
				?>
            </p>
        </div>
    </div>

<?php }

// render sermon sorting
function render_wpfc_sorting() {
	$html = '';
	$html .= '<div id="wpfc_sermon_sorting">';
	$html .= '<span class="sortPreacher">';
	$html .= '<form action="';
	$html .= home_url();
	$html .= '" method="get">';
	$html .= '<select name="wpfc_preacher" id="wpfc_preacher" onchange="return this.form.submit()">';
	$html .= '<option value="">';
	$html .= 'Sort by ' . ( \SermonManager::getOption( 'preacher_label' ) ?: 'Preacher' );
	$html .= '</option>';
	$html .= wpfc_get_term_dropdown( 'wpfc_preacher' );
	$html .= '</select>';
	$html .= '<noscript><div><input type="submit" value="Submit" /></div></noscript>';
	$html .= '</form>';
	$html .= '</span>';
	$html .= '<span class="sortSeries">';
	$html .= '<form action="';
	$html .= home_url();
	$html .= '" method="get">';
	$html .= '<select name="wpfc_sermon_series" id="wpfc_sermon_series" onchange="return this.form.submit()">';
	$html .= '<option value="">';
	$html .= 'Sort by Series';
	$html .= '</option>';
	$html .= wpfc_get_term_dropdown( 'wpfc_sermon_series' );
	$html .= '</select>';
	$html .= '<noscript><div><input type="submit" value="Submit" /></div></noscript>';
	$html .= '</form>';
	$html .= '</span>';
	$html .= '<span class="sortTopics">';
	$html .= '<form action="';
	$html .= home_url();
	$html .= '" method="get">';
	$html .= '<select name="wpfc_sermon_topics" id="wpfc_sermon_topics" onchange="return this.form.submit()">';
	$html .= '<option value="">';
	$html .= 'Sort by Topic';
	$html .= '</option>';
	$html .= wpfc_get_term_dropdown( 'wpfc_sermon_topics' );
	$html .= '</select>';
	$html .= '<noscript><div><input type="submit" value="Submit" /></div></noscript>';
	$html .= '</form>';
	$html .= '</span>';
	$html .= '<span class="sortBooks">';
	$html .= '<form action="';
	$html .= home_url();
	$html .= '" method="get">';
	$html .= '<select name="wpfc_bible_book" id="wpfc_bible_book" onchange="return this.form.submit()">';
	$html .= '<option value="">';
	$html .= 'Sort by Book';
	$html .= '</option>';
	$html .= wpfc_get_term_dropdown( 'wpfc_bible_book' );
	$html .= '</select>';
	$html .= '</select>';
	$html .= '<noscript><div><input type="submit" value="Submit" /></div></noscript>';
	$html .= '</form>';
	$html .= '</span>';
	$html .= '</div>';

	return $html;
}

// echo any sermon meta
function wpfc_sermon_meta( $args, $before = '', $after = '' ) {
	global $post;
	$data = get_post_meta( $post->ID, $args, true );
	if ( $data != '' ) {
		echo $before . $data . $after;
	}

	echo '';
}

// return any sermon meta
function get_wpfc_sermon_meta( $args ) {
	global $post;
	$data = get_post_meta( $post->ID, $args, true );
	if ( $data != '' ) {
		return $data;
	}

	return null;
}

function process_wysiwyg_output( $meta_key, $post_id = 0 ) {
	global $wp_embed;

	$post_id = $post_id ? $post_id : get_the_id();

	$content = get_post_meta( $post_id, $meta_key, true );
	$content = $wp_embed->autoembed( $content );
	$content = $wp_embed->run_shortcode( $content );
	$content = wpautop( $content );
	$content = do_shortcode( $content );

	return $content;
}

// render sermon description
function wpfc_sermon_description( $before = '', $after = '' ) {
	global $post;
	$data = process_wysiwyg_output( 'sermon_description', get_the_ID() );
	if ( $data != '' ) {
		echo $before . wpautop( $data ) . $after;
	}
}

// render any sermon date
function wpfc_sermon_date( $args, $before = '', $after = '' ) {
	global $post;
	$ugly_date = get_post_meta( $post->ID, 'sermon_date', true );

	// seems like it was stored as a text in the db sometime in the past
	if ( ! is_numeric( $ugly_date ) ) {
		$ugly_date = strtotime( $ugly_date );
	}

	$date = date_i18n( $args, $ugly_date );
	echo $before . $date . $after;
}

// Change the_author to the preacher on frontend display
function wpfc_sermon_author_filter() {
	global $post;
	$preacher = the_terms( $post->ID, 'wpfc_preacher', '', ', ', ' ' );

	return $preacher;
}

//add_filter('the_author', 'wpfc_sermon_author_filter');

// render sermon image - loops through featured image, series image, speaker image, none
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

/*
 * render media files section
 * for template files use
 * do_action ('sermon_media');
 *
 */
function wpfc_sermon_media() {
	$html = '';

	if ( get_wpfc_sermon_meta( 'sermon_video_link' ) ) {
		$html .= '<div class="wpfc_sermon-video-link cf">';
		$html .= process_wysiwyg_output( 'sermon_video_link', get_the_ID() );
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
 * Renders the audio player
 *
 * @param string $url The URL of the audio file
 *
 * @return string Audio player HTML
 */
function wpfc_render_audio( $url = '' ) {
	if ( ! is_string( $url ) || trim( $url ) === '' ) {
		return '';
	}

	if ( \SermonManager::getOption( 'use_old_player' ) ) {
		$attr = array(
			'src'     => $url,
			'preload' => 'none'
		);

		$output = wp_audio_shortcode( $attr );
	} else {
		$output = '<audio controls preload="metadata" class="wpfc-sermon-player">';
		$output .= '<source src="' . $url . '">';
		$output .= '</audio>';
	}

	return $output;
}

// legacy function
function wpfc_sermon_files() {
	do_action( 'sermon_media' );
}

// just get the sermon audio
function wpfc_sermon_audio() {
	$html = '';
	$html .= '<div class="wpfc_sermon-audio cf">';
	$html .= wpfc_render_audio( get_wpfc_sermon_meta( 'sermon_audio' ) );
	$html .= '</div>';

	return $html;
}

// render additional files
function wpfc_sermon_attachments() {
	global $post;
	$args        = array(
		'post_type'   => 'attachment',
		'numberposts' => - 1,
		'post_status' => null,
		'post_parent' => $post->ID,
		'exclude'     => get_post_thumbnail_id()
	);
	$attachments = get_posts( $args );
	$html        = '';
	$html .= '<div id="wpfc-attachments" class="cf">';
	$html .= '<p><strong>' . __( 'Download Files', 'sermon-manager' ) . '</strong>';
	if ( $attachments ) {
		foreach ( $attachments as $attachment ) {
			// skip audio, so we don't have double URLs
			if ( get_wpfc_sermon_meta( 'sermon_audio' ) === wp_get_attachment_url( $attachment->ID ) ) {
				continue;
			}

			$html .= '<br/><a target="_blank" href="' . wp_get_attachment_url( $attachment->ID ) . '">';
			$html .= $attachment->post_title;
			$html .= '</a>';
		}
	}
	if ( get_wpfc_sermon_meta( 'sermon_audio' ) ) {
		$html .= '<a href="' . get_wpfc_sermon_meta( 'sermon_audio' ) . '" class="sermon-attachments" download><span class="dashicons dashicons-media-audio"></span>' . __( 'MP3', 'sermon-manager' ) . '</a>';
	}
	if ( get_wpfc_sermon_meta( 'sermon_notes' ) ) {
		$html .= '<a href="' . get_wpfc_sermon_meta( 'sermon_notes' ) . '" class="sermon-attachments"><span class="dashicons dashicons-media-document"></span>' . __( 'Notes', 'sermon-manager' ) . '</a>';
	}
	if ( get_wpfc_sermon_meta( 'sermon_bulletin' ) ) {
		$html .= '<a href="' . get_wpfc_sermon_meta( 'sermon_bulletin' ) . '" class="sermon-attachments"><span class="dashicons dashicons-media-document"></span>' . __( 'Bulletin', 'sermon-manager' ) . '</a>';
	}
	$html .= '</p>';
	$html .= '</div>';

	return $html;
}

// legacy function
function render_wpfc_sermon_single() {
	do_action( 'sermon_single' );
}

// single sermon action
function wpfc_sermon_single() {
	global $post; ?>
    <div class="wpfc_sermon_wrap cf">
        <div class="wpfc_sermon_image">
			<?php render_sermon_image( 'sermon_small' ); ?>
        </div>
        <div class="wpfc_sermon_meta cf">
            <p>
				<?php
				wpfc_sermon_date( get_option( 'date_format' ), '<span class="sermon_date">', '</span> ' );
				the_terms( $post->ID, 'wpfc_service_type', ' <span class="service_type">(', ' ', ')</span>' );
				?></p>
            <p><?php
				wpfc_sermon_meta( 'bible_passage', '<span class="bible_passage">' . __( 'Bible Text: ', 'sermon-manager' ), '</span> | ' );
				the_terms( $post->ID, 'wpfc_preacher', '<span class="preacher_name">', ', ', '</span>' );
				the_terms( $post->ID, 'wpfc_sermon_series', '<p><span class="sermon_series">' . __( 'Series: ', 'sermon-manager' ), ' ', '</span></p>' );
				?>
            </p>
        </div>
    </div>
    <div class="wpfc_sermon cf">

		<?php echo wpfc_sermon_media(); ?>

		<?php wpfc_sermon_description(); ?>

		<?php echo wpfc_sermon_attachments(); ?>

		<?php echo the_terms( $post->ID, 'wpfc_sermon_topics', '<p class="sermon_topics">' . __( 'Sermon Topics: ', 'sermon-manager' ), ',', '', '</p>' ); ?>

    </div>
	<?php
}

// render single sermon entry
function render_wpfc_sermon_excerpt() {
	do_action( 'sermon_excerpt' );
}

function wpfc_sermon_excerpt() {
	global $post; ?>
    <div class="wpfc_sermon_wrap cf">
        <div class="wpfc_sermon_image">
			<?php render_sermon_image( 'sermon_small' ); ?>
        </div>
        <div class="wpfc_sermon_meta cf">
            <p>
				<?php
				wpfc_sermon_date( get_option( 'date_format' ), '<span class="sermon_date">', '</span> ' );
				echo the_terms( $post->ID, 'wpfc_service_type', ' <span class="service_type">(', ' ', ')</span>' );
				?></p>
            <p><?php
				wpfc_sermon_meta( 'bible_passage', '<span class="bible_passage">' . __( 'Bible Text: ', 'sermon-manager' ), '</span> | ' );
				echo the_terms( $post->ID, 'wpfc_preacher', '<span class="preacher_name">', ', ', '</span>' );
				echo the_terms( $post->ID, 'wpfc_sermon_series', '<p><span class="sermon_series">' . __( 'Series: ', 'sermon-manager' ), ' ', '</span></p>' );
				?>
            </p>
        </div>
		<?php if ( \SermonManager::getOption( 'archive_player' ) ): ?>
            <div class="wpfc_sermon cf">
				<?php echo wpfc_sermon_media(); ?>
            </div>
		<?php endif; ?>
    </div>
	<?php
}

function add_wpfc_sermon_content( $content ) {
	if ( 'wpfc_sermon' == get_post_type() && in_the_loop() == true ) {
		if ( ! is_feed() && ( is_archive() || is_search() ) ) {
			$new_content = render_wpfc_sermon_excerpt();
		} elseif ( is_singular() && is_main_query() ) {
			$new_content = wpfc_sermon_single();
		}
		$content = $new_content;
	}

	return $content;
}

//Podcast Feed URL
function wpfc_podcast_url( $feed_type = false ) {
	if ( $feed_type == false ) { //return URL to feed page
		return home_url() . '/feed/podcast';
	} else { //return URL to itpc itunes-loaded feed page
		$itunes_url = str_replace( "http", "itpc", home_url() );

		return $itunes_url . '/feed/podcast';
	}
}

/**
 * Display series info on an individual sermon
 */
function wpfc_footer_series() {
	global $post;
	$terms = get_the_terms( $post->ID, 'wpfc_sermon_series' );
	if ( $terms ) {
		foreach ( $terms as $term ) {
			if ( $term->description ) {
				echo '<div class="single_sermon_info_box series clearfix">';
				echo '<div class="sermon-footer-description clearfix">';
				echo '<h3 class="single-preacher-name"><a href="' . get_term_link( $term->slug, 'wpfc_sermon_series' ) . '">' . $term->name . '</a></h3>';
				/* Image */
				print apply_filters( 'sermon-images-list-the-terms', '', array(
					'attr'         => array(
						'class' => 'alignleft',
					),
					'image_size'   => 'thumbnail',
					'taxonomy'     => 'wpfc_sermon_series',
					'after'        => '</div>',
					'after_image'  => '',
					'before'       => '<div class="sermon-footer-image">',
					'before_image' => ''
				) );
				/* Description */
				echo $term->description . '</div>';
				echo '</div>';
			}
		}
	}
}

/**
 * Display preacher info on an individual sermon
 */
function wpfc_footer_preacher() {
	global $post;
	$terms = get_the_terms( $post->ID, 'wpfc_preacher' );
	if ( $terms ) {
		foreach ( $terms as $term ) {
			if ( $term->description ) {
				echo '<div class="single_sermon_info_box preacher clearfix">';
				echo '<div class="sermon-footer-description clearfix">';
				echo '<h3 class="single-preacher-name"><a href="' . get_term_link( $term->slug, 'wpfc_preacher' ) . '">' . $term->name . '</a></h3>';
				/* Image */
				print apply_filters( 'sermon-images-list-the-terms', '', array(
					'attr'         => array(
						'class' => 'alignleft',
					),
					'image_size'   => 'thumbnail',
					'taxonomy'     => 'wpfc_preacher',
					'after'        => '</div>',
					'after_image'  => '',
					'before'       => '<div class="sermon-footer-image">',
					'before_image' => ''
				) );
				/* Description */
				echo $term->description . '</div>';
				echo '</div>';
			}
		}
	}
}

/**
 * Change published date to sermon date on frontend display.
 * Filters the output of `get_the_time` because `get_the_time` returns post time instead of sermon time.
 *
 * @param string|int       $the_time Already filtered time. This will be returned if post type is not `wpfc_sermon`
 * @param string           $d        Format to use for retrieving the time the post was written.
 * @param null|int|WP_Post $post     WP_Post object or ID
 *
 * @return string|int|false
 */
function wpfc_sermon_time_filter( $the_time = 0, $d = '', $post = null ) {
	if ( 'wpfc_sermon' == get_post_type( $post ) ) {
		// if the post is not set, try to get current one
		if ( $post === null ) {
			$post = the_post();
		}

		// get the post
		$post = get_post( $post );

		// this check is maybe not needed, post will be validated on first call of `get_the_time`
		if ( ! $post ) {
			return false;
		}

		// use specified format, or get default one if not specified
		$format = $d === '' ? get_option( 'time_format' ) : $d;

		// get sermon date
		$date = get_post_meta( $post->ID, 'sermon_date', true );

		// if the sermon time is not represented as Unix timestamp, convert it
		if ( ! is_numeric( $date ) ) {
			$date = strtotime( $date );
		}

		// get post hour, minute and second, in seconds
		$his = explode( ':', date( 'H:i:s', strtotime( $post->post_date ) ) );

		// add hour, minute and second to sermon time
		$date += ( $his[0] * 60 * 60 + $his[1] * 60 + $his[2] );

		// convert time to expected format for `mysql2date`
		$date = date( 'Y-m-d H:i:s', $date );

		// do the actual conversion
		$the_time = mysql2date( $format, $date, true );
	}

	return $the_time;
}

add_filter( 'get_the_time', 'wpfc_sermon_time_filter', 10, 3 );

/**
 * Change published date to sermon date on frontend display.
 * Filters the output of `get_the_date` because `get_the_date` returns post date instead of sermon date.
 *
 * @param string|int  $the_date Already filtered time. This will be returned if post type is not `wpfc_sermon`
 * @param string      $d        Format to use for retrieving the time the post was written.
 * @param int|WP_Post $post     WP_Post object or ID
 *
 * @return string|int|false
 */
function wpfc_sermon_date_filter( $the_date = 0, $d = '', $post = null ){
	if ( 'wpfc_sermon' == get_post_type() ) {
		// if the post is not set, try to get current one
		if ( ! is_single() && $post === null ) {
			$post = the_post();
		}

		// get the post
		$post = get_post( $post );

		// this check is maybe not needed, post will be validated on first call of `get_the_time`
		if ( ! $post ) {
			return false;
		}

		// use specified format, or get default one if not specified
		$format = $d === '' ? get_option( 'date_format' ) : $d;

		// get sermon date
		$date = get_post_meta( $post->ID, 'sermon_date', true );

		// if the sermon time is not represented as Unix timestamp, convert it
		if ( ! is_numeric( $date ) ) {
			$date = strtotime( $date );
		}

		// get post hour, minute and second, in seconds
		$his = explode( ':', date( 'H:i:s', strtotime( $post->post_date ) ) );

		// add hour, minute and second to sermon time
		$date += ( $his[0] * 60 * 60 + $his[1] * 60 + $his[2] );

		// convert time to expected format for `mysql2date`
		$date = date( 'Y-m-d H:i:s', $date );

		// do the actual conversion
		$the_date = mysql2date( $format, $date, true );
	}

	return $the_date;
}

add_filter( 'get_the_date', 'wpfc_sermon_date_filter', 10, 3 );
