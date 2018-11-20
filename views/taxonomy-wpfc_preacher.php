<?php // phpcs:ignore
/**
 * Template used for displaying taxonomy archive pages
 *
 * @package SM/Views
 */

get_header();
?>

<?php echo wpfc_get_partial( 'content-sermon-wrapper-start' ); ?>

<?php
echo render_wpfc_sorting();

if ( have_posts() ) :

	echo apply_filters( 'taxonomy-wpfc_preacher-before-sermons', '' );

	while ( have_posts() ) :
		the_post();
		wpfc_sermon_excerpt_v2();
	endwhile;

	echo apply_filters( 'taxonomy-wpfc_preacher-after-sermons', '' );

	echo '<div class="sm-pagination ast-pagination">';
	if ( SermonManager::getOption( 'use_prev_next_pagination' ) ) {
		posts_nav_link();
	} else {
		if ( function_exists( 'wp_pagenavi' ) ) :
			wp_pagenavi();
		elseif ( function_exists( 'oceanwp_pagination' ) ) :
			oceanwp_pagination();
		else :
			the_posts_pagination();
		endif;
	}
	echo '</div>';
else :
	echo __( 'Sorry, but there are no posts matching your query.' );
endif;
?>

<?php echo wpfc_get_partial( 'content-sermon-wrapper-end' ); ?>

<?php
get_footer();
