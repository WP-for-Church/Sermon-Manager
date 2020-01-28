<?php
/**
 * Admin View: Settings
 *
 * @package SM/Core/Admin/Settings
 */

defined( 'ABSPATH' ) or die;

$current_tab = empty( $current_tab ) ? 'general' : $current_tab;
?>
<div class="wrap sm sm_settings_<?php echo $current_tab; ?>">
	<div class="intro">
		<h1 class="wp-heading-inline">Sermon Manager Settings</h1>
	</div>
	<?php SM_Admin_Settings::show_messages(); ?>
	<div class="settings-main">
		<div class="settings-content">
			<form method="<?php echo esc_attr( apply_filters( 'sm_settings_form_method_tab_' . $current_tab, 'post' ) ); ?>"
					id="mainform" action="" enctype="multipart/form-data">
				<nav class="nav-tab-wrapper sm-nav-tab-wrapper">
					<?php
					foreach ( $tabs as $name => $label ) {
						echo '<a href="' . admin_url( 'edit.php?post_type=wpfc_sermon&page=sm-settings&tab=' . $name ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
					}
					do_action( 'sm_settings_tabs' );
					?>
				</nav>
				<div class="inside">
					<h1 class="screen-reader-text"><?php echo esc_html( $tabs[ $current_tab ] ); ?></h1>
					<?php
					do_action( 'sm_sections_' . $current_tab );
					do_action( 'sm_settings_' . $current_tab );
					?>
					<p class="submit">
						<?php if ( empty( $GLOBALS['hide_save_button'] ) ) : ?>
							<input name="save" class="button-primary sm-save-button" type="submit"
									value="<?php esc_attr_e( 'Save changes', 'sermon-manager-for-wordpress' ); ?>"/>
						<?php endif; ?>
						<?php wp_nonce_field( 'sm-settings' ); ?>
					</p>
				</div>
			</form>
		</div>
		<div class="settings-side">
			<?php if ( ! defined('SMP_SM_VERSION') ) : ?>
			<div class="postbox sm-box" style="background: #f6fbff;">
				<h3><span>Sermon Manager Pro</span>
				</h3>
				<div class="inside">
					<p>Imagine being able to change the look of your sermons with one click or use your favorite page
						builder, or create multiple podcasts...well now you can with Sermon Manager Pro, plus a lot
						more. </p>
					<div style="text-align:center">
						<a href="https://sermonmanager.pro/?utm_source=sermon-manager&amp;utm_medium=wordpress"
								target="_blank" class="button-secondary">Get Pro</a>&nbsp;
						<a href="https://sermonmanager.pro/?utm_source=sermon-manager&amp;utm_medium=wordpress"
								class="button-primary">Learn More</a>
					</div>
					<div class="description">
						<span>Early Adopter pricing ends Nov 23</span>
					</div>
				</div>
			</div>
			<div class="postbox sm-box">
				<h3><span><?php esc_html_e( 'Need Some Help?', 'sermon-manager-for-wordpress' ); ?></span>
				</h3>
				<div class="inside">
					<?php // translators: %s Sign up URL. ?>
					<p><?php echo wp_sprintf( esc_html__( 'Did you know you can get expert support for only $49 per year! %s today and get support from the developers who are building the Sermon Manager.', 'sermon-manager-for-wordpress' ), '<a href="https://wpforchurch.com/wordpress-plugins/sermon-manager/?utm_source=sermon-manager&utm_medium=wordpress" target="_blank">' . esc_html__( 'Sign up', 'sermon-manager-for-wordpress' ) . '</a>' ); ?></p>
					<div style="text-align:center">
						<a href="https://wordpress.org/support/plugin/sermon-manager-for-wordpress"
								target="_blank" class="button-secondary">
							<?php esc_html_e( 'Free&nbsp;Support', 'sermon-manager-for-wordpress' ); ?></a>&nbsp;
						<a href="https://wpforchurch.com/my/clientarea.php?utm_source=sermon-manager&utm_medium=wordpress"
								class="button-primary">
							<?php esc_html_e( 'Priority&nbsp;Support', 'sermon-manager-for-wordpress' ); ?></a>
					</div>
					<div class="description">
						<span><?php esc_html_e( 'We offer limited free support via WordPress.org', 'sermon-manager-for-wordpress' ); ?></span>
					</div>
				</div>
			</div>
			<?php endif; ?>
			<div class="postbox sm-box">
				<h3>
					<span><?php esc_html_e( 'Frequently Asked Questions', 'sermon-manager-for-wordpress' ); ?></span>
				</h3>
				<div class="inside">
					<ol>
						<li>
							<a href="https://www.wpforchurch.com/my/knowledgebase/72/Getting-Started-with-Sermon-Manager-for-WordPress.html?utm_source=sermon-manager&utm_medium=wordpress"
									title="" target="_blank">Getting Started with Sermon Manager</a></li>
						<li>
							<a href="https://www.wpforchurch.com/my/knowledgebase/75/Sermon-Manager-Shortcodes.html?utm_source=sermon-manager&utm_medium=wordpress"
									title="Sermon Manager Shortcodes" target="_blank">
								Sermon Manager Shortcodes</a></li>
						<li>
							<a href="https://www.wpforchurch.com/my/knowledgebase/67/Troubleshooting-Sermon-Manager.html?utm_source=sermon-manager&utm_medium=wordpress"
									title="Troubleshooting Sermon Manager" target="_blank">
								Troubleshooting Sermon Manager</a></li>
					</ol>
					<div class="text-align:center;font-size:0.85em;padding:0.4rem 0 0">
						<?php // translators: %s Knowledgebase URL. ?>
						<span><?php echo wp_sprintf( esc_html__( 'Find out more in our %s', 'sermon-manager-for-wordpress' ), '<a href="https://www.wpforchurch.com/my/knowledgebase.php?utm_source=sermon-manager&utm_medium=wordpress" title="Knowledgebase" target="_blank">' . esc_html__( 'knowledge base', 'sermon-manager-for-wordpress' ) . '</a>' ); ?></span>
					</div>
				</div>
			</div>

			<div class="postbox sm-box">
				<h3>
					<span><?php esc_html_e( 'Lets Make It Even Better!', 'sermon-manager-for-wordpress' ); ?></span>
				</h3>
				<div class="inside">
					<p><?php esc_html_e( 'If you have ideas on how to make Sermon Manager or any of our products better, let us know!', 'sermon-manager-for-wordpress' ); ?></p>
					<div style="text-align:center">
						<a href="https://feedback.userreport.com/05ff651b-670e-4eb7-a734-9a201cd22906/"
								target="_blank"
								class="button-secondary"><?php esc_html_e( 'Submit&nbsp;Your&nbsp;Idea', 'sermon-manager-for-wordpress' ); ?></a>
					</div>
				</div>
			</div>
		   <?php  
			echo apply_filters( 'settings_page_sidebar_extra_boxs', $arg='' );
			?>
		</div>
	</div>
</div>
