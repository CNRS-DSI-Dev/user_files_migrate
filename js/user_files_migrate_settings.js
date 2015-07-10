$(document).ready(function() {
    // var url = OC.generateUrl('apps/user_files_migrate/api/1.0/request');

    // Create request
    $('#ufm_request_form input[type=submit]').on('click', function(event) {
        event.preventDefault();

        OC.msg.startSaving('#ufm_notifications_msg');

        var value = $('#recipient_uid').val();
        $.post(
            OC.generateUrl('apps/user_files_migrate/api/1.0/request'),
            {'recipientUid': value }
        )
        .success(function(data) {
            OC.msg.finishedSaving('#ufm_notifications_msg', data);
            if (data.status == 'self') {
                alert(data.data.msg);
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
            alert('KO, server error while creating request.');
        })

        return false;
    });

    // Confirm request
    if ($('#migrationConfirm').is(':disabled') == false) {
        $('#migrationConfirm').on('click', function(event) {
            event.preventDefault();

            // /usr/share/doc/udev/README.Debian.gz
            var requesterUid = $('#requester_uid').val();
            OCdialogs.confirm('Are you sure to CONFIRM this migration request from ' + requesterUid + ' to ' + OC.currentUser + ' ?', 'Confirm migration request', confirmExtMigrationRequest, true);
        });
    }

    // Cancel request
    $('#ownMigrationCancel').on('click', function(event) {
        event.preventDefault();

        OCdialogs.confirm('Are you sure to CANCEL this migration request ?', 'Cancel migration request', cancelOwnMigrationRequest, true);
    });
    if ($('#migrationCancel').is(':disabled') == false) {
        $('#migrationCancel').on('click', function(event) {
            event.preventDefault();

            OCdialogs.confirm('Are you sure to CANCEL this migration request ?', 'Cancel migration request', cancelExtMigrationRequest, true);
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
                $('#extRequestWaiting').text('Your migration request from ' + $('#requester_uid').val() + ' is confirmed. It will be processed soon.');
            }
        })
        .fail(function() {
            alert('KO, server error while confirming request.')
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
            $('#extRequestWaiting').text('Your migration request has been cancelled.');
        }
    })
    .fail(function() {
        alert('KO, server error while confirming request.')
    })
}
