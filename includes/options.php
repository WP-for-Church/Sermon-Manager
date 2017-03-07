<?php

/*
 * Sermon Manager Plugin Settings
 */

class Sermon_Manager_Settings {

	/* Construct */
	public function __construct() {
		// Set-up Action and Filter Hooks
		add_action( 'admin_init', array( $this, 'wpfc_init' ) );
		// Settings Menu Page
		add_action( 'admin_menu', array( $this, 'wpfc_add_options_page' ) );
		// Link for Settings on Plugin Page
		add_filter( 'plugin_action_links', array( $this, 'wpfc_plugin_action_links' ), 10, 2 );
		// Plugin Meta Links
		add_filter( 'plugin_row_meta', array( $this, 'wpfc_sermon_manager_plugin_row_meta' ), 10, 2 );
	}

	// Init plugin options to white list our options
	function wpfc_init() {
		global $wp_version;

		$args = 'wpfc_validate_options';

		if ( version_compare( $wp_version, '4.7.0', '>=' ) ) {
			$args = array(
				'sanitize_callback' => 'wpfc_validate_options'
			);
		}

		register_setting( 'wpfc_plugin_options', 'wpfc_options', $args );
	}

	// Add menu page
	function wpfc_add_options_page() {
		$page = add_submenu_page( 'edit.php?post_type=wpfc_sermon', __( 'Sermon Manager Settings', 'sermon-manager' ), __( 'Settings', 'sermon-manager' ), 'manage_options', __FILE__, array(
			$this,
			'wpfc_sermon_options_render_form'
		) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'wpfc_sermon_admin_styles' ) );
	}

	// Plugin Meta Links.
	function wpfc_sermon_manager_plugin_row_meta( $links, $file ) {
		static $plugin_name = '';

		if ( empty( $plugin_name ) ) {
			$plugin_name = plugin_basename( __FILE__ );
		}

		if ( $plugin_name != $file ) {
			return $links;
		}

		$link = wpfc_sermon_manager_settings_page_link( __( 'Settings', 'sermon-manager' ) );
		if ( ! empty( $link ) ) {
			$links[] = $link;
		}

		$links[] = '<a href="http://www.wpforchurch.com/support/" target="_blank">' . __( 'Support', 'sermon-manager' ) . '</a>';

		return $links;
	}

	// Settings Page Link.
	function wpfc_sermon_manager_settings_page_link( $link_text = '' ) {
		if ( empty( $link_text ) ) {
			$link_text = __( 'Manage Settings', 'sermon-manager' );
		}

		$link = '';
		if ( current_user_can( 'manage_options' ) ) {
			$link = '<a href="' . admin_url( 'edit.php?post_type=wpfc_sermon&page=sermon-manager-for-wordpress/includes/options.php' ) . '">' . esc_html( $link_text ) . '</a>';
		}

		return $link;
	}

	// Add scripts
	function wpfc_sermon_admin_styles() {
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
	}

	// Render the Plugin options form
	function wpfc_sermon_options_render_form() {
		if ( ! isset( $_REQUEST['settings-updated'] ) ) {
			$_REQUEST['settings-updated'] = false;
		}
		?>
		<div class="wrap">
			<script type="text/javascript">
				jQuery( document ).ready( function ()
				{
					jQuery( '.sermon-option-tabs' ).tabs();
					jQuery( '#upload_cover_image' ).click( function ()
					{
						uploadID = jQuery( this ).prev( 'input' );
						tb_show( '', 'media-upload.php?type=image&amp;TB_iframe=true' );
						return false;
					} );
					window.send_to_editor = function ( html )
					{
						imgurl = jQuery( 'img', html ).attr( 'src' );
						uploadID.val( imgurl );
						/*assign the value to the input*/
						tb_remove();
					};
				} );
			</script>
			<style type="text/css">
				.sermon-option-tabs .ui-tabs-nav li {
					display: inline;
				}

				.sermon-option-tabs .ui-tabs-nav {
					margin-top: 0;
					margin-bottom: 0;
				}

				.ui-tabs-active a {
					background: #ffffff
				}
			</style>
			<?php $sermon_settings = get_option( 'wpfc_options' );
			$sermon_version        = isset( $sermon_settings['version'] ) ? $sermon_settings['version'] : '';
			//echo '<pre>'.$sermon_version.'</pre>';
			if ( $sermon_version < '1.8' ):
				$Sermon_Manager_Upgrade = new Sermon_Manager_Upgrade();
				$Sermon_Manager_Upgrade->wpfc_sermon_update();
			endif; ?>
			<!-- Display Plugin Icon, Header, and Description -->
			<div class="sermon-option-tabs">
				<div class="icon32" id="icon-options-general"><br></div>
				<h2><?php _e( 'Sermon Manager Options', 'sermon-manager' ); ?></h2>
				<?php //echo '<pre>'; print_r($sermon_settings); echo '</pre>'; ?>

				<h2 class="nav-tab-wrapper">
					<ul class="ui-tabs-nav">
						<li><a id="sermon-general" class="nav-tab"
						       href="#sermon-options-general"><?php _e( 'General', 'sermon-manager' ); ?></a></li>
						<li><a id="sermon-verse" class="nav-tab"
						       href="#sermon-options-verse"><?php _e( 'Verse', 'sermon-manager' ); ?></a></li>
						<li><a id="sermon-podcast" class="nav-tab"
						       href="#sermon-options-podcast"><?php _e( 'Podcast', 'sermon-manager' ); ?></a></li>
						<?php do_action( 'wpfc_settings_form_tabs' ); ?>
					</ul>
				</h2>

				<?php if ( false !== $_REQUEST['settings-updated'] ) : ?>
					<div class="updated fade"><p><strong><?php _e( 'Options saved', 'sermon-manager' ); ?></strong></p>
					</div>
				<?php endif; ?>

				<div class="metabox-holder has-right-sidebar">

					<div class="inner-sidebar">

						<div class="postbox">
							<h3><span><?php _e( 'Need Help?', 'sermon-manager' ); ?></span></h3>
							<div class="inside">
								<p><?php _e( 'If you need help, please visit <a href="http://www.wpforchurch.com/" target="_blank">WP for Church</a>', 'sermon-manager' ); ?></p>
							</div>
						</div>

					</div> <!-- .inner-sidebar -->

					<div id="post-body">
						<div id="post-body-content">
							<form method="post" action="options.php">
								<?php settings_fields( 'wpfc_plugin_options' ); ?>
								<?php $options = get_option( 'wpfc_options' ); ?>

								<div class="postbox tab-content" id="sermon-options-general">
									<h3><span><?php _e( 'General Settings', 'sermon-manager' ); ?></span></h3>
									<div class="inside">
										<table class="form-table">
											<tr valign="top">
												<th scope="row"><?php _e( 'Archive Page Title', 'sermon-manager' ); ?></th>
												<td>
													<input type="text" size="65" name="wpfc_options[archive_title]"
													       value="<?php echo empty( $options['archive_title'] ) ? '' : $options['archive_title']; ?>"/>
												</td>
											</tr>
											<!-- Slug -->
											<tr valign="top">
												<th scope="row"><?php _e( 'Archive Page Slug', 'sermon-manager' ); ?></th>
												<td>
													<input type="text" size="65" name="wpfc_options[archive_slug]"
													       value="<?php echo empty( $options['archive_slug'] ) ? '' : $options['archive_slug']; ?>"/>
												</td>
											</tr>
											<!-- Common Slug -->
											<tr valign="top">
												<th scope="row"><?php _e( 'Common Base Slug - this is for users who want to have a common base slug across all taxonomies, e.g. sermons/preacher or sermons/series.', 'sermon-manager' ); ?></th>
												<td>
													<label><input name="wpfc_options[common_base_slug]" type="checkbox"
													              value="1" <?php if ( isset( $options['common_base_slug'] ) ) {
															checked( '1', $options['common_base_slug'] );
														} ?> /> <?php _e( 'Enable a common base slug across all taxonomies', 'sermon-manager' ); ?>
													</label>
												</td>
											</tr>
											<!-- Enable Template Files -->
											<tr valign="top">
												<th scope="row"><?php _e( 'Enable Template Files - this is for users upgrading from an older version who have issues with version 1.5+.', 'sermon-manager' ); ?></th>
												<td>
													<label><input name="wpfc_options[template]" type="checkbox"
													              value="1" <?php if ( isset( $options['template'] ) ) {
															checked( '1', $options['template'] );
														} ?> /> <?php _e( 'Enable template files found in the /views folder', 'sermon-manager' ); ?>
													</label><br/>
												</td>
											</tr>
											<!-- Disable Sermon Styles -->
											<tr valign="top">
												<th scope="row"><?php _e( 'Disable Sermon Styles', 'sermon-manager' ); ?></th>
												<td>
													<label><input name="wpfc_options[css]" type="checkbox"
													              value="1" <?php if ( isset( $options['css'] ) ) {
															checked( '1', $options['css'] );
														} ?> /> <?php _e( 'Disable Sermon CSS. If you do this, you should copy the styles from sermons.css and include them in your theme CSS.', 'sermon-manager' ); ?>
													</label><br/>
												</td>
											</tr>
											<!-- Display player on archive -->
											<tr valign="top">
												<th scope="row"><?php _e( 'Display audio player or video on archive pages', 'sermon-manager' ); ?></th>
												<td>
													<label><input name="wpfc_options[archive_player]" type="checkbox"
													              value="1" <?php if ( isset( $options['archive_player'] ) ) {
															checked( '1', $options['archive_player'] );
														} ?> /> <?php _e( 'Display an audio player or video embed in the archive listing.', 'sermon-manager' ); ?>
													</label><br/>
												</td>
											</tr>
											<!-- Plugin Version - Hidden field -->
											<tr valign="top" style="display:none;">
												<th scope="row"><?php _e( 'Version ', 'sermon-manager' ); ?><?php echo $options['version']; ?></th>
												<td>
													<input type="text" size="65" name="wpfc_options[version]"
													       value="<?php echo SERMON_MANAGER_VERSION; ?>"/>
													<span
														style="color:#666666;margin-left:2px;"><?php _e( 'Current Version', 'sermon-manager' ); ?></span>
												</td>
											</tr>
										</table>
									</div> <!-- .inside -->
								</div>

								<div class="postbox" id="sermon-options-verse" class="tab-content">
									<h3><span><?php _e( 'Verse Settings', 'sermon-manager' ); ?></span></h3>
									<div class="inside">
										<table class="form-table">
											<!-- Enable Bib.ly -->
											<tr valign="top">
												<th scope="row"><?php _e( 'Verse Popups', 'sermon-manager' ); ?></th>
												<td>
													<!-- Bibly -->
													<label><input name="wpfc_options[bibly]" type="checkbox"
													              value="1" <?php if ( isset( $options['bibly'] ) ) {
															checked( '1', $options['bibly'] );
														} ?> /> <?php _e( 'Disable Bib.ly verse popups', 'sermon-manager' ); ?>
													</label><br/>
												</td>
											</tr>
											<!-- Select Bible Version -->
											<tr>
												<th scope="row"><?php _e( 'Select Bible Version for Verse Popups', 'sermon-manager' ); ?></th>
												<td>
													<select name='wpfc_options[bibly_version]'>
														<!-- ESV, NET, KJV, or LEB are the currently supported popups. -->
														<option
															value='KJV' <?php selected( 'KJV', $options['bibly_version'] ); ?>>
															KJV
														</option>
														<option
															value='ESV' <?php selected( 'ESV', $options['bibly_version'] ); ?>>
															ESV
														</option>
														<option
															value='NET' <?php selected( 'NET', $options['bibly_version'] ); ?>>
															NET
														</option>
														<option
															value='LEB' <?php selected( 'LEB', $options['bibly_version'] ); ?>>
															LEB
														</option>
													</select>
													<span
														style="color:#666666;margin-left:2px;"><?php _e( 'ESV, NET, KJV, or LEB are the currently supported popups for <a href="http://bib.ly">bib.ly</a>.', 'sermon-manager' ); ?></span>
												</td>
											</tr>
										</table>
									</div> <!-- .inside -->
								</div>

								<div class="postbox tab-content" id="sermon-options-podcast">
									<h3><span><?php _e( 'Podcast Settings', 'sermon-manager' ); ?></span></h3>
									<div class="inside">
										<?php
										/* set variables from $option */
										$title               = isset( $options['title'] ) ? $options['title'] : '';
										$description         = isset( $options['description'] ) ? $options['description'] : '';
										$website_link        = isset( $options['website_link'] ) ? $options['website_link'] : '';
										$language            = isset( $options['language'] ) ? $options['language'] : '';
										$copyright           = isset( $options['copyright'] ) ? $options['copyright'] : '';
										$webmaster_name      = isset( $options['webmaster_name'] ) ? $options['webmaster_name'] : '';
										$webmaster_email     = isset( $options['webmaster_email'] ) ? $options['webmaster_email'] : '';
										$itunes_author       = isset( $options['itunes_author'] ) ? $options['itunes_author'] : '';
										$itunes_subtitle     = isset( $options['itunes_subtitle'] ) ? $options['itunes_subtitle'] : '';
										$itunes_summary      = isset( $options['itunes_summary'] ) ? $options['itunes_summary'] : '';
										$itunes_owner_name   = isset( $options['itunes_owner_name'] ) ? $options['itunes_owner_name'] : '';
										$itunes_owner_email  = isset( $options['itunes_owner_email'] ) ? $options['itunes_owner_email'] : '';
										$itunes_cover_image  = isset( $options['itunes_cover_image'] ) ? $options['itunes_cover_image'] : '';
										$itunes_top_category = isset( $options['itunes_top_category'] ) ? $options['itunes_top_category'] : '';
										$itunes_sub_category = isset( $options['itunes_sub_category'] ) ? $options['itunes_sub_category'] : '';
										?>
										<table class="form-table">
											<tr>
												<th scope="row"><?php _e( 'Title', 'sermon-manager' ); ?></th>
												<td class="option" colspan="2">
													<input id="wpfc_options[title]" type="text" size="65"
													       name="wpfc_options[title]"
													       placeholder="<?php _e( 'e.g. ' . get_bloginfo( 'name' ), 'sermon-manager' ); ?>"
													       value="<?php esc_attr_e( $title ); ?>"/>
												</td>
											</tr>

											<tr>
												<th scope="row"><?php _e( 'Description', 'sermon-manager' ); ?></th>
												<td class="option" colspan="2">
													<input id="wpfc_options[description]" type="text" size="65"
													       name="wpfc_options[description]"
													       placeholder="<?php _e( 'e.g. ' . get_bloginfo( 'description' ), 'sermon-manager' ); ?>"
													       value="<?php esc_attr_e( $description ); ?>"/>
												</td>
											</tr>

											<tr>
												<th scope="row"><?php _e( 'Website Link', 'sermon-manager' ); ?></th>
												<td class="option" colspan="2">
													<input id="wpfc_options[website_link]" type="text" size="65"
													       name="wpfc_options[website_link]"
													       placeholder="<?php _e( 'e.g. ' . home_url(), 'sermon-manager' ); ?>"
													       value="<?php esc_attr_e( $website_link ); ?>"/>
												</td>
											</tr>

											<tr>
												<th scope="row"><?php _e( 'Language', 'sermon-manager' ); ?></th>
												<td class="option" colspan="2">
													<input id="wpfc_options[language]" type="text" size="65"
													       name="wpfc_options[language]"
													       placeholder="<?php _e( 'e.g. ' . get_bloginfo( 'language' ), 'sermon-manager' ); ?>"
													       value="<?php esc_attr_e( $language ); ?>"/>
												</td>
											</tr>

											<tr>
												<th scope="row"><?php _e( 'Copyright', 'sermon-manager' ); ?></th>
												<td class="option">
													<input id="wpfc_options[copyright]" type="text" size="65"
													       name="wpfc_options[copyright]"
													       placeholder="<?php _e( 'e.g. Copyright &copy; ' . get_bloginfo( 'name' ), 'sermon-manager' ); ?>"
													       value="<?php esc_attr_e( $copyright ); ?>"/>
												</td>
												<td class="info">
													<p>
														<em><?php _e( 'Tip: Use "' . htmlspecialchars( '&copy;' ) . '" to generate a copyright symbol.', 'sermon-manager' ); ?></em>
													</p>
												</td>
											</tr>

											<tr>
												<th scope="row"><?php _e( 'Webmaster Name', 'sermon-manager' ); ?></th>
												<td class="option" colspan="2">
													<input id="wpfc_options[webmaster_name]" type="text" size="65"
													       name="wpfc_options[webmaster_name]"
													       placeholder="<?php _e( 'e.g. Your Name', 'sermon-manager' ); ?>"
													       value="<?php esc_attr_e( $webmaster_name ); ?>"/>
												</td>
											</tr>

											<tr>
												<th scope="row"><?php _e( 'Webmaster Email', 'sermon-manager' ); ?></th>
												<td class="option" colspan="2">
													<input id="wpfc_options[webmaster_email]" type="text" size="65"
													       name="wpfc_options[webmaster_email]"
													       placeholder="<?php _e( 'e.g. ' . get_bloginfo( 'admin_email' ), 'sermon-manager' ); ?>"
													       value="<?php esc_attr_e( $webmaster_email ); ?>"/>
												</td>
											</tr>

											<tr>
												<th scope="row"><?php _e( 'Author', 'sermon-manager' ); ?></th>
												<td class="option">
													<input id="wpfc_options[itunes_author]" type="text" size="65"
													       name="wpfc_options[itunes_author]"
													       placeholder="<?php _e( 'e.g. Primary Speaker or Church Name', 'sermon-manager' ); ?>"
													       value="<?php esc_attr_e( $itunes_author ); ?>"/>
												</td>
												<td class="info">
													<p><?php _e( 'This will display at the "Artist" in the iTunes Store.', 'sermon-manager' ); ?></p>
												</td>
											</tr>

											<tr>
												<th scope="row"><?php _e( 'Subtitle', 'sermon-manager' ); ?></th>
												<td class="option">
													<input id="wpfc_options[itunes_subtitle]" type="text" size="65"
													       name="wpfc_options[itunes_subtitle]"
													       placeholder="<?php _e( 'e.g. Preaching and teaching audio from ' . get_bloginfo( 'name' ), 'sermon-manager' ); ?>"
													       value="<?php esc_attr_e( $itunes_subtitle ); ?>"/>
												</td>
												<td class="info">
													<p><?php _e( 'Your subtitle should briefly tell the listener what they can expect to hear.', 'sermon-manager' ); ?></p>
												</td>
											</tr>

											<tr>
												<th scope="row"><?php _e( 'Summary', 'sermon-manager' ); ?></th>
												<td class="option">
													<textarea id="wpfc_options[itunes_summary]" class="large-text"
													          cols="65" rows="5" name="wpfc_options[itunes_summary]"
													          placeholder="<?php _e( 'e.g. Weekly teaching audio brought to you by ' . get_bloginfo( 'name' ) . ' in City, State.', 'sermon-manager' ); ?>"><?php echo esc_textarea( $itunes_summary ); ?></textarea>
												</td>
												<td class="info">
													<p><?php _e( 'Keep your Podcast Summary short, sweet and informative. Be sure to include a brief statement about your mission and in what region your audio content originates.', 'sermon-manager' ); ?></p>
												</td>
											</tr>

											<tr>
												<th scope="row"><?php _e( 'Owner Name', 'sermon-manager' ); ?></th>
												<td class="option">
													<input id="wpfc_options[itunes_owner_name]" type="text" size="65"
													       name="wpfc_options[itunes_owner_name]"
													       placeholder="<?php _e( 'e.g. ' . get_bloginfo( 'name' ), 'sermon-manager' ); ?>"
													       value="<?php esc_attr_e( $itunes_owner_name ); ?>"/>
												</td>
												<td class="info">
													<p><?php _e( 'This should typically be the name of your Church.', 'sermon-manager' ); ?></p>
												</td>
											</tr>

											<tr>
												<th scope="row"><?php _e( 'Owner Email', 'sermon-manager' ); ?></th>
												<td class="option">
													<input id="wpfc_options[itunes_owner_email]" type="text" size="65"
													       name="wpfc_options[itunes_owner_email]"
													       placeholder="<?php _e( 'e.g. ' . get_bloginfo( 'admin_email' ), 'sermon-manager' ); ?>"
													       value="<?php esc_attr_e( $itunes_owner_email ); ?>"/>
												</td>
												<td class="info">
													<p><?php _e( 'Use an email address that you don\'t mind being made public. If someone wants to contact you regarding your Podcast this is the address they will use.', 'sermon-manager' ); ?></p>
												</td>
											</tr>

											<tr class="top">
												<th scope="row"><?php _e( 'Cover Image', 'sermon-manager' ); ?></th>
												<td class="option">
													<input id="wpfc_options[itunes_cover_image]" size="45" type="text"
													       name="wpfc_options[itunes_cover_image]"
													       value="<?php esc_attr_e( $itunes_cover_image ); ?>"/>
													<input id="upload_cover_image" type="button" class="button"
													       value="Upload Image"/>
													<?php if ( $itunes_cover_image ): ?>
														<br/>
														<img src="<?php esc_attr_e( $itunes_cover_image ); ?>"
														     width="300px" height="300px" class="preview"/>
													<?php endif; ?>
												</td>
												<td class="info">
													<p><?php _e( 'This JPG will serve as the Podcast artwork in the iTunes Store. The image should be 1400px by 1400px', 'sermon-manager' ); ?></p>
												</td>
											</tr>

											<tr>
												<th scope="row"><?php _e( 'Top Category', 'sermon-manager' ); ?></th>
												<td class="option">
													<input id="wpfc_options[itunes_top_category]" size="65" type="text"
													       name="wpfc_options[itunes_top_category]"
													       placeholder="<?php _e( 'e.g. Religion & Spirituality', 'sermon-manager' ); ?>"
													       value="<?php esc_attr_e( $itunes_top_category ); ?>"/>
												</td>
												<td class="info">
													<p><?php _e( 'Choose the appropriate top-level category for your Podcast listing in iTunes.', 'sermon-manager' ); ?></p>
												</td>
											</tr>

											<tr>
												<th scope="row"><?php _e( 'Sub Category', 'sermon-manager' ); ?></th>
												<td class="option">
													<input id="wpfc_options[itunes_sub_category]" size="65" type="text"
													       name="wpfc_options[itunes_sub_category]"
													       placeholder="<?php _e( 'e.g. Christianity', 'sermon-manager' ); ?>"
													       value="<?php esc_attr_e( $itunes_sub_category ); ?>"/>
												</td>
												<td class="info">
													<p><?php _e( 'Choose the appropriate sub category for your Podcast listing in iTunes.', 'sermon-manager' ); ?></p>
												</td>
											</tr>

										</table>

										<br/>
										<tr>
											<p>
												<strong><?php _e( 'Feed URL to Submit to iTunes', 'sermon-manager' ); ?></strong><br/>
												<?php
												$archive_slug = $options['archive_slug'];
												if ( empty( $archive_slug ) ) {
													$archive_slug = 'sermons';
												} ?>
												<input type="text" class="regular-text" readonly="readonly"
												       value="<?php echo home_url( '/' ) . $archive_slug; ?>/feed/"/>
											</p>

											<p><?php _e( 'Use the ', 'sermon-manager' ); ?><a
													href="http://www.feedvalidator.org/check.cgi?url=<?php echo home_url( '/' ) . $archive_slug; ?>/feed/"
													target="_blank"><?php _e( 'Feed Validator', 'sermon-manager' ); ?></a><?php _e( ' to diagnose and fix any problems before submitting your Podcast to iTunes.', 'sermon-manager' ); ?>
											</p>

											<p><?php _e( 'Once your Podcast Settings are complete and your Sermons are ready, it\'s time to ', 'sermon-manager' ); ?>
												<a href="http://www.apple.com/itunes/podcasts/specs.html#submitting"
												   target="_blank"><?php _e( 'Submit Your Podcast', 'sermon-manager' ); ?></a><?php _e( ' to the iTunes Store!', 'sermon-manager' ); ?>
											</p>

											<p><?php _e( 'Alternatively, if you want to track your Podcast subscribers, simply pass the Podcast Feed URL above through ', 'sermon-manager' ); ?>
												<a href="http://feedburner.google.com/"
												   target="_blank"><?php _e( 'FeedBurner', 'sermon-manager' ); ?></a><?php _e( '. FeedBurner will then give you a new URL to submit to iTunes instead.', 'sermon-manager' ); ?>
											</p>

											<p><?php _e( 'Please read the ', 'sermon-manager' ); ?><a
													href="http://www.apple.com/itunes/podcasts/creatorfaq.html"
													target="_blank"><?php _e( 'iTunes FAQ for Podcast Makers', 'sermon-manager' ); ?></a><?php _e( ' for more information.', 'sermon-manager' ); ?>
											</p>

									</div> <!-- .inside -->
								</div>


								<?php do_action( 'wpfc_settings_form' ); ?>
								<p class="submit">
									<input type="submit" class="button-primary"
									       value="<?php _e( 'Save Changes', 'sermon-manager' ) ?>"/>
								</p>
							</form>

						</div> <!-- #post-body-content -->
					</div> <!-- #post-body -->

				</div> <!-- .metabox-holder -->
			</div> <!-- .sermon-option-tabs -->

		</div> <!-- .wrap -->
		<?php
	}

	// Sanitize and validate input. Accepts an array, return a sanitized array.
	function wpfc_validate_options( $input ) {
		add_option( 'sermon_image_plugin_settings', array(
			'taxonomies' => array( 'wpfc_sermon_series', 'wpfc_preacher', 'wpfc_sermon_topics' )
		) );
		// Flush rewrite rules on save
		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$input['archive_slug']  = wp_filter_nohtml_kses( $input['archive_slug'] ); // Sanitize textbox input (strip html tags, and escape characters)
		$input['archive_title'] = wp_filter_nohtml_kses( $input['archive_title'] ); // Sanitize textbox input (strip html tags, and escape characters)
		return $input;
	}

	// Display a Settings link on the main Plugins page
	function wpfc_plugin_action_links( $links, $file ) {

		if ( $file == plugin_basename( __FILE__ ) ) {
			$wpfc_links = '<a href="' . get_admin_url() . 'options-general.php?page=sermon-manager-for-wordpress/options.php">' . __( 'Settings' ) . '</a>';
			// make the 'Settings' link appear first
			array_unshift( $links, $wpfc_links );
		}

		return $links;
	}
}

$Sermon_Manager_Settings = new Sermon_Manager_Settings();

// required for WP sanitation to work
function wpfc_validate_options( $input ) {
	return Sermon_Manager_Settings::wpfc_validate_options( $input );
}