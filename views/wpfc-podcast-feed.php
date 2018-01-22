<?php defined( 'ABSPATH' ) or exit;
/**
 * To Whom It May Concern,
 *
 * This file and the associated code is a product of unfinished feature.
 *
 * When this code is finalized, you will be able to easily modify the RSS feed content.
 * But for now, this file is largely broken.
 *
 * To use this file, please add the following code to your theme's "functions.php" or custom plugin file:
 *
 * ```if ( isset( $_GET['sm_feed'] ) ) { do_action( 'do_feed_podcast' ); }```
 *
 * When you append "?sm_feed" to any page, this file will, hopefully, be shown.
 *
 * You may edit it and play with it, and if you fix anything - no matter how small; please submit
 * a pull request on GitHub.
 *
 * Thank you,
 *
 * The developers of Sermon Manager
 *
 * @modified 2018-01-22
 */

header( "Content-Type: application/rss+xml; charset=UTF-8" );

$args                 = array(
	'post_type'      => 'wpfc_sermon',
	'posts_per_page' => - 1,
	'meta_key'       => 'sermon_date',
	'meta_value_num' => time(),
	'meta_compare'   => '<=',
	'orderby'        => 'meta_value_num',
);
$sermon_podcast_query = new WP_Query( $args );

?><?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<rss version="2.0"
	<?= 'xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"' . PHP_EOL ?>
	<?php wpfc_podcast_add_namespace(); ?>>

    <channel>
        <title><?php echo esc_html( \SermonManager::getOption( 'title' ) ) ?></title>
        <link><?php echo esc_url( \SermonManager::getOption( 'website_link' ) ) ?></link>
        <atom:link href="<?php if ( ! empty( $_SERVER['HTTPS'] ) ) {
			echo 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		} else {
			echo 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		} ?>" rel="self" type="application/rss+xml"/>
        <description><?php echo esc_html( \SermonManager::getOption( 'description' ) ) ?></description>
        <language><?php echo esc_html( \SermonManager::getOption( 'language' ) ) ?></language>
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