$(document).ready(function() {
    // var url = OC.generateUrl('apps/user_files_migrate/api/1.0/request');

    $('#user_files_migrate_form input[type=submit]').on('click', function(event) {
        event.preventDefault();

        var value = $('#recipient_uid').val();
        $.post(
            OC.generateUrl('apps/user_files_migrate/api/1.0/request'),
            {'recipientUid': value }
        )
        .success(function() {
            // good
        })
        .fail(function() {
            alert('KO, server error while creating request.');
        })

        return false;
    });

    if ($('#migrationConfirm').is(':disabled') == false) {
        $('#migrationConfirm').on('click', function(event) {
            event.preventDefault();

            var value = $('#ext_request_id').val()
            var url = OC.generateUrl('apps/user_files_migrate/api/1.0/confirm/' + value);
            $.get(url)
            .success(function(data) {
                if (data.code == 'ok') {
                    $('#migrationConfirm').attr('disabled', 'disabled');
                }
            })
            .fail(function() {
                alert('KO, server error while confirming request.')
            })

            return false;
        });
    }
});
