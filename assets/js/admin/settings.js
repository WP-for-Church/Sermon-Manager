/**
 * Media Chooser
 */
jQuery(document).ready(function($){
    var custom_uploader;
    $('#upload_default_image').click(function(e) {
        e.preventDefault();
        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }
        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });
        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.on('select', function() {
            attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#default_image').val(attachment.url);
			$("#default-image-thumb-load").html('<br /><img style="width: inherit;" src="' + attachment.url + '" />');
        });
        //Open the uploader dialog
        custom_uploader.open();
    });
});

/**
 * Hide or show fields on checkbox change
 */
jQuery('#enable_podcast_redirection').change(function () {
    podcast_redirect(this.checked);
});

/**
 * Hide or show the fields on document load
 */
jQuery(document).ready(function () {
    var check = jQuery('#enable_podcast_redirection');
    if (check.length) {
        podcast_redirect(check.is(':checked'));
    }
});

/**
 * Shows or hides podcast redirection fields
 *
 * @param {bool} show True to show, false to hide
 */
function podcast_redirect(show) {
    if (typeof(show) !== 'boolean')
        return;

    var el = [
        jQuery('#podcast_redirection_old_url'),
        jQuery('#podcast_redirection_new_url')
    ];

    for (var i = 0; i < el.length; i++) {
        var temp = el[i];

        while (!temp.is("tr")) {
            temp = temp.parent();
        }

        show ? temp.show() : temp.hide();
    }
}
