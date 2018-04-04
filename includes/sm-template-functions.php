<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

if ( ! SermonManager::getOption( 'disable_layouts', false ) ) {
	/**
	 * Include template files
	 */
	if ( ! \SermonManager::getOption( 'theme_compatibility' ) ) {
		add_filter( 'template_include', function ( $template ) {
			if ( is_singular( 'wpfc_sermon' ) ) {
				$default_file = 'single-wpfc_sermon.php';
			} elseif ( is_tax( get_object_taxonomies( 'wpfc_sermon' ) ) ) {
				$term = get_queried_object();

				if ( is_tax( 'wpfc_preacher' ) ||
				     is_tax( 'wpfc_sermon_series' ) ||
				     is_tax( 'wpfc_sermon_topics' ) ||
				     is_tax( 'wpfc_bible_book' ) ||
				     is_tax( 'wpfc_service_type' )
				) {
					$default_file = 'taxonomy-' . $term->taxonomy . '.php';

					if ( ! file_exists( get_stylesheet_directory() . '/' . $default_file ) ) {
						$default_file = 'archive-wpfc_sermon.php';
					}
				} else {
					$default_file = 'archive-wpfc_sermon.php';
				}
			} elseif ( is_post_type_archive( 'wpfc_sermon' ) ) {
				$default_file = 'archive-wpfc_sermon.php';
			} else {
				$default_file = '';
			}

			if ( $default_file ) {
				if ( file_exists( get_stylesheet_directory() . '/' . $default_file ) ) {
					return get_stylesheet_directory() . '/' . $default_file;
				}

				return SM_PATH . 'views/' . $default_file;
			}

			return $template;
		} );
	}

	/**
	 * Replaces default the_content and/or the_excerpt with proper sermon content
	 *
	 * @param string $content The default content
	 *
	 * @return string The modified content if it's Sermon related data
	 */
	function add_wpfc_sermon_content( $content ) {
		if ( 'wpfc_sermon' === get_post_type() && in_the_loop() == true ) {
			if ( ! is_feed() && ( is_archive() || is_search() ) ) {
				$content = wpfc_sermon_excerpt_v2( true );
			} elseif ( is_singular() && is_main_query() ) {
				$content = wpfc_sermon_single_v2( true );
			}
		}

		return $content;
	}

	add_filter( 'the_content', 'add_wpfc_sermon_content' );
	if ( ! \SermonManager::getOption( 'disable_the_excerpt' ) ) {
		add_filter( 'the_excerpt', 'add_wpfc_sermon_content' );
	}
}

/**
 * Render sermon sorting/filtering
 *
 * @param array $args Display options. See the 'sermon_sort_fields' shortcode for array items
 *
 * @see   WPFC_Shortcodes->displaySermonSorting()
 *
 * @return string the HTML
 *
 * @since 2.5.0 added $args
 */
