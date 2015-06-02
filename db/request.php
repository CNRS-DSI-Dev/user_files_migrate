<?php

/**
 * ownCloud - User Files Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Files_Migrate\Db;

use \OCP\AppFramework\Db\Entity;

class Request extends Entity {
    protected $requesterUid;
    protected $recipientUid;
    protected $dateRequest;
    protected $dateEnd;
    protected $closed;

    public function __construct() {
        $this->addType('closed', 'boolean');
    }
}
