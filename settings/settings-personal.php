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
$infos = $c->query('\OCA\User_Files_Migrate\Service\RequestService')->getInfos();
$ownRequest = $infos['ownRequest'];
$extRequest = $infos['extRequest'];

// no owned request
$createDisplay = 'block';
$cancelDisplay = 'none';
$msgValidateDisplay = 'none';
$msgConfirmedDisplay = 'none';
$ownRequestId = '';
$ownRequestRecipient = '';

// with owned request
if (!empty($ownRequest)) {
    if ($ownRequest->getStatus() == Db\RequestMapper::CREATED) {
        $createDisplay = 'none';
        $cancelDisplay = 'block';
        $msgValidateDisplay = 'block';
        $ownRequestId = $ownRequest->getId();
        $ownRequestRecipient = $ownRequest->getRecipientUid();
    }
    elseif ($ownRequest->getStatus() == Db\RequestMapper::CONFIRMED) {
        $createDisplay = 'none';
        $msgConfirmedDisplay = 'block';
    }
}

// no ext request
$waitingDisplay = 'none';
$extRequestRequester = '';
$sizeWarning = false;
$humanRequesterFileSize = '';
$humanOwnFileSize = '';
$extRequestId = '';

// with ext request
if (!empty($extRequest)) {
    $createDisplay = 'none';

    if ($extRequest->getStatus() == Db\RequestMapper::CREATED) {
        $waitingDisplay = 'block';
        $extRequestId = $extRequest->getId();
        $extRequestRequester = $extRequest->getRequesterUid();

        $requesterFileSize = $c->query('\OCA\User_Files_Migrate\Service\RequestService')->getRequesterUsedSpace($extRequestRequester);
        if (empty($requesterFileSize)) {
            $humanRequesterFileSize = "Empty";
        }
        else {
            $humanRequesterFileSize = \OC_Helper::humanFileSize($requesterFileSize);
        }

        $ownFileSize = $c->query('\OCA\User_Files_Migrate\Service\RequestService')->getFreeSpace();
        // $ownFileSize = 10; // test
        $humanOwnFileSize = \OC_Helper::humanFileSize($ownFileSize);

        if ($requesterFileSize > $ownFileSize) {
            $sizeWarning = true;
        }
    }
    elseif ($extRequest->getStatus() == Db\RequestMapper::CONFIRMED) {
        $msgConfirmedDisplay = 'block';
    }
}


$tmpl->assign('createDisplay', $createDisplay);
$tmpl->assign('cancelDisplay', $cancelDisplay);
$tmpl->assign('msgValidateDisplay', $msgValidateDisplay);
$tmpl->assign('msgConfirmedDisplay', $msgConfirmedDisplay);
$tmpl->assign('ownRequestId', $ownRequestId);
$tmpl->assign('ownRequestRecipient', $ownRequestRecipient);

$tmpl->assign('waitingDisplay', $waitingDisplay);
$tmpl->assign('from_uid', $extRequestRequester);
$tmpl->assign('size_warning', $sizeWarning);
$tmpl->assign('ext_file_size', $humanRequesterFileSize);
$tmpl->assign('own_file_size', $humanOwnFileSize);
$tmpl->assign('extRequestId', $extRequestId);
if ($sizeWarning) {
    $tmpl->assign('ufm_confirm', ' disabled="disabled"');
}
else {
    $tmpl->assign('ufm_confirm', '');
}

return $tmpl->fetchPage();
