<?php
defined( 'ABSPATH' ) or die;

/**
 * Debug page
 */
class SM_Settings_Debug extends SM_Settings_Page {
	public function __construct() {
		$this->id    = 'debug';
		$this->label = __( 'Debug', 'sermon-manager-for-wordpress' );

		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = apply_filters( 'sm_debug_settings', array(
			array(
				'title' => __( 'Debug Settings', 'sermon-manager-for-wordpress' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'debug_settings'
			),
			array(
				'title'   => __( 'Force Sermon Manager\'s WP_Background_Updater class', 'sermon-manager-for-wordpress' ),
				'type'    => 'checkbox',
				'desc'    => __( 'Override other plugin class with same name', 'sermon-manager-for-wordpress' ),
				'id'      => 'in_house_background_update',
				'default' => 'no',
			),
			array(
				'title'   => __( 'Show detailed data during import', 'sermon-manager-for-wordpress' ),
				'type'    => 'checkbox',
				'id'      => 'debug_import',
				'default' => 'no',
			),
			array(
				'title'   => __( 'Enable book sorting in dropdown box' ),
				'type'    => 'checkbox',
				'id'      => 'sort_bible_books',
				'default' => 'yes'
			),

			array( 'type' => 'sectionend', 'id' => 'debug_settings' ),
		) );

		return apply_filters( 'sm_get_settings_' . $this->id, $settings );
	}
}

return new SM_Settings_Debug();
