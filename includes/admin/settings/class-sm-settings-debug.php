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
				'title'   => __( 'Enable output of PHP errors in Sermon Manager (disable in production)', 'sermon-manager-for-wordpress' ),
				'type'    => 'checkbox',
				'id'      => 'sm_debug',
				'default' => 'no',
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
			array(
				'title'   => __( 'Execute all update functions that have not been executed yet' ),
				'type'    => 'checkbox',
				'id'      => 'execute_unexecuted_functions',
				'default' => 'no',
			),
			array(
				'title'   => '"post_content" creation',
				'type'    => 'select',
				'options' => array(
					1  => 'Enable',
					11 => 'Enable and re-create all',
					0  => 'Disable',
					10 => 'Disable and flush existing'
				),
				'id'      => 'post_content_enabled',
				'default' => 1,
			),
			array(
				'title'   => '"post_excerpt" creation',
				'type'    => 'select',
				'options' => array(
					1  => 'Enable',
					11 => 'Enable and re-create all',
					0  => 'Disable',
					10 => 'Disable and flush existing'
				),
				'id'      => 'post_excerpt_enabled',
				'default' => 1,
			),
			array(
				'title'   => 'Use home_url in dropdown filter',
				'type'    => 'checkbox',
				'id'      => 'home_url_filtering',
				'desc'    => 'Check this if you have HTTP 404 error when you use filtering',
				'default' => 0,
			),
			array(
				'title'   => __( 'Execute a specific update function' ),
				'type'    => 'select',
				'id'      => 'execute_specific_unexecuted_function',
				'default' => '',
				'options' => sm_debug_get_update_functions(),
				'desc'    => '<code>[AE]</code> - Already Executed; <code>[NE]</code> - Not Executed',
			),
			array(
				'title'   => 'Disable override of <code>the_excerpt</code>',
				'type'    => 'checkbox',
				'id'      => 'disable_the_excerpt',
				'desc'    => 'Check this if you have double sermon content on archive page',
				'default' => 0,
			),
			array(
				'title'   => 'Load Plyr JS in footer (applies only to Plyr player)',
				'type'    => 'checkbox',
				'id'      => 'player_js_footer',
				'desc'    => 'Check this if Plyr is not loading',
				'default' => 0,
			),
			array(
				'title'   => 'Completely disable loading of Sermon Manager layouts',
				'type'    => 'checkbox',
				'id'      => 'disable_layouts',
				'default' => 0,
			),

			array( 'type' => 'sectionend', 'id' => 'debug_settings' ),
		) );

		return apply_filters( 'sm_get_settings_' . $this->id, $settings );
	}
}

return new SM_Settings_Debug();
