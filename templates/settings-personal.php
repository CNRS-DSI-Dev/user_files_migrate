<?php

/**
 * ownCloud - User Files Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

\OCP\Util::addStyle('user_files_migrate', 'settings-personnal');
\OCP\Util::addScript('user_files_migrate', 'user_files_migrate_settings');

?>

<div id="user_files_migrate" class="section">
    <h2><?php p($l->t('User Files Migrate')); ?></h2>

    <span id="ufm_notifications_msg" class="msg"></span>

    <div id="ufm_request" style="display: <?php p($_['createDisplay']);?>">
        <form id="ufm_request_form">
            <?php p($l->t('If you want to request files migration from your account to another account, please set the other account identifier: ')); ?>
            <input type="text" id="recipient_uid">
            <input type="submit" value="<?php p($l->t('Request'));?>">
        </form>
    </div>

    <div id="ufm_cancel" style="display: <?php p($_['cancelDisplay']);?>">
        <form id="ufm_cancel_form">
            <?php print_unescaped($l->t('You requested a files migration from your account to the account which identifier is: <span>%s</span>', array($_['ownRequestRecipient']))); ?>
            <input type="hidden" id="own_request_id" value="<?php p($_['ownRequestId']); ?>" disabled="disabled">
            <input type="submit" id="ownMigrationCancel" value="<?php p($l->t('Cancel'));?>">
        </form>
    </div>

    <p class="ufm_msg_validate" style="display: <?php p($_['msgValidateDisplay']);?>">
        <?php p($l->t("Please connect with the other account to validate this request")); ?>.
    </p>

    <p class="ufm_msg_confirmed" style="display: <?php p($_['msgConfirmedDisplay']);?>">
        <?php p($l->t("Your files migration request has been confirmed. It will be processed soon.")); ?>
    </p>

    <div id="extRequestWaiting" style="display: <?php p($_['waitingDisplay']);?>">
        <div>
            <?php p($l->t('A files migration has been requested from the account which identifier is: ')); ?>
            <input type="text" id="requester_uid" disabled="disabled" value="<?php p($_['from_uid']); ?>">
        </div>
        <div id="dataSize">
            <div class="dataRow">
                <div><?php p($l->t("From account's files size")); ?></div><div><?php p($l->t("Your account disk space available")); ?></div>
            </div>
            <div class="dataRow">
                <div<?php if ($_['size_warning']) { ?> class="sizeWarning"<?php } ?>>
                    <?php p($_['ext_file_size']); ?>
                </div>
                <div>
                    <?php p($_['own_file_size']); ?>
                </div>
                <form id="user_files_migrate_confirm">
                    <input type="hidden" id="ext_request_id" name="ext_request_id" value="<?php echo $_['extRequestId'];?>">
                    <input type="submit" id="migrationCancel"<?php echo $_['ufm_confirm'];?> value="<?php p($l->t('Cancel'));?>">
                    <input type="submit" id="migrationConfirm"<?php echo $_['ufm_confirm'];?> value="<?php p($l->t('Confirm'));?>">
                </form>
            </div>
        </div>
    </div>

</div>

