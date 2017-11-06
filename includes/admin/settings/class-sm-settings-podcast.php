<?php
defined( 'ABSPATH' ) or die;

/**
 * Verse Settings page
 */
class SM_Settings_Podcast extends SM_Settings_Page {
	public function __construct() {
		$this->id    = 'podcast';
		$this->label = __( 'Podcast', 'sermon-manager-for-wordpress' );
		add_action( 'sm_settings_podcast_settings_after', array( $this, 'after' ) );

		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = apply_filters( 'sm_podcast_settings', array(
			array(
				'title' => __( 'Podcast Settings', 'sermon-manager-for-wordpress' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'podcast_settings',
			),
			array(
				'title'       => __( 'Title' ),
				'type'        => 'text',
				'id'          => 'title',
				'placeholder' => get_bloginfo( 'name' ),
			),
			array(
				'title'       => __( 'Description', 'sermon-manager-for-wordpress' ),
				'type'        => 'text',
				'id'          => 'description',
				'placeholder' => get_bloginfo( 'description' ),
			),
			array(
				'title'       => __( 'Website Link', 'sermon-manager-for-wordpress' ),
				'type'        => 'text',
				'id'          => 'website_link',
				'placeholder' => home_url(),
			),
			array(
				'title'       => __( 'Language', 'sermon-manager-for-wordpress' ),
				'type'        => 'text',
				'id'          => 'language',
				'placeholder' => get_bloginfo( 'language' ),
			),
			array(
				'title'       => __( 'Copyright', 'sermon-manager-for-wordpress' ),
				'type'        => 'text',
				'id'          => 'copyright',
				'placeholder' => wp_sprintf( __( 'Copyright &copy; %s', 'sermon-manager-for-wordpress' ), get_bloginfo( 'name' ) ),
				'desc'        => wp_sprintf( esc_html__( 'Tip: Use %s to generate a copyright symbol.', 'sermon-manager-for-wordpress' ), '<code>' . htmlspecialchars( '&copy;' ) . '</code>' ),
			),
			array(
				'title'       => __( 'Webmaster Name', 'sermon-manager-for-wordpress' ),
				'type'        => 'text',
				'id'          => 'webmaster_name',
				'placeholder' => __( 'e.g. Your Name', 'sermon-manager-for-wordpress' ),
			),
			array(
				'title'       => __( 'Webmaster Email', 'sermon-manager-for-wordpress' ),
				'type'        => 'email',
				'id'          => 'webmaster_email',
				'placeholder' => __( 'e.g. Your Email', 'sermon-manager-for-wordpress' ),
			),
			array(
				'title'       => __( 'Author', 'sermon-manager-for-wordpress' ),
				'type'        => 'text',
				'id'          => 'itunes_author',
				'placeholder' => __( 'e.g. Primary Speaker or Church Name', 'sermon-manager-for-wordpress' ),
				'desc'        => __( 'This will display at the &ldquo;Artist&rdquo; in the iTunes Store.', 'sermon-manager-for-wordpress' ),
			),
			array(
				'title'       => __( 'Subtitle', 'sermon-manager-for-wordpress' ),
				'type'        => 'text',
				'id'          => 'itunes_subtitle',
				'placeholder' => wp_sprintf( __( 'e.g. Preaching and teaching audio from %s', 'sermon-manager-for-wordpress' ), get_bloginfo( 'name' ) ),
				'desc'        => __( 'Your subtitle should briefly tell the listener what they can expect to hear.', 'sermon-manager-for-wordpress' ),
			),
			array(
				'title'       => __( 'Summary', 'sermon-manager-for-wordpress' ),
				'type'        => 'text',
				'id'          => 'itunes_summary',
				'placeholder' => __(
					wp_sprintf( 'e.g. Weekly teaching audio brought to you by %s in City, State.', 'sermon-manager-for-wordpress' ),
					get_bloginfo( 'name' ) ),
				'desc'        => __( 'Keep your Podcast Summary short, sweet and informative. Be sure to include a brief statement about your mission and in what region your audio content originates.', 'sermon-manager-for-wordpress' ),
			),
			array(
				'title'       => __( 'Owner Name', 'sermon-manager-for-wordpress' ),
				'type'        => 'text',
				'id'          => 'itunes_owner_name',
				'placeholder' => get_bloginfo( 'name' ),
				'desc'        => __( 'This should typically be the name of your Church.', 'sermon-manager-for-wordpress' ),
			),
			array(
				'title'       => __( 'Owner Email', 'sermon-manager-for-wordpress' ),
				'type'        => 'text',
				'id'          => 'itunes_owner_email',
				'placeholder' => __( 'e.g. Your Email', 'sermon-manager-for-wordpress' ),
				'desc'        => __( 'Use an email address that you don&rsquo;t mind being made public. If someone wants to contact you regarding your Podcast this is the address they will use.', 'sermon-manager-for-wordpress' ),
			),
			array(
				'title' => __( 'Cover Image', 'sermon-manager-for-wordpress' ),
				'type'  => 'image',
				'id'    => 'itunes_cover_image',
				'desc'  => __( 'This JPG will serve as the Podcast artwork in the iTunes Store. The image must be between 1,400px by 1,400px and 3,000px by 3,000px or else iTunes will not accept your feed.', 'sermon-manager-for-wordpress' ),
			),
			array(
				'title'   => __( 'Sub Category', 'sermon-manager-for-wordpress' ),
				'type'    => 'select',
				'id'      => 'itunes_sub_category',
				'options' => array(
					'0' => __( 'Sub Category', 'sermon-manager-for-wordpress' ),
					'1' => __( 'Buddhism', 'sermon-manager-for-wordpress' ),
					'2' => __( 'Christianity', 'sermon-manager-for-wordpress' ),
					'3' => __( 'Hinduism', 'sermon-manager-for-wordpress' ),
					'4' => __( 'Islam', 'sermon-manager-for-wordpress' ),
					'5' => __( 'Judaism', 'sermon-manager-for-wordpress' ),
					'6' => __( 'Other', 'sermon-manager-for-wordpress' ),
					'7' => __( 'Spirituality', 'sermon-manager-for-wordpress' ),
				),
			),
			array(
				'title'    => __( 'PodTrac Tracking', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'id'       => 'podtrac',
				'desc'     => __( 'Enables PodTrac tracking.', 'sermon-manager-for-wordpress' ),
				// translators: %s <a href="http://podtrac.com">podtrac.com</a>
				'desc_tip' => wp_sprintf( __( 'For more info on PodTrac or to sign up for an account, visit %s', 'sermon-manager-for-wordpress' ), '<a href="http://podtrac.com">podtrac.com</a>' ),
			),
			array(
				'title' => __( 'HTML in description', 'sermon-manager-for-wordpress' ),
				'type'  => 'checkbox',
				'id'    => 'enable_podcast_html_description',
				'desc'  => __( 'Enables showing of HTML in iTunes description field. Uncheck if description looks messy.', 'sermon-manager-for-wordpress' ),
			),
			array(
				'title'       => __( 'Number of podcasts to show', 'sermon-manager-for-wordpress' ),
				'type'        => 'number',
				'id'          => 'podcasts_per_page',
				'placeholder' => get_option( 'posts_per_rss' ),
			),

			array( 'type' => 'sectionend', 'id' => 'podcast_settings' ),
		) );

		return apply_filters( 'sm_get_settings_' . $this->id, $settings );
	}

	public function after() {
		?>
        <div>
            <p>
                <label for="feed_url"><?= __( 'Feed URL to Submit to iTunes', 'sermon-manager-for-wordpress' ) ?></label>
                <input type="text" disabled="disabled" value="<?= home_url( '/' ) . '?feed=rss2&post_type=wpfc_sermon' ?>" id="feed_url">
            </p>
            <p>
				<?= // translators: %s Feed Validator link, see msgid "Feed Validator"
				wp_sprintf( esc_html__( 'Use the %s to diagnose and fix any problems before submitting your Podcast to iTunes.', 'sermon-manager-for-wordpress' ), '<a href="http://www.feedvalidator.org/check.cgi?url=' . home_url( '/' ) . SermonManager::getOption( 'archive_slug', 'sermons' ) . '/feed/" target="_blank">' . esc_html__( 'Feed Validator', 'sermon-manager-for-wordpress' ) . '</a>' ) ?>
            </p>
            <p>
				<?= // translators: %s see msgid "Submit Your Podcast"
				wp_sprintf( esc_html__( 'Once your Podcast Settings are complete and your Sermons are ready, it&rsquo;s time to %s to the iTunes Store!', 'sermon-manager-for-wordpress' ), '<a href="https://www.apple.com/itunes/podcasts/specs.html#submitting" target="_blank">' . esc_html__( 'Submit Your Podcast', 'sermon-manager-for-wordpress' ) . '</a>' ) ?>
            </p>
            <p>
				<?= // translators: %s see msgid "FeedBurner"
				wp_sprintf( esc_html__( 'Alternatively, if you want to track your Podcast subscribers, simply pass the Podcast Feed URL above through %s. FeedBurner will then give you a new URL to submit to iTunes instead.', 'sermon-manager-for-wordpress' ), '<a href="http://feedburner.google.com/" target="_blank">' . esc_html__( 'FeedBurner', 'sermon-manager-for-wordpress' ) . '</a>' ) ?>
            </p>
            <p>
				<?= // translators: %s see msgid "iTunes FAQ for Podcast Makers"
				wp_sprintf( esc_html__( 'Please read the %s for more information.', 'sermon-manager-for-wordpress' ), '<a href="https://www.apple.com/itunes/podcasts/creatorfaq.html" target="_blank">' . esc_html__( 'iTunes FAQ for Podcast Makers', 'sermon-manager-for-wordpress' ) . '</a>' ) ?>
            </p>
        </div>
		<?php
	}
}

return new SM_Settings_Podcast();
