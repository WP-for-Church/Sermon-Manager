<?php defined( 'ABSPATH' ) or die; ?>
<div class="sm wrap">
    <div class="intro">
        <h1 class="wp-heading-inline"><?php _e( 'Sermon Manager Import/Export', 'sermon-manager-for-wordpress' ) ?></h1>
    </div>
    <div class="wp-list-table widefat">
        <p><?php _e( 'We have made it easy to backup, migrate or bring sermons from another plugin. Choose the relevant option below to get started.', 'sermon-manager-for-wordpress' ) ?></p>
        <div id="the-list">
            <div class="plugin-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3><a href="#"><?php _e( 'Import from file', 'sermon-manager-for-wordpress' ) ?>
                                <span class="dashicons dashicons-download plugin-icon"></a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <li><a href="" class="button disabled"
                                   aria-label="<?php esc_attr_e( 'Import from file', 'sermon-manager-for-wordpress' ) ?>"
                                   onclick="alert('Coming soon!'); return false;">
									<?php _e( 'Import', 'sermon-manager-for-wordpress' ) ?>
                                </a></li>
                            <li><a href="" class=""
                                   aria-label="<?php esc_attr_e( 'More Details', 'sermon-manager-for-wordpress' ) ?>">
									<?php _e( 'More Details', 'sermon-manager-for-wordpress' ) ?>
                                </a></li>
                        </ul>
                    </div>
                    <div class="desc column-description">
                        <p><?php _e( 'Import sermons from another Sermon Manager installation.', 'sermon-manager-for-wordpress' ) ?></p>
                    </div>
                </div>
            </div>
            <div class="plugin-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3><a href="#"><?php _e( 'Export to file', 'sermon-manager-for-wordpress' ) ?>
                                <span class="dashicons dashicons-upload plugin-icon"></a></a>
                        </h3></div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <li><a href="" class="button activate-now disabled"
                                   aria-label="<?php esc_attr_e( 'Export to file', 'sermon-manager-for-wordpress' ) ?>"
                                   onclick="alert('Coming soon!'); return false;">
									<?php _e( 'Export', 'sermon-manager-for-wordpress' ) ?>
                                </a>
                            </li>
                            <li><a href="" class=""
                                   aria-label="<?php esc_attr_e( 'More Details', 'sermon-manager-for-wordpress' ) ?>">
									<?php _e( 'More Details', 'sermon-manager-for-wordpress' ) ?></a></li>
                        </ul>
                    </div>
                    <div class="desc column-description">
                        <p><?php _e( 'Create an export for the purpose of backup or migration to another website.', 'sermon-manager-for-wordpress' ) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="wp-list-table widefat">
        <h2><?php _e( 'Import From 3rd Party Plugins', 'sermon-manager-for-wordpress' ) ?></h2>
        <p><?php _e( 'You can import sermons from the following plugins into Sermon Manager', 'sermon-manager-for-wordpress' ) ?></p>
        <div id="the-list">
			<?php if ( ! SM_Import_SB::is_installed() && ! SM_Import_SE::is_installed() ): ?>
                <div class="plugin-card">
                    <div class="desc column-description">
                        <p><?php _e( 'We can not detect any other sermon plugin', 'sermon-manager-for-wordpress' ) ?></p>
                    </div>
                </div>
			<?php endif; ?>
			<?php if ( SM_Import_SB::is_installed() ): ?>
                <div class="plugin-card">
                    <div class="plugin-card-top">
                        <div class="name column-name">
                            <h3><a href="#"><?php _e( 'Import from Sermon Browser', 'sermon-manager-for-wordpress' ) ?>
                                    <span class="dashicons dashicons-editor-aligncenter plugin-icon"></a>
                            </h3></div>
                        <div class="action-links">
                            <ul class="plugin-action-buttons">
                                <li><a href="<?php echo $_SERVER['REQUEST_URI'] ?>&doimport=sb"
                                       class="button activate-now"
                                       aria-label="<?php esc_attr_e( 'Import from Sermon Browser', 'sermon-manager-for-wordpress' ) ?>">
										<?php _e( 'Import', 'sermon-manager-for-wordpress' ) ?></a>
                                </li>
                                <li><a href="" class=""
                                       aria-label="<?php esc_attr_e( 'More Details', 'sermon-manager-for-wordpress' ) ?>">
										<?php _e( 'More Details', 'sermon-manager-for-wordpress' ) ?></a>
                                </li>
                            </ul>
                        </div>
                        <div class="desc column-description">
                            <p><?php _e( 'Import from current database.', 'sermon-manager-for-wordpress' ) ?></p>
                            <p style="margin-bottom: 0">Notes:</p>
                            <ul>
                                <li><?php _e( 'Files will not be visible (they will appear once we add this feature)', 'sermon-manager-for-wordpress' ) ?></li>
                                <li><?php _e( 'Scriptures will not be visible (they will appear once we add this feature)', 'sermon-manager-for-wordpress' ) ?></li>
                                <li><?php _e( 'Tags will not get imported', 'sermon-manager-for-wordpress' ) ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
			<?php endif; ?>
			<?php if ( SM_Import_SE::is_installed() ): ?>
                <div class="plugin-card">
                    <div class="plugin-card-top">
                        <div class="name column-name">
                            <h3><a href="#"><?php _e( 'Series Engine', 'sermon-manager-for-wordpress' ) ?>
                                    <span class="dashicons dashicons-editor-aligncenter plugin-icon"></a></a></a>
                            </h3></div>
                        <div class="action-links">
                            <ul class="plugin-action-buttons">
                                <li><a href="<?php echo $_SERVER['REQUEST_URI'] ?>&doimport=se" class="button activate-now"
                                       aria-label="<?php esc_attr_e( 'Import from Series Engine', 'sermon-manager-for-wordpress' ) ?>">
										<?php _e( 'Import', 'sermon-manager-for-wordpress' ) ?></a>
                                </li>
                                <li><a href="" class=""
                                       aria-label="<?php esc_attr_e( 'More Details', 'sermon-manager-for-wordpress' ) ?>">
										<?php _e( 'More Details', 'sermon-manager-for-wordpress' ) ?></a>
                                </li>
                            </ul>
                        </div>
                        <div class="desc column-description">
                            <p><?php _e( 'Import from current database.', 'sermon-manager-for-wordpress' ); ?></p>
                            <p style="margin-bottom: 0"><?php _e( 'Notes:', 'sermon-manager-for-wordpress' ); ?></p>
                            <ul>
                                <li><?php _e( 'Series Types will not be imported', 'sermon-manager-for-wordpress' ); ?></li>
                                <li><?php _e( 'Files will not be visible (except main file) (they will appear once we add this feature)', 'sermon-manager-for-wordpress' ); ?></li>
                                <li><?php _e( 'Only main scripture will be imported', 'sermon-manager-for-wordpress' ) ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
			<?php endif; ?>
        </div>
    </div>
    <p class="description">
		<?php _e( 'Note: We recommend you create a backup of your current database just in case.', 'sermon-manager-for-wordpress' ) ?>
    </p>
</div>