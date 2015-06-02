<?php

/**
 * ownCloud - User Files Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Files_Migrate\Db;

use \OCP\IDb;
use \OCP\AppFramework\Db\Mapper;

class RequestMapper extends Mapper {
    public function __construct(IDb $db) {
        parent::__construct($db, 'user_files_migrate');
    }

    public function findOwnRequest($uid, $limit=null, $offset=null) {
        $sql = "SELECT * FROM *PREFIX*user_files_migrate WHERE requester_uid = ? AND closed = 0";
        try {
            $request = $this->findEntity($sql, array($uid), $limit, $offset);
        }
        catch (Exception $e) {
            $request = array();
        }

        return $request;
    }

    public function findExtRequest($uid, $limit=null, $offset=null) {
        $sql = "SELECT * FROM *PREFIX*user_files_migrate WHERE recipient_uid = ? AND closed = 0";
        try {
            $request = $this->findEntity($sql, array($uid), $limit, $offset);
        }
        catch (Exception $e) {
            $request = array();
        }

        return $request;
    }
}
