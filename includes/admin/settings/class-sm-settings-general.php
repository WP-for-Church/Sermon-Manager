<?php
defined( 'ABSPATH' ) or die;

/**
 * General Settings page
 */
class SM_Settings_General extends SM_Settings_Page {
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
				'id'    => 'general_settings'
			),
			array(
				'title'       => __( 'Archive Page Title', 'sermon-manager-for-wordpress' ),
				'type'        => 'text',
				'id'          => 'archive_title',
				'placeholder' => wp_sprintf( __( 'e.g. %s', 'sermon-manager-for-wordpress' ), __( 'Sermons', 'sermon-manager-for-wordpress' ) ),
			),
			array(
				'title'       => __( 'Archive Page Slug', 'sermon-manager-for-wordpress' ),
				'type'        => 'text',
				'id'          => 'archive_slug',
				'placeholder' => wp_sprintf( __( 'e.g. %s', 'sermon-manager-for-wordpress' ), sanitize_title( __( 'Sermons', 'sermon-manager-for-wordpress' ) ) ),
			),
			array(
				'title'    => __( 'Common Base Slug', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Enable a common base slug across all taxonomies', 'sermon-manager-for-wordpress' ),
				// translators: %1$s see msgid "sermons/preacher", effectively <code>sermons/preacher</code>
				// translators: %2$s see msgid "sermons/series", effectively <code>sermons/series</code>
				'desc_tip' => wp_sprintf( __( 'This is for users who want to have a common base slug across all taxonomies, e.g. %1$s or %2$s.', 'sermon-manager-for-wordpress' ), '<code>' . __( 'sermons/preacher', 'sermon-manager-for-wordpress' ) . '</code>', '<code>' . __( 'sermons/series', 'sermon-manager-for-wordpress' ) . '</code>' ),
				'id'       => 'common_base_slug',
			),
			array(
				'title'    => __( 'Enable Template Files', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				// translators: %s effectively <code>/views</code>
				// translators: Since /views is a locale independent folder name it MUST NOT be localized
				'desc'     => wp_sprintf( __( 'Enable template files found in the %s folder', 'sermon-manager-for-wordpress' ), '<code>/views</code>' ),
				'desc_tip' => __( 'This is for users upgrading from an older version who have issues with version 1.5+.', 'sermon-manager-for-wordpress' ),
				'id'       => 'template',
			),
			array(
				'title'    => __( 'Disable Sermon Styles', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Disable Sermon CSS', 'sermon-manager-for-wordpress' ),
				// translators: %s effectively <code>sermons.css</code>
				'desc_tip' => wp_sprintf( __( 'If you do this, you should copy the styles from %s and include them in your theme CSS.', 'sermon-manager-for-wordpress' ), '<code>sermons.css</code>' ),
				'id'       => 'css',
			),
			array(
				'title' => __( 'Display audio player or video on archive pages', 'sermon-manager-for-wordpress' ),
				'type'  => 'checkbox',
				'desc'  => __( 'Display audio player or video on archive pages', 'sermon-manager-for-wordpress' ),
				'id'    => 'archive_player',
			),
			array(
				'title' => __( 'Use old audio player', 'sermon-manager-for-wordpress' ),
				'type'  => 'checkbox',
				'desc'  => __( 'Use old audio player', 'sermon-manager-for-wordpress' ),
				'id'    => 'use_old_player',
			),
			array(
				'title'    => __( 'Custom label for &ldquo;Preacher&rdquo;', 'sermon-manager-for-wordpress' ),
				'type'     => 'text',
				'desc_tip' => __( 'Note: it will also change preacher slugs.', 'sermon-manager-for-wordpress' ),
				'id'       => 'preacher_label',
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
				)
			),

			array( 'type' => 'sectionend', 'id' => 'general_settings' ),
		) );

		return apply_filters( 'sm_get_settings_' . $this->id, $settings );
	}
}

return new SM_Settings_General();
