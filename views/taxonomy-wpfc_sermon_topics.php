<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

/**
 * The template for displaying Sermon Topics pages.
 *
 * @package    WordPress
 * @subpackage Twenty_Ten
 * @since      Twenty Ten 1.0
 */

get_header();

?>

<div id="container">
    <div id="content" role="main">

        <h1 class="page-title"><?php
			echo wp_sprintf( esc_html__( 'Sermons Topic: %s', 'sermon-manager-for-wordpress' ), '<span>' . esc_html( single_cat_title( '', false ) ) . '</span>' );
			?></h1>
        <div id="wpfc_sermon_tax_description">
			<?php
			/* Image */
			print apply_filters( 'sermon-images-queried-term-image', '', array(
				'attr'       => array( 'class' => 'alignleft' ),
				'after'      => '</div>',
				'before'     => '<div id="wpfc_sermon_image">',
				'image_size' => 'thumbnail',
			) );
			/* Description */
			$category_description = category_description();
			if ( ! empty( $category_description ) ) {
				echo '<div class="archive-meta">' . $category_description . '</div>';
			}
			?>
        </div>

		<?php /* Display navigation to next/previous pages when applicable */ ?>
		<?php if ( $wp_query->max_num_pages > 1 ) : ?>
            <div id="nav-above" class="navigation">
                <div
                        class="nav-previous"><?php next_posts_link( wp_sprintf( esc_html__( '%s Older posts', 'sermon-manager-for-wordpress' ), '<span class="meta-nav">' . esc_html__( '&larr;', 'sermon-manager-for-wordpress' ) . '</span>' ) ); ?></div>
                <div
                        class="nav-next"><?php previous_posts_link( wp_sprintf( esc_html__( 'Newer posts %s', 'sermon-manager-for-wordpress' ), '<span class="meta-nav">' . esc_html__( '&rarr;', 'sermon-manager-for-wordpress' ) . '</span>' ) ); ?></div>
            </div><!-- #nav-above -->
		<?php endif; ?>

		<?php /* If there are no posts to display, such as an empty archive page */ ?>
		<?php if ( ! have_posts() ) : ?>
            <div id="post-0" class="post error404 not-found">
                <h1 class="entry-title"><?php esc_html_e( 'Not Found', 'sermon-manager-for-wordpress' ); ?></h1>
                <div class="entry-content">
                    <p><?php esc_html_e( 'Apologies, but no sermons were found.', 'sermon-manager-for-wordpress' ); ?></p>
					<?php get_search_form(); ?>
                </div><!-- .entry-content -->
            </div><!-- #post-0 -->
		<?php endif; ?>

		<?php
		/* Start the Loop.
		 *
		 * In Twenty Ten we use the same loop in multiple contexts.
		 * It is broken into three main parts: when we're displaying
		 * posts that are in the gallery category, when we're displaying
		 * posts in the asides category, and finally all other posts.
		 *
		 * Additionally, we sometimes check for whether we are on an
		 * archive page, a search page, etc., allowing for small differences
		 * in the loop on each template without actually duplicating
		 * the rest of the loop that is shared.
		 *
		 * Without further ado, the loop:
		 */ ?>
		<?php while ( have_posts() ) : the_post(); ?>

            <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <h2 class="entry-title"><a href="<?php the_permalink(); ?>"
                                           title="<?php echo wp_sprintf( esc_attr__( 'Permalink to %s', 'sermon-manager-for-wordpress' ), the_title_attribute( 'echo=0' ) ); ?>"
                                           rel="bookmark"><?php the_title(); ?></a></h2>

                <div class="entry-meta">
					<span
                            class="meta-prep meta-prep-author"><?php echo wp_sprintf( esc_html__( 'Preached on %s', 'sermon-manager-for-wordpress' ), date_i18n( get_option( 'date_format' ), sm_the_date( 'U' ) ) ); ?></span>
                    <span
                            class="meta-sep"> by </span> <?php the_terms( $post->ID, 'wpfc_preacher', '', ', ', ' ' ); ?>
                </div><!-- .entry-meta -->

                <div class="entry-content">
					<?php wpfc_sermon_excerpt(); ?>
                </div><!-- .entry-content -->

                <div class="entry-utility">
	                <?php $comment_count = (object) wp_count_comments( $post->ID ); ?>
					<span
                            class="comments-link"><?php comments_popup_link( esc_html__( 'Leave a comment', 'sermon-manager-for-wordpress' ), wp_sprintf( esc_html( _n( '%s comment', '%s comments', 1, 'sermon-manager-for-wordpress' ) ), number_format_i18n( 1 ) ), wp_sprintf( esc_html( _n( '%s comment', '%s comments', $approved_comments_count = intval( $comment_count->approved ), 'sermon-manager-for-wordpress' ) ), number_format_i18n( $approved_comments_count ) ) ); ?></span>
					<?php edit_post_link( esc_html__( 'Edit', 'sermon-manager-for-wordpress' ), '<span class="meta-sep">|</span> <span class="edit-link">', '</span>' ); ?>
                </div><!-- .entry-utility -->
            </div><!-- #post-## -->

		<?php endwhile; // End the loop. Whew. ?>

		<?php /* Display navigation to next/previous pages when applicable */ ?>
		<?php if ( $wp_query->max_num_pages > 1 ) : ?>
            <div id="nav-below" class="navigation">
                <div
                        class="nav-previous"><?php next_posts_link( wp_sprintf( esc_html__( '%s Older posts', 'sermon-manager-for-wordpress' ), '<span class="meta-nav">' . esc_html__( '&larr;', 'sermon-manager-for-wordpress' ) . '</span>' ) ); ?></div>
                <div
                        class="nav-next"><?php previous_posts_link( wp_sprintf( esc_html__( 'Newer posts %s', 'sermon-manager-for-wordpress' ), '<span class="meta-nav">' . esc_html__( '&rarr;', 'sermon-manager-for-wordpress' ) . '</span>' ) ); ?></div>
            </div><!-- #nav-below -->
		<?php endif; ?>

    </div><!-- #content -->
</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
