<?php

/**
 * ownCloud - User Files Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2015 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Files_Migrate\Db;

use \OCP\IDb;
use \OCP\IL10N;
use \OCP\AppFramework\Db\Mapper;

class RequestMapper extends Mapper
{
    const CREATED = 1;
    const CONFIRMED = 2;
    const PROCESSED = 3;

    protected $l;

    public function __construct(IDb $db, IL10N $l)
    {
        $this->l = $l;

        parent::__construct($db, 'user_files_migrate');
    }

    public function saveRequest($requester_uid, $recipient_uid, $limit=null, $offset=null)
    {
        // recipient has already a pending request, keep the pending request and reject the new
        $sql = "SELECT * FROM *PREFIX*user_files_migrate WHERE recipient_uid = ? AND status != " . self::PROCESSED;
        try {
            $request = $this->findEntity($sql, array($recipient_uid), $limit, $offset);

            throw new \Exception($this->l->t('Server error: a pending request already exists for this recipient.'));
            return false;
        }
        catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
        }
        catch (\OCP\AppFramework\Db\MultipleObjectsReturnedException $e) {
            throw new \Exception($this->l->t('Server error: more than one request with same requester/recipient pair.'));
            return false;
        }

        // requester has already a pending request, delete this pending request and create the new
        $sql = "SELECT * FROM *PREFIX*user_files_migrate WHERE requester_uid = ? AND status != " . self::PROCESSED;
        try {
            $request = $this->findEntity($sql, array($requester_uid), $limit, $offset);

            $this->delete($request);
        }
        catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
        }
        catch (\OCP\AppFramework\Db\MultipleObjectsReturnedException $e) {
            throw new \Exception($this->l->t('Server error: more than one request with same requester/recipient pair.'));
            return false;
        }

        $request = new Request;
        $request->setRequesterUid($requester_uid);
        $request->setRecipientUid($recipient_uid);
        $request->setDateRequest(date('Y-m-d H:i:s'));
        $request->setStatus(self::CREATED);

        $this->insert($request);

        return $request;
    }

    public function confirmRequest($recipientUid, $requestId, $limit=null, $offset=null)
    {
        // additionnal clauses (recipient_uid and closed) are here for security check
        $sql = "SELECT * FROM *PREFIX*user_files_migrate WHERE id = ? AND recipient_uid = ? AND status = " . self::CREATED;

        try {
            $request = $this->findEntity($sql, array($requestId, $recipientUid), $limit, $offset);
        }
        catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
            throw new \Exception($this->l->t('Server error: no open unconfirmed request for this recipient.'));
            return false;
        }
        catch (\OCP\AppFramework\Db\MultipleObjectsReturnedException $e) {
            throw new \Exception($this->l->t('Server error: more than one request with same requester/recipient pair.'));
            return false;
        }

        $request->setStatus(self::CONFIRMED);
        $this->update($request);

        return $request;
    }

    public function cancelRequest($userId, $requestId, $limit=null, $offset=null)
    {
        // additionnal clauses (recipient_uid and closed) are here for security check
        $sql = "SELECT * FROM *PREFIX*user_files_migrate WHERE id = ? AND (recipient_uid = ? OR requester_uid = ?) AND status = " . self::CREATED;

        try {
            $request = $this->findEntity($sql, array($requestId, $userId, $userId), $limit, $offset);
        }
        catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
            throw new \Exception($this->l->t('Server error: no open unconfirmed request with this id.'));
            return false;
        }
        catch (\OCP\AppFramework\Db\MultipleObjectsReturnedException $e) {
            throw new \Exception($this->l->t('Server error: more than one request with same id!'));
            return false;
        }

        $this->delete($request);

        return $request;
    }

    public function findOwnRequest($uid, $limit=null, $offset=null)
    {
        $sql = "SELECT * FROM *PREFIX*user_files_migrate WHERE requester_uid = ? AND status != " . self::PROCESSED;
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
        $sql = "SELECT * FROM *PREFIX*user_files_migrate WHERE recipient_uid = ? AND status != " . self::PROCESSED;
        try {
            $request = $this->findEntity($sql, array($uid), $limit, $offset);
        }
        catch (\Exception $e) {
            $request = array();
        }

        return $request;
    }

    public function findUnconfirmedRequests($limit=null, $offset=null)
    {
        return $this->findRequests(self::CREATED);
    }

    public function findConfirmedRequests($limit=null, $offset=null)
    {
        return $this->findRequests(self::CONFIRMED);
    }

    public function findClosedRequests($limit=null, $offset=null)
    {
        return $this->findRequests(self::PROCESSED);
    }

    protected function findRequests($status, $limit=null, $offset=null)
    {
        $sql = "SELECT * FROM *PREFIX*user_files_migrate WHERE status = ?";

        $requests = $this->findEntities($sql, array($status), $limit, $offset);

        return $requests;
    }

    public function closeRequest($requestId, $limit=null, $offset=null)
    {
        // additionnal clauses (closed and confirmed) are here for security check
        $sql = "SELECT *
            FROM *PREFIX*user_files_migrate
            WHERE id = ? AND status = " . self::CONFIRMED;

        try {
            $request = $this->findEntity($sql, array($requestId), $limit, $offset);
        }
        catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
            throw new \Exception($this->l->t('Server error: no open confirmed request with this id.'));
            return false;
        }
        // useless ?
        catch (\OCP\AppFramework\Db\MultipleObjectsReturnedException $e) {
            throw new \Exception($this->l->t('Server error: more than one request with this id !!'));
            return false;
        }

        $request->setStatus(self::PROCESSED);
        $request->setDateEnd(date('Y-m-d H:i:s'));
        $this->update($request);

        return $request;
    }
}
