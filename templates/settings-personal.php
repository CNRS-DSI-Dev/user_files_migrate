<?php

/**
 * ownCloud - User Files Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

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
</style>
<div id="user_files_migrate" class="section">
    <h2><?php p($l->t('User Files Migrate')); ?></h2>

    <?php if ($_['own_request_waiting']): ?>
    <div>
        <?php p($l->t('You want to request files migration from your account to the account which identifier is: ')); ?>
        <input type="text" value="<?php p($_['recipient_uid']); ?>">
        <button>Request</button>
    </div>
    <?php else: ?>
    <div>
        <?php p($l->t('If you want to request files migration from your account to another account, please set the other account identifier: ')); ?>
        <input type="text">
        <button>Request</button>
    </div>
    <?php endif; ?>

    <?php if ($_['ext_request_waiting']): ?>
    <div id="extRequestWaiting">
        <div>
            <?php p($l->t('A files migration has been requested from the account which identifier is: ')); ?>
            <input type="text" disabled="disabled" value="<?php p($_['from_uid']); ?>">
        </div>
        <div id="dataSize">
            <div class="dataRow">
                <div>From account's files size</div><div>Your account size available</div>
            </div>
            <div class="dataRow">
                <div style="color: red">1,2Go</div><div>1Go</div><button<?php echo $_['ufm_confirm'];?>>Confirm</button>
            </div>
        </div>
    </div>
    <?php endif; ?>


</div>

