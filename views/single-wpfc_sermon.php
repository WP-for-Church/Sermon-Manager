<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

/**
 * The Template for displaying all single posts.
 *
 * @package    WordPress
 * @subpackage Twenty_Ten
 * @since      Twenty Ten 1.0
 */

get_header(); ?>

<?php

$template_layout = '';

if ( class_exists( 'SM_Template_Builder' ) ) {
	$template_layout = $SM_Template_Builder::wpfc_render_template_builder( 'single' );
}

if ( empty( $template_layout ) ) {

	?>

    <div id="container">
        <div id="content" role="main">
            <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                <h1 class="entry-title"><?php the_title(); ?></h1>

				<?php wpfc_sermon_single(); ?>

                <div class="entry-utility">
					<?php edit_post_link( __( 'Edit', 'sermon-manager-for-wordpress' ), '<span class="edit-link">', '</span>' ); ?>
                </div><!-- .entry-utility -->

            </div><!-- #post-## -->

            <div id="nav-below" class="navigation">
                <div
                        class="nav-previous"><?php previous_post_link( '%link', '<span class="meta-nav">' . esc_html_x( '&larr;', 'Previous post link', 'sermon-manager-for-wordpress' ) . '</span> %title' ); ?></div>
                <div
                        class="nav-next"><?php next_post_link( '%link', '%title <span class="meta-nav">' . esc_html_x( '&rarr;', 'Next post link', 'sermon-manager-for-wordpress' ) . '</span>' ); ?></div>
            </div><!-- #nav-below -->

			<?php comments_template( '', true ); ?>
        </div><!-- #content -->
    </div><!-- #container -->

	<?php get_sidebar(); ?>
<?php } else {
	echo $template_layout;
} ?>
<?php get_footer(); ?>
