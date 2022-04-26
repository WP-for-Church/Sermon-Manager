<?php
/**
 * Template functions, used when displaying content on frontend.
 *
 * @package SM/Core/Templating
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) or die;

if ( ! SermonManager::getOption( 'disable_layouts', false ) ) {
	/**
	 * Include template files.
	 */
	if ( ! SermonManager::getOption( 'theme_compatibility' ) ) {
		add_filter(
			'template_include',
			function ( $template ) {
				return sm_get_views_path( $template );
			}
		);
	}

	/**
	 * Replaces default the_content and/or the_excerpt with proper sermon content.
	 *
	 * @param string $content The default content.
	 *
	 * @return string The modified content if it's Sermon related data.
	 */
	function add_wpfc_sermon_content( $content ) {
		if ( 'wpfc_sermon' === get_post_type() && in_the_loop() == true ) {
			if ( ! is_feed() && ( is_archive() || is_search() ) ) {
				$content .= wpfc_sermon_excerpt_v2( true );
			} elseif ( is_singular() && is_main_query() ) {
				$content .= wpfc_sermon_single_v2( true );
			}
		}

		return $content;
	}

	//add_filter( 'the_content', 'add_wpfc_sermon_content' );
	if ( ! SermonManager::getOption( 'disable_the_excerpt' ) ) {
		add_filter( 'the_excerpt', 'add_wpfc_sermon_content' );
	}
}

/**
 * Render sermon sorting/filtering.
 *
 * @param array $args Display options. See the 'sermon_sort_fields' shortcode for array items.
 *
 * @return string The HTML.
 *
 * @see   WPFC_Shortcodes->displaySermonSorting()
 *
 * @since 2.5.0 added $args
 */
function render_wpfc_sorting( $args = array() ) {
	// Action is not needed anymore, yay!
	// Left here so filters below have the argument value.
	$action = '';

	// Filters HTML fields data.
	$filters = apply_filters(
		'render_wpfc_sorting_filters',
		array(
			array(
				'className' => 'sortPreacher',
				'taxonomy'  => 'wpfc_preacher',
				'title'     => sm_get_taxonomy_field( 'wpfc_preacher', 'singular_name' ),
			),
			array(
				'className' => 'sortSeries',
				'taxonomy'  => 'wpfc_sermon_series',
				'title'     => __( 'Series', 'sermon-manager-for-wordpress' ),
			),
			array(
				'className' => 'sortTopics',
				'taxonomy'  => 'wpfc_sermon_topics',
				'title'     => __( 'Topic', 'sermon-manager-for-wordpress' ),
			),
			array(
				'className' => 'sortBooks',
				'taxonomy'  => 'wpfc_bible_book',
				'title'     => __( 'Book', 'sermon-manager-for-wordpress' ),
			),
			array(
				'className' => 'sortServiceTypes',
				'taxonomy'  => 'wpfc_service_type',
				'title'     => sm_get_taxonomy_field( 'wpfc_service_type', 'singular_name' ),
			),
		)
	);

	$visibility_mapping = apply_filters(
		'render_wpfc_sorting_visibility_mapping',
		array(
			'wpfc_sermon_topics' => 'hide_topics',
			'wpfc_sermon_series' => 'hide_series',
			'wpfc_preacher'      => 'hide_preachers',
			'wpfc_bible_book'    => 'hide_books',
			'wpfc_service_type'  => 'hide_service_types',
			'wpfc_dates'         => 'hide_dates',
		)
	);

	// Save orig args for filters.
	$orig_args = $args;

	$default = array(
		'id'                  => 'wpfc_sermon_sorting',
		'classes'             => '',
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
		'hide_dates'          => '',
		'hide_filters'        => ! SermonManager::getOption( 'hide_filters' ),
		'action'              => 'none',
	);
	$args    = $args + $default;

	// Populate the action field.
	switch ( $args['action'] ) {
		case 'home':
			$args['action'] = get_home_url();
			break;
		case 'site':
			$args['action'] = get_site_url();
			break;
		case 'none':
		default:
			if ( get_query_var( 'paged' ) === 0 ) {
				$args['action'] = '';
			} else {
				$args['action'] = str_replace( parse_url( get_pagenum_link(), PHP_URL_QUERY ), '', get_pagenum_link() );
			}
			break;
	}

	/**
	 * Allows to filter filtering args.
	 *
	 * @param array  $args               The args.
	 * @param array  $orig_args          The unmodified args.
	 * @param string $action             The form URL.
	 * @param array  $filters            Filters HTML form data. i.e. no idea.
	 * @param array  $visibility_mapping Taxonomy slug -> args parameter name
	 *
	 * @since 2.15.0 - add other args, except $args.
	 *
	 * @since 2.13.5
	 */
	$args = apply_filters( 'sm_render_wpfc_sorting_args', $args, $orig_args, $action, $filters, $visibility_mapping );

	$hide_filters = $args['hide_filters'];

	/**
	 * Allows to skip rendering of filtering completely.
	 *
	 * @param bool   $hide_filters       True to show, false to hide. Default as it is defined in settings.
	 * @param array  $args               The args.
	 * @param array  $orig_args          The unmodified args.
	 * @param string $action             The form URL.
	 * @param array  $filters            Filters HTML form data. i.e. no idea.
	 * @param array  $visibility_mapping Taxonomy slug -> args parameter name
	 *
	 * @since 2.13.5
	 * @since 2.15.0 - add other parameters, except $hide_filters.
	 */
	if ( apply_filters( 'sm_render_wpfc_sorting', $hide_filters, $args, $orig_args, $action, $filters, $visibility_mapping ) ) {
		$content = wpfc_get_partial(
			'content-sermon-filtering',
			array(
				'action'             => $action,
				'filters'            => $filters,
				'visibility_mapping' => $visibility_mapping,
				'args'               => $args,
			)
		);
	} else {
		$content = '';
	}

	/**
	 * Allows to filter the output of filter rendering.
	 *
	 * @param string $content            The original content.
	 * @param array  $args               The args.
	 * @param array  $orig_args          The unmodified args.
	 * @param string $action             The form URL.
	 * @param array  $filters            Filters HTML form data. i.e. no idea.
	 * @param array  $visibility_mapping Taxonomy slug -> args parameter name
	 *
	 * @since 2.15.0
	 */
	return apply_filters( 'render_wpfc_sorting_output', $content, $args, $orig_args, $action, $filters, $visibility_mapping );
}

