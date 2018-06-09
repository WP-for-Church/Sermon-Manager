<?php
/**
 * Import settings page.
 *
 * @since   2.13.0
 *
 * @package SM/Core/Admin/Settings
 */

defined( 'ABSPATH' ) or die;

/**
 * Initialize settings
 */
class SM_Settings_Import extends SM_Settings_Page {
	/**
	 * SM_Settings_Verse constructor.
	 */
	public function __construct() {
		$this->id    = 'import';
		$this->label = __( 'Import', 'sermon-manager-for-wordpress' );

		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = apply_filters( 'sm_import_settings', array(
			array(
				'title' => __( 'Import Settings', 'sermon-manager-for-wordpress' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'import_settings',
			),
			array(
				'title'   => 'Disallow comments by default',
				'type'    => 'checkbox',
				'id'      => 'import_disallow_comments',
				'default' => 'no',
			),
			array(
				'title'   => 'Do not mark preached dates as auto update',
				'type'    => 'checkbox',
				'desc'    => __( 'If checked, it will show date preached in Edit Sermon view, but it will not auto-update them when Date Published changes.', 'sermon-manager-for-wordpress' ),
				'id'      => 'import_disable_auto_dates',
				'default' => 'no',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'verse_settings',
			),
		) );

		return apply_filters( 'sm_get_settings_' . $this->id, $settings );
	}
}

return new SM_Settings_Import();
