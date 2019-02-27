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

	echo apply_filters( 'taxonomy-wpfc_sermon_topics-before-sermons', '' );

	while ( have_posts() ) :
		the_post();
		wpfc_sermon_excerpt_v2();
	endwhile;

	echo apply_filters( 'taxonomy-wpfc_sermon_topics-after-sermons', '' );

	echo '<div class="sm-pagination ast-pagination">';
	sm_pagination();
	echo '</div>';
else :
	echo __( 'Sorry, but there are no posts matching your query.' );
endif;
?>

<?php echo wpfc_get_partial( 'content-sermon-wrapper-end' ); ?>

<?php
get_footer();
