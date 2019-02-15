/**
 * Handles conditional loading of specific setting fields.
 *
 * @package SM
 */

var sm_conditionals = typeof sm_conditionals !== 'undefined' ? sm_conditionals : [];

jQuery( document ).ready(
	function () {
		jQuery.each(
			sm_conditionals,
			function ( element_id, element_conditionals ) {
				var element = jQuery( '#' + element_id ).closest( 'tr' );

				jQuery.each(
					element_conditionals,
					function ( index, condition_data ) {
						var conditional_element_id = condition_data.id;
						var conditional_element    = jQuery( '#' + conditional_element_id );
						var value                  = sm_isset( condition_data[ "value" ] ) ? condition_data[ "value" ] : condition_data[ "!value" ];
						var not                    = sm_isset( condition_data[ "!value" ] );

						conditional_element.off().on(
							'change',
							function () {
								var selected_category = this.value;
								hide_show_elements( not, value, selected_category, element );
							}
						);

						hide_show_elements( not, value, conditional_element.val(), element );
					}
				);
			}
		);
	}
);

function hide_show_elements( not, value, current_value, element ) {
	element.find( 'select' ).find( 'option' ).each( function () {
		jQuery( this.remove() );
	} );
	element.find( 'select' ).append( jQuery( '<option/>' ).val( '' ).text( 'Loading...' ) );

	if ( not ) {
		if ( value !== current_value ) {
			element.removeClass( 'hidden' );
		} else {
			element.addClass( 'hidden' );
		}
	} else {
		if ( value === current_value ) {
			element.removeClass( 'hidden' );
		} else {
			element.addClass( 'hidden' );
		}
	}

	var data = {
		'action': 'sm_settings_get_select_data',
		'id': current_value,
	};

	// Request subcategory list.
	jQuery.post(
		ajaxurl,
		data,
		function ( response ) {
			if ( 'false' === response ) {
				element.addClass( 'hidden' );
			} else {
				response = JSON.parse( response );
				element.find( 'select' ).find( 'option' ).each( function () {
					jQuery( this.remove() );
				} );
				jQuery.each(
					response,
					function ( id, item ) {
						element.find( 'select' ).append( jQuery( '<option/>' ).val( id ).text( item ) );
					}
				)
			}
		}
	);
}


/**
 * Checks to see if a value is set.
 *
 * @param {Function} accessor Function that returns our value
 */
function sm_isset( accessor ) {
	try {
		// Note we're seeing if the returned value of our function is not
		// undefined
		return typeof accessor !== 'undefined'
	} catch ( e ) {
		// And we're able to catch the Error it would normally throw for
		// referencing a property of undefined
		return false
	}
}
