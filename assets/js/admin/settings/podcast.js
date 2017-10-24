var frame;

function uploadImage(event) {
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

        jQuery(event.target).prev().val(attachment.url);
    });

    frame.open();
}

for (var i = 0; i < jQuery('.upload-image').length; i++) {
    var button = jQuery('.upload-image');

    button.on('click', uploadImage)
}