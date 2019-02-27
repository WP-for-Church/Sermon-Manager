<?php
/**
 * Debug settings page.
 *
 * @package SM/Core/Admin/Settings
 */

defined( 'ABSPATH' ) or die;

/**
 * Initialize settings
 */
class SM_Settings_Debug extends SM_Settings_Page {
	/**
	 * SM_Settings_Debug constructor.
	 */
	public function __construct() {
		$this->id    = 'debug';
		$this->label = __( 'Advanced', 'sermon-manager-for-wordpress' );

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
				'title' => __( 'Advanced Settings', 'sermon-manager-for-wordpress' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'debug_settings',
			),
			array(
				'title'    => __( 'Import Log', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Show log after finished data import.', 'sermon-manager-for-wordpress' ),
				'desc_tip' => __( 'Shows log after import is finished, with a lot of useful data for debugging. Default unchecked.', 'sermon-manager-for-wordpress' ),
				'id'       => 'debug_import',
				'default'  => 'no',
			),
			array(
				'title'    => __( 'Book Sorting', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Enable book sorting.', 'sermon-manager-for-wordpress' ),
				'desc_tip' => __( 'Orders book in filtering by biblical order, rather than alphabetical. Default checked.', 'sermon-manager-for-wordpress' ),
				'id'       => 'sort_bible_books',
				'default'  => 'yes',
			),
			array(
				'title'    => __( 'Background Updates', 'sermon-manager-for-wordpress' ),
				'desc'     => __( 'Execute all update functions that have not been executed yet.', 'sermon-manager-for-wordpress' ),
				'desc_tip' => __( 'Sometimes, some update functions may not execute after plugin update. This will make them do it. Executes functions and restores to unchecked after settings save.', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'id'       => 'execute_unexecuted_functions',
				'default'  => 'no',
			),
			array(
				'title'    => __( 'Excerpt Override', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'id'       => 'disable_the_excerpt',
				'desc'     => __( 'Disable override of excerpt.', 'sermon-manager-for-wordpress' ),
				'desc_tip' => __( 'By default, Sermon Manager overrides excerpt output to show audio player, detailed sermon data, etc... Some themes use different ways of outputting the excerpt, so sermon data can mistakenly be shown multiple times under the title. By checking this option, we try to fix that. Default unchecked.', 'sermon-manager-for-wordpress' ),
				'default'  => 'no',
			),
			array(
				'title'    => __( 'Theme Compatibility', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Use alternative layout override.', 'sermon-manager-for-wordpress' ),
				'desc_tip' => __( 'This will disable full-page layout override, and use alternative layout algorithm, which was used in very old Sermon Manager versions.', 'sermon-manager-for-wordpress' ),
				'id'       => 'theme_compatibility',
				'default'  => 'no',
			),
			array(
				'title'    => __( 'Safari Player', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Use native player on Safari.', 'sermon-manager-for-wordpress' ),
				'desc_tip' => __( 'Sometimes, Plyr does not work well on Safari, and by checking this box, Safari users will see native browser player instead of it. Only affects Plyr player. Default unchecked.', 'sermon-manager-for-wordpress' ),
				'id'       => 'use_native_player_safari',
				'default'  => 'no',
			),
			array(
				'title'    => __( 'Cloudflare Compatibility', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Load Plyr script immediately.', 'sermon-manager-for-wordpress' ),
				'desc_tip' => __( 'Cloudflare uses some caching methods which break player loading, mostly when displaying sermons via shortcodes. Checking this option will most likely fix it. Default unchecked.', 'sermon-manager-for-wordpress' ),
				'id'       => 'disable_cloudflare_plyr',
				'default'  => 'no',
			),
			array(
				'title'    => __( '"Views" count', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Enable "views" count for admin and editor users.', 'sermon-manager-for-wordpress' ),
				'desc_tip' => __( 'Disable this option if you do not want to count sermon views for editors and admins.', 'sermon-manager-for-wordpress' ),
				'id'       => 'enable_views_count_logged_in',
				'default'  => 'yes',
			),
			array(
				'title' => __( 'Importing Settings', 'sermon-manager-for-wordpress' ),
				'type'  => 'separator_title',
			),
			array(
				'title'    => __( 'Comments Status', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Disallow comments', 'sermon-manager-for-wordpress' ),
				'desc_tip' => __( 'When this is checked, the comments on all imported sermons in future will be disabled. Default unchecked.', 'sermon-manager-for-wordpress' ),
				'id'       => 'import_disallow_comments',
				'default'  => 'no',
			),
			array(
				'title' => __( 'Very Advanced Settings', 'sermon-manager-for-wordpress' ),
				'type'  => 'separator_title',
			),
			array(
				'title'    => __( 'Force Background Updates', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Override other plugin\'s class with same name. (<code>WP_Background_Updater</code>)', 'sermon-manager-for-wordpress' ),
				'desc_tip' => __( 'Typically, you won\'t need to have this checked, unless you know what it does or if WP For Church support instructs you to do it. Default unchecked.', 'sermon-manager-for-wordpress' ),
				'id'       => 'in_house_background_update',
				'default'  => 'no',
			),
			array(
				'title'   => __( 'Specific Background Updates', 'sermon-manager-for-wordpress' ),
				'type'    => 'select',
				'id'      => 'execute_specific_unexecuted_function',
				'default' => '',
				'options' => sm_debug_get_update_functions(),
				'desc'    => __( 'The option named "Background updates" executes all un-executed update functions. This option allows you to execute a specific one, even if it\'s already been executed. Usually used when WP For Church support instructs to do so. Just select a function and save settings.<br><code>[AE]</code> - Already Executed; <code>[NE]</code> - Not Executed', 'sermon-manager-for-wordpress' ),
			),
			array(
				'title'   => __( 'Automatic Excerpt Creation', 'sermon-manager-for-wordpress' ),
				'type'    => 'select',
				'options' => array(
					1  => __( 'Enable', 'sermon-manager-for-wordpress' ),
					11 => __( 'Enable and re-create all', 'sermon-manager-for-wordpress' ),
					0  => __( 'Disable', 'sermon-manager-for-wordpress' ),
					10 => __( 'Disable and flush existing', 'sermon-manager-for-wordpress' ),
				),
				'desc'    => __( 'Enables or disables creation of short plaintext excerpt in sermon\'s <code>post_content</code> database field. Could be removed in future versions. Default enabled.', 'sermon-manager-for-wordpress' ),
				'id'      => 'post_content_enabled',
				'default' => 1,
			),
			array(
				'title'    => __( 'Plyr JavaScript Loading', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'id'       => 'player_js_footer',
				'desc'     => __( 'Load files after the website content.', 'sermon-manager-for-wordpress' ),
				'desc_tip' => __( 'It should never happen now, but we are leaving this option here anyway. Plyr JavaScript files are loaded into <code>&lt;head&gt;</code> by default (before page content), but it used to happen that it\'s too early. This tried to fix that. But, it is likely that it is not needed in the latest Sermon Manager version, since the loader has been changed. Default unchecked.', 'sermon-manager-for-wordpress' ),
				'default'  => 'no',
			),
			array(
				'title'    => __( 'Disable Plugin Views', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Disable loading of Sermon Manager\'s views.', 'sermon-manager-for-wordpress' ),
				'desc_tip' => __( 'Completely disables loading of views, including overrides. Uses whatever the theme is using. Default disabled.', 'sermon-manager-for-wordpress' ),
				'id'       => 'disable_layouts',
				'default'  => 'no',
			),
			array(
				'title'    => __( 'Force Plugin Views', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Force plugin views.', 'sermon-manager-for-wordpress' ),
				'desc_tip' => __( 'Forces loading of Sermon Manager views, while overriding theme overrides.', 'sermon-manager-for-wordpress' ),
				'id'       => 'force_layouts',
				'default'  => 'no',
			),
			array(
				'title'    => __( 'Clear All Transients', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Clear all transients on save.', 'sermon-manager-for-wordpress' ),
				'desc_tip' => __( 'Removes all transients from the database on save. Useful for debugging RSS feed. Your website will not break by executing this.', 'sermon-manager-for-wordpress' ),
				'id'       => 'clear_transients',
				'default'  => 'no',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'debug_settings',
			),
		) );

		return apply_filters( 'sm_get_settings_' . $this->id, $settings );
	}
}

return new SM_Settings_Debug();
