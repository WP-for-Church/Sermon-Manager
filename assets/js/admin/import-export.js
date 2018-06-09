jQuery(document).ready(function () {
    var smImportForm = jQuery('#sm-import-upload-form');
    if (smImportForm.length) {
        var fileField = smImportForm.find('#upload');
        var importTrigger = smImportForm.siblings('#sm-import-trigger');
        var submitButton = smImportForm.find('#submit');
        importTrigger.click(function() {
           fileField.click(); 
        });
        fileField.change(function (){
            importTrigger.find('.import-sniper').css('display', 'inline-block');
            importTrigger.attr('disabled', true);
            submitButton.click();
        });
    }
});