function render_wpfc_sorting( $args = array() ) {
	$action = ( SermonManager::getOption( 'home_url_filtering' ) ? home_url() : site_url() ) . '/' . ( SermonManager::getOption( 'common_base_slug' ) ? ( SermonManager::getOption( 'archive_slug' ) ?: 'sermons' ) : '' );

	// Filters HTML fields data
	$filters = array(
		array(
			'className' => 'sortPreacher',
			'taxonomy'  => 'wpfc_preacher',
			/* Translators: %s: Preacher label (sentence case; singular) */
			'title'     => sprintf( __( 'Filter by %s', 'sermon-manager-for-wordpress' ), \SermonManager::getOption( 'preacher_label' ) ?: 'Preacher' ),
		),
		array(
			'className' => 'sortSeries',
			'taxonomy'  => 'wpfc_sermon_series',
			'title'     => __( 'Filter by Series', 'sermon-manager-for-wordpress' )
		),
		array(
			'className' => 'sortTopics',
			'taxonomy'  => 'wpfc_sermon_topics',
			'title'     => __( 'Filter by Topic', 'sermon-manager-for-wordpress' )
		),
		array(
			'className' => 'sortBooks',
			'taxonomy'  => 'wpfc_bible_book',
			'title'     => __( 'Filter by Book', 'sermon-manager-for-wordpress' )
		),
		array(
			'className' => 'sortServiceTypes',
			'taxonomy'  => 'wpfc_service_type',
			'title'     => __( 'Filter by Service Type', 'sermon-manager-for-wordpress' )
		),
	);

	$visibility_mapping = array(
		'wpfc_sermon_topics' => 'hide_topics',
		'wpfc_sermon_series' => 'hide_series',
		'wpfc_preacher'      => 'hide_preachers',
		'wpfc_bible_book'    => 'hide_books',
		'wpfc_service_type'  => 'hide_service_types',
	);

	if ( empty( $args ) ) {
		$args = array(
			'series_filter'       => '',
			'service_type_filter' => '',
			'series'              => '',
			'preachers'           => '',
			'topics'              => '',
			'books'               => '',
			'visibility'          => 'suggest',
			'hide_topics'         => '',
			'hide_series'         => '',
			'hide_preachers'      => '',
			'hide_books'          => '',
			'hide_service_types'  => SermonManager::getOption( 'service_type_filtering' ) ? '' : 'yes',
		);
	}

	ob_start(); ?>
    <div id="wpfc_sermon_sorting">
		<?php foreach ( $filters as $filter ): ?>
			<?php if ( $args[ $visibility_mapping[ $filter['taxonomy'] ] ] === 'yes' ): ?>
				<?php continue; ?>
			<?php endif; ?>

			<?php if ( ( ! empty( $args[ $filter['taxonomy'] ] ) && $args['visibility'] !== 'none' ) || empty( $args[ $filter['taxonomy'] ] ) ): ?>
                <div class="<?php echo $filter['className'] ?>" style="display: inline-block">
                    <form action="<?php echo $action; ?>">
                        <select name="<?php echo $filter['taxonomy'] ?>"
                                title="<?php echo $filter['title'] ?>"
                                id="<?php echo $filter['taxonomy'] ?>"
                                onchange="if(this.options[this.selectedIndex].value !== ''){return this.form.submit()}else{window.location = '<?= site_url() . '/' . ( SermonManager::getOption( 'archive_slug' ) ?: 'sermons' ) ?>';}"
							<?php echo ! empty( $args[ $filter['taxonomy'] ] ) && $args['visibility'] === 'disable' ? 'disabled' : '' ?>>
                            <option value=""><?php echo $filter['title'] ?></option>
							<?php echo wpfc_get_term_dropdown( $filter['taxonomy'], ! empty( $args[ $filter['taxonomy'] ] ) ? $args[ $filter['taxonomy'] ] : '' ); ?>
                        </select>
						<?php if ( isset( $args['series_filter'] ) && $args['series_filter'] !== '' && $series = explode( ',', $args['series_filter'] ) ): ?>
							<?php if ( $series > 1 ): ?>
								<?php foreach ( $series as $item ): ?>
                                    <input type="hidden" name="wpfc_sermon_series[]"
                                           value="<?= esc_attr( trim( $item ) ) ?>">
								<?php endforeach; ?>
							<?php else: ?>
                                <input type="hidden" name="wpfc_sermon_series"
                                       value="<?= esc_attr( $series[0] ) ?>">
							<?php endif; ?>
						<?php endif; ?>
						<?php if ( isset( $args['service_type_filter'] ) && $args['service_type_filter'] !== '' && $service_types = explode( ',', $args['service_type_filter'] ) ): ?>
							<?php if ( $service_types > 1 ): ?>
								<?php foreach ( $service_types as $service_type ): ?>
                                    <input type="hidden" name="wpfc_service_type[]"
                                           value="<?= esc_attr( trim( $service_type ) ) ?>">
								<?php endforeach; ?>
							<?php else: ?>
                                <input type="hidden" name="wpfc_service_type"
                                       value="<?= esc_attr( $service_types[0] ) ?>">
							<?php endif; ?>
						<?php endif; ?>
                        <noscript>
                            <div><input type="submit" value="Submit"/></div>
                        </noscript>
                    </form>
                </div>
			<?php endif; ?>
		<?php endforeach; ?>
    </div>
	<?php
	return ob_get_clean();
}

/**
 * Echo sermon meta key content from inside a loop
 *
 * @param string $meta_key The meta key name
 * @param string $before   Content before key value
 * @param string $after    Content after key value
 */
