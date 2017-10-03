jQuery(document).ready(function ($) {
    $('.sm-actions > .button').on('click', function () {
        var dialog = '',
            curtain = $("#sm-curtain"),
            notice = $('#sm-fatal-error-notice');

        switch (this.id) {
            case "send-report":
                dialog = $("#send-report-dialog");
                dialog.dialog({
                    autoOpen: false,
                    buttons: {
                        "Send": function () {
                            jQuery.get('https://wpforchurch.com/?WPFC=submit_issue&stack=' + sm_error_recovery_data.stacktrace + '&environment_info=' + sm_error_recovery_data.environment_info + '&user_comments=' + encodeURIComponent($('#issue-info').val()) + '&email=' + encodeURIComponent($('#issue-email').val()), '', function () {
                                $('#send-report').hide();
                                $('#notice-message').replaceWith('<p>The issue has been submitted. Thank you for helping us make <strong>' + sm_error_recovery_data.plugin_name + '</strong> even better.</p>' +
                                    '<p>If you want to help us resolve this issue quicker, why not submit <a href="https://github.com/WP-for-Church/Sermon-Manager/issues/new" target="_blank">an issue</a> or if you have a support plan, <a href="https://wpforchurch.com/my/submitticket.php?step=2&deptid=2&subject=Sermon%20Manager%3A%20Fatal%20Error" target="_blank">a ticket</a>.</p>');

                                $.post(ajaxurl, {
                                    'action': 'sm_recovery_disable_send_report'
                                });

                                notice.removeClass('loading').removeClass('notice-error').addClass('notice-warning');
                            });
                            $(this).dialog("close");
                        },
                        "Cancel": function () {
                            $(this).dialog("close");
                        }
                    },
                    beforeClose: function () {
                        curtain.removeClass('dialog');
                    }
                });
                dialog.dialog("open");

                notice.addClass('loading');
                curtain.addClass('dialog');
                curtain.on('click', function () {
                    dialog.dialog("close");
                    $(this).off('click');
                });
                break;
            case "view-error":
                if (~this.innerHTML.indexOf('Show')) {
                    $('#sm-error').show();
                    this.innerHTML = 'Hide error message';
                } else {
                    $('#sm-error').hide();
                    this.innerHTML = 'Show error message';
                }
                break;
            case "reactivate-plugin":
                dialog = $("#reactivate-dialog");
                dialog.dialog({
                    autoOpen: false,
                    buttons: {
                        "Re-activate": function () {
                            $.post(ajaxurl, {
                                'action': 'sm_clear_fatal_error'
                            }, function () {
                                notice.html('<p><strong>' + sm_error_recovery_data.plugin_name + '</strong> has been activated successfully.</p>');
                                notice.removeClass('loading').removeClass('notice-error').addClass('notice-success');
                            });
                            $(this).dialog("close");
                        },
                        "Cancel": function () {
                            notice.removeClass('loading');
                            $(this).dialog("close");
                        }
                    },
                    beforeClose: function () {
                        curtain.removeClass('dialog');
                    }
                });
                dialog.dialog("open");

                notice.addClass('loading');
                curtain.addClass('dialog');
                curtain.on('click', function () {
                    dialog.dialog("close");
                    notice.removeClass('loading');
                    $(this).off('click');
                });
                break;
        }
    });


});