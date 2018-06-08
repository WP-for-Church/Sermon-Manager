<?php
/**
 * Settings page/tab code.
 *
 * @package SM/Core/Admin/Settings
 */

defined( 'ABSPATH' ) or die;

/**
 * Initialize the tab.
 *
 * @since 2.9
 */
abstract class SM_Settings_Page {

	/**
	 * Setting page id
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Setting page label
	 *
	 * @var string
	 */
	protected $label = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'sm_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'sm_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'sm_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'sm_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get settings page ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get settings page label.
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Add this page to settings.
	 *
	 * @param array $pages The pages.
	 *
	 * @return mixed
	 */
	public function add_settings_page( $pages ) {
		$pages[ $this->id ] = $this->label;

		return $pages;
	}

	/**
	 * Output sections.
	 */
	public function output_sections() {
		global $current_section;

		$sections = $this->get_sections();

		if ( empty( $sections ) || 1 === sizeof( $sections ) ) {
			return;
		}

		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . admin_url( 'edit.php?post_type=wpfc_sermon&page=sm-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
		}

		echo '</ul><br class="clear" />';
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		return apply_filters( 'sm_get_sections_' . $this->id, array() );
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		$settings = $this->get_settings();

		SM_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		return apply_filters( 'sm_get_settings_' . $this->id, array() );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings();
		SM_Admin_Settings::save_fields( $settings );

		if ( $current_section ) {
			do_action( 'sm_update_options_' . $this->id . '_' . $current_section );
		}
	}
}
