<?php
/**
 * Most of Sermon Manager Settings related functions.
 *
 * @package SM/Core/Admin/Settings
 */

defined( 'ABSPATH' ) or die;

/**
 * New settings page
 *
 * @since 2.9
 */
class SM_Admin_Settings {
	/**
	 * Setting pages
	 *
	 * @var array
	 */
	private static $settings = array();

	/**
	 * Error messages
	 *
	 * @var array
	 */
	private static $errors = array();

	/**
	 * Update messages
	 *
	 * @var array
	 */
	private static $messages = array();

	/**
	 * Output messages + errors.
	 *
	 * @return void
	 */
	public static function show_messages() {
		if ( sizeof( self::$errors ) > 0 ) {
			foreach ( self::$errors as $error ) {
				echo '<div id="message" class="error inline"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
			}
		} elseif ( sizeof( self::$messages ) > 0 ) {
			foreach ( self::$messages as $message ) {
				echo '<div id="message" class="updated inline"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
			}
		}
	}

	/**
	 * Settings page.
	 *
	 * Handles the display of the main Sermon Manager settings page in admin.
	 */
	public static function output() {
		global $current_section, $current_tab;

		do_action( 'sm_settings_start' );

		wp_enqueue_media();
		wp_enqueue_script( 'sm_settings', SM_URL . 'assets/js/admin/settings' . ( ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) ? '' : '.min' ) . '.js', array(
			'jquery',
			'jquery-ui-datepicker',
			'jquery-ui-sortable',
		), SM_VERSION, true );

