<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

/**
 * The template for displaying Sermon Archive pages.
 */

get_header(); ?>


<div id="container">
    <div id="content" role="main">
        <h1 class="page-title"><?php echo trim( \SermonManager::getOption( 'archive_title' ) ) === '' ? 'Sermons' : \SermonManager::getOption( 'archive_title' ); ?></h1>
		<?php echo render_wpfc_sorting(); ?>
		<?php if ( $wp_query->max_num_pages > 1 ) : ?>
            <div id="nav-above" class="navigation">
                <div class="nav-previous">
					<?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older sermons', 'sermon-manager' ) ); ?>
                </div>
                <div class="nav-next">
					<?php previous_posts_link( __( 'Newer sermons <span class="meta-nav">&rarr;</span>', 'sermon-manager' ) ); ?>
                </div>
            </div>
		<?php endif; ?>

		<?php if ( ! have_posts() ) : ?>
            <div id="post-0" class="post error404 not-found">
                <h1 class="entry-title"><?php _e( 'Not Found', 'sermon-manager' ); ?></h1>
                <div class="entry-content">
                    <p><?php _e( 'Apologies, but no sermons were found.', 'sermon-manager' ); ?></p>
					<?php get_search_form(); ?>
                </div>
            </div>
		<?php endif; ?>

		<?php while ( have_posts() ) : the_post(); ?>
            <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <h2 class="entry-title">
                    <a href="<?php the_permalink(); ?>" rel="bookmark"
                       title="<?php printf( esc_attr__( 'Permalink to %s', 'sermon-manager' ), the_title_attribute( 'echo=0' ) ); ?>">
						<?php the_title(); ?>
                    </a>
                </h2>

                <div class="entry-meta">
					<span class="meta-prep meta-prep-author">
                        Preached on
                    </span>
					<?php sm_the_date( 'l, F j, Y' ); ?>
                    <span class="meta-sep"> by </span>
					<?php echo the_terms( $post->ID, 'wpfc_preacher', '', ', ', ' ' ); ?>
                </div>

                <div class="entry-content">
					<?php wpfc_sermon_excerpt(); ?>
                </div>

                <div class="entry-utility">
					<span class="comments-link">
                        <?php comments_popup_link( __( 'Leave a comment', 'sermon-manager' ), __( '1 Comment', 'sermon-manager' ), __( '% Comments', 'sermon-manager' ) ); ?>
                    </span>
					<?php edit_post_link( __( 'Edit', 'sermon-manager' ), '<span class="meta-sep">|</span> <span class="edit-link">', '</span>' ); ?>
                </div>
            </div>

		<?php endwhile; ?>

		<?php if ( $wp_query->max_num_pages > 1 ) : ?>
            <div id="nav-below" class="navigation">
                <div class="nav-previous">
					<?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older sermons', 'sermon-manager' ) ); ?>
                </div>
                <div class="nav-next">
					<?php previous_posts_link( __( 'Newer sermons <span class="meta-nav">&rarr;</span>', 'sermon-manager' ) ); ?>
                </div>
            </div>
		<?php endif; ?>

    </div>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