function wpfc_sermon_meta( $meta_key = '', $before = '', $after = '' ) {
	echo $before . get_wpfc_sermon_meta( $meta_key ) . $after;
}

/**
 * Return single sermon meta key content from inside a loop
 *
 * @param string $meta_key The meta key name
 *
 * @return mixed|null The meta key content/null if it's blank
 */
function get_wpfc_sermon_meta( $meta_key = '' ) {
	global $post;
	$data = get_post_meta( $post->ID, $meta_key, true );
	if ( $data !== '' ) {
		return $data;
	}

	return null;
}

/**
 * Pass sermon content through WordPres functions, to render shortcodes, etc
 *
 * @param string $meta_key Sermon meta key
 * @param int    $post_id  Post ID
 *
 * @return string The processed content
 */
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

/**
 * Render sermon description
 *
 * @param string $before content before description
 * @param string $after  content after description
 * @param bool   $return True to return, false to echo (default)
 *
 * @return string The HTML, if $return is set to true
 */
function wpfc_sermon_description( $before = '', $after = '', $return = false ) {
	$output = $before . wpautop( process_wysiwyg_output( 'sermon_description', get_the_ID() ) ) . $after;

	if ( ! $return ) {
		echo $output;
	}

	return $output;
}

/**
 * Renders the video player
 *
 * @param string $url  The URL of the video file
 * @param int    $seek Allows seeking to specific second in audio file
 *
 * @since 2.11.0
 * @since 2.12.3 added $seek
 *
 * @return string Video player HTML
 */
function wpfc_render_video( $url = '', $seek = null ) {
	if ( ! is_string( $url ) || trim( $url ) === '' ) {
		return '';
	}

	if ( strpos( $url, 'facebook.' ) !== false ) {
		wp_enqueue_script( 'wpfc-sm-fb-player' );

		parse_str( parse_url( $url, PHP_URL_QUERY ), $query );

		return '<div class="fb-video" data-href="' . $url . '" data-width="' . ( isset( $query['width'] ) ? ( is_numeric( $query['width'] ) ? $query['width'] : '600' ) : '600' ) . '" data-allowfullscreen="' . ( isset( $query['fullscreen'] ) ? ( $query['width'] === 'yes' ? 'true' : 'false' ) : 'true' ) . '"></div>';
	}

	$player = \SermonManager::getOption( 'player' ) ?: 'plyr';

	if ( $player === 'wordpress' ) {
		$attr = array(
			'src'     => $url,
			'preload' => 'none'
		);

		$output = wp_video_shortcode( $attr );
	} else {
		$is_youtube_long  = strpos( strtolower( $url ), 'youtube.com' );
		$is_youtube_short = strpos( strtolower( $url ), 'youtu.be' );
		$is_youtube       = $is_youtube_long || $is_youtube_short;
		$is_vimeo         = strpos( strtolower( $url ), 'vimeo.com' );
		$extra_settings   = '';

		if ( is_numeric( $seek ) ) {
			// sanitation just in case
			$extra_settings = 'data-plyr_seek=\'' . intval( $seek ) . '\'';
		}

		if ( $player === 'plyr' && ( $is_youtube || $is_vimeo ) ) {
			$output = '<div data-type="' . ( $is_youtube ? 'youtube' : 'vimeo' ) . '" data-video-id="' . $url . '" class="wpfc-sermon-video-player video-' . ( $is_youtube ? 'youtube' : 'vimeo' ) . ( $player === 'mediaelement' ? 'mejs__player' : '' ) . '" ' . $extra_settings . '></div>';
		} else {
			$output = '<video controls preload="metadata" class="wpfc-sermon-video-player ' . ( $player === 'mediaelement' ? 'mejs__player' : '' ) . '" ' . $extra_settings . '>';
			$output .= '<source type="video/youtube" src="' . $url . '">';
			$output .= '</video>';
		}
	}

	/**
	 * Allows changing of the video player to any HTML
	 *
	 * @param string $output Video player HTML
	 * @param string $url    Video source URL
	 */
	return apply_filters( 'sm_video_player', $output, $url );
}

