<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

/**
 * The template for displaying Sermon Archive pages.
 */

get_header(); ?>


<div id="container">
    <div id="content" role="main">
        <h1 class="page-title"><?php echo trim( \SermonManager::getOption( 'archive_title' ) ) === '' ? esc_html__( 'Sermons', 'sermon-manager-for-wordpress' ) : \SermonManager::getOption( 'archive_title' ); ?></h1>
		<?php echo render_wpfc_sorting(); ?>
		<?php if ( $wp_query->max_num_pages > 1 ) : ?>
            <div id="nav-above" class="navigation">
                <div class="nav-previous">
					<?php next_posts_link( wp_sprintf( esc_html__( '%s Older sermons', 'sermon-manager-for-wordpress' ), '<span class="meta-nav">' . esc_html__( '&larr;' ) . '</span>' ) ); ?>
                </div>
                <div class="nav-next">
					<?php previous_posts_link( wp_sprintf( esc_html__( 'Newer sermons %s', 'sermon-manager-for-wordpress' ), '<span class="meta-nav">' . esc_html__( '&rarr;' ) . '</span>' ) ); ?>
                </div>
            </div>
		<?php endif; ?>

		<?php if ( ! have_posts() ) : ?>
            <div id="post-0" class="post error404 not-found">
                <h1 class="entry-title"><?php esc_html_e( 'Not Found', 'sermon-manager-for-wordpress' ); ?></h1>
                <div class="entry-content">
                    <p><?php esc_html_e( 'Apologies, but no sermons were found.', 'sermon-manager-for-wordpress' ); ?></p>
					<?php get_search_form(); ?>
                </div>
            </div>
		<?php endif; ?>

		<?php while ( have_posts() ) : the_post(); ?>
            <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <h2 class="entry-title">
                    <a href="<?php the_permalink(); ?>" rel="bookmark"
                       title="<?php echo wp_sprintf( esc_attr__( 'Permalink to %s', 'sermon-manager-for-wordpress' ), the_title_attribute( 'echo=0' ) ); ?>">
						<?php the_title(); ?>
                    </a>
                </h2>

                <div class="entry-meta">
					<span class="meta-prep meta-prep-author">
                        <?php echo wp_sprintf( esc_html__( 'Preached on %s', 'sermon-manager-for-wordpress' ), date_i18n( get_option( 'date_format' ), sm_the_date( 'U' ) ) ); ?>
                    </span>
                    <span class="meta-sep"> by </span>
					<?php echo the_terms( $post->ID, 'wpfc_preacher', '', ', ', ' ' ); ?>
                </div>

                <div class="entry-content">
					<?php wpfc_sermon_excerpt(); ?>
                </div>

                <div class="entry-utility">
					<span class="comments-link">
                        <?php $comment_count = (object) wp_count_comments( $post->ID ); ?>
                        <?php comments_popup_link( esc_html__( 'Leave a comment', 'sermon-manager-for-wordpress' ), wp_sprintf( esc_html( _n( '%s comment', '%s comments', 1, 'sermon-manager-for-wordpress' ) ), number_format_i18n( 1 ) ), wp_sprintf( esc_html( _n( '%s comment', '%s comments', $approved_comments_count = intval( $comment_count->approved ), 'sermon-manager-for-wordpress' ) ), number_format_i18n( $approved_comments_count ) ) ); ?>
                    </span>
					<?php edit_post_link( esc_html__( 'Edit', 'sermon-manager-for-wordpress' ), '<span class="meta-sep">|</span> <span class="edit-link">', '</span>' ); ?>
                </div>
            </div>

		<?php endwhile; ?>

		<?php if ( $wp_query->max_num_pages > 1 ) : ?>
            <div id="nav-below" class="navigation">
                <div class="nav-previous">
					<?php next_posts_link( wp_sprintf( esc_html__( '%s Older sermons', 'sermon-manager-for-wordpress' ), '<span class="meta-nav">' . esc_html__( '&larr;' ) . '</span>' ) ); ?>
                </div>
                <div class="nav-next">
					<?php previous_posts_link( wp_sprintf( esc_html__( 'Newer sermons %s', 'sermon-manager-for-wordpress' ), '<span class="meta-nav">' . esc_html__( '&rarr;' ) . '</span>' ) ); ?>
                </div>
            </div>
		<?php endif; ?>

    </div>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