		wp_register_script( 'sm_settings_podcast', SM_URL . 'assets/js/admin/settings/podcast' . ( ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) ? '' : '.min' ) . '.js', 'sm_settings', SM_VERSION, true );
		wp_register_script( 'sm_settings_verse', SM_URL . 'assets/js/admin/settings/verse' . ( ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) ? '' : '.min' ) . '.js', 'sm_settings', SM_VERSION, true );

		wp_localize_script( 'sm_settings', 'sm_settings_params', array(
			'i18n_nav_warning'        => __( 'The changes you made will be lost if you navigate away from this page.', 'sermon-manager-for-wordpress' ),
			'i18n_bible_spanish_note' => __( 'Note: WordPress is not set to any Spanish variant. Reverted to ESV.', 'sermon-manager-for-wordpress' ),
			'is_wp_spanish'           => strpos( get_locale(), 'es_' ) !== false,
		) );

		// Include settings pages.
		self::get_settings_pages();

		// Get current tab/section.
		$current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( $_GET['tab'] );
		$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( $_REQUEST['section'] );

		// Save settings if data has been posted.
		if ( ! empty( $_POST ) ) {
			self::save();
		}

		// Add any posted messages.
		if ( ! empty( $_GET['sm_error'] ) ) {
			self::add_error( stripslashes( $_GET['sm_error'] ) );
		}

		if ( ! empty( $_GET['sm_message'] ) ) {
			self::add_message( stripslashes( $_GET['sm_message'] ) );
		}

		switch ( $current_tab ) {
			case 'podcast':
				wp_enqueue_script( 'sm_settings_podcast' ); // todo: i18n the script & make it more dynamic.
				wp_enqueue_media();
				break;
			case 'verse':
				wp_enqueue_script( 'sm_settings_verse' );
				break;
		}

		// Get tabs for the settings page.
		/* @noinspection PhpUnusedLocalVariableInspection */
		$tabs = apply_filters( 'sm_settings_tabs_array', array() );

		include 'views/html-admin-settings.php';
	}

	/**
	 * Include the settings page classes.
	 */
	public static function get_settings_pages() {
		if ( empty( self::$settings ) ) {
			$settings = array();

			include_once 'settings/class-sm-settings-page.php';

			$settings[] = include 'settings/class-sm-settings-general.php';
			$settings[] = include 'settings/class-sm-settings-display.php';
			$settings[] = include 'settings/class-sm-settings-podcast.php';
			$settings[] = include 'settings/class-sm-settings-verse.php';
			$settings[] = include 'settings/class-sm-settings-debug.php';

			self::$settings = apply_filters( 'sm_get_settings_pages', $settings );
		}

		return self::$settings;
	}

	/**
	 * Save the settings.
	 */
	public static function save() {
		global $current_tab, $wpdb;

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'sm-settings' ) ) {
			die( __( 'Action failed. Please refresh the page and retry.', 'sermon-manager-for-wordpress' ) );
		}

		/**
		 * Flush rewrite rules on archive page slug change.
		 */
		if ( 'general' === $current_tab && SermonManager::getOption( 'archive_slug' ) !== $_POST['archive_slug'] ) {
			flush_rewrite_rules( true );
		}

		// Trigger actions.
		do_action( 'sm_settings_save_' . $current_tab );
		do_action( 'sn_update_options_' . $current_tab );
		do_action( 'sm_update_options' );

		self::add_message( __( 'Your settings have been saved.', 'sermon-manager-for-wordpress' ) );

		// Clear any unwanted data and flush rules.
		wp_schedule_single_event( time(), 'sm_flush_rewrite_rules' );

		/**
		 * Pass any false value to `sm_clear_feed_transients` filter to skip clearing transients.
		 */
		if ( 'podcast' === $current_tab && apply_filters( 'sm_clear_feed_transients', true ) ) {
			/* @noinspection SqlNoDataSourceInspection */

			/* @noinspection SqlResolve */
			$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_feed_%') OR `option_name` LIKE ('_transient_timeout_feed_%')" );
		}

		do_action( 'sm_settings_saved' );
	}

	/**
	 * Add a message.
	 *
	 * @param string $text The message to add.
	 */
	public static function add_message( $text ) {
		self::$messages[] = $text;
	}

	/**
	 * Add an error.
	 *
	 * @param string $text The error to add.
	 */
	public static function add_error( $text ) {
		self::$errors[] = $text;
	}

	/**
	 * Output admin fields.
	 *
	 * Loops though the Sermon Manager options array and outputs each field.
	 *
	 * @param array[] $options Opens array to output.
	 * @param array   $values  The array of custom values. Optional.
	 */
	public static function output_fields( $options, $values = array() ) {
		$display_conditions = array();

		foreach ( $options as $option ) {
			if ( ! isset( $option['type'] ) ) {
				continue;
			}

			// Fill out data that is not set.
			$option += array(
				'id'          => '',
				'title'       => isset( $option['name'] ) ? $option['name'] : '',
				'class'       => '',
				'css'         => '',
				'default'     => '',
				'desc'        => '',
				'desc_tip'    => '',
				'placeholder' => '',
				'size'        => '',
				'disabled'    => false,
				'display_if'  => array(),
				'ajax'        => false,
			);

			// Get conditional display.
			if ( ! empty( $option['display_if'] ) ) {
				$display_conditions[ $option['id'] ]   = isset( $display_conditions[ $option['id'] ] ) ? $display_conditions[ $option['id'] ] : array();
				$display_conditions[ $option['id'] ][] = $option['display_if'];
			}

			// Custom attribute handling.
			$custom_attributes = array();

			if ( ! empty( $option['custom_attributes'] ) ) {
				if ( is_array( $option['custom_attributes'] ) ) {
					foreach ( $option['custom_attributes'] as $attribute => $attribute_value ) {
						$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
					}
				} elseif ( is_string( $option['custom_attributes'] ) ) {
					$custom_attributes[] = $option['custom_attributes'];
				}
			}

			if ( $option['ajax'] ) {
				$custom_attributes[] = 'data-ajax="true"';
			}

			// Get descriptions.
			$field_description = self::get_field_description( $option );
			$description       = $field_description['description'];
			$tooltip_html      = $field_description['tooltip_html'];

			// Execute a function to get the options in (multi)select if it's specified.
			if ( isset( $option['options'] ) ) {
				$option['options'] = self::_maybe_populate_options( $option['options'] );
			}

			// Get the value.
			if ( empty( $values ) ) {
				$option_value = self::get_option( $option['id'], $option['default'] );
			} else {
				$option_value = empty( $values[ $option['id'] ] ) ? $option['default'] : $values[ $option['id'] ];
			}

			// Output the field based on type.
			switch ( $option['type'] ) {
				// Section Titles.
				case 'title':
					if ( ! empty( $option['title'] ) ) {
						echo '<h2 class="forminp-title">' . esc_html( $option['title'] ) . '</h2>';
					}
					if ( ! empty( $option['desc'] ) ) {
						echo wpautop( wptexturize( wp_kses_post( $option['desc'] ) ) );
					}
					echo '<table class="form-table">' . "\n\n";
					if ( ! empty( $option['id'] ) ) {
						do_action( 'sm_settings_' . sanitize_title( $option['id'] ) );
					}
					break;

				// Section Ends.
				case 'sectionend':
					if ( ! empty( $option['id'] ) ) {
						do_action( 'sm_settings_' . sanitize_title( $option['id'] ) . '_end' );
					}
					echo '</table>';
					if ( ! empty( $option['id'] ) ) {
						do_action( 'sm_settings_' . sanitize_title( $option['id'] ) . '_after' );
					}
					break;

				// Standard text inputs and subtypes like 'number'.
				case 'text':
				case 'email':
				case 'number':
				case 'password':
					if ( substr( $option['id'], 0, 2 ) === '__' && strlen( $option['id'] ) > 2 ) {
						$option_value = $option['value'];
					}

					?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $option['id'] ); ?>"><?php echo esc_html( $option['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $option['type'] ); ?>">
							<input
									name="<?php echo esc_attr( $option['id'] ); ?>"
									id="<?php echo esc_attr( $option['id'] ); ?>"
									type="<?php echo esc_attr( $option['type'] ); ?>"
									style="<?php echo esc_attr( $option['css'] ); ?>"
									value="<?php echo esc_attr( $option_value ); ?>"
									class="<?php echo esc_attr( $option['class'] ); ?>"
									placeholder="<?php echo esc_attr( $option['placeholder'] ); ?>"
									size="<?php echo esc_attr( $option['size'] ); ?>"
								<?php if ( $option['disabled'] ) : ?>
									disabled="disabled"
								<?php endif; ?>
								<?php echo implode( ' ', $custom_attributes ); ?>
							/> <?php echo $description; ?>
						</td>
					</tr>
					<?php
					break;

				// Color picker.
				case 'color':
					?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $option['id'] ); ?>"><?php echo esc_html( $option['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $option['type'] ); ?>">&lrm;
							<span class="colorpickpreview"
									style="background: <?php echo esc_attr( $option_value ); ?>"></span>
							<input
									name="<?php echo esc_attr( $option['id'] ); ?>"
									id="<?php echo esc_attr( $option['id'] ); ?>"
									type="text"
									dir="ltr"
									style="<?php echo esc_attr( $option['css'] ); ?>"
									value="<?php echo esc_attr( $option_value ); ?>"
									class="<?php echo esc_attr( $option['class'] ); ?>colorpick"
									placeholder="<?php echo esc_attr( $option['placeholder'] ); ?>"
								<?php if ( $option['disabled'] ) : ?>
									disabled="disabled"
								<?php endif; ?>
								<?php echo implode( ' ', $custom_attributes ); ?>
							/>&lrm; <?php echo $description; ?>
							<div id="colorPickerDiv_<?php echo esc_attr( $option['id'] ); ?>" class="colorpickdiv"
									style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;display:none;"></div>
						</td>
					</tr>
					<?php
					break;

				// Textarea.
				case 'textarea':
					?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $option['id'] ); ?>"><?php echo esc_html( $option['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $option['type'] ); ?>">
							<?php echo $description; ?>

							<textarea
									name="<?php echo esc_attr( $option['id'] ); ?>"
									id="<?php echo esc_attr( $option['id'] ); ?>"
									style="<?php echo esc_attr( $option['css'] ); ?>"
									class="<?php echo esc_attr( $option['class'] ); ?>"
									placeholder="<?php echo esc_attr( $option['placeholder'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								<?php if ( $option['disabled'] ) : ?>
									disabled="disabled"
								<?php endif; ?>
							><?php echo esc_textarea( $option_value ); ?></textarea>
						</td>
					</tr>
					<?php
					break;

				// Select boxes.
				case 'select':
				case 'multiselect':
					?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $option['id'] ); ?>"><?php echo esc_html( $option['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $option['type'] ); ?>">
							<select
									name="<?php echo esc_attr( $option['id'] ); ?><?php echo ( 'multiselect' === $option['type'] ) ? '[]' : ''; ?>"
									id="<?php echo esc_attr( $option['id'] ); ?>"
									style="<?php echo esc_attr( $option['css'] ); ?>"
									class="<?php echo esc_attr( $option['class'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								<?php echo ( 'multiselect' == $option['type'] ) ? 'multiple="multiple"' : ''; ?>
								<?php if ( $option['disabled'] ) : ?>
									disabled="disabled"
								<?php endif; ?>
							>
								<?php
								foreach ( $option['options'] as $key => $val ) {
									?>
									<option value="<?php echo esc_attr( $key ); ?>"
										<?php

										if ( is_array( $option_value ) ) {
											selected( in_array( $key, $option_value ), true );
										} else {
											selected( $option_value, $key );
										}

										?>
									><?php echo $val; ?></option>
									<?php
								}
								?>
							</select> <?php echo $description; ?>
						</td>
					</tr>
					<?php
					break;

				// Radio inputs.
				case 'radio':
					?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $option['id'] ); ?>"><?php echo esc_html( $option['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $option['type'] ); ?>">
							<fieldset
								<?php if ( $option['disabled'] ) : ?>
									disabled="disabled"
								<?php endif; ?>
							>
								<?php echo $description; ?>
								<ul>
									<?php
									foreach ( $option['options'] as $key => $val ) {
										?>
										<li>
											<label>
												<input
														name="<?php echo esc_attr( $option['id'] ); ?>"
														value="<?php echo $key; ?>"
														type="radio"
														style="<?php echo esc_attr( $option['css'] ); ?>"
														class="<?php echo esc_attr( $option['class'] ); ?>"
													<?php echo implode( ' ', $custom_attributes ); ?>
													<?php checked( $key, $option_value ); ?>
												/> <?php echo $val; ?>
											</label>
										</li>
										<?php
									}
									?>
								</ul>
							</fieldset>
						</td>
					</tr>
					<?php
					break;

				// Checkbox input.
				case 'checkbox':
					$option_value = is_bool( $option_value ) ? ( $option_value ? 'yes' : 'no' ) : $option_value;

					$visbility_class = array();
					if ( ! isset( $option['hide_if_checked'] ) ) {
						$option['hide_if_checked'] = false;
					}
					if ( ! isset( $option['show_if_checked'] ) ) {
						$option['show_if_checked'] = false;
					}
					if ( 'yes' == $option['hide_if_checked'] || 'yes' == $option['show_if_checked'] ) {
						$visbility_class[] = 'hidden_option';
					}
					if ( 'option' == $option['hide_if_checked'] ) {
						$visbility_class[] = 'hide_options_if_checked';
					}
					if ( 'option' == $option['show_if_checked'] ) {
						$visbility_class[] = 'show_options_if_checked';
					}
					?>
					<tr valign="top" class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
						<!--suppress XmlDefaultAttributeValue -->
						<th scope="row" class="titledesc"><?php echo esc_html( $option['title'] ); ?></th>
						<td class="forminp forminp-checkbox">
							<fieldset
								<?php if ( $option['disabled'] ) : ?>
									disabled="disabled"
								<?php endif; ?>
							>
								<?php

								if ( ! empty( $option['title'] ) ) {
									?>
									<legend class="screen-reader-text">
										<span><?php echo esc_html( $option['title'] ); ?></span>
									</legend>
									<?php
								}
								?>
								<label for="<?php echo $option['id']; ?>">
									<input
											name="<?php echo esc_attr( $option['id'] ); ?>"
											id="<?php echo esc_attr( $option['id'] ); ?>"
											type="checkbox"
											class="<?php echo esc_attr( isset( $option['class'] ) ? $option['class'] : '' ); ?>"
											value="1"
										<?php checked( $option_value, 'yes' ); ?>
										<?php echo implode( ' ', $custom_attributes ); ?>
									/> <?php echo $description; ?>
								</label> <?php echo $tooltip_html; ?>
							</fieldset>
						</td>
					</tr>
					<?php
					break;

				// Image upload select.
				case 'image':
					?>
					<tr valign="top">
						<!--suppress XmlDefaultAttributeValue -->
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $option['id'] ); ?>"><?php echo esc_html( $option['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $option['type'] ); ?>">
							<div class="image-picker-form-container">
								<input
										name="<?php echo esc_attr( $option['id'] ); ?>"
										id="<?php echo esc_attr( $option['id'] ); ?>"
										type="text"
										style="<?php echo esc_attr( $option['css'] ); ?>"
										value="<?php echo esc_attr( $option_value ); ?>"
										class="<?php echo esc_attr( $option['class'] ); ?>"
										placeholder="<?php echo esc_attr( $option['placeholder'] ); ?>"
									<?php if ( $option['disabled'] ) : ?>
										disabled="disabled"
									<?php endif; ?>
									<?php echo implode( ' ', $custom_attributes ); ?>
								/>
								<a
										id="upload_<?php echo esc_attr( $option['id'] ); ?>"
										href="#"
										class="button upload-image"
										title="Choose Default Image">
									<img
											src="<?php echo admin_url( '/images/media-button.png' ); ?>"
											alt="Upload Default Image"
											width="15"
											height="15"
											class="upload_image_button"
									/>
									&nbsp;Upload Image
								</a>
							</div>
							<?php echo $description; ?>
							<div id="default-image-thumb-load" style="width: 250px;">
								<br/>
								<?php if ( ! empty( $option_value ) ) : ?>
									<img style="width: inherit;"
											src="<?php echo esc_attr( $option_value ); ?>"
											alt="default image"
									/>
								<?php endif; ?>
							</div>
						</td>
					</tr>
					<?php
					break;
				case 'description':
					?>
					<tr valign="top">
						<td class="forminp forminp-<?php echo sanitize_title( $option['type'] ); ?>" colspan="2">
							<p><?php echo $option['desc']; ?></p>
						</td>
					</tr>
					<?php
					break;
				case 'separator':
					?>
					<tr valign="top">
						<td class="forminp forminp-<?php echo sanitize_title( $option['type'] ); ?>" colspan="2">
							<hr/>
						</td>
					</tr>
					<?php
					break;
				case 'separator_title':
					?>
					<tr valign="top">
						<td class="forminp forminp-<?php echo sanitize_title( $option['type'] ); ?>" colspan="2">
							<h2><?php echo esc_html( $option['title'] ); ?></h2>
						</td>
					</tr>
					<?php
					break;
				// Default: run an action.
				default:
					/**
					 * Allows to add additional settings type.
					 *
					 * @param array  $option             The option data.
					 * @param mixed  $option_value      The option value.
					 * @param string $description       The option description HTML.
					 * @param string $tooltip_html      The option tooltip HTML.
					 * @param array  $custom_attributes The custom attributes.
					 *
					 * @since 2.9 - Added.
					 * @since 2.15.6 - Added additional options, beside `$value`.
					 */
					do_action( 'sm_admin_field_' . $option['type'], $option, $option_value, $description, $tooltip_html, $custom_attributes );
					break;
			}
		}

		// Load conditionals script.
		wp_register_script( 'sm_settings_conditionals', SM_URL . 'assets/js/admin/settings/conditionals' . ( ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) ? '' : '.min' ) . '.js', 'sm_settings', SM_VERSION, true );
		wp_localize_script( 'sm_settings_conditionals', 'sm_conditionals', $display_conditions );
		wp_enqueue_script( 'sm_settings_conditionals' );
	}

	/**
	 * Helper function to get the formatted description and tip HTML for a
	 * given form field
	 *
	 * @param  array $option The option array.
	 *
	 * @return array The description and tip as a 2 element array
	 */
	public static function get_field_description( $option ) {
		$description  = '';
		$tooltip_html = '';

		if ( true === $option['desc_tip'] ) {
			$tooltip_html = $option['desc'];
		} elseif ( ! empty( $option['desc_tip'] ) ) {
			$description  = $option['desc'];
			$tooltip_html = $option['desc_tip'];
		} elseif ( ! empty( $option['desc'] ) ) {
			$description = $option['desc'];
		}

		if ( $description && in_array( $option['type'], array( 'textarea', 'radio' ) ) ) {
			$description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';
		} elseif ( $description && in_array( $option['type'], array( 'checkbox' ) ) ) {
			$description = wp_kses_post( $description );
		} elseif ( $description && in_array( $option['type'], array( 'select', 'multiselect' ) ) ) {
			$description = '<p class="description">' . $description . '</p>';
		} elseif ( $description ) {
			$description = '<p class="description">' . wp_kses_post( $description ) . '</p>';
		}

		if ( $tooltip_html && in_array( $option['type'], array( 'checkbox' ) ) ) {
			$tooltip_html = '<p class="description">' . $tooltip_html . '</p>';
		} elseif ( $tooltip_html ) {
			$tooltip_html = sm_help_tip( $tooltip_html );
		}

		return array(
			'description'  => $description,
			'tooltip_html' => $tooltip_html,
		);
	}

	/**
	 * Used to populate options field of select and multiselect fields. Calls a function if the value is string
	 * and function exists, otherwise returns the (maybe empty) array.
	 *
	 * @param array|string $options The options or the function.
	 *
	 * @return array The options.
	 */
	protected static function _maybe_populate_options( $options ) {
		$function = false;
		$args     = null;

		if ( is_string( $options ) ) {
			$function = $options;
		} elseif ( is_array( $options ) ) {
			if ( count( $options ) === 1 ) {
				$function = key( $options );
				$args     = $options[ $function ];

				if ( ! function_exists( $function ) ) {
					return $options;
				}
			} else {
				return $options;
			}
		}

		if ( $function && function_exists( $function ) ) {
			if ( is_array( $args ) ) {
				$options = call_user_func_array( $function, $args );
			} else {
				$options = call_user_func( $function );
			}

			if ( ! is_array( $options ) ) {
				$options = array();
			}

			if ( count( $options ) === 0 ) {
				$options = array( 0 => '-- ' . __( 'None' ) . ' --' ); // phpcs:ignore
			}
		} else {
			$options = array(
				0 => __( 'Error in populating field options.', 'sermon-manager-for-wordpress' ),
			);
		}

		return $options;
	}

	/**
	 * Get a setting from the settings API.
	 *
	 * @param string $option_name The option name.
	 * @param mixed  $default     The default value to return.
	 *
	 * @return mixed
	 */
	public static function get_option( $option_name, $default = '' ) {
		// Check if it is an array value.
		if ( strstr( $option_name, '[' ) ) {

			parse_str( $option_name, $option_array );

			// Option name is first key.
			$option_name = current( array_keys( $option_array ) );

			// Get value.
			$option_values = get_option( 'sermonmanager_' . $option_name, '' );

			$key = key( $option_array[ $option_name ] );

			if ( isset( $option_values[ $key ] ) ) {
				$option_value = $option_values[ $key ];
			} else {
				$option_value = null;
			}

			// Single value.
		} else {
			$option_value = get_option( 'sermonmanager_' . $option_name, null );
		}

		if ( is_array( $option_value ) ) {
			$option_value = array_map( 'stripslashes', $option_value );
		} elseif ( ! is_null( $option_value ) ) {
			$option_value = stripslashes( $option_value );
		}

		if ( 'no' === $option_value || 'yes' === $option_value ) {
			$option_value = 'yes' === $option_value ? true : false;
		}

		return ( null === $option_value ) ? $default : $option_value;
	}

	/**
	 * Save admin fields.
	 *
	 * Loops though the Sermon Manager options array and outputs each field.
	 *
	 * @param array $options Options array to output.
	 * @param array $data    Optional. Data to use for saving. Defaults to $_POST.
	 *
	 * @return bool
	 */
	public static function save_fields( $options, $data = null ) {
		if ( is_null( $data ) ) {
			$data = $_POST;
		}
		if ( empty( $data ) ) {
			return false;
		}

		// Options to update will be stored here and saved later.
		$update_options = array();

		// Loop options and get values to save.
		foreach ( $options as $option ) {
			if ( ! isset( $option['id'] ) || ! isset( $option['type'] ) ) {
				continue;
			}

			if ( substr( $option['id'], 0, 2 ) === '__' && strlen( $option['id'] ) > 2 ) {
				continue;
			}

			// Get posted value.
			if ( strstr( $option['id'], '[' ) ) {
				parse_str( $option['id'], $option_name_array );
				$option_name  = current( array_keys( $option_name_array ) );
				$setting_name = key( $option_name_array[ $option_name ] );
				$raw_value    = isset( $data[ $option_name ][ $setting_name ] ) ? wp_unslash( $data[ $option_name ][ $setting_name ] ) : null;
			} else {
				$option_name  = $option['id'];
				$setting_name = '';
				$raw_value    = isset( $data[ $option['id'] ] ) ? wp_unslash( $data[ $option['id'] ] ) : null;
			}

			// Format the value based on option type.
			switch ( $option['type'] ) {
				case 'checkbox':
					$value = '1' === $raw_value || 'yes' === $raw_value ? 'yes' : 'no';
					break;
				case 'textarea':
					$value = wp_kses_post( trim( $raw_value ) );
					break;
				case 'multiselect':
					$value = array_filter( array_map( 'sm_clean', (array) $raw_value ) );
					break;
				case 'select':
					$option['options'] = self::_maybe_populate_options( $option['options'] );

					$allowed_values = empty( $option['options'] ) ? array() : array_keys( $option['options'] );
					if ( empty( $option['default'] ) && empty( $allowed_values ) ) {
						$value = null;
						break;
					}
					$default = ( empty( $option['default'] ) ? $allowed_values[0] : $option['default'] );
					$value   = in_array( $raw_value, $allowed_values ) ? $raw_value : $default;
					break;
				default:
					$value = sm_clean( $raw_value );
					break;
			}

			/**
			 * Sanitize the value of an option.
			 *
			 * @since 2.9
			 */
			$value = apply_filters( 'sm_admin_settings_sanitize_option', $value, $option, $raw_value );

			/**
			 * Sanitize the value of an option by option name.
			 *
			 * @since 2.9
			 */
			$value = apply_filters( "sm_admin_settings_sanitize_option_$option_name", $value, $option, $raw_value );

			if ( is_null( $value ) ) {
				continue;
			}

			// Check if option is an array and handle that differently to single values.
			if ( $option_name && $setting_name ) {
				if ( ! isset( $update_options[ $option_name ] ) ) {
					$update_options[ $option_name ] = get_option( $option_name, array() );
				}
				if ( ! is_array( $update_options[ $option_name ] ) ) {
					$update_options[ $option_name ] = array();
				}
				$update_options[ $option_name ][ $setting_name ] = $value;
			} else {
				$update_options[ $option_name ] = $value;
			}
		}

		// Save all options in our array.
		foreach ( $update_options as $name => $value ) {
			update_option( 'sermonmanager_' . $name, $value );
		}

		return true;
	}
}