/**
 * Renders the audio player
 *
 * @param string $url  The URL of the audio file
 * @param int    $seek Allows seeking to specific second in audio file
 *
 * @since 2.12.3 added $seek
 *
 * @return string Audio player HTML
 */
function wpfc_render_audio( $url = '', $seek = null ) {
	if ( ! is_string( $url ) || trim( $url ) === '' ) {
		return '';
	}

	$player = \SermonManager::getOption( 'player' ) ?: 'plyr';

	if ( $player === 'wordpress' ) {
		$attr = array(
			'src'     => $url,
			'preload' => 'none'
		);

		$output = wp_audio_shortcode( $attr );
	} else {
		$extra_settings = '';

		if ( is_numeric( $seek ) ) {
			// sanitation just in case
			$extra_settings = 'data-plyr_seek=\'' . intval( $seek ) . '\'';
		}

		$output = '<audio controls preload="metadata" class="wpfc-sermon-player ' . ( $player === 'mediaelement' ? 'mejs__player' : '' ) . '" ' . $extra_settings . '>';
		$output .= '<source src="' . $url . '">';
		$output .= '</audio>';
	}

	/**
	 * Allows changing of the audio player to any HTML
	 *
	 * @param string $output Audio player HTML
	 * @param string $url    Audio source URL
	 */
	return apply_filters( 'sm_audio_player', $output, $url );
}

/**
 * Render sermon attachments HTML
 *
 * @return string
 */
function wpfc_sermon_attachments() {
	if ( ! get_wpfc_sermon_meta( 'sermon_notes' ) &&
	     ! get_wpfc_sermon_meta( 'sermon_bulletin' ) ) {
		return '';
	}

	$html = '<div id="wpfc-attachments" class="cf">';
	$html .= '<p><strong>' . __( 'Download Files', 'sermon-manager-for-wordpress' ) . '</strong>';
	if ( get_wpfc_sermon_meta( 'sermon_notes' ) ) {
		$html .= '<a href="' . get_wpfc_sermon_meta( 'sermon_notes' ) . '" class="sermon-attachments" download="' . basename( get_wpfc_sermon_meta( 'sermon_notes' ) ) . '"><span class="dashicons dashicons-media-document"></span>' . __( 'Notes', 'sermon-manager-for-wordpress' ) . '</a>';
	}
	if ( get_wpfc_sermon_meta( 'sermon_bulletin' ) ) {
		$html .= '<a href="' . get_wpfc_sermon_meta( 'sermon_bulletin' ) . '" class="sermon-attachments" download="' . basename( get_wpfc_sermon_meta( 'sermon_bulletin' ) ) . '"><span class="dashicons dashicons-media-document"></span>' . __( 'Bulletin', 'sermon-manager-for-wordpress' ) . '</a>';
	}
	$html .= '</p>';
	$html .= '</div>';

	return apply_filters( 'sm_attachments_html', $html );
}

/**
 * Renders updates single sermon view
 *
 * @param bool    $return True to return output, false to echo (default)
 * @param WP_Post $post   WP_Post instance of the sermon
 *
 * @return string The HTML if $return is set to true
 */
