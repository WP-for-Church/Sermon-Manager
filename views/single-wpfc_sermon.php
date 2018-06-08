<?php
/**
 * Template used for displaying single pages
 *
 * @package SM/Views
 */

get_header(); ?>

<?php include 'partials/wrapper-start.php'; ?>

<?php
while ( have_posts() ) :
	global $post;
	the_post();

	if ( ! post_password_required( $post ) ) {
		wpfc_sermon_single_v2(); // You can edit the content of this function in `partials/content-sermon-single.php`.
	} else {
		echo get_the_password_form( $post );
	}

	if ( comments_open() || get_comments_number() ) :
		comments_template();
	endif;
endwhile;
?>

<?php include 'partials/wrapper-end.php'; ?>

<?php
get_footer();
