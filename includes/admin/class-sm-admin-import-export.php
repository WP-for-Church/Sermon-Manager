<?php

class SM_Admin_Import_Export {
	public static function output() {
		?>
        <div class="sm wrap">
            <div class="intro">
                <h2>Sermon Manager Import/Export</h2>
                <p>Hello, you can here import sermons from Sermon Manager export file (or other sermon plugins) or you
                    can export data from Sermon Manager.</p>
            </div>
            <div class="actions">
                <input type="button" class="button button-primary" value="Import from file" disabled>
                <input type="button" class="button button-primary" value="Import from URL" disabled>
                <input type="button" class="button" value="Export" disabled>
                <p class="description">
                    Note: Please make a backup of your database before importing, in case that something goes wrong.
                </p>
            </div>
        </div>
		<?php
	}
}