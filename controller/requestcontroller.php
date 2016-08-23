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
use \OCP\IRequest;
use \OCP\IL10N;
use \OCA\User_Files_Migrate\Db\RequestMapper;
use \OCA\User_Files_Migrate\Db\Request;

class RequestController extends ApiController
{

    protected $requestService;
    protected $userId;

    public function __construct($appName, IRequest $request, IL10N $l, RequestMapper $requestMapper, \OCA\User_Files_Migrate\Service\RequestService$requestService, $userId)
    {
        parent::__construct($appName, $request, 'GET, POST');
        $this->l = $l;
        $this->requestMapper = $requestMapper;
        $this->requestService = $requestService;
        $this->userId = $userId;
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

}
