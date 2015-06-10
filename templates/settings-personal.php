<?php

/**
 * ownCloud - User Files Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

\OCP\Util::addScript('user_files_migrate', 'user_files_migrate_settings');

?>
<style type="text/css">
    #user_files_migrate #extRequestWaiting > div:first-child {
        color: green;
    }
    #user_files_migrate #extRequestWaiting #dataSize{
        display: table;
        width: 35%;
        text-align: center;
    }

    #user_files_migrate #extRequestWaiting #dataSize .dataRow {
        display: table-row;
    }

    #user_files_migrate #extRequestWaiting #dataSize .dataRow div {
        display: table-cell;
    }
    #user_files_migrate .sizeWarning {
        color: red;
        font-weight: bold;
    }
</style>
<div id="user_files_migrate" class="section">
    <h2><?php p($l->t('User Files Migrate')); ?></h2>

    <?php if ($_['own_request_waiting']): ?>
    <div>
        <form id="user_files_migrate_form">
            <?php p($l->t('You want to request files migration from your account to the account which identifier is: ')); ?>
            <input type="text" id="recipient_uid" value="<?php p($_['recipient_uid']); ?>">
            <input type="submit" value="Request">
        </form>
    </div>
    <?php else: ?>
    <div>
        <form id="user_files_migrate_form">
            <?php p($l->t('If you want to request files migration from your account to another account, please set the other account identifier: ')); ?>
            <input type="text" id="recipient_uid">
            <input type="submit" value="Request">
        </form>
    </div>
    <?php endif; ?>

    <?php if ($_['ext_request_waiting']): ?>
    <div id="extRequestWaiting">
        <div>
            <?php p($l->t('A files migration has been requested from the account which identifier is: ')); ?>
            <input type="text" id="requester_uid" disabled="disabled" value="<?php p($_['from_uid']); ?>">
        </div>
        <div id="dataSize">
            <div class="dataRow">
                <div>From account's files size</div><div>Your account disk space available</div>
            </div>
            <div class="dataRow">
                <div<?php if ($_['size_warning']) { ?> class="sizeWarning"<?php } ?>>
                    <?php p($_['ext_file_size']); ?>
                </div>
                <div>
                    <?php p($_['own_file_size']); ?>
                </div>
                <form id="user_files_migrate_confirm">
                    <input type="hidden" id="ext_request_id" name="ext_request_id" value="<?php echo $_['ext_request_id'];?>">
                    <input type="submit" id="migrationConfirm"<?php echo $_['ufm_confirm'];?> value="Confirm">
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>


</div>

