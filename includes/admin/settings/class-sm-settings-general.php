<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * General Settings page
 */
class SM_Settings_General extends SM_Settings_Page {
	public function __construct() {
		$this->id    = 'general';
		$this->label = __( 'General', 'sermon-manager-for-wordpress' );

		add_filter( 'sm_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'sm_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'sm_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Output a color picker input box.
	 *
	 * @param mixed  $name
	 * @param string $id
	 * @param mixed  $value
	 * @param string $desc (default: '')
	 */
	public function color_picker( $name, $id, $value, $desc = '' ) {
		echo '<div class="color_box">' . sm_help_tip( $desc ) . '
			<input name="' . esc_attr( $id ) . '" id="' . esc_attr( $id ) . '" type="text" value="' . esc_attr( $value ) . '" class="colorpick" /> <div id="colorPickerDiv_' . esc_attr( $id ) . '" class="colorpickdiv"></div>
		</div>';
	}

	/**
	 * Save settings.
	 */
	public function save() {
		$settings = $this->get_settings();

		SM_Admin_Settings::save_fields( $settings );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = apply_filters( 'sm_general_settings', array(

			array(
				'title' => esc_html__( 'General Settings', 'sermon-manager-for-wordpress' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'general_options'
			),
			array(
				'title'       => esc_html__( 'Archive Page Title', 'sermon-manager-for-wordpress' ),
				'type'        => 'text',
				'id'          => 'archive_title',
				'placeholder' => wp_sprintf( esc_attr__( 'e.g. %s', 'sermon-manager-for-wordpress' ), esc_attr__( 'Sermons', 'sermon-manager-for-wordpress' ) ),
			),
			array(
				'title'       => esc_html__( 'Archive Page Slug', 'sermon-manager-for-wordpress' ),
				'type'        => 'text',
				'id'          => 'archive_slug',
				'placeholder' => wp_sprintf( esc_attr__( 'e.g. %s', 'sermon-manager-for-wordpress' ), sanitize_title( esc_attr__( 'Sermons', 'sermon-manager-for-wordpress' ) ) ),
			),
			array(
				'title'    => esc_html__( 'Common Base Slug', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => esc_html__( 'Enable a common base slug across all taxonomies', 'sermon-manager-for-wordpress' ),
				// translators: %1$s see msgid "sermons/preacher", effectively <code>sermons/preacher</code>
				// translators: %2$s see msgid "sermons/series", effectively <code>sermons/series</code>
				'desc_tip' => wp_sprintf( esc_html__( 'This is for users who want to have a common base slug across all taxonomies, e.g. %1$s or %2$s.', 'sermon-manager-for-wordpress' ), '<code>' . esc_html__( 'sermons/preacher', 'sermon-manager-for-wordpress' ) . '</code>', '<code>' . esc_html__( 'sermons/series', 'sermon-manager-for-wordpress' ) . '</code>' ),
				'id'       => 'common_base_slug',
			),
			array(
				'title'    => esc_html__( 'Enable Template Files', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				// translators: %s effectively <code>/views</code>
				// translators: Since /views is a locale independent folder name it MUST NOT be localized
				'desc'     => wp_sprintf( esc_html__( 'Enable template files found in the %s folder', 'sermon-manager-for-wordpress' ), '<code>/views</code>' ),
				'desc_tip' => esc_html__( 'This is for users upgrading from an older version who have issues with version 1.5+.', 'sermon-manager-for-wordpress' ),
				'id'       => 'template',
			),
			array(
				'title'    => esc_html__( 'Disable Sermon Styles', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => esc_html__( 'Disable Sermon CSS', 'sermon-manager-for-wordpress' ),
				// translators: %s effectively <code>sermons.css</code>
				'desc_tip' => wp_sprintf( esc_html__( 'If you do this, you should copy the styles from %s and include them in your theme CSS.', 'sermon-manager-for-wordpress' ), '<code>sermons.css</code>' ),
				'id'       => 'css',
			),
			array(
				'title' => esc_html__( 'Display audio player or video on archive pages', 'sermon-manager-for-wordpress' ),
				'type'  => 'checkbox',
				'desc'  => esc_html__( 'Display audio player or video on archive pages', 'sermon-manager-for-wordpress' ),
				'id'    => 'archive_player',
			),
			array(
				'title' => esc_html__( 'Use old audio player', 'sermon-manager-for-wordpress' ),
				'type'  => 'checkbox',
				'desc'  => esc_html__( 'Use old audio player', 'sermon-manager-for-wordpress' ),
				'id'    => 'use_old_player',
			),
			array(
				'title'    => esc_html__( 'Custom label for &ldquo;Preacher&rdquo;', 'sermon-manager-for-wordpress' ),
				'type'     => 'text',
				'desc_tip' => esc_html__( 'Note: it will also change preacher slugs.', 'sermon-manager-for-wordpress' ),
				'id'       => 'preacher_label',
			),
			array(
				'title'   => esc_html__( 'Sermon date format', 'sermon-manager-for-wordpress' ),
				'type'    => 'select',
				'desc'    => esc_html__( '(used when creating a new Sermon)', 'sermon-manager-for-wordpress' ),
				'id'      => 'date_format',
				'options' => array(
					'0' => 'mm/dd/YY',
					'1' => 'dd/mm/YY',
					'2' => 'YY/mm/dd',
					'3' => 'YY/dd/mm',
				)
			),
			array(
				'title' => esc_html__( 'Show key verse in widget', 'sermon-manager-for-wordpress' ),
				'type'  => 'checkbox',
				'desc'  => esc_html__( 'Show key verse in widget', 'sermon-manager-for-wordpress' ),
				'id'    => 'widget_show_key_verse',
			),

			array( 'type' => 'sectionend', 'id' => 'general_options' ),
		) );

		return apply_filters( 'sm_get_settings_' . $this->id, $settings );
	}
}

return new SM_Settings_General();
