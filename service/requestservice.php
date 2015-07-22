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

    public function __construct(\OCA\User_Files_Migrate\Db\RequestMapper $requestMapper, $userId)
    {
        $this->requestMapper = $requestMapper;
        $this->userId = $userId;
    }

    /**
     * Returns owned and external migration requests
     * @return array
     */
    public function getInfos()
    {
        $ownRequest = $this->requestMapper->findOwnRequest($this->userId);
        $extRequest = $this->requestMapper->findExtRequest($this->userId);

        $infos = array(
            'ownRequest' => $ownRequest,
            'extRequest' => $extRequest,
        );

        return $infos;
    }

    /**
     * Returns the total size of a user folder (files only)
     * @param  string $uid User ID
     * @return int|false
     */
    function getRequesterUsedSpace($uid)
    {
        $view = new \OC\Files\View();
        $this->getFilesSize($view, '/' . $uid . '/files', $requesterFileSize);

        return $requesterFileSize;
    }

    /**
     * Returns the free space size for the current user
     * @return int
     */
    public function getFreeSpace()
    {
        $storageInfo = \OC_Helper::getStorageInfo();
        $fileSize = $storageInfo['free'];

        return $fileSize;
    }

    /**
     * Get some user informations on files and folders
     * @param \OC\Files\View $view
     * @param string $path the path
     * @param int $fileSize to store the total filesize
     */
    protected function getFilesSize($view, $path='', &$fileSize) {
        $dc = $view->getDirectoryContent($path);

        foreach($dc as $item) {
            if ($item->isShared()) {
                continue;
            }

            // if folder, recurse
            if ($item->getType() == \OCP\Files\FileInfo::TYPE_FOLDER) {
                $this->getFilesSize($view, $item->getPath(), $fileSize);
            }
            else {
                $fileSize += $item->getSize();
            }
        }
    }

}
