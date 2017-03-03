<?php

/**
 * ownCloud - User Files Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2015 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Files_Migrate\Controller;

use \OCP\AppFramework\ApiController;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\IUserManager;
use \OCP\IGroupManager;
use \OCP\IRequest;
use \OCP\IL10N;
use \OCA\User_Files_Migrate\Db\RequestMapper;
use \OCA\User_Files_Migrate\Db\Request;

class RequestController extends ApiController
{

    protected $requestService;
    protected $userId;
    protected $userManager;
    protected $groupManager;

    public function __construct($appName, IRequest $request, IL10N $l, RequestMapper $requestMapper, \OCA\User_Files_Migrate\Service\RequestService$requestService, $userId, IUserManager $userManager, IGroupManager $groupManager)
    {
        parent::__construct($appName, $request, 'GET, POST');
        $this->l = $l;
        $this->requestMapper = $requestMapper;
        $this->requestService = $requestService;
        $this->userId = $userId;
        $this->groupManager = $groupManager;
        $this->userManager = $userManager;
    }

    /**
     * Create a request
     * @NoAdminRequired
     * @param string $recipientUid
     */
    public function ask($recipientUid)
    {
        if ($recipientUid == $this->userId) {
            // $response = new JSONResponse();
            return array(
                'status' => 'self',
                'data' => array(
                    'msg' => "Requesting a migration to self is not allowed.",
                ),
            );
        }

        try {
            if (empty($recipientUid)) {
                throw new \Exception($this->l->t('Please set the recipient identifier.'));
            }
            $request = $this->requestMapper->saveRequest($this->userId, $recipientUid);
        }
        catch(\Exception $e) {
            $response = new JSONResponse();
            return array(
                'status' => 'error',
                'data' => array(
                    'msg' => $e->getMessage(),
                ),
            );
        }

        return array(
            'status' => 'success',
            'data' => array(
                'msg' => 'Request saved',
                'requestId' => $request->getId(),
            ),
        );
    }

    /**
     * Confirm a request
     * @NoAdminRequired
     * @param string $recipientUid
     */
    public function confirm($request_id)
    {
        try {
            $this->requestMapper->confirmRequest($this->userId, $request_id);
        }
        catch(\Exception $e) {
            $response = new JSONResponse();
            return array('code' => 'ko', 'msg' => $e->getMessage());
        }

        return array(
            'status' => 'success',
            'data' => array(
                'msg' => $this->l->t('Request confirmed'),
            ),
        );
    }

    /**
     * Cancel a request
     * @NoAdminRequired
     * @param string $recipientUid
     */
    public function cancel($request_id)
    {
        try {
            $this->requestMapper->cancelRequest($this->userId, $request_id);
        }
        catch(\Exception $e) {
            $response = new JSONResponse();
            return array('code' => 'ko', 'msg' => $e->getMessage());
        }

        return array(
            'status' => 'success',
            'data' => array(
                'msg' => $this->l->t('Request cancelled'),
            ),
        );
    }

    /**
     * Returns
     * @NoAdminRequired
     */
    public function get()
    {
        $result = array(
            'test' => 'ok',
        );

        return $result;
    }

    /**
     * returns migration requests list for a given user
     * @param  [type]  $uid    [description]
     * @param  integer $status [description]
     * @return [type]          [description]
     * @NoAdminRequired
     */
    public function requests($uid=null, $status=0)
    {
        \OC_Util::checkSubAdminUser();

        if (is_null($uid)) {
            $uid = $this->userId;
            $response = new JSONResponse();
            return array(
                'status' => 'error',
                'data' => array(
                    'msg' => 'No uid given',
                ),
            );
        }

        $user = $this->userManager->get($uid);
        $currentUser = $this->userManager->get($this->userId);

        $isAdmin = $this->groupManager->isAdmin($this->userId);

        if (!$isAdmin and !$this->groupManager->getSubAdmin()->isUserAccessible($currentUser, $user)) {
            return array(
                'status' => 'error',
                'data' => array(
                    'msg' => 'Authentication error',
                ),
            );
        }

        try {
            $ownRequest = $this->requestMapper->findOwnRequest($uid);
            $extRequest = $this->requestMapper->findExtRequest($uid);
        }
        catch(\Exception $e) {
            $response = new JSONResponse();
            return array(
                'status' => 'error',
                'data' => array(
                    'msg' => $e->getMessage(),
                ),
            );
        }

        $requestList = [];
        if (!empty($ownRequest)) {
            $row = [
                'requester' => ($ownRequest->getRequesterUid() == $uid) ? '[ user ]' : $ownRequest->getRequesterUid(),
                'recipient' => ($ownRequest->getRecipientUid() == $uid) ? '[ user ]' : $ownRequest->getRecipientUid(),
                'date' => $ownRequest->getDateRequest(),
            ];
            switch($ownRequest->getStatus()) {
                case RequestMapper::CREATED: {
                    $status = 'CREATED';
                    break;
                }
                case RequestMapper::CONFIRMED: {
                    $status = 'CONFIRMED';
                    break;
                }
                default: {
                    $status = '';
                }
            }
            $row['status'] = $status;
            array_push($requestList, $row);
        }

        if (!empty($extRequest)) {
            $row = [
                'requester' => ($extRequest->getRequesterUid() == $uid) ? '[ user ]' : $extRequest->getRequesterUid(),
                'recipient' => ($extRequest->getRecipientUid() == $uid) ? '[ user ]' : $extRequest->getRecipientUid(),
                'date' => $extRequest->getDateRequest(),
            ];
            switch($extRequest->getStatus()) {
                case RequestMapper::CREATED: {
                    $status = 'CREATED';
                    break;
                }
                case RequestMapper::CONFIRMED: {
                    $status = 'CONFIRMED';
                    break;
                }
                default: {
                    $status = '';
                }
            }
            $row['status'] = $status;
            array_push($requestList, $row);
        }

        return array(
            'status' => 'success',
            'data' => array(
                'msg' => $this->l->t('Migration requests'),
                'requests' => $requestList,
            ),
        );
    }

}
