<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

/*
 * Sermon Manager Plugin Settings
 */

class Sermon_Manager_Settings {

	public function __construct() {
		// Flush rewrite rules before everything else (if required)
		add_action( 'init', array( $this, 'maybe_flush_rewrite_rules' ) );
		// Set-up Action and Filter Hooks
		add_action( 'admin_init', array( $this, 'wpfc_init' ) );
		// Settings Menu Page
		add_action( 'admin_menu', array( $this, 'wpfc_add_options_page' ) );
	}

	static function wpfc_validate_options( $input ) {
		$input['archive_slug']      = wp_filter_nohtml_kses( $input['archive_slug'] ); // Sanitize textbox input (strip html tags, and escape characters)
		$input['archive_title']     = wp_filter_nohtml_kses( $input['archive_title'] ); // Sanitize textbox input (strip html tags, and escape characters)
		$input['podcasts_per_page'] = intval( $input['podcasts_per_page'] );

		if ( isset( $input['sm_do_not_catch'] ) && $input['sm_do_not_catch'] === 'on' ) {
			update_option( 'sm_do_not_catch', '1' );
			unset( $input['sm_do_not_catch'] );
		} else {
			update_option( '_sm_recovery_do_not_catch', '0' );
		}

		if ( SermonManager::getOption( 'archive_slug' ) != $input['archive_slug'] ||
		     SermonManager::getOption( 'common_base_slug' ) != $input['common_base_slug'] ||
		     SermonManager::getOption( 'preacher_label' ) != $input['preacher_label'] ) {
			update_option( 'sm_flush_rewrite_rules', '1' );
		}

		return $input;
	}

	/**
	 * Checks if archive slug has changed and flushes rewrite rules if necessary
	 *
	 * @since 2.5.2
	 */
	function maybe_flush_rewrite_rules() {
		if ( (bool) get_option( 'sm_flush_rewrite_rules' ) ) {
			flush_rewrite_rules();
			update_option( 'sm_flush_rewrite_rules', '0' );
		}
	}

