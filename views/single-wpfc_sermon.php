<?php
get_header(); ?>

<?php include 'partials/wrapper-start.php'; ?>

<?php
	while ( have_posts() ) : the_post();
		wpfc_sermon_single_v2();
		if ( comments_open() || get_comments_number() ) :
			comments_template();
		endif;
	endwhile;
?>

<?php include 'partials/wrapper-end.php'; ?>

<?php get_footer();