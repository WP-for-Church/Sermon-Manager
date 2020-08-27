<?php
/**
 * Used to display the RSS feed.
 *
 * @package  SM/Views/Podcasting
 */

defined( 'ABSPATH' ) or exit;

global $taxonomy, $term;

if ( isset( $GLOBALS['sm_podcast_data'] ) && is_array( $GLOBALS['sm_podcast_data'] ) ) {
	$settings = $GLOBALS['sm_podcast_data'];
	$is_pro   = true;
} else {
	$settings = array();
	$is_pro   = false;
}

// Option ID => escape function.
$default_settings = array(
	'podcasts_per_page'               => 'intval',
	'title'                           => 'esc_html',
	'website_link'                    => 'esc_url',
	'description'                     => 'esc_html',
	'language'                        => 'esc_html',
	'copyright'                       => 'esc_html',
	'itunes_subtitle'                 => 'esc_html',
	'itunes_author'                   => 'esc_html',
	'enable_podcast_html_description' => '',
	'itunes_summary'                  => '',
	'itunes_owner_name'               => 'esc_html',
	'itunes_owner_email'              => 'esc_html',
	'itunes_cover_image'              => 'esc_url',
	'itunes_sub_category'             => '',
	'podcast_sermon_image_series'     => '',
	'podtrac'                         => '',
);

// If there is no default.
$wordpress_settings = array(
	'podcasts_per_page' => 10,
	'title'             => get_wp_title_rss(),
	'website_link'      => get_bloginfo_rss( 'url' ),
	'description'       => get_bloginfo_rss( 'description' ),
	'language'          => get_bloginfo_rss( 'language' ),
);

foreach ( $default_settings as $id => $escape_function ) {
	// Get SM podcast setting if there is no custom.
	if ( ! isset( $settings[ $id ] ) ) {
		$settings[ $id ] = SermonManager::getOption( $id );
	}

	// Escape the data.
	if ( $escape_function ) {
		$settings[ $id ] = call_user_func( $escape_function, $settings[ $id ] );
	}

	// Get the WordPress or custom default if there is no custom setting or SM setting.
	if ( ! $settings[ $id ] ) {
		$settings[ $id ] = '';

		if ( isset( $wordpress_settings[ $id ] ) ) {
			$settings[ $id ] = $wordpress_settings[ $id ];
		}
	}

	// No need to escape again here, since the data will either come from WordPress podcast functions or be pre-escaped
	// in this script (or be blank).
}

// Show published date if we are ordering by published.
$settings['use_published_date'] = 'date' === SermonManager::getOption( 'archive_orderby' );

/**
 * Create the query for sermons.
 */
