<?php
/**
 * Used to display the RSS feed.
 *
 * @package  SM/Views/Podcasting
 */

defined( 'ABSPATH' ) or exit;

/**
 * Create the query for sermons.
 */
$args = array(
	'post_type'      => 'wpfc_sermon',
	'posts_per_page' => intval( \SermonManager::getOption( 'podcasts_per_page' ) ) ?: 10,
	'meta_key'       => 'sermon_date',
	'meta_value_num' => time(),
	'meta_compare'   => '<=',
	'orderby'        => 'meta_value_num',
	'paged'          => isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1,
	'meta_query'     => array(
		'relation' => 'AND',
		array(
			'key'     => 'sermon_audio',
			'compare' => 'EXISTS',
		),
		array(
			'key'     => 'sermon_audio',
			'value'   => '',
			'compare' => '!=',
		),
	),
);

/**
 * Allow filtering by taxonomies.
 *
 * Example: To filter sermons preached by John Doe, and are in series named Jesus, just execute the following query:
 * "https://www.example.com/?post_type=wpfc_sermon&wpfc_preacher=john-doe&wpfc_sermon_series=jesus"
 */
foreach (
	array(
		'wpfc_preacher',
		'wpfc_sermon_series',
		'wpfc_sermon_topics',
		'wpfc_bible_book',
		'wpfc_service_type',
	) as $taxonomy
) {
	if ( isset( $_GET[ $taxonomy ] ) ) {
		$terms               = $_GET[ $taxonomy ];
		$args['tax_query']   = ! empty( $args['tax_query'] ) ? $args['tax_query'] : array();
		$args['tax_query'][] = array(
			'taxonomy' => $taxonomy,
			'field'    => is_numeric( $terms ) ? 'term_id' : 'slug',
			'terms'    => is_numeric( $terms ) ? intval( $terms ) : false !== strpos( $terms, ',' ) ? array_walk( explode( ',', $terms ), 'sanitize_title' ) : sanitize_title( $terms ),
		);

		if ( count( $args['tax_query'] ) > 1 ) {
			$args['tax_query']['relation'] = 'AND';
		}
	}
}

/**
 * Allows to filter the sermon feed query arguments.
 *
 * @param array $args WP_Query arguments.
 *
 * @since 2.13.0
 */
$args = apply_filters( 'sermon_feed_query_args', $args );

$sermon_podcast_query = new WP_Query( $args );

$categories = array(
	'0' => '',
	'1' => 'Buddhism',
	'2' => 'Christianity',
	'3' => 'Hinduism',
	'4' => 'Islam',
	'5' => 'Judaism',
	'6' => 'Other',
	'7' => 'Spirituality',
);

$title            = esc_html( \SermonManager::getOption( 'title' ) ) ?: get_wp_title_rss();
$link             = esc_url( \SermonManager::getOption( 'website_link' ) ) ?: get_bloginfo_rss( 'url' );
$atom_link        = ( ! empty( $_SERVER['HTTPS'] ) ? 'https://' : 'http://' ) . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
$description      = esc_html( \SermonManager::getOption( 'description' ) ) ?: get_bloginfo_rss( 'description' );
$language         = esc_html( \SermonManager::getOption( 'language' ) ) ?: get_bloginfo_rss( 'language' );
$last_sermon_date = get_post_meta( $sermon_podcast_query->posts[0]->ID, 'sermon_date', true ) ?: null;
$copyright        = html_entity_decode( esc_html( \SermonManager::getOption( 'copyright' ) ), ENT_COMPAT, 'UTF-8' );
$subtitle         = esc_html( \SermonManager::getOption( 'itunes_subtitle' ) );
$author           = esc_html( \SermonManager::getOption( 'itunes_author' ) );
$summary          = str_replace( '&nbsp;', '', \SermonManager::getOption( 'enable_podcast_html_description' ) ? stripslashes( wpautop( wp_filter_kses( \SermonManager::getOption( 'itunes_summary' ) ) ) ) : stripslashes( wp_filter_nohtml_kses( \SermonManager::getOption( 'itunes_summary' ) ) ) );
$owner_name       = esc_html( \SermonManager::getOption( 'itunes_owner_name' ) );
$owner_email      = esc_html( \SermonManager::getOption( 'itunes_owner_email' ) );
$cover_image_url  = esc_url( \SermonManager::getOption( 'itunes_cover_image' ) );
$subcategory      = esc_attr( ! empty( $categories[ \SermonManager::getOption( 'itunes_sub_category' ) ] ) ? $categories[ \SermonManager::getOption( 'itunes_sub_category' ) ] : 'Christianity' );

?>
<rss version="2.0"
		xmlns:dc="http://purl.org/dc/elements/1.1/"
		xmlns:atom="http://www.w3.org/2005/Atom"
		xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
		xmlns:content="http://purl.org/rss/1.0/modules/content/"
		xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
