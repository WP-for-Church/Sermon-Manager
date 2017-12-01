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
                    <img src="<?= SM_URL ?>assets/images/import-sm.jpg" class="plugin-icon"
                         alt="<?php esc_attr_e( 'Import from file', 'sermon-manager-for-wordpress' ) ?>">
                    <div class="name column-name">
                        <h3>
							<?php _e( 'Import from file', 'sermon-manager-for-wordpress' ) ?>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <li><a href="" class="button disabled"
                                   aria-label="<?php esc_attr_e( 'Import from file', 'sermon-manager-for-wordpress' ) ?>"
                                   onclick="return false;">
									<?php _e( 'Coming soon!', 'sermon-manager-for-wordpress' ) ?>
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
                    <img src="<?= SM_URL ?>assets/images/export-sm.jpg" class="plugin-icon"
                         alt="<?php esc_attr_e( 'Export to file', 'sermon-manager-for-wordpress' ) ?>">
                    <div class="name column-name">
                        <h3>
							<?php _e( 'Export to file', 'sermon-manager-for-wordpress' ) ?>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <li><a href="" class="button activate-now disabled"
                                   aria-label="<?php esc_attr_e( 'Export to file', 'sermon-manager-for-wordpress' ) ?>"
                                   onclick="return false;">
									<?php _e( 'Coming soon!', 'sermon-manager-for-wordpress' ) ?>
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
            <div class="plugin-card <?php echo SM_Import_SB::is_installed() ? '' : 'not-available'; ?>">
                <h2>Plugin not installed</h2>
                <div class="plugin-card-top">
                    <img src="<?= SM_URL ?>assets/images/import-sb.jpg" class="plugin-icon"
                         alt="<?php esc_attr_e( 'Sermon Browser', 'sermon-manager-for-wordpress' ) ?>">
                    <div class="name column-name">
                        <h3>
							<?php _e( 'Sermon Browser', 'sermon-manager-for-wordpress' ) ?>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <li><a href="<?php echo $_SERVER['REQUEST_URI'] ?>&doimport=sb"
                                   class="button activate-now <?php echo SM_Import_SB::is_installed() ? '' : 'disabled'; ?>"
                                   aria-label="<?php esc_attr_e( 'Import from Sermon Browser', 'sermon-manager-for-wordpress' ) ?>">
									<?php _e( 'Import', 'sermon-manager-for-wordpress' ) ?></a>
                            </li>
                            <li><a href="https://wpforchurch.com/my/knowledgebase/96/Importing.html#sermon-browser?utm_source=sermon-manager&utm_medium=wordpress" target="_blank"
                                   aria-label="<?php esc_attr_e( 'More Details', 'sermon-manager-for-wordpress' ) ?>">
									<?php _e( 'More Details', 'sermon-manager-for-wordpress' ) ?></a>
                            </li>
                        </ul>
                    </div>
                    <div class="desc column-description">
                        <p><?php // translators: %s Plugin name
							echo wp_sprintf( __( 'Import your existing %s sermon library into Sermon Manager', 'sermon-manager-for-wordpress' ), 'Sermon Browser' ); ?></p>
                        <p class="import-note">
							<?php // translators: %s Documentation URL
							echo wp_sprintf( __( 'Note: Some restrictions apply. Click %s for more details.', 'sermon-manager-for-wordpress' ), ' <a href="https://wpforchurch.com/my/knowledgebase/96/Importing.html#sermon-browser?utm_source=sermon-manager&utm_medium=wordpress" target="_blank">here</a>' ); ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="plugin-card <?php echo SM_Import_SE::is_installed() ? '' : 'not-available'; ?>">
                <h2>Plugin not installed</h2>
                <div class="plugin-card-top">
                    <img src="<?= SM_URL ?>assets/images/import-se.jpg" class="plugin-icon"
                         alt="<?php esc_attr_e( 'Series Engine', 'sermon-manager-for-wordpress' ) ?>">
                    <div class="name column-name">
                        <h3>
							<?php _e( 'Series Engine', 'sermon-manager-for-wordpress' ) ?>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <li><a href="<?php echo $_SERVER['REQUEST_URI'] ?>&doimport=se"
                                   class="button activate-now <?php echo SM_Import_SE::is_installed() ? '' : 'disabled'; ?>"
                                   aria-label="<?php esc_attr_e( 'Import from Series Engine', 'sermon-manager-for-wordpress' ) ?>">
									<?php _e( 'Import', 'sermon-manager-for-wordpress' ) ?></a>
                            </li>
                            <li><a href="https://wpforchurch.com/my/knowledgebase/96/Importing.html#series-engine?utm_source=sermon-manager&utm_medium=wordpress" target="_blank"
                                   aria-label="<?php esc_attr_e( 'More Details', 'sermon-manager-for-wordpress' ) ?>">
									<?php _e( 'More Details', 'sermon-manager-for-wordpress' ) ?></a>
                            </li>
                        </ul>
                    </div>
                    <div class="desc column-description">
                        <p><?php // translators: %s Plugin name
							echo wp_sprintf( __( 'Import your existing %s sermon library into Sermon Manager', 'sermon-manager-for-wordpress' ), 'Series Engine' ); ?></p>
                        <p class="import-note">
							<?php // translators: %s Documentation URL
							echo wp_sprintf( __( 'Note: Some restrictions apply. Click %s for more details.', 'sermon-manager-for-wordpress' ), ' <a href="https://wpforchurch.com/my/knowledgebase/96/Importing.html#series-engine?utm_source=sermon-manager&utm_medium=wordpress" target="_blank">here</a>' ); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <p class="description">
		<?php _e( 'Note: We recommend you create a backup of your current database just in case.', 'sermon-manager-for-wordpress' ) ?>
    </p>
</div>