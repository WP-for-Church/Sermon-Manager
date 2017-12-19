<?php
defined( 'ABSPATH' ) or die;

/**
 * Verse Settings page
 */
class SM_Settings_Verse extends SM_Settings_Page {
	public function __construct() {
		$this->id    = 'verse';
		$this->label = __( 'Verse', 'sermon-manager-for-wordpress' );

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
				'title' => __( 'Verse Settings', 'sermon-manager-for-wordpress' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'verse_settings'
			),
			array(
				'title'   => __( 'Verse Popups', 'sermon-manager-for-wordpress' ),
				'type'    => 'checkbox',
				'desc'    => __( 'Disable verse popups', 'sermon-manager-for-wordpress' ),
				'id'      => 'verse_popup',
				'default' => 'no',
			),
			array(
				'title'   => __( 'Bible Version for Verse Popups', 'sermon-manager-for-wordpress' ),
				'type'    => 'select',
				'id'      => 'verse_bible_version',
				'options' => array(
					'AMP'         => 'Amplified Bible (AMP)',
					'ASV'         => 'American Standard Version (ASV)',
					'DAR'         => 'Darby',
					'ESV'         => 'English Standard Version (ESV)',
					'GW'          => 'God\'s Word',
					'HCSB'        => 'Holman Christian Standard Bible (HCSB)',
					'KJV'         => 'King James Version (KJV)',
					'LEB'         => 'Lexham English Bible (LEB)',
					'MESSAGE'     => 'Message Bible',
					'NASB'        => 'New American Standard Bible (NASB)',
					'NCV'         => 'New Century Version (NCV)',
					'NIRV'        => 'New International Reader\'s Version (NIRV)',
					'NKJV'        => 'New King James Version (NKJV)',
					'NLT'         => 'New Living Translation (NLT)',
					'DOUAYRHEIMS' => 'Douay-Rheims',
					'YLT'         => 'Young\'s Literal Translation (YLT)',
				),
				'default' => 'ESV',
			),
			array(
				'title' => __( 'Show key verse in widget', 'sermon-manager-for-wordpress' ),
				'type'  => 'checkbox',
				'desc'  => __( 'Show key verse in widget', 'sermon-manager-for-wordpress' ),
				'id'    => 'widget_show_key_verse',
			),

			array( 'type' => 'sectionend', 'id' => 'verse_settings' ),
		) );

		return apply_filters( 'sm_get_settings_' . $this->id, $settings );
	}
}

return new SM_Settings_Verse();
