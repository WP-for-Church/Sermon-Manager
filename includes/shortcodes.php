<?php
/**
 * Sermons Shortcodes
 *
 */
// List all series or speakers in a simple unordered list
add_shortcode( 'list_sermons', 'wpfc_list_sermons_shortcode' ); //preferred markup
add_shortcode( 'list-sermons', 'wpfc_list_sermons_shortcode' ); //left for compatibility
// Display all series or speakers in a grid of images
add_shortcode( 'sermon_images', 'wpfc_display_images_shortcode' ); //preferred markup
add_shortcode( 'sermon-images', 'wpfc_display_images_shortcode' ); //left for compatibility
// Display the latest sermon series image (optional - by service type)
add_shortcode( 'latest_series', 'wpfc_get_latest_series_image' );
// Create the shortcode
add_shortcode( 'sermons', 'wpfc_display_sermons_shortcode' );
add_shortcode( 'sermon_sort_fields', 'wpfc_sermons_sorting_shortcode' );

// List all series or speakers in a simple unordered list
function wpfc_list_sermons_shortcode( $atts = array() ) {
	extract( shortcode_atts( array(
		'tax'     => 'wpfc_sermon_series',
		// options: wpfc_sermon_series, wpfc_preacher, wpfc_sermon_topics, wpfc_bible_book
		'order'   => 'ASC',
		// options: DESC
		'orderby' => 'name',
		// options: id, count, name, slug, term_group, none
	), $atts ) );
	$args  = array(
		'orderby' => $orderby,
		'order'   => $order,
	);
	$terms = get_terms( $tax, $args );
	$count = count( $terms );
	if ( $count > 0 ) {
		$list = '<ul id="list-sermons">';
		foreach ( $terms as $term ) {
			$list .= '<li><a href="' . esc_url( get_term_link( $term, $term->taxonomy ) ) . '" title="' . $term->name . '">' . $term->name . '</a></li>';
		}
		$list .= '</ul>';

		return $list;
	}
}

// Display all series or speakers in a grid of images
function wpfc_display_images_shortcode( $atts = array() ) {
	extract( shortcode_atts( array(
		'tax'       => 'wpfc_sermon_series', // options: wpfc_sermon_series, wpfc_preacher, wpfc_sermon_topics
		'order'     => 'DESC', // options: ASC, DESC
		'orderby'   => 'name', // options: id, count, name, slug, term_group, none
		'size'      => 'sermon_medium', // options: any size registered with add_image_size
		'show_desc' => 'false'
	), $atts ) );

	$terms = apply_filters( 'sermon-images-get-terms', '', array( 'taxonomy'  => $tax,
	                                                              'term_args' => array( 'order'   => $order,
	                                                                                    'orderby' => $orderby
	                                                              )
	) );
	if ( ! empty( $terms ) ) {
		$list = '<ul id="wpfc_images_grid">';
		foreach ( (array) $terms as $term ) {
			$list .= '<li class="wpfc_grid_image">';
			$list .= '<a href="' . esc_url( get_term_link( $term, $term->taxonomy ) ) . '">' . wp_get_attachment_image( $term->image_id, $size ) . '</a>';
			$list .= '<h3 class="wpfc_grid_title"><a href="' . esc_url( get_term_link( $term, $term->taxonomy ) ) . '">' . $term->name . '</a></h3>';
			if ( $show_desc == 'true' ) {
				if ( ! empty( $term->description ) ) {
					$list .= '<div class="taxonomy-description">' . $term->description . '</div>';
				}
			}
			$list .= '</li>';
		}
		$list .= '</ul>';
	}

	return $list;
}

/**
 * Get the latest sermon ID
 */
function wpfc_get_latest_sermon( $service_type = 0 ) {
	$args = array(
		'post_type'              => 'wpfc_sermon',
		'posts_per_page'         => 1,
		'post_status'            => 'publish',
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false
	);

	if ( ! empty( $service_type ) ) {

		$tax_args = array(
			'wpfc_service_type' => $service_type,
		);
		$args     = array_merge( $args, $tax_args );
	}
	$latest_sermon = new WP_Query( $args );

	if ( $latest_sermon->have_posts() ) : while ( $latest_sermon->have_posts() ) : $latest_sermon->the_post();
		$latest_id = get_the_ID();

		return $latest_id;

	endwhile;
		wp_reset_postdata(); endif;
}

function wpfc_get_latest_series( $latest_sermon = 0, $service_type = 0 ) {
	if ( empty( $latest_sermon ) ) {
		$latest_sermon = wpfc_get_latest_sermon( $service_type );
	}
	$latest_series = wp_get_object_terms( $latest_sermon, 'wpfc_sermon_series' );
	foreach ( $latest_series as $series_object ) {
		return $series_object;
	}
}

function wpfc_get_latest_series_image_id( $latest_series = 0 ) {
	$associations = sermon_image_plugin_get_associations();
	$tt_id        = absint( $latest_series->term_taxonomy_id );

	$ID = 0;
	if ( array_key_exists( $tt_id, $associations ) ) {
		$ID = absint( $associations[ $tt_id ] );
	}

	return $ID;
}

