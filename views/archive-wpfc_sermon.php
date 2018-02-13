<?php get_header(); ?>

<?php include 'partials/wrapper-start.php'; ?>

<?php echo render_wpfc_sorting(); ?>
<?php
if ( have_posts() ) :
	while ( have_posts() ) : the_post();
		wpfc_sermon_excerpt_v2();
	endwhile;
	the_posts_pagination();
else :
	__( 'Sorry, but there aren\'t any posts matching your query.', 'placeholder' );
endif;
?>

<?php include 'partials/wrapper-end.php'; ?>
