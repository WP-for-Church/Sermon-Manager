jQuery(document).ready(function ($) {
    $('.sm-actions > .button').on('click', function () {
        var dialog = '',
            curtain = $("#sm-curtain");

        switch (this.id) {
            case "send-report":
                dialog = $("#send-report-dialog");
                dialog.dialog({
                    autoOpen: false,
                    buttons: {
                        "Send": function () {
                            jQuery.get('https://wpforchurch.com/?WPFC=submit_issue&stack=' + sm_error_recovery_data.stacktrace + '&environment_info=' + sm_error_recovery_data.environment_info + '&user_comments=' + encodeURIComponent($('#issue-info').val()) + '&email=' + encodeURIComponent($('#issue-email').val()), '', function () {
                                $('#send-report').hide();
                                $('#notice-message').replaceWith('<p>The issue has been submitted. <strong>Thank you</strong> for helping <strong>' + sm_error_recovery_data.plugin_name + '</strong> be a better plugin.</p>' +
                                    '<p>If you have any details about the issue, you can open <a href="https://github.com/WP-for-Church/Sermon-Manager/issues/new" target="_blank">an issue</a> or if you have purchased a support plan, <a href="https://wpforchurch.com/my/submitticket.php?step=2&deptid=2&subject=Sermon%20Manager%3A%20Fatal%20Error" target="_blank">an ticket</a>.</p>');

                                $('#sm-fatal-error-notice').removeClass('loading').removeClass('notice-error').addClass('notice-warning');
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

                $('#sm-fatal-error-notice').addClass('loading');
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
                                'action': 'sm_clear_fatal_error',
                                'disable_recovery': $('#sm-disable-recovery').is(':checked') ? '1' : '0'
                            }, function () {
                                var notice = $('#sm-fatal-error-notice');
                                notice.html('<p><strong>' + sm_error_recovery_data.plugin_name + '</strong> has been activated successfully.</p>');
                                notice.removeClass('notice-error').addClass('notice-success');
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

                curtain.addClass('dialog');
                curtain.on('click', function () {
                    dialog.dialog("close");
                    $(this).off('click');
                });
                break;
        }
    });

    $('#sm-disable-recovery').on('change', function () {
        if (this.checked) {
            $('#sm-disable-recovery-notice').show();
        } else {
            $('#sm-disable-recovery-notice').hide();
        }
    })
});