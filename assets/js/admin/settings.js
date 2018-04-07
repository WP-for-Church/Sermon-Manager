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
