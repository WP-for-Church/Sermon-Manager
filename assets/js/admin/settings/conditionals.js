/**
 * Handles conditional loading of specific setting fields.
 *
 * @package SM
 */

if ( typeof sm_conditionals !== 'undefined' ) {
	// Bind to elements.
	jQuery(
		function () {
			jQuery.each(
				sm_conditionals,
				function ( element_id, element_conditionals ) {
					/**
					 * The element that should be shown or hidden.
					 *
					 * @type object
					 */
					let target_element = jQuery( '#' + element_id );

					/**
					 * The element's table row.
					 *
					 * @type object
					 */
					let table_row = target_element.closest( 'tr' );

					// Go through each conditional.
					jQuery.each(
						element_conditionals,
						function ( index, condition_data ) {
							/**
							 * The element's HTML ID & database ID.
							 */
							let conditional_element_id = condition_data.id;

							/**
							 * The element itself.
							 *
							 * @type object
							 */
							let conditional_element = jQuery( '#' + conditional_element_id );

							/**
							 * The value that we are looking for in the element.
							 *
							 * @type string
							 */
							let target_value = sm_isset( condition_data[ "value" ] ) ? condition_data[ "value" ] : condition_data[ "!value" ];

							/**
							 * If we should invert the value.
							 *
							 * @type boolean
							 */
							let not = sm_isset( condition_data[ "!value" ] );

							// Hook into element's change event, so we can act on it.
							conditional_element.on(
								'change',
								function () {
									/**
									 * Currently selected value.
									 *
									 * @type string
									 */
									let selected_value = this.value;


									// Hide or show the elements.
									sm_hide_show_elements( target_value, conditional_element.closest( 'tr' ).hasClass( 'hidden' ) ? false : selected_value, not, table_row );
								}
							);

							// Call the function first time.
							sm_hide_show_elements( target_value, conditional_element.closest( 'tr' ).hasClass( 'hidden' ) ? false : conditional_element.val(), not, table_row );
						}
					);
				}
			);
		}
	);
}

/**
 * Hides or shows the element based on its value.
 *
 * @param {string} target_value Value that we are looking for.
 * @param {string} current_value The current value of the element.
 * @param {boolean} not If we should invert the value.
 * @param {object} table_row The table row to hide or show.
 */
function sm_hide_show_elements( target_value, current_value, not, table_row ) {
	let element = table_row.find( 'select' );

	/**
	 * If we should hide the row.
	 *
	 * @type {boolean}
	 */
	let hide = target_value !== current_value;

	/**
	 * If the element should get data via Ajax.
	 *
	 * @type boolean
	 */
	let is_ajax = element.data( 'ajax' ) ? element.data( 'ajax' ) : false;

	// Invert if needed.
	hide = not ? ! hide : hide;

	// Do hide.
	if ( ! is_ajax ) {
		if ( hide ) {
			table_row.addClass( 'hidden' );
		} else {
			table_row.removeClass( 'hidden' );
		}
	}

	// If we should get options via Ajax.
	if ( is_ajax ) {
		sm_reset_option_value( element, ! table_row.hasClass( 'hidden' ) );

		// The GET parameters.
		let $_GET = sm_get_query_params( document.location.search );

		let data = {
			'action': 'sm_settings_get_select_data',
			'category': current_value,
			'option_id': table_row.find( 'select' ).attr( 'id' ),
			'podcast_id': $_GET[ 'post' ],
		};

		// Request element data.
		jQuery.ajax(
			{
				method: 'POST',
				url: ajaxurl,
				data: data,
			}
		).always(
			function () {
				sm_reset_option_value( element, ! table_row.hasClass( 'hidden' ) );
			}
		).done(
			function ( response ) {
				// Convert JSON to array/object.
				if ( 'false' === response ) {
					response = false;
				} else {
					try {
						response = JSON.parse( response );
					} catch ( err ) {
						response = false;
					}
				}

				// Write received values to element.
				if ( typeof response === 'object' ) {
					table_row.removeClass( 'hidden' );

					switch ( element.prop( 'tagName' ) ) {
						case 'SELECT':
							element.find( 'option' ).each(
								function () {
									jQuery( this.remove() );
								}
							);

							jQuery.each(
								response.options,
								function ( id, item ) {
									table_row.find( 'select' ).append( jQuery( '<option/>' ).val( id ).text( item ) );
								}
							);

							if ( response.selected ) {
								table_row.find( 'select' ).val( response.selected ).change();
							} else {
								table_row.find( 'select' ).prop( "selectedIndex", 0 );
							}

							if ( Object.keys( response.options ).length === 0 ) {
								table_row.addClass( 'hidden' );
								return;
							}
							break;
					}
				}

				if ( false === response ) {
					table_row.addClass( 'hidden' );
				}
			}
		).fail(
			function () {
				// Write error message if response is invalid.
				switch ( element.prop( 'tabName' ) ) {
					case 'SELECT':
						element.append( jQuery( '<option/>' ).val( '' ).text( 'Error.' ) );
						break;
				}
			}
		);
	}
}

/**
 * Clears the current option value.
 *
 * @type {object} The option element.
 * @type {boolean} If we should show that it's loading.
 */
function sm_reset_option_value( element, write_loading ) {
	switch ( element.prop( 'tagName' ) ) {
		case 'SELECT':
			element.find( 'option' ).each(
				function () {
					jQuery( this.remove() );
				}
			);
	}
	if ( write_loading ) {
		element.append( jQuery( '<option/>' ).val( '' ).text( 'Loading...' ) );
	}
}

/**
 * Checks to see if a value is set.
 *
 * @param {*} value The value to check.
 */
function sm_isset( value ) {
	try {
		return typeof value !== 'undefined'
	} catch ( e ) {
		return false
	}
}

/**
 * Gets query parameters from the URL (GET for example).
 *
 * @param {string} qs Query string.
 */
function sm_get_query_params( qs ) {
	qs         = qs.split( "+" ).join( " " );
	var params = {},
		tokens,
		re     = /[?&]?([^=]+)=([^&]*)/g;

	while ( tokens = re.exec( qs ) ) {
		params[ decodeURIComponent( tokens[ 1 ] ) ]
			= decodeURIComponent( tokens[ 2 ] );
	}

	return params;
}
