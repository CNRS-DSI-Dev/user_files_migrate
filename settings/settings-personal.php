<?php

/**
 * ownCloud - User Files Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Files_Migrate;

use \OCA\User_Files_Migrate\App\User_Files_Migrate;

// Logged
// \OCP\User::checkAdminUser();

$app = new User_Files_Migrate;
$c = $app->getContainer();

$tmpl = new \OCP\Template($c->query('AppName'), 'settings-personal');

$uid = $c->query('UserId');
$infos = $c->query('RequestService')->getInfos();

$ownRequestWaiting = false;
$ownRequestRecipient = '';
if (!empty($infos['ownRequest']['requesterUid']) and $infos['ownRequest']['requesterUid'] == $uid) {
    $ownRequestWaiting = true;
    $ownRequestRecipient = $infos['ownRequest']['recipientUid'];
}

$extRequestWaiting = false;
$extRequestRequester = '';
if (!empty($infos['extRequest']['recipientUid']) and $infos['extRequest']['recipientUid'] == $uid) {
    $extRequestWaiting = true;
    $extRequestId = $infos['extRequest']['requestId'];
    $extRequestRequester = $infos['extRequest']['requesterUid'];

    $requesterFileSize = $c->query('RequestService')->getRequesterUsedSpace($extRequestRequester);
    $humanRequesterFileSize = \OC_Helper::humanFileSize($requesterFileSize);

    $ownFileSize = $c->query('RequestService')->getFreeSpace();
    // $ownFileSize = 10; // test
    $humanOwnFileSize = \OC_Helper::humanFileSize($ownFileSize);

    $sizeWarning = false;
    if ($requesterFileSize > $ownFileSize) {
        $sizeWarning = true;
    }
}

// the current user has a request waiting
$tmpl->assign('own_request_waiting', $ownRequestWaiting);
if ($ownRequestWaiting) {
    $tmpl->assign('recipient_uid', $ownRequestRecipient);
}

// there is a request for current user
$tmpl->assign('ext_request_waiting', $extRequestWaiting);
$tmpl->assign('ext_file_size', $humanRequesterFileSize);
$tmpl->assign('own_file_size', $humanOwnFileSize);
$tmpl->assign('ext_request_id', $extRequestId);
$tmpl->assign('size_warning', $sizeWarning);

$tmpl->assign('from_uid', $extRequestRequester);

if ($sizeWarning) {
    $tmpl->assign('ufm_confirm', ' disabled="disabled"');
}
else {
    $tmpl->assign('ufm_confirm', '');
}

return $tmpl->fetchPage();
