<?php
/**
 * Verse settings page.
 *
 * @package SM/Core/Admin/Settings
 */

defined( 'ABSPATH' ) or die;

/**
 * Initialize settings
 */
class SM_Settings_Verse extends SM_Settings_Page {
	/**
	 * SM_Settings_Verse constructor.
	 */
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
		if ( strpos( get_locale(), 'es_' ) !== false ) {
			// Add Spanish Bible translations.
			add_filter( 'sm_verse_settings', function ( $settings ) {
				foreach ( $settings as &$setting ) {
					if ( 'verse_bible_version' === $setting['id'] ) {
						$setting['options'] = array_merge( array(
							'LBLA95' => 'LBLA95',
							'NBLH'   => 'NBLH',
							'NVI'    => 'NVI',
							'RVR60'  => 'RVR60',
							'RVA'    => 'RVA',
						), $setting['options'] );

						$setting['default'] = 'NVI';

						break;
					}
				}

				return $settings;
			} );
		} else {
			// Check if Spanish Bible translation was selected previously,
			// and if it was - append it to the list.
			add_filter( 'sm_verse_settings', function ( $settings ) {
				foreach ( $settings as &$setting ) {
					if ( 'verse_bible_version' === $setting['id'] ) {
						switch ( SermonManager::getOption( 'verse_bible_version' ) ) {
							case $setting['default']:
							case '':
								break;
							default:
								foreach (
									array(
										'LBLA95',
										'NBLH',
										'NVI',
										'RVR60',
										'RVA',
									) as $value
								) {
									if ( SermonManager::getOption( 'verse_bible_version' ) === $value ) {
										$setting['options'] = array_merge( array(
											$value => $value,
										), $setting['options'] );

										$setting['desc'] = __( 'Note: WordPress is not set to any Spanish variant. Reverted to ESV.', 'sermon-manager-for-wordpress' );

										break 2;
									}
								}
						}
					}
				}

				return $settings;
			} );
		}

		$settings = apply_filters( 'sm_verse_settings', array(
			array(
				'title' => __( 'Verse Settings', 'sermon-manager-for-wordpress' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'verse_settings',
			),
			array(
				'title'    => __( 'Verse Popups', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Disable verse popups', 'sermon-manager-for-wordpress' ),
				'desc_tip' => __( 'If this option is checked, the verses will not have a link and a popup anymore. But, you would be free to use any other service you want to use without interfering with Sermon Manager.', 'sermon-manager-for-wordpress' ),
				'id'       => 'verse_popup',
				'default'  => 'no',
			),
			array(
				'title'   => __( 'Bible Version for Verse Popups', 'sermon-manager-for-wordpress' ),
				'type'    => 'select',
				'desc'    => __( 'Default: ESV.', 'sermon-manager-for-wordpress' ),
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
					'NIV'         => 'New International Version (NIV)',
					'NIRV'        => 'New International Reader\'s Version (NIRV)',
					'NKJV'        => 'New King James Version (NKJV)',
					'NLT'         => 'New Living Translation (NLT)',
					'DOUAYRHEIMS' => 'Douay-Rheims',
					'YLT'         => 'Young\'s Literal Translation (YLT)',
				),
				'default' => 'ESV',
			),
			array(
				'title'    => __( 'Verse In Sermons Widget', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Show verse.', 'sermon-manager-for-wordpress' ),
				'desc_tip' => __( 'By checking this option, the verse in our WordPress widget will show up. Default unchecked.', 'sermon-manager-for-wordpress' ),
				'id'       => 'widget_show_key_verse',
				'default'  => 'no',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'verse_settings',
			),
		) );

		return apply_filters( 'sm_get_settings_' . $this->id, $settings );
	}
}

return new SM_Settings_Verse();
