<?php
/**
 * Template used for displaying archive pages
 *
 * @package SM/Views
 */

get_header(); ?>

<?php echo wpfc_get_partial('content-sermon-wrapper-start'); ?>

<?php echo render_wpfc_sorting(); ?>
<?php
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		wpfc_sermon_excerpt_v2(); // You can edit the content of this function in `partials/content-sermon-archive.php`.
	endwhile;
	if ( function_exists( 'wp_pagenavi' ) ) :
		wp_pagenavi();
	else :
		the_posts_pagination();
	endif;
else :
	__( 'Sorry, but there aren\'t any posts matching your query.' );
endif;
?>

<?php echo wpfc_get_partial('content-sermon-wrapper-end'); ?>

<?php
get_footer();
