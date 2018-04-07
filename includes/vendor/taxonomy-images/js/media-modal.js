// Media Modal Frame
var taxonomy_images_file_frame;

(function ($) {

    $(document).ready(function () {

        // Store the old id (not sure if this is application when editing a term)
        taxonomyImagesMediaModal.ttID = 0;

        // When the remove icon is clicked...
        $('.wp-list-table, .form-table').on('click', '.sermon-image-control a.remove', function (event) {

            event.preventDefault();

            var tt_id = $(this).data('tt-id');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    'action': 'sermon_image_plugin_remove_association',
                    'wp_nonce': $(this).data('nonce'),
                    'tt_id': $(this).data('tt-id')
                },
                cache: false,
                success: function (response) {
                    if ('good' === response.status) {

                        selector = $('#sermon-image-control-' + tt_id);

                        /* Update the image on the screen below */
                        selector.find('.taxonomy-image-thumbnail img').attr('src', taxonomyImagesMediaModal.default_img_src);

                        selector.find('a.taxonomy-image-thumbnail').data('attachment-id', 0);
                        selector.find('a.upload').data('attachment-id', 0);

                        /* Show delete control on the screen below */
                        selector.find('.remove').addClass('hide');

                    }
                    else if ('bad' === response.status) {
                        alert(response.why);
                    }
                }
            });

        });

        // When image or upload icon clicked...
        $('.wp-list-table, .form-table').on('click', '.sermon-image-control a.upload, .sermon-image-control a.sermon-image-thumbnail', function (event) {

            event.preventDefault();

            button = $(this);

            taxonomyImagesMediaModal.ttID = $(this).data('tt-id');
            taxonomyImagesMediaModal.attachment_id = $(this).data('attachment-id');
            taxonomyImagesMediaModal.nonce = $(this).data('nonce');

            // If the media frame already exists, reopen it.
            if (taxonomy_images_file_frame) {

                // Set the post ID to the term being edited and open
                taxonomy_images_file_frame.open();
                return;

            } else {

                // Set the wp.media post id so the uploader grabs the term ID being edited
                taxonomyImagesMediaModal.ttID = $(this).data('tt-id');

            }

            // if we are on sermon series page
            var series = !!~window.location.href.indexOf('wpfc_sermon_series');

            // Create the media frame.
            taxonomy_images_file_frame = wp.media.frames.taxonomy_images_file_frame = wp.media({
                title: series ? taxonomyImagesMediaModal.series_title : taxonomyImagesMediaModal.uploader_title,
                button: {text: series ? taxonomyImagesMediaModal.series_button_text : taxonomyImagesMediaModal.uploader_button_text},
                library: {type: 'image'},
                multiple: false
            });

            // Pre-select selected attachment
            wp.media.frames.taxonomy_images_file_frame.on('open', function () {
                var selection = wp.media.frames.taxonomy_images_file_frame.state().get('selection');
                var selected_id = taxonomyImagesMediaModal.attachment_id;
                if (selected_id > 0) {
                    attachment = wp.media.attachment(selected_id);
                    attachment.fetch();
                    selection.add(attachment ? [attachment] : []);
                }
            });

            // When an image is selected, run a callback.
            taxonomy_images_file_frame.on('select', function () {

                // We set multiple to false so only get one image from the uploader
                attachment = taxonomy_images_file_frame.state().get('selection').first().toJSON();

                var tt_id = taxonomyImagesMediaModal.ttID;
                var attachment_id = attachment.id;

                // Do something with attachment.id and/or attachment.url here
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'action': 'sermon_image_create_association',
                        'wp_nonce': taxonomyImagesMediaModal.nonce,
                        'attachment_id': attachment.id,
                        'tt_id': parseInt(taxonomyImagesMediaModal.ttID)
                    },
                    success: function (response) {
                        if ('good' === response.status) {
                            var parent_id = button.parent().attr('id');

                            /* Set state of all other buttons. */
                            $('.sermon-image-modal-control').each(function (i, e) {
                                if (parent_id == $(e).attr('id')) {
                                    return true;
                                }
                                $(e).find('.create-association').show();
                                $(e).find('.remove-association').hide();
                            });

                            selector = $('#sermon-image-control-' + tt_id);

                            /* Update the image on the screen below */
                            selector.find('.sermon-image-thumbnail img').attr('src', response.attachment_thumb_src);

                            selector.find('a.sermon-image-thumbnail').data('attachment-id', attachment_id);
                            selector.find('a.upload').data('attachment-id', attachment_id);

                            /* Show delete control on the screen below */
                            $(selector).find('.remove').each(function (i, e) {
                                $(e).removeClass('hide');
                            });

                        }
                        else if ('bad' === response.status) {
                            alert(response.why);
                        }
                    }
                });

            });

            // Finally, open the modal
            taxonomy_images_file_frame.open();

        });

    });

})(jQuery);
