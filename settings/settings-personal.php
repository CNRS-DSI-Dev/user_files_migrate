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
$confirmDisabled = true;
if (!empty($infos['extRequest']['recipientUid']) and $infos['extRequest']['recipientUid'] == $uid) {
    $extRequestWaiting = true;
    $extRequestRequester = $infos['extRequest']['requesterUid'];
}

// the current user has a request waiting
$tmpl->assign('own_request_waiting', $ownRequestWaiting);
if ($ownRequestWaiting) {
    $tmpl->assign('recipient_uid', $ownRequestRecipient);
}

// there is a request for current user
$tmpl->assign('ext_request_waiting', $extRequestWaiting);
$tmpl->assign('from_uid', $extRequestRequester);

if ($confirmDisabled) {
    $tmpl->assign('ufm_confirm', ' disabled="disabled"');
}
else {
    $tmpl->assign('ufm_confirm', '');
}

return $tmpl->fetchPage();
