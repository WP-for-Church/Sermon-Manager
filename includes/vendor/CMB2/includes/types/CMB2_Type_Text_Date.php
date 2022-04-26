<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

/**
 * CMB text_date field type
 *
 * @since     2.2.2
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    WebDevStudios
 * @license   GPL-2.0+
 * @link      http://webdevstudios.com
 */
class CMB2_Type_Text_Date extends CMB2_Type_Picker_Base {

	public function render( $args = array() ) {
		$args = $this->parse_args( 'text_date', array(
			'class'           => 'cmb2-text-small cmb2-datepicker',
			'value'           => isset( $_GET['post'] ) ? ( get_post_meta( $_GET['post'], 'sermon_date_auto', true ) ? '' : $this->field->get_timestamp_format() ) : '',
			'placeholder'     => isset( $_GET['post'] ) ? get_post_meta( $_GET['post'], 'sermon_date_auto', true) ? 'Same as Published' : '' : '',
			'desc'            => $this->_desc(),
			'js_dependencies' => array( 'jquery-ui-core', 'jquery-ui-datepicker' ),
			'autocomplete'    => 'off',
		) );

		if ( false === strpos( $args['class'], 'timepicker' ) ) {
			$this->parse_picker_options( 'date' );
		}

		return parent::render( $args );
	}

}