>

	<channel>
		<title><?php echo $title; ?></title>
		<link><?php echo $link; ?></link>
		<atom:link href="<?php echo $atom_link; ?>" rel="self" type="application/rss+xml"/>
		<description><?php echo $description; ?></description>
		<language><?php echo $language; ?></language>
		<lastBuildDate><?php echo $last_sermon_date ? date( 'r', intval( $last_sermon_date ) ) : date( 'r' ); ?></lastBuildDate>
		<sy:updatePeriod>hourly</sy:updatePeriod>
		<sy:updateFrequency>1</sy:updateFrequency>
		<copyright><?php echo $copyright; ?></copyright>
		<itunes:subtitle><?php echo $subtitle; ?></itunes:subtitle>
		<itunes:author><?php echo $author; ?></itunes:author>
		<itunes:summary><?php echo $summary; ?></itunes:summary>
		<itunes:owner>
			<itunes:name><?php echo $owner_name; ?></itunes:name>
			<itunes:email><?php echo $owner_email; ?></itunes:email>
		</itunes:owner>
		<itunes:explicit>no</itunes:explicit>
		<?php if ( \SermonManager::getOption( 'itunes_cover_image' ) ) : ?>
			<itunes:image href="<?php echo $cover_image_url; ?>"/>
		<?php endif; ?>

		<itunes:category text="Religion &amp; Spirituality">
			<itunes:category text="<?php echo $subcategory; ?>"/>
		</itunes:category>
		<?php
		if ( $sermon_podcast_query->have_posts() ) :
			while ( $sermon_podcast_query->have_posts() ) :
				$sermon_podcast_query->the_post();
				global $post;

				$audio_id        = get_post_meta( $post->ID, 'sermon_audio_id', true );
				$audio_url       = $audio_id ? wp_get_attachment_url( intval( $audio_id ) ) : get_post_meta( $post->ID, 'sermon_audio', true );
				$audio_raw       = str_ireplace( 'https://', 'http://', $audio_url );
				$audio_p         = strrpos( $audio_raw, '/' ) + 1;
				$audio_raw       = urldecode( $audio_raw );
				$audio           = substr( $audio_raw, 0, $audio_p ) . rawurlencode( substr( $audio_raw, $audio_p ) );
				$speakers        = strip_tags( get_the_term_list( $post->ID, 'wpfc_preacher', '', ' &amp; ', '' ) );
				$speakers_terms  = get_the_terms( $post->ID, 'wpfc_preacher' );
				$speaker         = $speakers_terms ? $speakers_terms[0]->name : '';
				$series          = strip_tags( get_the_term_list( $post->ID, 'wpfc_sermon_series', '', ', ', '' ) );
				$topics          = strip_tags( get_the_term_list( $post->ID, 'wpfc_sermon_topics', '', ', ', '' ) );
				$post_image      = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );
				$post_image      = str_ireplace( 'https://', 'http://', ! empty( $post_image['0'] ) ? $post_image['0'] : '' );
				$audio_duration  = get_post_meta( $post->ID, '_wpfc_sermon_duration', true ) ?: '0:00';
				$audio_file_size = get_post_meta( $post->ID, '_wpfc_sermon_size', 'true' ) ?: 0;
				$description     = strip_shortcodes( get_post_meta( 'sermon_description' ) );
				$description     = str_replace( '&nbsp;', '', \SermonManager::getOption( 'enable_podcast_html_description' ) ? stripslashes( wpautop( wp_filter_kses( $description ) ) ) : stripslashes( wp_filter_nohtml_kses( $description ) ) );

				// Fix for relative audio file URLs.
				if ( substr( $audio, 0, 1 ) === '/' ) {
					$audio = site_url( $audio );
				}

				if ( \SermonManager::getOption( 'podtrac' ) ) {
					$audio = 'http://dts.podtrac.com/redirect.mp3/' . esc_url( preg_replace( '#^https?://#', '', $audio ) );
				} else {
					// As per RSS 2.0 spec, the enclosure URL must be HTTP only:
					// http://www.rssboard.org/rss-specification#ltenclosuregtSubelementOfLtitemgt .
					$audio = preg_replace( '/^https:/i', 'http:', $audio );
				}
				?>

				<item>
					<title><?php the_title_rss(); ?></title>
					<link><?php the_permalink_rss(); ?></link>
					<?php if ( get_comments_number() || comments_open() ) : ?>
						<comments><?php comments_link_feed(); ?></comments>
					<?php endif; ?>

					<pubDate><?php echo SM_Dates::get( 'D, d M Y H:i:s +0000' ); ?></pubDate>
					<dc:creator><![CDATA[<?php echo esc_html( $speaker ); ?>]]></dc:creator>
					<?php the_category_rss( 'rss2' ); ?>

					<guid isPermaLink="false"><?php the_guid(); ?></guid>
					<description><![CDATA[<?php echo $description; ?>]]></description>
					<content:encoded><![CDATA[<?php echo $description; ?>]]></content:encoded>

					<itunes:author><?php echo esc_html( $speakers ); ?></itunes:author>
					<itunes:subtitle><?php echo esc_html( $series ); ?></itunes:subtitle>
					<?php if ( $post_image ) : ?>
						<itunes:image href="<?php echo esc_url( $post_image ); ?>"/>
					<?php endif; ?>

					<enclosure url="<?php echo esc_url( $audio ); ?>"
							length="<?php echo esc_attr( $audio_file_size ); ?>"
							type="audio/mpeg"/>
					<itunes:duration><?php echo esc_html( $audio_duration ); ?></itunes:duration>
					<?php if ( $topics ) : ?>
						<itunes:keywords><?php echo esc_html( $topics ); ?></itunes:keywords>
					<?php endif; ?>

				</item>
			<?php
			endwhile;
		endif;
		wp_reset_query();
		?>

	</channel>
</rss>
