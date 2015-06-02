<?php

/**
 * ownCloud - User Files Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Files_Migrate\Service;

class RequestService
{

    protected $requestMapper;
    protected $userId;

    public function __construct(\OCA\Dashboard\Db\RequestMapper $requestMapper, $userId)
    {
        $this->requestMapper = $requestMapper;
        $this->userId = $userId;
    }

    /**
     * Returns
     * @return array
     */
    public function getInfos()
    {
        $ownRequest = $this->requestMapper->findOwnRequest($this->userId);
        $extRequest = $this->requestMapper->findExtRequest($this->userId);

        return array(
            'ownRequest' => array(
                'requesterUid' => $ownRequest->getRequesterUid(),
                'recipientUid' => $ownRequest->getRecipientUid(),
            ),
            'extRequest' => array(
                'requesterUid' => $extRequest->getRequesterUid(),
                'recipientUid' => $extRequest->getRecipientUid(),
            ),
        );
    }

}