/**
 * Echo sermon meta key content from inside a loop.
 *
 * @param string $meta_key The meta key name.
 * @param string $before   Content before key value.
 * @param string $after    Content after key value.
 */
function wpfc_sermon_meta( $meta_key = '', $before = '', $after = '' ) {
	echo $before . get_wpfc_sermon_meta( $meta_key ) . $after;
}

/**
 * Return single sermon meta key content from inside a loop.
 *
 * @param string       $meta_key The meta key name.
 * @param WP_Post|null $post     The sermon post object.
 *
 * @return mixed|null The meta key content/null if it's blank.
 */
function get_wpfc_sermon_meta( $meta_key = '', $post = null ) {
	if ( null === $post ) {
		global $post;
	}

	$data = get_post_meta( $post->ID, $meta_key, true );
	if ( '' !== $data ) {
		return $data;
	}

	return null;
}

/**
 * Pass sermon content through WordPress functions, to render shortcodes, etc.
 *
 * @param string $meta_key Sermon meta key.
 * @param int    $post_id  Post ID.
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
 * Render sermon description.
 *
 * @param string $before Content before description.
 * @param string $after  Content after description.
 * @param bool   $return True to return, false to echo (default).
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
 * Renders the video player.
 *
 * @param string   $url  The URL of the video file.
 * @param int|bool $seek Allows seeking to specific second in audio file. Pass an int to override auto detection or
 *                       false to disable auto detection.
 *
 * @return string Video player HTML.
 * @since 2.12.3 added $seek
 *
 * @since 2.11.0
 */
