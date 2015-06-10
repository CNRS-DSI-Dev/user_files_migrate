<?php

/**
 * ownCloud - User Files Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
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

    public function __construct($appName, IRequest $request, RequestMapper $requestMapper, $RequestService, $userId)
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
        try {
            if (empty($recipientUid)) {
                throw new \Exception('Please set the recipient identifier.');
            }
            $this->requestMapper->saveRequest($this->userId, $recipientUid);
        }
        catch(\Exception $e) {
            $response = new JSONResponse();
            return array('msg' => $e->getMessage());
        }

        return true;
    }

    /**
     * Confirm a request
     * @NoAdminRequired
     * @CORS
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

        return array('code' => 'ok');
    }

    /**
     * Returns
     * @NoAdminRequired
     * @CORS
     */
    public function get()
    {
        $result = array(
            'test'              => 'ok',
        );

        return $result;
    }

}