	function wpfc_init() {
		global $wp_version;

		$args = 'wpfc_validate_options';

		if ( version_compare( $wp_version, '4.7.0', '>=' ) ) {
			$args = array(
				'sanitize_callback' => 'wpfc_validate_options'
			);
		}

		register_setting( 'wpfc_plugin_options', 'wpfc_options', $args );

		if ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'options.php' ) !== false ) {
			wp_enqueue_media();
		}
	}

	function wpfc_add_options_page() {
		$page = add_submenu_page( 'edit.php?post_type=wpfc_sermon', __( 'Sermon Manager Settings', 'sermon-manager-for-wordpress' ), 'Old Settings Page', 'manage_options', __FILE__, array(
			$this,
			'wpfc_sermon_options_render_form'
		) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'wpfc_sermon_admin_styles' ) );
	}

	function wpfc_sermon_admin_styles() {
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
	}

	function wpfc_sermon_options_render_form() {
		if ( ! isset( $_REQUEST['settings-updated'] ) ) {
			$_REQUEST['settings-updated'] = false;
		}
		?>
        <div class="wrap">
            <script type="text/javascript">
                jQuery(document).ready(function () {
                    jQuery('.sermon-option-tabs').tabs();

                    var frame,
                        addImgLink = jQuery('#upload_cover_image'),
                        imgSrcInput = jQuery('.itunes_cover_image_field');

                    addImgLink.on('click', function (event) {
                        event.preventDefault();

                        if (frame) {
                            frame.open();
                            return;
                        }

                        frame = wp.media({
                            title: 'Select or Upload Cover Image',
                            button: {
                                text: 'Use this image'
                            },
                            library: {
                                type: ['image']
                            },
                            multiple: false
                        });

                        frame.on('select', function () {
                            var attachment = frame.state().get('selection').first().toJSON();

                            imgSrcInput.val(attachment.url);
                        });

                        frame.open();
                    });
                });
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

                .sm-box h3 {
                    text-align: center;
                    background: #f7f7f7;
                    border-bottom: 1px solid #efefef;
                }
            </style>
            <!-- Display Plugin Icon, Header, and Description -->
            <div class="sermon-option-tabs">
                <div class="metabox-holder has-right-sidebar">
                    <div class="inner-sidebar">
                        <div class="postbox sm-box">
                            <h3>
                                <span><?php esc_html_e( 'Need Some Help?', 'sermon-manager-for-wordpress' ); ?></span>
                            </h3>
                            <div class="inside">
                                <p style="text-align:justify"><?php // translators: %s see msgid "Sign up"
									echo wp_sprintf( esc_html__( 'Did you know you can get expert support for only $49 per year! %s today and get support from the developers who are building the Sermon Manager.', 'sermon-manager-for-wordpress' ), '<a href="https://wpforchurch.com/wordpress-plugins/sermon-manager/?utm_source=sermon-manager&utm_medium=wordpress" target="_blank">' . esc_html__( 'Sign up', 'sermon-manager-for-wordpress' ) . '</a>' ); ?></p>
                                <div style="text-align:center">
                                    <a href="https://wordpress.org/support/plugin/sermon-manager-for-wordpress"
                                       target="_blank"
                                       class="button-secondary"><?php esc_html_e( 'Free&nbsp;Support', 'sermon-manager-for-wordpress' ); ?></a>&nbsp;
                                    <a href="https://wpforchurch.com/my/clientarea.php"
                                       class="button-primary"><?php esc_html_e( 'Priority&nbsp;Support', 'sermon-manager-for-wordpress' ); ?></a>
                                </div>
                                <div style="text-align:center;font-size:0.85em;padding:0.7rem 0 0">
                                    <span><?php esc_html_e( 'We offer limited free support via WordPress.org', 'sermon-manager-for-wordpress' ); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="postbox sm-box">
                            <h3>
                                <span><?php esc_html_e( 'Frequently Asked Questions', 'sermon-manager-for-wordpress' ); ?></span>
                            </h3>
                            <div class="inside">
                                <ul>
                                    <li>- <a
                                                href="https://www.wpforchurch.com/my/knowledgebase/72/Getting-Started-with-Sermon-Manager-for-WordPress.html"
                                                title="" target="_blank">Getting Started with Sermon Manager</a></li>
                                    <li>- <a
                                                href="https://www.wpforchurch.com/my/knowledgebase/75/Sermon-Manager-Shortcodes.html"
                                                title="Sermon Manager Shortcodes" target="_blank">Sermon Manager
                                            Shortcodes</a></li>
                                    <li>- <a
                                                href="https://www.wpforchurch.com/my/knowledgebase/67/Troubleshooting-Sermon-Manager.html"
                                                title="Troubleshooting Sermon Manager" target="_blank">Troubleshooting
                                            Sermon Manager</a></li>
                                </ul>
                                <div style="text-align:center;font-size:0.85em;padding:0.4rem 0 0">
                                    <span><?php // translators: %s see msgid "knowledge base"
	                                    echo wp_sprintf( esc_html__( 'Find out more in our %s', 'sermon-manager-for-wordpress' ), '<a href="https://www.wpforchurch.com/my/knowledgebase.php" title="Knowledgebase" target="_blank">' . esc_html__( 'knowledge base', 'sermon-manager-for-wordpress' ) . '</a>' ); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="postbox sm-box">
                            <h3>
                                <span><?php esc_html_e( 'Lets Make It Even Better!', 'sermon-manager-for-wordpress' ); ?></span>
                            </h3>
                            <div class="inside">
                                <p style="text-align:justify"><?php esc_html_e( 'If you have ideas on how to make Sermon Manager or any of our products better, let us know!', 'sermon-manager-for-wordpress' ); ?></p>
                                <div style="text-align:center">
                                    <a href="https://feedback.userreport.com/05ff651b-670e-4eb7-a734-9a201cd22906/"
                                       target="_blank"
                                       class="button-secondary"><?php esc_html_e( 'Submit&nbsp;Your&nbsp;Idea', 'sermon-manager-for-wordpress' ); ?></a>
                                </div>
                            </div>
                        </div>
                    </div> <!-- .inner-sidebar -->
                    <div id="post-body">
                        <div id="post-body-content">
                            <form method="post" action="options.php">
								<?php settings_fields( 'wpfc_plugin_options' ); ?>
								<?php $options = get_option( 'wpfc_options' ); ?>

                                <div class="postbox" id="sermon-options-verse" class="tab-content">
                                    <div class="inside">
                                        <table class="form-table">
                                            <!-- Select Bible Version -->
                                            <tr>
                                                <th scope="row"><?php esc_html_e( 'Select Bible Version for Verse Popups', 'sermon-manager-for-wordpress' ); ?></th>
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
                                                    <span style="color:#666666;margin-left:2px;">
														<?php // translators: %1$s effectively <code>ESV</code>
														// translators: %2$s effectively <code>NET</code>
														// translators: %3$s effectively <code>KJV</code>
														// translators: %4$s effectively <code>LEB</code>
														// translators: %5$s see msgid "bib.ly", effectively <a href="http://bib.ly">bib.ly</a>
														echo wp_sprintf( esc_html__( '%1$s, %2$s, %3$s, or %4$s are the currently supported popups for %5$s.', 'sermon-manager-for-wordpress' ),
															'<code>ESV</code>',
															'<code>NET</code>',
															'<code>KJV</code>',
															'<code>LEB</code>',
															'<a href="http://bib.ly">' . esc_html__( 'bib.ly', 'sermon-manager-for-wordpress' ) . '</a>' ); ?>
                                                        <br>
														<?php // translators: %s see effectively <code>ESV</code>
														echo wp_sprintf( esc_html__( 'Warning! %s is not supported if your site uses SSL (HTTPS).', 'sermon-manager-for-wordpress' ), '<code>ESV</code>' ); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div> <!-- .inside -->
                                </div>

                                <div class="postbox tab-content" id="sermon-options-podcast">
                                    <h3>
                                        <span><?php esc_html_e( 'Podcast Settings', 'sermon-manager-for-wordpress' ); ?></span>
                                    </h3>
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
                                                <th scope="row"><?php esc_html_e( 'Title', 'sermon-manager-for-wordpress' ); ?></th>
                                                <td class="option" colspan="2">
                                                    <input id="wpfc_options[title]" type="text" size="65"
                                                           name="wpfc_options[title]"
                                                           placeholder="<?php echo esc_attr( wp_sprintf( __( 'e.g. %s', 'sermon-manager-for-wordpress' ), get_bloginfo( 'name' ) ) ); ?>"
                                                           value="<?php echo esc_attr( $title ); ?>"/>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php esc_html_e( 'Description', 'sermon-manager-for-wordpress' ); ?></th>
                                                <td class="option" colspan="2">
                                                    <input id="wpfc_options[description]" type="text" size="65"
                                                           name="wpfc_options[description]"
                                                           placeholder="<?php echo esc_attr( wp_sprintf( __( 'e.g. %s', 'sermon-manager-for-wordpress' ), get_bloginfo( 'description' ) ) ); ?>"
                                                           value="<?php echo esc_attr( $description ); ?>"/>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php esc_html_e( 'Website Link', 'sermon-manager-for-wordpress' ); ?></th>
                                                <td class="option" colspan="2">
                                                    <input id="wpfc_options[website_link]" type="text" size="65"
                                                           name="wpfc_options[website_link]"
                                                           placeholder="<?php echo esc_attr( wp_sprintf( __( 'e.g. %s', 'sermon-manager-for-wordpress' ), home_url() ) ); ?>"
                                                           value="<?php echo esc_attr( $website_link ); ?>"/>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php esc_html_e( 'Language', 'sermon-manager-for-wordpress' ); ?></th>
                                                <td class="option" colspan="2">
                                                    <input id="wpfc_options[language]" type="text" size="65"
                                                           name="wpfc_options[language]"
                                                           placeholder="<?php echo esc_attr( wp_sprintf( __( 'e.g. %s', 'sermon-manager-for-wordpress' ), get_bloginfo( 'language' ) ) ); ?>"
                                                           value="<?php echo esc_attr( $language ); ?>"/>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php esc_html_e( 'Copyright', 'sermon-manager-for-wordpress' ); ?></th>
                                                <td class="option">
                                                    <input id="wpfc_options[copyright]" type="text" size="65"
                                                           name="wpfc_options[copyright]"
                                                           placeholder="<?php echo esc_attr( wp_sprintf( __( 'e.g. Copyright &copy; %s', 'sermon-manager-for-wordpress' ), get_bloginfo( 'name' ) ) ); ?>"
                                                           value="<?php echo esc_attr( $copyright ); ?>"/>
                                                </td>
                                                <td class="info">
                                                    <p>
                                                        <em><?php // translators: %s <code>&amp;copy;</code>
															echo wp_sprintf( esc_html__( 'Tip: Use %s to generate a copyright symbol.', 'sermon-manager-for-wordpress' ), '<code>' . htmlspecialchars( '&copy;' ) . '</code>' ); ?></em>
                                                    </p>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php esc_html_e( 'Webmaster Name', 'sermon-manager-for-wordpress' ); ?></th>
                                                <td class="option" colspan="2">
                                                    <input id="wpfc_options[webmaster_name]" type="text" size="65"
                                                           name="wpfc_options[webmaster_name]"
                                                           placeholder="<?php esc_attr_e( 'e.g. Your Name', 'sermon-manager-for-wordpress' ); ?>"
                                                           value="<?php echo esc_attr( $webmaster_name ); ?>"/>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php esc_html_e( 'Webmaster Email', 'sermon-manager-for-wordpress' ); ?></th>
                                                <td class="option" colspan="2">
                                                    <input id="wpfc_options[webmaster_email]" type="text" size="65"
                                                           name="wpfc_options[webmaster_email]"
                                                           placeholder="<?php echo esc_attr( wp_sprintf( __( 'e.g. %s', 'sermon-manager-for-wordpress' ), get_bloginfo( 'admin_email' ) ) ); ?>"
                                                           value="<?php echo esc_attr( $webmaster_email ); ?>"/>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php esc_html_e( 'Author', 'sermon-manager-for-wordpress' ); ?></th>
                                                <td class="option">
                                                    <input id="wpfc_options[itunes_author]" type="text" size="65"
                                                           name="wpfc_options[itunes_author]"
                                                           placeholder="<?php esc_attr_e( 'e.g. Primary Speaker or Church Name', 'sermon-manager-for-wordpress' ); ?>"
                                                           value="<?php echo esc_attr( $itunes_author ); ?>"/>
                                                </td>
                                                <td class="info">
                                                    <p><?php esc_html_e( 'This will display at the &ldquo;Artist&rdquo; in the iTunes Store.', 'sermon-manager-for-wordpress' ); ?></p>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php esc_html_e( 'Subtitle', 'sermon-manager-for-wordpress' ); ?></th>
                                                <td class="option">
                                                    <input id="wpfc_options[itunes_subtitle]" type="text" size="65"
                                                           name="wpfc_options[itunes_subtitle]"
                                                           placeholder="<?php echo esc_attr( wp_sprintf( __( 'e.g. Preaching and teaching audio from %s', 'sermon-manager-for-wordpress' ), get_bloginfo( 'name' ) ) ); ?>"
                                                           value="<?php echo esc_attr( $itunes_subtitle ); ?>"/>
                                                </td>
                                                <td class="info">
                                                    <p><?php esc_html_e( 'Your subtitle should briefly tell the listener what they can expect to hear.', 'sermon-manager-for-wordpress' ); ?></p>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php esc_html_e( 'Summary', 'sermon-manager-for-wordpress' ); ?></th>
                                                <td class="option">
													<textarea id="wpfc_options[itunes_summary]" class="large-text"
                                                              cols="65" rows="5" name="wpfc_options[itunes_summary]"
                                                              placeholder="<?php echo esc_attr( wp_sprintf( __( 'e.g. Weekly teaching audio brought to you by %s in City, State.', 'sermon-manager-for-wordpress' ), get_bloginfo( 'name' ) ) ); ?>"><?php echo esc_textarea( $itunes_summary ); ?></textarea>
                                                </td>
                                                <td class="info">
                                                    <p><?php esc_html_e( 'Keep your Podcast Summary short, sweet and informative. Be sure to include a brief statement about your mission and in what region your audio content originates.', 'sermon-manager-for-wordpress' ); ?></p>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php esc_html_e( 'Owner Name', 'sermon-manager-for-wordpress' ); ?></th>
                                                <td class="option">
                                                    <input id="wpfc_options[itunes_owner_name]" type="text" size="65"
                                                           name="wpfc_options[itunes_owner_name]"
                                                           placeholder="<?php echo esc_attr( wp_sprintf( __( 'e.g. %s', 'sermon-manager-for-wordpress' ), get_bloginfo( 'name' ) ) ); ?>"
                                                           value="<?php echo esc_attr( $itunes_owner_name ); ?>"/>
                                                </td>
                                                <td class="info">
                                                    <p><?php esc_html_e( 'This should typically be the name of your Church.', 'sermon-manager-for-wordpress' ); ?></p>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php esc_html_e( 'Owner Email', 'sermon-manager-for-wordpress' ); ?></th>
                                                <td class="option">
                                                    <input id="wpfc_options[itunes_owner_email]" type="text" size="65"
                                                           name="wpfc_options[itunes_owner_email]"
                                                           placeholder="<?php echo esc_attr( wp_sprintf( __( 'e.g. %s', 'sermon-manager-for-wordpress' ), get_bloginfo( 'admin_email' ) ) ); ?>"
                                                           value="<?php echo esc_attr( $itunes_owner_email ); ?>"/>
                                                </td>
                                                <td class="info">
                                                    <p><?php esc_html_e( 'Use an email address that you don&rsquo;t mind being made public. If someone wants to contact you regarding your Podcast this is the address they will use.', 'sermon-manager-for-wordpress' ); ?></p>
                                                </td>
                                            </tr>

                                            <tr class="top">
                                                <th scope="row"><?php esc_html_e( 'Cover Image', 'sermon-manager-for-wordpress' ); ?></th>
                                                <td class="option">
                                                    <input id="wpfc_options[itunes_cover_image]" size="45" type="text"
                                                           name="wpfc_options[itunes_cover_image]"
                                                           class="itunes_cover_image_field"
                                                           value="<?php echo esc_attr( $itunes_cover_image ); ?>"/>
                                                    <input id="upload_cover_image" type="button" class="button"
                                                           value="<?php esc_attr_e( 'Upload Image', 'sermon-manager-for-wordpress' ); ?>"/>
                                                </td>
                                                <td class="info">
                                                    <p><?php esc_html_e( 'This JPG will serve as the Podcast artwork in the iTunes Store. The image must be between 1,400px by 1,400px and 3,000px by 3,000px or else iTunes will not accept your feed.', 'sermon-manager-for-wordpress' ); ?></p>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php esc_html_e( 'Top Category', 'sermon-manager-for-wordpress' ); ?></th>
                                                <td class="option">
                                                    <input id="wpfc_options[itunes_top_category]" size="65" type="text"
                                                           name="wpfc_options[itunes_top_category]"
                                                           placeholder="<?php echo esc_attr( wp_sprintf( __( 'e.g. %s', 'sermon-manager-for-wordpress' ), 'Religion & Spirituality' ) ); ?>"
                                                           value="<?php echo esc_attr( $itunes_top_category ); ?>"/>
                                                </td>
                                                <td class="info">
                                                    <p><?php esc_html_e( 'Choose the appropriate top-level category for your Podcast listing in iTunes.', 'sermon-manager-for-wordpress' ); ?></p>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php esc_html_e( 'Sub Category', 'sermon-manager-for-wordpress' ); ?></th>
                                                <td class="option">
                                                    <input id="wpfc_options[itunes_sub_category]" size="65" type="text"
                                                           name="wpfc_options[itunes_sub_category]"
                                                           placeholder="<?php echo esc_attr( wp_sprintf( __( 'e.g. %s', 'sermon-manager-for-wordpress' ), 'Christianity' ) ); ?>"
                                                           value="<?php echo esc_attr( $itunes_sub_category ); ?>"/>
                                                </td>
                                                <td class="info">
                                                    <p><?php esc_html_e( 'Choose the appropriate sub category for your Podcast listing in iTunes.', 'sermon-manager-for-wordpress' ); ?></p>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php esc_html_e( 'PodTrac Tracking', 'sermon-manager-for-wordpress' ); ?></th>
                                                <td>
                                                    <label><input name="wpfc_options[podtrac]" type="checkbox"
                                                                  value="1" <?php if ( isset( $options['podtrac'] ) ) {
															checked( '1', $options['podtrac'] );
														} ?>/><?php esc_html_e( 'Enables PodTrac tracking.', 'sermon-manager-for-wordpress' ); ?>
                                                    </label><br/>
                                                </td>
                                                <td class="info">
                                                    <p><?php // translators: %s <a href="http://podtrac.com">podtrac.com</a>
														echo wp_sprintf( esc_html__( 'For more info on PodTrac or to sign up for an account, visit %s', 'sermon-manager-for-wordpress' ), '<a href="http://podtrac.com">podtrac.com</a>' ); ?></p>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php esc_html_e( 'HTML in description', 'sermon-manager-for-wordpress' ); ?></th>
                                                <td>
                                                    <label><input name="wpfc_options[enable_podcast_html_description]"
                                                                  type="checkbox"
                                                                  value="1" <?php if ( isset( $options['enable_podcast_html_description'] ) ) {
															checked( '1', $options['enable_podcast_html_description'] );
														} ?>/><?php esc_html_e( 'Enable HTML description', 'sermon-manager-for-wordpress' ); ?>
                                                    </label><br/>
                                                </td>
                                                <td class="info">
                                                    <p><?php esc_html_e( 'Enables showing of HTML in iTunes description field. Uncheck if description looks messy.', 'sermon-manager-for-wordpress' ); ?></p>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th scope="row"><?php esc_html_e( 'Number of podcasts to show', 'sermon-manager-for-wordpress' ); ?></th>
                                                <td>
                                                    <label>
                                                        <input name="wpfc_options[podcasts_per_page]" type="number"
                                                               value="<?php echo isset( $options['podcasts_per_page'] ) ? intval( $options['podcasts_per_page'] ) : ''; ?>"
                                                               placeholder="<?php echo get_option( 'posts_per_rss' ); ?>">
                                                    </label><br/>
                                                </td>
                                                <td class="info">
                                                    <p><?php esc_html_e( 'Shows custom podcast count. If not defined, it uses WordPress default count.', 'sermon-manager-for-wordpress' ); ?></p>
                                                </td>
                                            </tr>
                                        </table>

                                        <br/>
                                        <p>
                                            <strong><?php esc_html_e( 'Feed URL to Submit to iTunes', 'sermon-manager-for-wordpress' ); ?></strong><br/>
                                            <input type="text" class="regular-text" readonly="readonly"
                                                   value="<?php
											       $archive_slug = $options['archive_slug'];
											       if ( empty( $archive_slug ) ) {
												       $archive_slug = 'sermons';
											       }
											       echo home_url( '/' ) . $archive_slug; ?>/feed/"/>
                                        </p>

                                        <p><?php // translators: %s Feed Validator link, see msgid "Feed Validator"
											echo wp_sprintf( esc_html__( 'Use the %s to diagnose and fix any problems before submitting your Podcast to iTunes.', 'sermon-manager-for-wordpress' ), '<a href="http://www.feedvalidator.org/check.cgi?url=' . home_url( '/' ) . $archive_slug . '/feed/" target="_blank">' . esc_html__( 'Feed Validator', 'sermon-manager-for-wordpress' ) . '</a>' ); ?>
                                        </p>

                                        <p><?php // translators: %s see msgid "Submit Your Podcast"
											echo wp_sprintf( esc_html__( 'Once your Podcast Settings are complete and your Sermons are ready, it&rsquo;s time to %s to the iTunes Store!', 'sermon-manager-for-wordpress' ), '<a href="https://www.apple.com/itunes/podcasts/specs.html#submitting" target="_blank">' . esc_html__( 'Submit Your Podcast', 'sermon-manager-for-wordpress' ) . '</a>' ); ?>
                                        </p>

                                        <p><?php // translators: %s see msgid "FeedBurner"
											echo wp_sprintf( esc_html__( 'Alternatively, if you want to track your Podcast subscribers, simply pass the Podcast Feed URL above through %s. FeedBurner will then give you a new URL to submit to iTunes instead.', 'sermon-manager-for-wordpress' ), '<a href="http://feedburner.google.com/" target="_blank">' . esc_html__( 'FeedBurner', 'sermon-manager-for-wordpress' ) . '</a>' ); ?>
                                        </p>

                                        <p><?php // translators: %s see msgid "iTunes FAQ for Podcast Makers"
											echo wp_sprintf( esc_html__( 'Please read the %s for more information.', 'sermon-manager-for-wordpress' ), '<a href="https://www.apple.com/itunes/podcasts/creatorfaq.html" target="_blank">' . esc_html__( 'iTunes FAQ for Podcast Makers', 'sermon-manager-for-wordpress' ) . '</a>' ); ?>
                                        </p>
                                    </div> <!-- .inside -->
                                </div>

								<?php do_action( 'wpfc_settings_form' ); ?>
                                <p class="submit">
                                    <input type="submit" class="button-primary"
                                           value="<?php esc_attr_e( 'Save Changes', 'sermon-manager-for-wordpress' ) ?>"/>
                                </p>
                            </form>

                        </div> <!-- #post-body-content -->
                    </div> <!-- #post-body -->

                </div> <!-- .metabox-holder -->
            </div> <!-- .sermon-option-tabs -->

        </div> <!-- .wrap -->
		<?php
	}
}

$Sermon_Manager_Settings = new Sermon_Manager_Settings();

// required for WP sanitation to work
function wpfc_validate_options( $input ) {
	return Sermon_Manager_Settings::wpfc_validate_options( $input );
}
