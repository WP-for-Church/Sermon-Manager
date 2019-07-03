<?php
/**
 * Display settings page.
 *
 * @package SM/Core/Admin/Settings
 */

defined( 'ABSPATH' ) or die;

/**
 * Initialize settings
 */
class SM_Settings_Display extends SM_Settings_Page {
	/**
	 * SM_Settings_Display constructor.
	 */
	public function __construct() {
		$this->id    = 'display';
		$this->label = __( 'Display', 'sermon-manager-for-wordpress' );

		parent::__construct();
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = apply_filters( 'sm_display_settings', array(

			array(
				'title' => __( 'Display Settings', 'sermon-manager-for-wordpress' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'display_settings',
			),
			array(
				'title'   => __( 'Default Image', 'sermon-manager-for-wordpress' ),
				'type'    => 'image',
				'desc'    => __( 'Sets the default sermon image that would show up if there is no sermon or series (if applicable) image is set.', 'sermon-manager-for-wordpress' ),
				'id'      => 'default_image',
				'default' => '',
			),
			array(
				'title'    => __( 'Sermon Manager Styles', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Disable Sermon Manager\'s CSS.', 'sermon-manager-for-wordpress' ),
				// translators: %s effectively <code>sermons.css</code>.
				'desc_tip' => wp_sprintf( __( 'This will disable Sermon Manager\'s default CSS, so sermon styling will rely only on your CSS. It might be easier to check this option, instead of overriding our rules. Default unchecked.', 'sermon-manager-for-wordpress' ), '<code>/assets/css/sermon.min.css</code>' ),
				'id'       => 'css',
				'default'  => 'no',
			),
			array(
				'title' => __( 'Archive', 'sermon-manager-for-wordpress' ),
				'type'  => 'separator_title',
			),
			array(
				'title'   => __( 'Order sermons by', 'sermon-manager-for-wordpress' ),
				'type'    => 'select',
				'options' => array(
					'date_preached' => 'Date Preached',
					'date'          => 'Date Published',
					'title'         => 'Title',
					'ID'            => 'ID',
					'random'        => 'Random',
				),
				'desc'    => __( 'Changes the way sermons are ordered by default. Affects the RSS feed and shown date as well. Default "Date Preached".', 'sermon-manager-for-wordpress' ),
				'default' => 'date_preached',
				'id'      => 'archive_orderby',
			),
			array(
				'title'   => __( 'Order direction', 'sermon-manager-for-wordpress' ),
				'type'    => 'select',
				'options' => array(
					'desc' => 'Descending',
					'asc'  => 'Ascending',
				),
				'desc'    => __( 'Related to the setting above. Default descending.', 'sermon-manager-for-wordpress' ),
				'default' => 'desc',
				'id'      => 'archive_order',
			),
			array(
				'title'    => __( 'Display Audio Player', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc_tip' => __( 'Displays audio player on archive pages. Default unchecked.', 'sermon-manager-for-wordpress' ),
				'id'       => 'archive_player',
				'default'  => 'no',
			),
			array(
				'title'    => __( 'Display Attachments', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc_tip' => __( 'Displays attachments links on archive pages. Default unchecked.', 'sermon-manager-for-wordpress' ),
				'id'       => 'archive_meta',
				'default'  => 'no',
			),
			array(
				'title'    => __( 'Disable Sermon Image', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc_tip' => __( 'Disable image output on archive views. Default unchecked.', 'sermon-manager-for-wordpress' ),
				'id'       => 'disable_image_archive',
				'default'  => 'no',
			),
			array(
				'title'    => __( 'Read More', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Do not show read more when all the text is visible.', 'sermon-manager-for-wordpress' ),
				'desc_tip' => __( 'By default, "Read more..." text shows up regardless if sermon has or does not have description. By checking this option, it will show only if there is more text to read.', 'sermon-manager-for-wordpress' ),
				'id'       => 'hide_read_more_when_not_needed',
				'default'  => 'no',
			),
			array(
				'title'    => __( 'Hide Filtering', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Hide filters on archive pages.', 'sermon-manager-for-wordpress' ),
				'desc_tip' => __( 'Currently, the filter dropdowns are shown by default on all archive pages. By checking this option, you can hide them. Default unchecked.', 'sermon-manager-for-wordpress' ),
				'id'       => 'hide_filters',
				'default'  => 'no',
			),
			array(
				// translators: %s the taxonomy label. Default: Service Type.
				'title'    => wp_sprintf( __( 'Display %s Filtering', 'sermon-manager-for-wordpress' ), sm_get_taxonomy_field( 'wpfc_service_type', 'singular_name' ) ),
				'type'     => 'checkbox',
				// translators: %s the taxonomy label. Default: service type.
				'desc_tip' => wp_sprintf( __( 'Displays %s filtering on archive pages. Default unchecked.', 'sermon-manager-for-wordpress' ), strtolower( sm_get_taxonomy_field( 'wpfc_service_type', 'singular_name' ) ) ),
				'id'       => 'service_type_filtering',
				'default'  => 'no',
				'disabled' => method_exists( '\SermonManagerPro\Templating\Templating_Manager', 'is_active' ) ? \SermonManagerPro\Templating\Templating_Manager::is_active() : false,
			),
			array(
				'title'    => __( 'Use Previous/Next Pagination', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Instead of paginated pagination (1,2,3), use previous/next links.', 'sermon-manager-for-wordpress' ),
				'desc_tip' => __( 'Some themes do not have styling for paginated pagination, so it\'s easier for you to use previous/next pagination for them, instead of writing custom CSS. Default unchecked.', 'sermon-manager-for-wordpress' ),
				'id'       => 'use_prev_next_pagination',
				'default'  => 'no',
			),
			array(
				'title' => __( 'Single', 'sermon-manager-for-wordpress' ),
				'type'  => 'separator_title',
			),
			array(
				'title'    => __( 'Disable Sermon Image', 'sermon-manager-for-wordpress' ),
				'type'     => 'checkbox',
				'desc_tip' => __( 'Disable image output on single sermon view. Default unchecked.', 'sermon-manager-for-wordpress' ),
				'id'       => 'disable_image_single',
				'default'  => 'no',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'display_settings',
			),
		) );

		return apply_filters( 'sm_get_settings_' . $this->id, $settings );
	}
}

return new SM_Settings_Display();