function wpfc_sermon_single_v2( $return = false, $post = null ) {
	if ( $post === null ) {
		global $post;
	}

	ob_start();
	?>

	<?php if ( ! \SermonManager::getOption( 'theme_compatibility' ) ): ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php endif; ?>
    <div class="wpfc-sermon-single-inner">
		<?php if ( get_sermon_image_url() && ! \SermonManager::getOption( 'disable_image_single' ) ) : ?>
            <div class="wpfc-sermon-single-image">
                <img class="wpfc-sermon-single-image-img" alt="<?php the_title() ?>"
                     src="<?php echo get_sermon_image_url() ?>">
            </div>
		<?php endif; ?>
        <div class="wpfc-sermon-single-main">
            <div class="wpfc-sermon-single-header">
                <div class="wpfc-sermon-single-meta-item wpfc-sermon-single-meta-date">
					<?php sm_the_date() ?>
                </div>
				<?php if ( ! \SermonManager::getOption( 'theme_compatibility' ) ): ?>
                    <h2 class="wpfc-sermon-single-title"><?php the_title() ?></h2>
				<?php endif; ?>
                <div class="wpfc-sermon-single-meta">
					<?php if ( has_term( '', 'wpfc_preacher', $post->ID ) ) : ?>
                        <div class="wpfc-sermon-single-meta-item wpfc-sermon-single-meta-preacher <?php echo ( \SermonManager::getOption( 'preacher_label', '' ) ) ? 'custom-label' : ''; ?>">
                            <span class="wpfc-sermon-single-meta-prefix"><?php echo ( ( \SermonManager::getOption( 'preacher_label', '' ) ) ?: __( 'Preacher', 'sermon-manager-for-wordpress' ) ) . ':'; ?></span>
                            <span class="wpfc-sermon-single-meta-text"><?php the_terms( $post->ID, 'wpfc_preacher' ) ?></span>
                        </div>
					<?php endif; ?>
					<?php if ( has_term( '', 'wpfc_sermon_series', $post->ID ) ) : ?>
                        <div class="wpfc-sermon-single-meta-item wpfc-sermon-single-meta-series">
                            <span class="wpfc-sermon-single-meta-prefix">
                                <?php echo __( 'Series', 'sermon-manager-for-wordpress' ) ?>:</span>
                            <span class="wpfc-sermon-single-meta-text"><?php the_terms( $post->ID, 'wpfc_sermon_series' ) ?></span>
                        </div>
					<?php endif; ?>
					<?php if ( get_post_meta( $post->ID, 'bible_passage', true ) ) : ?>
                        <div class="wpfc-sermon-single-meta-item wpfc-sermon-single-meta-passage">
                            <span class="wpfc-sermon-single-meta-prefix">
                                <?php echo __( 'Passage', 'sermon-manager-for-wordpress' ) ?>:</span>
                            <span class="wpfc-sermon-single-meta-text"><?php wpfc_sermon_meta( 'bible_passage' ) ?></span>
                        </div>
					<?php endif; ?>
					<?php if ( has_term( '', 'wpfc_service_type', $post->ID ) ) : ?>
                        <div class="wpfc-sermon-single-meta-item wpfc-sermon-single-meta-service">
                            <span class="wpfc-sermon-single-meta-prefix">
                                <?php echo __( 'Service Type', 'sermon-manager-for-wordpress' ) ?>:</span>
                            <span class="wpfc-sermon-single-meta-text"><?php the_terms( $post->ID, 'wpfc_service_type' ) ?></span>
                        </div>
					<?php endif; ?>
                </div>
            </div>

            <div class="wpfc-sermon-single-media">
				<?php if ( get_wpfc_sermon_meta( 'sermon_video_link' ) ) : ?>
                    <div class="wpfc-sermon-single-video wpfc-sermon-single-video-link">
						<?php echo wpfc_render_video( get_wpfc_sermon_meta( 'sermon_video_link' ), wpfc_get_media_url_seconds( get_wpfc_sermon_meta( 'sermon_video_link' ) ) ); ?>
                    </div>
				<?php endif; ?>
				<?php if ( get_wpfc_sermon_meta( 'sermon_video' ) ) : ?>
                    <div class="wpfc-sermon-single-video wpfc-sermon-single-video-embed">
						<?php echo do_shortcode( get_wpfc_sermon_meta( 'sermon_video' ) ); ?>
                    </div>
				<?php endif; ?>

				<?php if ( get_wpfc_sermon_meta( 'sermon_audio' ) ) : ?>
                    <div class="wpfc-sermon-single-audio player-<?php echo \SermonManager::getOption( 'player', 'plyr' ); ?>">
						<?php echo wpfc_render_audio( get_wpfc_sermon_meta( 'sermon_audio' ), wpfc_get_media_url_seconds( get_wpfc_sermon_meta( 'sermon_audio' ) ) ); ?>
                        <a class="wpfc-sermon-single-audio-download"
                           href="<?php echo get_wpfc_sermon_meta( 'sermon_audio' ) ?>"
                           download="<?php echo basename( get_wpfc_sermon_meta( 'sermon_audio' ) ) ?>"
                           title="<?php echo __( 'Download Audio File', 'sermon-manager-for-wordpress' ); ?>">
                            <svg fill="#000000" height="24" viewBox="0 0 24 24" width="24"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 0h24v24H0z" fill="none"/>
                                <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/>
                            </svg>
                        </a>
                    </div>
				<?php endif; ?>
            </div>

            <div class="wpfc-sermon-single-description"><?php wpfc_sermon_description() ?></div>
			<?php if ( get_wpfc_sermon_meta( 'sermon_notes' ) || get_wpfc_sermon_meta( 'sermon_bulletin' ) ) : ?>
                <div class="wpfc-sermon-single-attachments"><?php echo wpfc_sermon_attachments(); ?></div>
			<?php endif; ?>
			<?php if ( has_term( '', 'wpfc_sermon_topics', $post->ID ) ) : ?>
                <div class="wpfc-sermon-single-topics">
                    <span class="wpfc-sermon-single-topics-prefix">
                        <?php echo __( 'Topics', 'sermon-manager-for-wordpress' ) ?>:</span>
                    <span class="wpfc-sermon-single-topics-text"><?php the_terms( $post->ID, 'wpfc_sermon_topics' ) ?></span>
                </div>
			<?php endif; ?>

			<?php if ( ! \SermonManager::getOption( 'theme_compatibility' ) ): ?>
				<?php $previous_sermon = sm_get_previous_sermon();
				$next_sermon           = sm_get_next_sermon(); ?>
				<?php if ( $previous_sermon || $next_sermon ): ?>
                    <div class="wpfc-sermon-single-navigation">
						<?php $previous_attr = apply_filters( 'previous_posts_link_attributes', 'class="previous-sermon"' );
						$next_attr           = apply_filters( 'next_posts_link_attributes', 'class="next-sermon"' ); ?>
						<?php if ( $previous_sermon !== null ): ?>
                            <a href="<?php echo get_the_permalink( $previous_sermon ) ?>" <?php echo $previous_attr ?>><?php echo preg_replace( '/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', '&laquo; ' . get_the_title( $previous_sermon ) ) ?></a>
						<?php else: ?>
                            <div></div>
						<?php endif; ?>
						<?php if ( $next_sermon !== null ): ?>
                            <a href="<?php echo get_the_permalink( $next_sermon ) ?>" <?php echo $next_attr ?>><?php echo preg_replace( '/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', get_the_title( $next_sermon ) . ' &raquo;' ) ?></a>
						<?php else: ?>
                            <div></div>
						<?php endif; ?>
                    </div>
				<?php endif; ?>
			<?php endif; ?>
        </div>
    </div>
	<?php if ( ! \SermonManager::getOption( 'theme_compatibility' ) ): ?>
        </article>
	<?php endif; ?>

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
	$output = apply_filters( 'wpfc_sermon_single_v2', $output, $post );

	if ( ! $return ) {
		echo $output;
	}

	return $output;
}

