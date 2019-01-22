<?php // phpcs:ignore
/**
 * Template used for displaying archive pages
 *
 * @package SM/Views
 */

get_header(); ?>

<?php echo wpfc_get_partial( 'content-sermon-wrapper-start' ); ?>

<?php
echo render_wpfc_sorting();

if ( have_posts() ) :

	echo apply_filters( 'archive-wpfc_sermon-before-sermons', '' );

	while ( have_posts() ) :
		the_post();
		wpfc_sermon_excerpt_v2(); // You can edit the content of this function in `partials/content-sermon-archive.php`.
	endwhile;

	echo apply_filters( 'archive-wpfc_sermon-after-sermons', '' );

	echo '<div class="sm-pagination ast-pagination">';
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
	echo '</div>';
else :
	echo __( 'Sorry, but there aren\'t any posts matching your query.' );
endif;
?>

<?php echo wpfc_get_partial( 'content-sermon-wrapper-end' ); ?>

<?php
get_footer();
