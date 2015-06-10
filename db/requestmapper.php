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

class RequestMapper extends Mapper
{
    public function __construct(IDb $db)
    {
        parent::__construct($db, 'user_files_migrate');
    }

    public function saveRequest($requester_uid, $recipient_uid, $limit=null, $offset=null)
    {
        $sql = "SELECT * FROM *PREFIX*user_files_migrate WHERE requester_uid = ? AND recipient_uid = ? AND closed = 0";
        try {
            $request = $this->findEntity($sql, array($requester_uid, $recipient_uid), $limit, $offset);

            $this->delete($request);
        }
        catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
        }
        catch (\OCP\AppFramework\Db\MultipleObjectsReturnedException $e) {
            throw new \Exception('Server error: more than one request with same requester/recipient pair.');
            return false;
        }

        $request = new Request;
        $request->setRequesterUid($requester_uid);
        $request->setRecipientUid($recipient_uid);

        $this->insert($request);

        return $request;
    }

    public function confirmRequest($recipientUid, $requestId, $limit=null, $offset=null)
    {
        // additionnal clauses (recipient_uid and closed) are here for security check
        $sql = "SELECT *
            FROM *PREFIX*user_files_migrate
            WHERE id = ? AND recipient_uid = ? AND closed = 0 AND confirmed = 0";

        try {
            $request = $this->findEntity($sql, array($requestId, $recipientUid), $limit, $offset);
        }
        catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
            throw new \Exception('Server error: no open unconfirmed request for this recipient.');
            return false;
        }
        catch (\OCP\AppFramework\Db\MultipleObjectsReturnedException $e) {
            throw new \Exception('Server error: more than one request with same requester/recipient pair.');
            return false;
        }

        $request->setConfirmed(true);
        $this->update($request);

        return $request;
    }

    public function findOwnRequest($uid, $limit=null, $offset=null)
    {
        $sql = "SELECT * FROM *PREFIX*user_files_migrate WHERE requester_uid = ? AND closed = 0";
        try {
            $request = $this->findEntity($sql, array($uid), $limit, $offset);
        }
        catch (\Exception $e) {
            $request = array();
        }

        return $request;
    }

    public function findExtRequest($uid, $limit=null, $offset=null)
    {
        $sql = "SELECT * FROM *PREFIX*user_files_migrate WHERE recipient_uid = ? AND closed = 0";
        try {
            $request = $this->findEntity($sql, array($uid), $limit, $offset);
        }
        catch (\Exception $e) {
            $request = array();
        }

        return $request;
    }

    public function findConfirmedRequest($limit=null, $offset=null)
    {
        $sql = "SELECT * FROM *PREFIX*user_files_migrate WHERE confirmed = 1 AND closed = 0";

        $requests = $this->findEntities($sql, array($uid), $limit, $offset);

        return $requests;
    }

    public function closeRequest($requestId, $limit=null, $offset=null)
    {
        // additionnal clauses (closed and confirmed) are here for security check
        $sql = "SELECT *
            FROM *PREFIX*user_files_migrate
            WHERE id = ? AND closed = 0 AND confirmed = 1";

        try {
            $request = $this->findEntity($sql, array($requestId), $limit, $offset);
        }
        catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
            throw new \Exception('Server error: no open unconfirmed request with this id.');
            return false;
        }
        // useless ?
        catch (\OCP\AppFramework\Db\MultipleObjectsReturnedException $e) {
            throw new \Exception('Server error: more than one request with this id !!!.');
            return false;
        }

        $request->setClosed(true);
        $this->update($request);

        return $request;
    }
}