/**
 * Renders updated archive sermon view
 *
 * @param bool $return True to return output, false to echo (default)
 *
 * @return string The HTML if $return is set to true
 */
function wpfc_sermon_excerpt_v2( $return = false ) {
	global $post;

	ob_start();
	?>
	<?php if ( ! ( \SermonManager::getOption( 'theme_compatibility' ) || ( defined( 'wpfc_sm_shortcode' ) && wpfc_sm_shortcode === true ) ) ) : ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php endif; ?>
    <div class="wpfc-sermon-inner">
		<?php if ( get_sermon_image_url() && ! \SermonManager::getOption( 'disable_image_archive' ) ) : ?>
            <div class="wpfc-sermon-image">
                <a href="<?php the_permalink() ?>">
                    <div class="wpfc-sermon-image-img"
                         style="background-image: url(<?php echo get_sermon_image_url() ?>)"></div>
                </a>
            </div>
		<?php endif; ?>
        <div class="wpfc-sermon-main <?= get_sermon_image_url() ? '' : 'no-image' ?>">
            <div class="wpfc-sermon-header">
                <div class="wpfc-sermon-header-main">
					<?php if ( has_term( '', 'wpfc_sermon_series', $post->ID ) ) : ?>
                        <div class="wpfc-sermon-meta-item wpfc-sermon-meta-series">
							<?php the_terms( $post->ID, 'wpfc_sermon_series' ) ?>
                        </div>
					<?php endif; ?>
					<?php if ( ! ( \SermonManager::getOption( 'theme_compatibility' ) && ! ( defined( 'wpfc_sm_shortcode' ) && wpfc_sm_shortcode === true ) ) ) : ?>
                        <h3 class="wpfc-sermon-title">
                            <a class="wpfc-sermon-title-text" href="<?php the_permalink() ?>"><?php the_title() ?></a>
                        </h3>
					<?php endif; ?>
                    <div class="wpfc-sermon-meta-item wpfc-sermon-meta-date">
						<?php sm_the_date() ?>
                    </div>
                </div>
				<?php if ( \SermonManager::getOption( 'archive_meta' ) ): ?>
                    <div class="wpfc-sermon-header-aside">
						<?php if ( get_wpfc_sermon_meta( 'sermon_audio' ) ) : ?>
                            <a class="wpfc-sermon-att-audio dashicons dashicons-media-audio"
                               href="<?php echo get_wpfc_sermon_meta( 'sermon_audio' ); ?>"
                               download="<?php echo basename( get_wpfc_sermon_meta( 'sermon_audio' ) ); ?>"
                               title="Audio"></a>
						<?php endif; ?>
						<?php if ( get_wpfc_sermon_meta( 'sermon_notes' ) ) : ?>
                            <a class="wpfc-sermon-att-notes dashicons dashicons-media-document"
                               href="<?php echo get_wpfc_sermon_meta( 'sermon_notes' ); ?>"
                               download="<?php echo basename( get_wpfc_sermon_meta( 'sermon_notes' ) ); ?>"
                               title="Notes"></a>
						<?php endif; ?>
						<?php if ( get_wpfc_sermon_meta( 'sermon_bulletin' ) ) : ?>
                            <a class="wpfc-sermon-att-bulletin dashicons dashicons-media-text"
                               href="<?php echo get_wpfc_sermon_meta( 'sermon_bulletin' ); ?>"
                               download="<?php echo basename( get_wpfc_sermon_meta( 'sermon_bulletin' ) ); ?>"
                               title="Bulletin"></a>
						<?php endif; ?>
                    </div>
				<?php endif; ?>
            </div>
			<?php $sermon_description = get_post_meta( $post->ID, 'sermon_description', true ); ?>
            <div class="wpfc-sermon-description"><?php echo wp_trim_words( $sermon_description, 30 ); ?></div>
			<?php if ( \SermonManager::getOption( 'archive_player' ) && get_wpfc_sermon_meta( 'sermon_audio' ) ) : ?>
                <div class="wpfc-sermon-audio">
					<?php echo wpfc_render_audio( get_wpfc_sermon_meta( 'sermon_audio' ), wpfc_get_media_url_seconds( get_wpfc_sermon_meta( 'sermon_audio' ) ) ); ?>
                </div>
			<?php endif; ?>

            <div class="wpfc-sermon-footer">
				<?php if ( has_term( '', 'wpfc_preacher', $post->ID ) ) : ?>
                    <div class="wpfc-sermon-meta-item wpfc-sermon-meta-preacher">
						<?php echo apply_filters( 'sermon-images-list-the-terms', '', array(
							'taxonomy'     => 'wpfc_preacher',
							'after'        => '',
							'after_image'  => '',
							'before'       => '',
							'before_image' => '',
						) ) ?>
                        <span class="wpfc-sermon-meta-prefix">
                            <?php echo ( \SermonManager::getOption( 'preacher_label', '' ) ) ?: __( 'Preacher', 'sermon-manager-for-wordpress' ); ?>
                            :</span>
                        <span class="wpfc-sermon-meta-text"><?php the_terms( $post->ID, 'wpfc_preacher' ) ?></span>
                    </div>
				<?php endif; ?>
				<?php if ( get_post_meta( $post->ID, 'bible_passage', true ) ) : ?>
                    <div class="wpfc-sermon-meta-item wpfc-sermon-meta-passage">
                        <span class="wpfc-sermon-meta-prefix">
                            <?php echo __( 'Passage', 'sermon-manager-for-wordpress' ) ?>:</span>
                        <span class="wpfc-sermon-meta-text"><?php wpfc_sermon_meta( 'bible_passage' ) ?></span>
                    </div>
				<?php endif; ?>
				<?php if ( has_term( '', 'wpfc_service_type', $post->ID ) ) : ?>
                    <div class="wpfc-sermon-meta-item wpfc-sermon-meta-service">
                        <span class="wpfc-sermon-meta-prefix">
                            <?php echo __( 'Service Type', 'sermon-manager-for-wordpress' ) ?>:</span>
                        <span class="wpfc-sermon-meta-text"><?php the_terms( $post->ID, 'wpfc_service_type' ) ?></span>
                    </div>
				<?php endif; ?>
            </div>
        </div>
    </div>

	<?php if ( ! ( \SermonManager::getOption( 'theme_compatibility' ) || ( defined( 'wpfc_sm_shortcode' ) && wpfc_sm_shortcode === true ) ) ): ?>
        </article>
	<?php endif; ?>

	<?php

	$output = ob_get_clean();

	/**
	 * Allows you to modify the sermon HTML on archive pages
	 *
	 * @param string  $output The HTML that will be outputted
	 * @param WP_Post $post   The sermon
	 *
	 * @since 2.12.0
	 */
	$output = apply_filters( 'wpfc_sermon_excerpt_v2', $output, $post );

	if ( ! $return ) {
		echo $output;
	}

	return $output;
}

