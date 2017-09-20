<?php die();
header( "Content-Type: application/rss+xml; charset=UTF-8" );

// Redirect to new feed location
if ( trim( \SermonManager::getOption( 'archive_slug' ) ) === '' ) {
	$archive_slug = 'sermons';
}
wp_redirect( home_url( $archive_slug . '/feed/' ), 301 );
exit;

$args                 = array(
	'post_type' => 'wpfc_sermon',
	'posts_per_page' => - 1,
	'meta_key' => 'sermon_date',
	'meta_value' => time(),
	'meta_compare' => '<=',
	'orderby' => 'meta_value_num',
);
$sermon_podcast_query = new WP_Query( $args );

echo '<?xml version="1.0" encoding="UTF-8"?>' ?>

<rss xmlns:atom="http://www.w3.org/2005/Atom" version="2.0" <?php wpfc_podcast_add_namespace(); ?>>
    <channel>
		<?php echo \SermonManager::getOption( 'title' ); ?>
        <title><?php echo esc_html( \SermonManager::getOption( 'title' ) ) ?></title>
        <link><?php echo esc_url( \SermonManager::getOption( 'website_link' ) ) ?></link>
        <atom:link href="<?php if ( ! empty( $_SERVER['HTTPS'] ) ) {
			echo 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		} else {
			echo 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		} ?>" rel="self" type="application/rss+xml"/>
        <language><?php echo esc_html( \SermonManager::getOption( 'language' ) ) ?></language>
        <description><?php echo esc_html( \SermonManager::getOption( 'description' ) ) ?></description>

		<?php wpfc_podcast_add_head(); ?>

		<?php if ( $sermon_podcast_query->have_posts() ) : while ( $sermon_podcast_query->have_posts() ) : $sermon_podcast_query->the_post(); ?>
			<?php global $post; ?>
			<?php if ( get_post_meta( $post->ID, 'sermon_audio', true ) !== '' ) : ?>
                <item>
                    <title><?php the_title_rss() ?></title>
                    <link><?php the_permalink_rss() ?></link>
					<?php if ( get_comments_number() || comments_open() ) : ?>
                        <comments><?php comments_link_feed(); ?></comments>
					<?php endif; ?>
                    <pubDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_post_time( 'Y-m-d H:i:s', true ), false ); ?></pubDate>
                    <dc:creator><![CDATA[<?php the_author() ?>]]></dc:creator>
					<?php the_category_rss( 'rss2' ) ?>
                    <guid isPermaLink="false"><?php the_guid(); ?></guid>

                    <description>asd<![CDATA[<?php echo get_wpfc_sermon_meta( 'sermon_description' ); ?>]]>
                    </description>
					<?php wpfc_podcast_add_item(); ?>
                </item>
			<?php endif; ?>
		<?php endwhile; endif;
		wp_reset_query(); ?>
    </channel>
</rss>
