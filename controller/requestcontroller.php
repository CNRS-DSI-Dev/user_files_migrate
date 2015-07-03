<?php

/**
 * ownCloud - User Files Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2015 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Files_Migrate\Controller;

use \OCP\AppFramework\APIController;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\IRequest;
use \OCA\User_Files_Migrate\Db\RequestMapper;
use \OCA\User_Files_Migrate\Db\Request;

class RequestController extends APIController
{

    protected $requestService;
    protected $userId;

    public function __construct($appName, IRequest $request, RequestMapper $requestMapper, $requestService, $userId)
    {
        parent::__construct($appName, $request, 'GET, POST');
        $this->requestMapper = $requestMapper;
        $this->requestService = $requestService;
        $this->userId = $userId;
    }

    /**
     * Create a request
     * @NoAdminRequired
     * @CORS
     * @param string $recipientUid
     */
    public function ask($recipientUid)
    {
        if ($recipientUid == $this->userId) {
            $response = new JSONResponse();
            return array(
                'status' => 'self',
                'data' => array(
                    'msg' => "Requesting a migration to self is not allowed.",
                ),
            );
        }

        try {
            if (empty($recipientUid)) {
                throw new \Exception('Please set the recipient identifier.');
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
                'msg' => 'Request confirmed',
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
                'msg' => 'Request cancelled',
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
            'test'              => 'ok',
        );

        return $result;
    }

}