/**
 * Build <option> fields for <select> element
 *
 * @param string $taxonomy Taxonomy name
 * @param string $default  Force a default value regardless the query var
 *
 * @return string HTML <option> fields
 *
 * @since 2.5.0 added $default
 */
function wpfc_get_term_dropdown( $taxonomy, $default = '' ) {
	// reset var
	$html = '';

	$terms = get_terms( array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => false, // todo: add option to disable/enable this globally
	) );

	if ( $taxonomy === 'wpfc_bible_book' && \SermonManager::getOption( 'sort_bible_books', true ) ) {
		// book order
		$books = array(
			'Genesis',
			'Exodus',
			'Leviticus',
			'Numbers',
			'Deuteronomy',
			'Joshua',
			'Judges',
			'Ruth',
			'1 Samuel',
			'2 Samuel',
			'1 Kings',
			'2 Kings',
			'1 Chronicles',
			'2 Chronicles',
			'Ezra',
			'Nehemiah',
			'Esther',
			'Job',
			'Psalms',
			'Proverbs',
			'Ecclesiastes',
			'Song of Songs',
			'Isaiah',
			'Jeremiah',
			'Lamentations',
			'Ezekiel',
			'Daniel',
			'Hosea',
			'Joel',
			'Amos',
			'Obadiah',
			'Jonah',
			'Micah',
			'Nahum',
			'Habakkuk',
			'Zephaniah',
			'Haggai',
			'Zechariah',
			'Malachi',
			'Matthew',
			'Mark',
			'Luke',
			'John',
			'Acts',
			'Romans',
			'1 Corinthians',
			'2 Corinthians',
			'Galatians',
			'Ephesians',
			'Philippians',
			'Colossians',
			'1 Thessalonians',
			'2 Thessalonians',
			'1 Timothy',
			'2 Timothy',
			'Titus',
			'Philemon',
			'Hebrews',
			'James',
			'1 Peter',
			'2 Peter',
			'1 John',
			'2 John',
			'3 John',
			'Jude',
			'Revelation',
			'Topical',
		);

		$ordered_terms = $unordered_terms = array();

		// assign every book a number
		foreach ( $terms as $term ) {
			if ( array_search( $term->name, $books ) !== false ) {
				$ordered_terms[ array_search( $term->name, $books ) ] = $term;
			} else {
				$unordered_terms[] = $term;
			}
		}

		// order the numbers (books)
		ksort( $ordered_terms );

		$terms = array_merge( $ordered_terms, $unordered_terms );
	}

	foreach ( $terms as $term ) {
		$html .= '<option value="' . $term->slug . '" ' . ( ( $default === '' ? $term->slug === get_query_var( $taxonomy ) : $term->slug === $default ) ? 'selected' : '' ) . '>' . $term->name . '</option>';
	}

	return $html;
}
