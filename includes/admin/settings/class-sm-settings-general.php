<?php
/**
 * General settings page.
 *
 * @package SM/Core/Admin/Settings
 */

defined( 'ABSPATH' ) or die;

/**
 * Initialize settings
 */
class SM_Settings_General extends SM_Settings_Page {
	/**
	 * SM_Settings_General constructor.
	 */
	public function __construct() {
		$this->id    = 'general';
		$this->label = __( 'General', 'sermon-manager-for-wordpress' );

		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = apply_filters( 'sm_general_settings', array(

			array(
				'title' => __( 'General Settings', 'sermon-manager-for-wordpress' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'general_settings',
			),
			array(
				'title'       => __( 'Archive Page Slug', 'sermon-manager-for-wordpress' ),
				'type'        => 'text',
				'id'          => 'archive_slug',
				// translators: %s: Archive page title, default: "Sermons".
				'placeholder' => wp_sprintf( __( 'e.g. %s', 'sermon-manager-for-wordpress' ), sanitize_title( __( 'Sermons', 'sermon-manager-for-wordpress' ) ) ),
				'default'     => 'sermons',
			),
			array(
				'title'    => __( 'Common Base Slug', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Enable a common base slug across all taxonomies', 'sermon-manager-for-wordpress' ),
				// translators: %1$s see msgid "sermons/preacher", effectively <code>sermons/preacher</code>.
				// translators: %2$s see msgid "sermons/series", effectively <code>sermons/series</code>.
				'desc_tip' => wp_sprintf( __( 'This is for users who want to have a common base slug across all taxonomies, e.g. %1$s or %2$s.', 'sermon-manager-for-wordpress' ), '<code>' . __( 'sermons/preacher', 'sermon-manager-for-wordpress' ) . '</code>', '<code>' . __( 'sermons/series', 'sermon-manager-for-wordpress' ) . '</code>' ),
				'id'       => 'common_base_slug',
				'default'  => 'no',
			),
			array(
				'title'    => __( 'Theme Compatibility', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Enable this if your sermon layout looks broken.', 'sermon-manager-for-wordpress' ),
				'desc_tip' => __( 'This will disable full-page layout override, and use alternative layout algorithm.', 'sermon-manager-for-wordpress' ),
				'id'       => 'theme_compatibility',
				'default'  => 'no',
			),
			array(
				'title'    => __( 'Disable Sermon Styles', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Disable Sermon CSS', 'sermon-manager-for-wordpress' ),
				// translators: %s effectively <code>sermons.css</code>.
				'desc_tip' => wp_sprintf( __( 'If you do this, you should copy the styles from %s and include them in your theme CSS.', 'sermon-manager-for-wordpress' ), '<code>/assets/css/sermon.min.css</code>' ),
				'id'       => 'css',
				'default'  => 'no',
			),
			array(
				'title'   => __( 'Display audio player on archive pages', 'sermon-manager-for-wordpress' ),
				'type'    => 'checkbox',
				'id'      => 'archive_player',
				'default' => 'no',
			),
			array(
				'title'   => __( 'Display attachments on archive pages', 'sermon-manager-for-wordpress' ),
				'type'    => 'checkbox',
				'id'      => 'archive_meta',
				'default' => 'no',
			),
			array(
				'title'   => __( 'Display Service Type filtering on archive pages', 'sermon-manager-for-wordpress' ),
				'type'    => 'checkbox',
				'id'      => 'service_type_filtering',
				'default' => 'no',
			),
			array(
				'title'   => __( 'Audio & Video Player', 'sermon-manager-for-wordpress' ),
				'type'    => 'select',
				'desc'    => __( 'Select which player to use for playing Sermons', 'sermon-manager-for-wordpress' ),
				'id'      => 'player',
				'options' => array(
					'plyr'         => 'Plyr',
					'mediaelement' => 'Mediaelement',
					'WordPress'    => 'Old WordPress player',
					'none'         => 'Browser HTML5',
				),
				'default' => 'plyr',
			),
			array(
				'title'    => __( 'Custom label for &ldquo;Preacher&rdquo;', 'sermon-manager-for-wordpress' ),
				'type'     => 'text',
				'desc_tip' => __( 'Note: it will also change preacher slugs.', 'sermon-manager-for-wordpress' ),
				'id'       => 'preacher_label',
				'default'  => '',
			),
			array(
				'title'   => __( 'Sermon date format', 'sermon-manager-for-wordpress' ),
				'type'    => 'select',
				'desc'    => __( '(used when creating a new Sermon)', 'sermon-manager-for-wordpress' ),
				'id'      => 'date_format',
				'options' => array(
					'0' => 'mm/dd/YY',
					'1' => 'dd/mm/YY',
					'2' => 'YY/mm/dd',
					'3' => 'YY/dd/mm',
				),
				'default' => '0',
			),
			array(
				'title'    => __( 'Disable sermon image on archive view', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc_tip' => __( 'Note: it will also hide images on shortcode output.', 'sermon-manager-for-wordpress' ),
				'id'       => 'disable_image_archive',
				'default'  => 'no',
			),
			array(
				'title'   => __( 'Disable sermon image on single sermon view', 'sermon-manager-for-wordpress' ),
				'type'    => 'checkbox',
				'id'      => 'disable_image_single',
				'default' => 'no',
			),
			array(
				'title'   => __( 'Do not show read more when all the text is visible', 'sermon-manager-for-wordpress' ),
				'type'    => 'checkbox',
				'id'      => 'hide_read_more_when_not_needed',
				'default' => 'no',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'general_settings',
			),
		) );

		return apply_filters( 'sm_get_settings_' . $this->id, $settings );
	}
}

return new SM_Settings_General();
