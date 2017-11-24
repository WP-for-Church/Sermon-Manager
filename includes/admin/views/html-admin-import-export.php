<?php defined( 'ABSPATH' ) or die; ?>
<div class="sm wrap">
    <div class="intro">
        <h1 class="wp-heading-inline">Sermon Manager Import/Export</h1>
    </div>
    <div class="wp-list-table widefat">
        <p>You can import/export content from and to Sermon Manager.</p>
        <div id="the-list">
            <div class="plugin-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3><a href="#">Import from file
                                <span class="dashicons dashicons-download plugin-icon"></a>
                        </h3>
                    </div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <li><a href="" class="button disabled" aria-label="Import from file"
                                   onclick="alert('Coming soon!'); return false;">Import</a></li>
                            <li><a href="" class="" aria-label="More Details">More Details</a></li>
                        </ul>
                    </div>
                    <div class="desc column-description">
                        <p>Import from file that you previously exported.</p>
                    </div>
                </div>
            </div>
            <div class="plugin-card">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3><a href="#">Export to file
                                <span class="dashicons dashicons-upload plugin-icon"></a></a>
                        </h3></div>
                    <div class="action-links">
                        <ul class="plugin-action-buttons">
                            <li><a href="" class="button activate-now disabled"
                                   aria-label="Import from file"
                                   onclick="alert('Coming soon!'); return false;">Export</a>
                            </li>
                            <li><a href="" class="" aria-label="More Details">More Details</a></li>
                        </ul>
                    </div>
                    <div class="desc column-description">
                        <p>Export for backup or to transfer sermons to another website.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="wp-list-table widefat">
        <h2>Import From 3rd Party Plugins</h2>
        <p>You can import content from other plugins into Sermon Manager.</p>
        <div id="the-list">
			<?php if ( ! SM_Import_SB::is_installed() || ! SM_Import_SE::is_installed() ): ?>
                <div class="plugin-card">
                    <div class="desc column-description">
                        <p>There are no detected plugins for importing.</p>
                    </div>
                </div>
			<?php endif; ?>
			<?php if ( SM_Import_SB::is_installed() ): ?>
                <div class="plugin-card">
                    <div class="plugin-card-top">
                        <div class="name column-name">
                            <h3><a href="#">Sermon Browser
                                    <span class="dashicons dashicons-editor-aligncenter plugin-icon"></a></a></a>
                            </h3></div>
                        <div class="action-links">
                            <ul class="plugin-action-buttons">
                                <li><a href="?doimport=sb" class="button activate-now"
                                       aria-label="Import from Sermon Browser">Import</a>
                                </li>
                                <li><a href="" class="" aria-label="More Details">More Details</a></li>
                            </ul>
                        </div>
                        <div class="desc column-description">
                            <p>Import from current database.</p>
                            <p style="margin-bottom: 0">Notes:</p>
                            <ul>
                                <li>Files will not be visible (they will appear once we add this feature)</li>
                                <li>Scriptures will not be visible (they will appear once we add this feature)</li>
                                <li>Tags will not get imported</li>
                            </ul>
                        </div>
                    </div>
                </div>
			<?php endif; ?>
			<?php if ( SM_Import_SE::is_installed() ): ?>
                <div class="plugin-card">
                    <div class="plugin-card-top">
                        <div class="name column-name">
                            <h3><a href="#">Series Engine
                                    <span class="dashicons dashicons-editor-aligncenter plugin-icon"></a></a></a>
                            </h3></div>
                        <div class="action-links">
                            <ul class="plugin-action-buttons">
                                <li><a href="?doimport=se" class="button activate-now"
                                       aria-label="Import from Series Engine">Import</a>
                                </li>
                                <li><a href="" class="" aria-label="More Details">More Details</a></li>
                            </ul>
                        </div>
                        <div class="desc column-description">
                            <p>Import from current database.</p>
                            <p style="margin-bottom: 0">Notes:</p>
                            <ul>
                                <li>Series Types will not be imported</li>
                                <li>Files will not be visible (except main file) (they will appear once we add this
                                    feature)
                                </li>
                                <li>Only main scripture will be imported</li>
                            </ul>
                        </div>
                    </div>
                </div>
			<?php endif; ?>
        </div>
    </div>
    <p class="description">
        Note: Please make a backup of your database before importing, in case that something goes wrong.
    </p>
</div>