// Display the latest sermon series image (optional - by service type)
function wpfc_get_latest_series_image( $atts ) {
	extract( shortcode_atts( array(
		'image_class'   => 'latest-series-image',
		'size'          => 'large',
		'show_title'    => true,
		'title_wrapper' => 'h3', //options p, h1, h2, h3, h4, h5, h6, div
		'title_class'   => 'latest-series-title',
		'service_type'  => '', //use the service type slug
		'show_desc'     => false,
		'wrapper_class' => 'latest-series',
	), $atts, 'latest_series' ) );

	$latest_sermon   = wpfc_get_latest_sermon( $service_type );
	$latest_series   = wpfc_get_latest_series( $latest_sermon );
	$series_link     = get_term_link( $latest_series, 'wpfc_sermon_series' );
	$series_image_id = wpfc_get_latest_series_image_id( $latest_series );

	if ( empty( $series_image_id ) ) {
		return;
	}
	$image_size      = sanitize_key( $size );
	$image_class     = sanitize_html_class( $image_class );
	$show_title      = wpfc_sanitize_bool( $show_title );
	$title_wrapper   = sanitize_text_field( $title_wrapper );
	$wrapper_options = array( 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div' );
	if ( ! in_array( $title_wrapper, $wrapper_options ) ) {
		$title_wrapper = 'h3';
	}
	$title_class = sanitize_html_class( $title_class );
	$show_desc   = wpfc_sanitize_bool( $show_desc );

	$link_open  = '<a href="' . $series_link . '" title="' . $latest_series->name . '" alt="' . $latest_series->name . '">';
	$link_close = '</a>';

	$image = wp_get_attachment_image( $series_image_id, $image_size, false, array( 'class' => $image_class ) );

	$title = $description = '';
	if ( $show_title ) {
		$title = $latest_series->name;
		$title = '<' . $title_wrapper . ' class="' . $title_class . '">' . $title . '</' . $title_wrapper . '>';
	}
	if ( $show_desc ) {
		$description = '<div class="latest-series-description">' . wpautop( $latest_series->description ) . '</div>';
	}

	$wrapper_class = sanitize_html_class( $wrapper_class );
	$before        = '<div class="' . $wrapper_class . '">';
	$after         = '</div>';

	$output = $before . $link_open . $image . $title . $link_close . $description . $after;

	return $output;

}

/**
 * Convert string to boolean
 * because (bool) "false" == true
 *
 */
function wpfc_sanitize_bool( $value ) {
	return ! empty( $value ) && 'true' == $value ? true : false;
}

// Create the shortcode
function wpfc_display_sermons_shortcode( $atts ) {

	// Pull in shortcode attributes and set defaults
	extract( shortcode_atts( array(
		'id'             => false,
		'posts_per_page' => '10',
		'order'          => 'DESC',
		'hide_nav'       => false,
		'taxonomy'       => false,
		'tax_term'       => false,
		'image_size'     => 'sermon_small',
		'tax_operator'   => 'IN'
	), $atts, 'sermons' ) );
	// pagination
	global $paged;
	if ( get_query_var( 'paged' ) ) {
		$my_page = get_query_var( 'paged' );
	} else {
		if ( get_query_var( 'page' ) ) {
			$my_page = get_query_var( 'page' );
		} else {
			$my_page = 1;
		}
		set_query_var( 'paged', $my_page );
		$paged = $my_page;
	}
	// pagination end
	$args = array(
		'post_type'      => 'wpfc_sermon',
		'posts_per_page' => $posts_per_page,
		'order'          => $order,
		'meta_key'       => 'sermon_date',
		'meta_value'     => date( "m/d/Y" ),
		'meta_compare'   => '>=',
		'orderby'        => 'meta_value',
		'paged'          => $my_page,
	);

	// If Post IDs
	if ( $id ) {
		$posts_in         = explode( ',', $id );
		$args['post__in'] = $posts_in;
	}

	// If taxonomy attributes, create a taxonomy query
	if ( ! empty( $taxonomy ) && ! empty( $tax_term ) ) {

		// Term string to array
		$tax_term = explode( ', ', $tax_term );

		// Validate operator
		if ( ! in_array( $tax_operator, array( 'IN', 'NOT IN', 'AND' ) ) ) {
			$tax_operator = 'IN';
		}

		$tax_args = array(
			'tax_query' => array(
				array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $tax_term,
					'operator' => $tax_operator
				)
			)
		);
		$args     = array_merge( $args, $tax_args );
	}

	$listing = new WP_Query( $args, $atts );
	// Now that you've run the query, finish populating the object
	ob_start(); ?>
	<div id="wpfc_sermon">
		<div id="wpfc_loading">
			<?php
			if ( ! $listing->have_posts() ) {
				return;
			}
			while ( $listing->have_posts() ): $listing->the_post();
				global $post; ?>
				<div id="wpfc_sermon_wrap">
					<h3 class="sermon-title"><a href="<?php the_permalink(); ?>"
					                            title="<?php printf( esc_attr__( 'Permalink to %s', 'sermon-manager' ), the_title_attribute( 'echo=0' ) ); ?>"
					                            rel="bookmark"><?php the_title(); ?></a></h3>
					<?php wpfc_sermon_excerpt(); ?>
				</div>
				<?php
			endwhile; //end loop ?>
			<div style="clear:both;"></div>

			<?php wp_reset_postdata(); ?>
			<?php if ( ! $hide_nav ) { ?>
				<div id="sermon-navigation">
					<?php
					$big = 999999;
					echo paginate_links( array(
						'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
						'format'  => '?paged=%#%',
						'current' => max( 1, $args['paged'] ),
						'total'   => $listing->max_num_pages
					) );
					?>
				</div>
			<?php } ?>
			<div style="clear:both;"></div>
		</div>
	</div>
	<?php
	$buffer = ob_get_clean();

	return $buffer;
}

function wpfc_sermons_sorting_shortcode( $atts ) {
	$sorting = render_wpfc_sorting();

	echo $sorting;
}