function wpfc_render_video( $url = '', $seek = true ) {
	if ( ! is_string( $url ) || trim( $url ) === '' ) {
		return '';
	}

	if ( strpos( $url, 'facebook.' ) !== false ) {
		wp_enqueue_script( 'wpfc-sm-fb-player' );

		parse_str( parse_url( $url, PHP_URL_QUERY ), $query );

		return '<div class="fb-video" data-href="' . $url . '" data-width="' . ( isset( $query['width'] ) ? ( is_numeric( $query['width'] ) ? $query['width'] : '600' ) : '600' ) . '" data-allowfullscreen="' . ( isset( $query['fullscreen'] ) ? ( 'yes' === $query['width'] ? 'true' : 'false' ) : 'true' ) . '"></div>';
	}

	$player = strtolower( SermonManager::getOption( 'player' ) ?: 'plyr' );

	if ( strtolower( 'WordPress' ) === $player ) {
		$attr = array(
			'src'     => $url,
			'preload' => 'none',
		);

		$output = wp_video_shortcode( $attr );
	} else {
		$is_youtube_long  = strpos( strtolower( $url ), 'youtube.com' );
		$is_youtube_short = strpos( strtolower( $url ), 'youtu.be' );
		$is_youtube       = $is_youtube_long || $is_youtube_short;
		$is_vimeo         = strpos( strtolower( $url ), 'vimeo.com' );
		$extra_settings   = '';
		$output           = '';

		if ( is_numeric( $seek ) || true === $seek ) {
			if ( is_numeric( $seek ) ) {
				$seconds = $seek;
			} else {
				$seconds = wpfc_get_media_url_seconds( $url );
			}

			// Sanitation just in case.
			$extra_settings = 'data-plyr_seek=\'' . intval( $seconds ) . '\'';
		}

		// Remove seek from URL.
		$url = preg_replace( '/(\?|#|&)t.*$/', '', $url );

		if ( 'plyr' === $player && ( $is_youtube || $is_vimeo ) ) {
			$output .= '<div data-plyr-provider="' . ( $is_youtube ? 'youtube' : 'vimeo' ) . '" data-plyr-embed-id="' . $url . '" class="plyr__video-embed wpfc-sermon-video-player video-' . ( $is_youtube ? 'youtube' : 'vimeo' ) . ( 'mediaelement' === $player ? 'mejs__player' : '' ) . '" ' . $extra_settings . '></div>';
		} else {
			$output .= '<video controls preload="metadata" class="wpfc-sermon-video-player ' . ( 'mediaelement' === $player ? 'mejs__player' : '' ) . '" ' . $extra_settings . '>';
			$output .= '<source src="' . $url . '">';
			$output .= '</video>';
		}
	}

	/**
	 * Allows changing of the video player to any HTML.
	 *
	 * @param string $output Video player HTML.
	 * @param string $url    Video source URL.
	 */
	return apply_filters( 'sm_video_player', $output, $url );
}

/**
 * Renders the audio player.
 *
 * @param int|string $source The ID of the sermon, or alternatively, the URL or the attachment ID of the audio file.
 * @param int        $seek   Seek to specific second in audio file.
 *
 * @return string|false Audio player HTML or false if sermon has no audio.
 * @since 2.15.15 The sermon can be used as first parameter
 *
 * @since 2.12.3 added $seek
 */
function wpfc_render_audio( $source = '', $seek = null ) {
	// For later filtering.
	$source_orig = $source;

	// Check if it's a sermon or attachment ID.
	if ( is_numeric( $source ) ) {
		$object = get_post( $source );

		if ( ! $object ) {
			return false;
		}

		switch ( $object->post_type ) {
			case 'wpfc_sermon':
				$sermon_audio_id     = get_wpfc_sermon_meta( 'sermon_audio_id' );
				$sermon_audio_url    = get_wpfc_sermon_meta( 'sermon_audio' );
				$sermon_audio_url_wp = $sermon_audio_id ? wp_get_attachment_url( intval( $sermon_audio_id ) ) : false;

				$source = $sermon_audio_id && $sermon_audio_url_wp ? $sermon_audio_url_wp : $sermon_audio_url;
				break;
			case 'attachment':
				$source = wp_get_attachment_url( $object->ID );
				break;
		}
	}

	// Check if set.
	if ( ! $source ) {
		return false;
	}

	// Get the current player.
	$player = strtolower( SermonManager::getOption( 'player' ) ?: 'plyr' );

	switch ( strtolower( $player ) ) {
		case 'wordpress': // phpcs:ignore
			$attr = array(
				'src'     => $source,
				'preload' => 'none',
			);

			$output = wp_audio_shortcode( $attr );
			break;
		default:
			$extra_settings = '';

			if ( is_numeric( $seek ) ) {
				// Sanitation just in case.
				$extra_settings = 'data-plyr_seek=\'' . intval( $seek ) . '\'';
			}

			$output = '';

			$output .= '<audio controls preload="metadata" class="wpfc-sermon-player ' . ( 'mediaelement' === $player ? 'mejs__player' : '' ) . '" ' . $extra_settings . '>';
			$output .= '<source src="' . $source . '" type="audio/mp3">';
			$output .= '</audio>';

			break;
	}

	/**
	 * Allows changing of the audio player to any HTML.
	 *
	 * @param string     $output      Audio player HTML.
	 * @param string     $source      Audio source URL.
	 * @param int|string $source_orig The original source parameter.
	 *
	 * @since 2.15.15 Added $source_orig.
	 */
	return apply_filters( 'sm_audio_player', $output, $source, $source_orig );
}

/**
 * Render sermon attachments HTML.
 *
 * @return string
 */
