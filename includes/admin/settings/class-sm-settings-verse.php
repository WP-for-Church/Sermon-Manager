<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Verse Settings page
 */
class SM_Settings_Verse extends SM_Settings_Page {
	public function __construct() {
		$this->id    = 'verse';
		$this->label = __( 'Verse Settings', 'sermon-manager-for-wordpress' );

		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = apply_filters( 'sm_verse_settings', array(
			array(
				'title' => esc_html__( 'Verse Settings', 'sermon-manager-for-wordpress' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'verse_settings'
			),
			array(
				'title' => esc_html__( 'Verse Popups', 'sermon-manager-for-wordpress' ),
				'type'  => 'checkbox',
				'desc'  => esc_html__( 'Disable Bib.ly verse popups', 'sermon-manager-for-wordpress' ),
				'id'    => 'bibly',
			),
			array(
				'title'   => esc_html__( 'Bible Version for Verse Popups', 'sermon-manager-for-wordpress' ),
				'type'    => 'select',
				// translators: %s see effectively <code>ESV</code>
				'desc'    => wp_sprintf( esc_html__( 'Warning! %s is not supported if your site uses SSL (HTTPS).', 'sermon-manager-for-wordpress' ), '<code>ESV</code>' ),
				'id'      => 'bibly_version',
				'options' => array(
					'KJV' => 'KJV',
					'ESV' => 'ESV',
					'NET' => 'NET',
					'LEB' => 'LEB'
				),
			),

			array( 'type' => 'sectionend', 'id' => 'verse_settings' ),
		) );

		return apply_filters( 'sm_get_settings_' . $this->id, $settings );
	}
}

return new SM_Settings_Verse();
