$(document).ready(function() {
    // var url = OC.generateUrl('apps/user_files_migrate/api/1.0/request');

    // Create request
    $('#ufm_request_form input[type=submit]').on('click', function(event) {
        event.preventDefault();

        var value = $('#recipient_uid').val();

        if (value == '') {
            alert(t('user_files_migrate', 'Please set the recipient identifier.'));
            return false;
        }

        OC.msg.startSaving('#ufm_notifications_msg');

        $.post(
            OC.generateUrl('apps/user_files_migrate/api/1.0/request'),
            {'recipientUid': value }
        )
        .success(function(data) {
            OC.msg.finishedSaving('#ufm_notifications_msg', data);
            if (data.status == 'self') {
                alert(t('user_files_migrate', data.data.msg));
                $('#ufm_notifications_msg').delay(3000).fadeOut(900);
                return
            }
            if (data.status == 'error') {
                alert(t('user_files_migrate', data.data.msg));
                $('#ufm_notifications_msg').delay(3000).fadeOut(900);
                return
            }
            $('#ufm_cancel').show();
            $('#ufm_cancel_form span').text($('#recipient_uid').val());

            $('#recipient_uid').val('');
            $('#ufm_request').hide();

            $('#own_request_id').val(data.data.requestId);

            $('.ufm_msg_validate').show();
            $('#extRequestWaiting').hide();
        })
        .fail(function(data) {
            alert(t('user_files_migrate', 'KO, server error while creating request.'));
        })

        return false;
    });

    // Confirm request
    if ($('#migrationConfirm').is(':disabled') == false) {
        $('#migrationConfirm').on('click', function(event) {
            event.preventDefault();

            // /usr/share/doc/udev/README.Debian.gz
            var requesterUid = $('#requester_uid').val();
            OCdialogs.confirm(t('user_files_migrate', 'Are you sure to CONFIRM this migration request from {requesterUid} to {currentUser} (the account {requesterUid2} will be blocked once this request confirmed) ?', {'requesterUid': requesterUid, 'currentUser': OC.currentUser, 'requesterUid2': requesterUid}), t('user_files_migrate', 'Confirm migration request'), confirmExtMigrationRequest, true);
        });
    }

    // Cancel request
    $('#ownMigrationCancel').on('click', function(event) {
        event.preventDefault();

        OCdialogs.confirm(t('user_files_migrate', 'Are you sure to CANCEL this migration request ?'), t('user_files_migrate', 'Cancel migration request'), cancelOwnMigrationRequest, true);
    });
    if ($('#migrationCancel').is(':disabled') == false) {
        $('#migrationCancel').on('click', function(event) {
            event.preventDefault();

            OCdialogs.confirm(t('user_files_migrate', 'Are you sure to CANCEL this migration request ?'), t('user_files_migrate', 'Cancel migration request'), cancelExtMigrationRequest, true);
        });
    }
});

function confirmExtMigrationRequest(ok) {
    if (ok) {
        OC.msg.startSaving('#ufm_notifications_msg');

        var value = $('#ext_request_id').val()
        var url = OC.generateUrl('apps/user_files_migrate/api/1.0/confirm/' + value);
        $.get(url)
        .success(function(data) {
            if (data.status == 'success') {
                OC.msg.finishedSaving('#ufm_notifications_msg', data);
                $('#extRequestWaiting').text(t('user_files_migrate', 'Your migration request from {requesterUid} is confirmed. It will be processed soon.', {'requesterUid': $('#requester_uid').val()}));
            }
        })
        .fail(function() {
            alert(t('user_files_migrate', 'KO, server error while confirming request.'));
        })
    }
}

function cancelOwnMigrationRequest(ok) {
    if (ok) {
        var value = $('#own_request_id').val();
        cancelMigrationRequest(value);
    }
}

function cancelExtMigrationRequest(ok) {
    if (ok) {
        var value = $('#ext_request_id').val();
        cancelMigrationRequest(value);
    }
}

function cancelMigrationRequest(requestId) {
    OC.msg.startSaving('#ufm_notifications_msg');

    var url = OC.generateUrl('apps/user_files_migrate/api/1.0/cancel/' + requestId);
    $.get(url)
    .success(function(data) {
        if (data.status == 'success') {
            OC.msg.finishedSaving('#ufm_notifications_msg', data);
            $('#ufm_request').show();
            $('#ufm_cancel').hide();
            $('.ufm_msg_validate').hide();
            $('#extRequestWaiting').show();
            $('#extRequestWaiting').text(t('user_files_migrate', 'Your migration request has been cancelled.'));
        }
    })
    .fail(function() {
        alert(t('user_files_migrate', 'KO, server error while cancelling request.'));
    })
}