function wpfc_sermon_attachments() {
	if ( ! get_wpfc_sermon_meta( 'sermon_notes' ) && ! get_wpfc_sermon_meta( 'sermon_bulletin' )  && ! get_wpfc_sermon_meta( 'sermon_notes_multiple' )  && ! get_wpfc_sermon_meta( 'sermon_bulletin_multiple' ) ) {
		return '';
	}

	$output = wpfc_get_partial( 'content-sermon-attachments' );
	// print_r($output);
	/**
	 * Allows to filter the output of sermon attachments HTML.
	 *
	 * @param string $output The HTML.
	 *
	 * @since 2.11.3
	 */
	return apply_filters( 'sm_attachments_html', $output );
}

/**
 * Renders updates single sermon view.
 *
 * @param bool    $return True to return output, false to echo (default).
 * @param WP_Post $post   WP_Post instance of the sermon.
 *
 * @return string The HTML if $return is set to true.
 */
function wpfc_sermon_single_v2( $return = false, $post = null ) {
	if ( null === $post ) {
		global $post;
	} else {
		// Save global $post value for later restoration. Just in case.
		$new_post = $post;
		$old_post = $GLOBALS['post'];
		$post     = $new_post;
	}

	// Get the partial.
	$output = wpfc_get_partial( 'content-sermon-single' );

	/**
	 * Allows you to modify the sermon HTML on single sermon pages.
	 *
	 * @param string  $output The HTML that will be outputted.
	 * @param WP_Post $post   The sermon.
	 *
	 * @since 2.12.0
	 */
	$output = apply_filters( 'wpfc_sermon_single_v2', $output, $post );

	// Restore the global $post value. Just in case.
	$GLOBALS['post'] = ! empty( $GLOBALS['post'] ) ? ! empty( $old_post ) ? $old_post : $post : null;

	if ( ! $return ) {
		echo $output;
	}

	return $output;
}

/**
 * Renders updated archive sermon view.
 *
 * @param bool  $return True to return output, false to echo (default).
 * @param array $args   Passed from shortcode.
 *
 * @return string The HTML if $return is set to true.
 */
function wpfc_sermon_excerpt_v2( $return = false, $args = array() ) {
	global $post;

	$args += array(
		'image_size' => 'post-thumbnail',
	);

	// Get the partial.
	$output = wpfc_get_partial( 'content-sermon-archive', $args );

	/**
	 * Allows you to modify the sermon HTML on archive pages.
	 *
	 * @param string  $output The HTML that will be outputted.
	 * @param WP_Post $post   The sermon.
	 * @param array   $args   Rendering arguments. Passed from shortcode.
	 *
	 * @since 2.12.0
	 */
	$output = apply_filters( 'wpfc_sermon_excerpt_v2', $output, $post, $args );

	if ( ! $return ) {
		echo $output;
	}

	return $output;
}

/**
 * Build <option> fields for <select> element.
 *
 * @param string $taxonomy Taxonomy name.
 * @param string $default  Force a default value regardless the query var.
 *
 * @return string HTML <option> fields
 *
 * @since 2.5.0 added $default
 */
function wpfc_get_term_dropdown( $taxonomy, $default = '' ) {
	// Reset var.
	$html = '';

	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false, // todo: add option to disable/enable this globally.
		)
	);

	if ( 'wpfc_bible_book' === $taxonomy && SermonManager::getOption( 'sort_bible_books', true ) ) {
		// Book order.
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

		$ordered_terms   = array();
		$unordered_terms = array();

		// Assign every book a number.
		foreach ( $terms as $term ) {
			if ( array_search( $term->name, $books ) !== false ) {
				$ordered_terms[ array_search( $term->name, $books ) ] = $term;
			} else {
				$unordered_terms[] = $term;
			}
		}

		// Order the numbers (books).
		//ksort( $ordered_terms );

		$terms = array_merge( $ordered_terms, $unordered_terms );
        sort( $terms );
	}

	$current_slug = get_query_var( $taxonomy ) ?: ( isset( $_GET[ $taxonomy ] ) ? $_GET[ $taxonomy ] : '' );

	foreach ( $terms as $term ) {
		$html .= '<option value="' . $term->slug . '" ' . ( ( '' === $default ? $current_slug === $term->slug : $default === $term->slug ) ? 'selected' : '' ) . '>' . $term->name . '</option>';
	}

	/**
	 * Allows you to filter the dropdown options (HTML).
	 *
	 * @var string $html         The existing HTML.
	 * @var array  $taxonomy     The taxonomy that is being used.
	 * @var string $default      The forced default value. See function PHPDoc.
	 * @var array  $terms        The array of terms, books will already be ordered.
	 * @var string $current_slug The term that is being requested.
	 *
	 * @since 2.15.12
	 */
	return apply_filters( 'wpfc_get_term_dropdown', $html, $taxonomy, $default, $terms, $current_slug );
}

