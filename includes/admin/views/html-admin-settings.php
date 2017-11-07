<?php
defined( 'ABSPATH' ) or die;
/**
 * Admin View: Settings
 */

$current_tab = empty( $current_tab ) ? 'general' : $current_tab;
?>
<div class="wrap sm">
    <div class="intro">
        <h1 class="wp-heading-inline">Sermon Manager Settings</h1>
    </div>
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
        <h1 class="screen-reader-text"><?php echo esc_html( $tabs[ $current_tab ] ); ?></h1>
		<?php
		do_action( 'sm_sections_' . $current_tab );

		/** @noinspection PhpUndefinedClassInspection */
		self::show_messages();

		do_action( 'sm_settings_' . $current_tab );
		?>
        <p class="submit">
			<?php if ( empty( $GLOBALS['hide_save_button'] ) ) : ?>
                <input name="save" class="button-primary sm-save-button" type="submit"
                       value="<?php esc_attr_e( 'Save changes', 'sermon-manager-for-wordpress' ); ?>"/>
			<?php endif; ?>
			<?php wp_nonce_field( 'sm-settings' ); ?>
        </p>
    </form>
</div>
