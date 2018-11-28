<?php // phpcs:ignore
/**
 * Template used for displaying single pages
 *
 * @package SM/Views
 */

get_header(); ?>

<?php echo wpfc_get_partial( 'content-sermon-wrapper-start' ); ?>

<?php

echo apply_filters( 'single-wpfc_sermon-before-sermons', '' );

while ( have_posts() ) :
	global $post;
	the_post();

	if ( ! post_password_required( $post ) ) {
		wpfc_sermon_single_v2(); // You can edit the content of this function in `partials/content-sermon-single.php`.
	} else {
		echo get_the_password_form( $post );
	}

	if ( comments_open() || get_comments_number() ) :
		if ( ! apply_filters( 'single-wpfc_sermon-disable-comments', false ) ) {
			comments_template();
		}
	endif;
endwhile;

echo apply_filters( 'single-wpfc_sermon-after-sermons', '' );

?>

<?php echo wpfc_get_partial( 'content-sermon-wrapper-end' ); ?>

<?php
get_footer();