/**
 * Allows user to override the partial file for rendering by placing it in either:
 * - `/wp-contents/themes/<theme_name>/partials/<partial_name>.php`
 * - `/wp-contents/themes/<theme_name>/template-parts/<partial_name>.php`
 * - `/wp-contents/themes/<theme_name>/<partial_name>.php`
 *
 * @param string $name File name of the partial file to load. Can include `.php`, but not required.
 * @param array  $args Array of variable => content, to use in the partial.
 *
 * @return string The contents of the partial.
 *
 * @since 2.13.0
 */
function wpfc_get_partial( $name = '', $args = array() ) {
	if ( '' === $name ) {
		$content = '';
	} else {
		$partial                      = null;
		$GLOBALS['wpfc_partial_args'] = $args;

		if ( false === strpos( $name, '.php' ) ) {
			$name .= '.php';
		}

		foreach (
			array(
				'partials/',
				'template-parts/',
				'',
			) as $path
		) {
			$partial = locate_template( $path . $name );

			if ( $partial ) {
				break;
			}
		}

		if ( SM_OB_ENABLED ) {
			ob_start();

			if ( $partial ) {
				load_template( $partial, false );
			} else {
				if ( file_exists( SM_PATH . 'views/partials/' . $name ) ) {
					load_template( SM_PATH . 'views/partials/' . $name, false );
				} else {
					echo '<p><b>Sermon Manager</b>: Failed loading partial "<i>' . str_replace( '.php', '', $name ) . '</i>", file does not exist.</p>';
				}
			}

			$content = ob_get_clean();
		} else {
			$content = '';
		}
	}

	/**
	 * Allows to filter the partial content.
	 *
	 * @param string $content The partial content.
	 * @param string $name    The partial file name.
	 *
	 * @since 2.13.0
	 */
	return apply_filters( 'wpfc_get_partial', $content, $name );
}

/**
 * Returns SM template path.
 *
 * @param string $template The template.
 *
 * @return string The template path.
 *
 * @since 2.13.4
 */
function sm_get_views_path( $template = '' ) {
	$force_views = SermonManager::getOption( 'force_layouts' );

	if ( is_singular( 'wpfc_sermon' ) ) {
		$default_file = 'single-wpfc_sermon.php';
	} elseif ( is_tax( get_object_taxonomies( 'wpfc_sermon' ) ) ) {
		$term = get_queried_object();

		if ( is_tax(
			array(
				'wpfc_preacher',
				'wpfc_sermon_series',
				'wpfc_sermon_topics',
				'wpfc_bible_book',
				'wpfc_service_type',
			)
		) ) {
			$default_file = 'taxonomy-' . $term->taxonomy . '.php';

			if ( ! file_exists( get_stylesheet_directory() . '/' . $default_file ) && ! $force_views ) {
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
		if ( file_exists( get_stylesheet_directory() . '/' . $default_file ) && ! $force_views ) {
			return get_stylesheet_directory() . '/' . $default_file;
		}

		return SM_PATH . 'views/' . $default_file;
	}

	return $template;
}

/**
 * Renders the pagination in views files.
 *
 * @since 2.15.14
 */
function sm_pagination() {
	if ( SermonManager::getOption( 'use_prev_next_pagination' ) ) {
		posts_nav_link();
	} else {
		if ( function_exists( 'wp_pagenavi' ) ) :
			wp_pagenavi();
		elseif ( function_exists( 'oceanwp_pagination' ) ) :
			oceanwp_pagination();
		elseif ( function_exists( 'pagination' ) ) :
			pagination();
		elseif ( function_exists( 'mfn_pagination' ) ) :
			echo mfn_pagination();
		elseif ( function_exists( 'presscore_complex_pagination' ) ) :
			presscore_complex_pagination( $GLOBALS['wp_query'] );
		elseif ( function_exists( 'cro_paging' ) ) :
			cro_paging();
		elseif ( function_exists( 'twentynineteen_the_posts_navigation' ) ) :
			twentynineteen_the_posts_navigation();
		elseif ( function_exists( 'exodoswp_pagination' ) ) :
			echo '<div class="modeltheme-pagination-holder col-md-12"><div class="modeltheme-pagination pagination">';
			exodoswp_pagination();
			echo '</div></div>';
		else :
			the_posts_pagination();
		endif;
	}
}