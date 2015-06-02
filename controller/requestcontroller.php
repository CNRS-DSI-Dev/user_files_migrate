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

class RequestController extends APIController {

    protected $requestService;

    public function __construct($appName, IRequest $request, RequestMapper $requestMapper, $RequestService, $userId){
        parent::__construct($appName, $request, 'GET, POST');
        $this->requestMapper = $requestMapper;
        $this->requestService = $requestService;
        $this->userId = $userId;
    }

    /**
     * Returns
     * @NoAdminRequired
     * @CORS
     */
    public function ask() {
        $request = new Request;

        $request->setRequesterUid($this->userId);
        $this->requestMapper->insert($request);

        return $request;
    }

    /**
     * Returns
     * @NoAdminRequired
     * @CORS
     */
    public function get() {
        $result = array(
            'test'              => 'ok',
        );

        return $result;
    }

}
