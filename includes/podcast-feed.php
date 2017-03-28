<?php
header( "Content-Type: application/rss+xml; charset=UTF-8" );

$settings = get_option( 'wpfc_options' );
// Redirect to new feed location
$archive_slug = $settings['archive_slug'];
if ( empty( $archive_slug ) ) {
	$archive_slug = 'sermons';
}
wp_redirect( home_url( $archive_slug . '/feed/' ), 301 );
exit;

$args                 = array(
	'post_type'      => 'wpfc_sermon',
	'posts_per_page' => - 1,
	'meta_key'       => 'sermon_date',
	'meta_value'     => date( "m/d/Y" ),
	'meta_compare'   => '>=',
	'orderby'        => 'meta_value',
	'order'          => 'DESC'
);
$sermon_podcast_query = new WP_Query( $args );

echo '<?xml version="1.0" encoding="UTF-8"?>' ?>

<rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:atom="http://www.w3.org/2005/Atom" version="2.0">
	<channel>
		<?php echo $settings['title']; ?>
		<title><?php echo esc_html( $settings['title'] ) ?></title>
		<link><?php echo esc_url( $settings['website_link'] ) ?></link>
		<atom:link href="<?php if ( ! empty( $_SERVER['HTTPS'] ) ) {
			echo 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		} else {
			echo 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		} ?>" rel="self" type="application/rss+xml"/>
		<language><?php echo esc_html( $settings['language'] ) ?></language>
		<copyright><?php echo esc_html( $settings['copyright'] ) ?></copyright>
		<itunes:subtitle><?php echo esc_html( $settings['itunes_subtitle'] ) ?></itunes:subtitle>
		<itunes:author><?php echo esc_html( $settings['itunes_author'] ) ?></itunes:author>
		<itunes:summary><?php echo esc_html( $settings['itunes_summary'] ) ?></itunes:summary>
		<description><?php echo esc_html( $settings['description'] ) ?></description>
		<itunes:owner>
			<itunes:name><?php echo esc_html( $settings['itunes_owner_name'] ) ?></itunes:name>
			<itunes:email><?php echo esc_html( $settings['itunes_owner_email'] ) ?></itunes:email>
		</itunes:owner>
		<itunes:explicit>no</itunes:explicit>
		<itunes:image href="<?php echo esc_url( $settings['itunes_cover_image'] ) ?>"/>
		<itunes:category text="<?php echo esc_attr( $settings['itunes_top_category'] ) ?>">
			<itunes:category text="<?php echo esc_attr( $settings['itunes_sub_category'] ) ?>"/>
		</itunes:category>
		<?php if ( $sermon_podcast_query->have_posts() ) : while ( $sermon_podcast_query->have_posts() ) : $sermon_podcast_query->the_post(); ?>
			<?php
			global $post;

			$speaker = strip_tags( get_the_term_list( $post->ID, 'wpfc_preacher', '', ' &amp; ', '' ) );
			$series  = strip_tags( get_the_term_list( $post->ID, 'wpfc_sermon_series', '', ', ', '' ) );
			$topic   = strip_tags( get_the_term_list( $post->ID, 'wpfc_sermon_topics', '', ', ', '' ) );
			$topic   = ( $topic ) ? sprintf( '<itunes:keywords>%s</itunes:keywords>', $topic ) : null;

			$post_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );
			$post_image = ( $post_image ) ? $post_image['0'] : null;

			$audio_file      = get_post_meta( $post->ID, 'sermon_audio', 'true' );
			$audio_file_size = get_post_meta( $post->ID, '_wpfc_sermon_size', 'true' ); //now using custom field T Hyde 9 Oct 2013
			if ( $audio_file_size < 0 ) {
				$audio_file_size = 0;
			} //itunes needs this to be zero if undefined
			$audio_duration = get_post_meta( $post->ID, '_wpfc_sermon_duration', 'true' ); // now using custom field T Hyde 9 Oct 2013
			?>
			<?php if ( $audio_file && $audio_duration ) :
				$Sermon_Date = wpfc_sermon_date( 'D, d M Y H:i:s O' ); ?>
				<item>
					<title><?php the_title() ?></title>
					<link><?php the_permalink() ?></link>
					<description><?php strip_tags( wpfc_sermon_meta( 'sermon_description' ) ); ?></description>
					<itunes:author><?php echo $series ?></itunes:author>
					<itunes:subtitle><?php strip_tags( wpfc_sermon_meta( 'sermon_description' ) ); ?></itunes:subtitle>
					<itunes:summary><?php strip_tags( wpfc_sermon_meta( 'sermon_description' ) ); ?></itunes:summary>
					<?php if ( $post_image ) : ?>
						<itunes:image href="<?php echo $post_image; ?>"/>
					<?php endif; ?>
					<enclosure url="<?php echo esc_url( $audio_file ); ?>" length="<?php echo $audio_file_size; ?>"
					           type="audio/mpeg"/>
					<guid><?php echo esc_url( get_post_meta( $post->ID, 'sermon_audio', true ) ) ?></guid>
					<pubDate><?php echo $Sermon_Date ?></pubDate>
					<itunes:duration><?php echo esc_html( $audio_duration ); ?></itunes:duration>
					<?php if ( $topic ) : ?>
						<?php echo $topic . "\n" ?>
					<?php endif; ?>
				</item>
			<?php endif; ?>
		<?php endwhile; endif;
		wp_reset_query(); ?>
	</channel>
</rss>