$args = array(
	'post_type'      => 'wpfc_sermon',
	'posts_per_page' => $settings['podcasts_per_page'],
	'order'          => strtoupper( SermonManager::getOption( 'archive_order' ) ),
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

switch ( SermonManager::getOption( 'archive_orderby' ) ) {
	case 'date_preached':
		$args += array(
			'meta_key'       => 'sermon_date',
			'meta_value_num' => time(),
			'meta_compare'   => '<=',
			'orderby'        => 'meta_value_num',
		);
		break;
	default:
		$args += array(
			'orderby' => SermonManager::getOption( 'archive_orderby' ),
		);
		break;
}


/**
 * If feed is being loaded via taxonomy feed URL, such as "https://www.example.com/service-type/service-type-slug"
 */
if ( $taxonomy && $term ) {
	$args['tax_query'] = array(
		array(
			'taxonomy' => $taxonomy,
			'field'    => 'slug',
			'terms'    => $term,
		),
	);

	// Append term name to the feed title, so it looks like "Feed Name - Term Name".
	$settings['title'] = single_term_title( $settings['title'] . ' - ', false );
}

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
		$terms = $_GET[ $taxonomy ];

		// Override the default tax_query for that taxonomy.
		if ( ! empty( $args['tax_query'] ) ) {
			foreach ( $args['tax_query'] as $id => $arg ) {
				if ( ! is_array( $arg ) ) {
					continue;
				}

				if ( $arg['taxonomy'] === $taxonomy ) {
					unset( $args['tax_query'][ $id ] );
				}
			}
		}

		$args['tax_query'][] = array(
			'taxonomy' => $taxonomy,
			'field'    => is_numeric( $terms ) ? 'term_id' : 'slug',
			'terms'    => is_numeric( $terms ) ? intval( $terms ) : false !== strpos( $terms, ',' ) ? array_map( 'sanitize_title', explode( ',', $terms ) ) : sanitize_title( $terms ),
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

if ( ! $is_pro ) {
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

	$category          = 'Religion &amp; Spirituality';
	$subcategory       = esc_attr( ! empty( $categories[ $settings['itunes_sub_category'] ] ) ? $categories[ $settings['itunes_sub_category'] ] : 'Christianity' );
	$category_override = false;
} else {
	if ( function_exists( 'smp_get_itunes_categories' ) && function_exists( 'smp_get_itunes_subcategories' ) ) {
		$category_override = PHP_EOL;
		$all_categories    = smp_get_itunes_categories();
		$all_subcategories = smp_get_itunes_subcategories();

		for ( $i = 1; $i <= 3; $i ++ ) {
			$category    = isset( $settings[ 'itunes_category_' . $i ] ) ? $settings[ 'itunes_category_' . $i ] : '';
			$category    = $category ? ( isset( $all_categories[ $category ] ) ? $all_categories[ $category ] : '' ) : '';
			$category    = str_replace( '&', '&amp;', $category );
			$subcategory = isset( $settings[ 'itunes_category_' . $i . '_subcategory' ] ) ? $settings[ 'itunes_category_' . $i . '_subcategory' ] : '';
			$subcategory = str_replace( '&', '&amp;', $subcategory );

			if ( $subcategory ) {
				foreach ( $all_subcategories as $cat_id => $cat_subs ) {
					foreach ( $cat_subs as $cat_sub_id => $cat_sub_name ) {
						if ( $cat_sub_id === $subcategory ) {
							$subcategory = $cat_sub_name;
							break 2;
						}
					}
				}
			}

			if ( ! $category ) {
				continue;
			}

			$category_override .= '<itunes:category text="' . $category . '">' . PHP_EOL;

			if ( $subcategory ) {
				$category_override .= '	<itunes:category text="' . $subcategory . '"/>' . PHP_EOL;
			}

			$category_override .= '</itunes:category>' . PHP_EOL;
		}

		unset( $category );
		unset( $subcategory );

		$category_override .= PHP_EOL;
	}
}

$title            = $settings['title'];
$link             = $settings['website_link'];
$description      = $settings['description'];
$language         = $settings['language'];
$last_sermon_date = ! empty( $sermon_podcast_query->posts ) ? get_post_meta( $sermon_podcast_query->posts[0]->ID, 'sermon_date', true ) ?: null : null;
$copyright        = html_entity_decode( $settings['copyright'], ENT_COMPAT, 'UTF-8' );
$subtitle         = $settings['itunes_subtitle'];
$author           = $settings['itunes_author'];
$summary          = str_replace( '&nbsp;', '', $settings['enable_podcast_html_description'] ? stripslashes( wpautop( wp_filter_kses( $settings['itunes_summary'] ) ) ) : stripslashes( wp_filter_nohtml_kses( $settings['itunes_summary'] ) ) );
$owner_name       = $settings['itunes_owner_name'];
$owner_email      = $settings['itunes_owner_email'];
$cover_image_url  = $settings['itunes_cover_image'];

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
		<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml"/>
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
		<?php if ( $cover_image_url ) : ?>
			<itunes:image href="<?php echo $cover_image_url; ?>"/>
		<?php endif; ?>

		<?php if ( ! $category_override ) : ?>
			<itunes:category text="<?php echo $category; ?>">
				<itunes:category text="<?php echo $subcategory; ?>"/>
			</itunes:category>
		<?php else : ?>
			<?php echo $category_override; ?>
		<?php endif; ?>
		<?php
		if ( $sermon_podcast_query->have_posts() ) :
			while ( $sermon_podcast_query->have_posts() ) :
				$sermon_podcast_query->the_post();
				global $post;

				$audio_id          = get_post_meta( $post->ID, 'sermon_audio_id', true );
				$audio_url_wp      = $audio_id ? wp_get_attachment_url( intval( $audio_id ) ) : false;
				$audio_url         = $audio_id && $audio_url_wp ? $audio_url_wp : get_post_meta( $post->ID, 'sermon_audio', true );
				$audio_raw         = str_ireplace( 'https://', 'http://', $audio_url );
				$audio_p           = strrpos( $audio_raw, '/' ) + 1;
				$audio_raw         = urldecode( $audio_raw );
				$audio             = substr( $audio_raw, 0, $audio_p ) . rawurlencode( substr( $audio_raw, $audio_p ) );
				$speakers          = strip_tags( get_the_term_list( $post->ID, 'wpfc_preacher', '', ' &amp; ', '' ) );
				$speakers_terms    = get_the_terms( $post->ID, 'wpfc_preacher' );
				$speaker           = $speakers_terms ? $speakers_terms[0]->name : '';
				$series            = strip_tags( get_the_term_list( $post->ID, 'wpfc_sermon_series', '', ', ', '' ) );
				$topics            = strip_tags( get_the_term_list( $post->ID, 'wpfc_sermon_topics', '', ', ', '' ) );
				$post_image        = get_sermon_image_url( $settings['podcast_sermon_image_series'], 'full' );
				$post_image        = str_ireplace( 'https://', 'http://', ! empty( $post_image ) ? $post_image : '' );
				$audio_duration    = get_post_meta( $post->ID, '_wpfc_sermon_duration', true ) ?: '0:00';
				$audio_file_size   = get_post_meta( $post->ID, '_wpfc_sermon_size', 'true' ) ?: 0;
				$description       = strip_shortcodes( get_post_meta( $post->ID, 'sermon_description', true ) );
				$description       = str_replace( '&nbsp;', '', $settings['enable_podcast_html_description'] ? stripslashes( wpautop( wp_filter_kses( $description ) ) ) : stripslashes( wp_filter_nohtml_kses( $description ) ) );
				$description_short = substr( wp_strip_all_tags( $description, true ), 0, 255 );
				$description_short = strlen( $description_short ) === 255 ? $description_short . '...' : $description_short;
				$date_preached     = SM_Dates::get( 'D, d M Y H:i:s +0000', null, false, false );
				$date_published    = get_the_date( 'D, d M Y H:i:s +0000', $post->ID );
				$custom_enclosure  = apply_filters( 'wpfc-podcast-feed-custom-enclosure', '', $post->ID, $settings );

				// Fix for relative audio file URLs.
				if ( substr( $audio, 0, 1 ) === '/' ) {
					$audio = site_url( $audio );
				}

				if ( $settings['podtrac'] ) {
					$audio = 'http://dts.podtrac.com/redirect.mp3/' . esc_url( preg_replace( '#^https?://#', '', $audio ) );
				} else {
					// As per RSS 2.0 spec, the enclosure URL must be HTTP only:
					// http://www.rssboard.org/rss-specification#ltenclosuregtSubelementOfLtitemgt .
					$audio = preg_replace( '/^https:/i', 'http:', $audio );
				}
				?>

				<item>
					<?php do_action( 'wpfc-podcast/feed-item-start' ); ?>

					<title><?php the_title_rss(); ?></title>
					<link><?php the_permalink_rss(); ?></link>
					<?php if ( get_comments_number() || comments_open() ) : ?>
						<comments><?php comments_link_feed(); ?></comments>
					<?php endif; ?>

					<pubDate><?php echo $settings['use_published_date'] ? $date_published : $date_preached; ?></pubDate>
					<dc:creator><![CDATA[<?php echo esc_html( $speaker ); ?>]]></dc:creator>
					<?php the_category_rss( 'rss2' ); ?>

					<guid isPermaLink="false"><?php the_guid(); ?></guid>
					<description><![CDATA[<?php echo $description; ?>]]></description>
					<content:encoded><![CDATA[<?php echo $description; ?>]]></content:encoded>
					<itunes:summary><![CDATA[<?php echo $description; ?>]]></itunes:summary>

					<itunes:author><?php echo esc_html( $speakers ); ?></itunes:author>
					<itunes:subtitle><?php echo $description_short; ?></itunes:subtitle>
					<?php if ( $post_image ) : ?>
						<itunes:image href="<?php echo esc_url( $post_image ); ?>"/>
					<?php endif; ?>

					<?php if ( $custom_enclosure ) : ?>
						<?php echo $custom_enclosure; ?>
					<?php else : ?>
						<!--suppress CheckEmptyScriptTag -->
						<enclosure url="<?php echo esc_url( $audio ); ?>"
								length="<?php echo esc_attr( $audio_file_size ); ?>"
								type="audio/mpeg"/>
					<?php endif; ?>

					<itunes:duration><?php echo esc_html( $audio_duration ); ?></itunes:duration>
					<?php if ( $topics ) : ?>
						<itunes:keywords><?php echo esc_html( $topics ); ?></itunes:keywords>
					<?php endif; ?>

					<?php do_action( 'wpfc-podcast/feed-item-end' ); ?>
				</item>
			<?php
			endwhile;
		endif;
		wp_reset_query();
		?>

	</channel>
</rss>
