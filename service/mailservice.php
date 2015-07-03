<?php

/**
 * ownCloud - User Files Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2015 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Files_Migrate\Service;

use \OCP\IL10N;
use \OCP\IConfig;

class mailService
{
    protected $appName;
    protected $l;
    protected $config;
    protected $userManager;
    protected $groupManager;

    public function __construct($appName, IL10N $l, IConfig $config, $userManager, $groupManager)
    {
        $this->appName = $appName;
        $this->l = $l;
        $this->config = $config;
        $this->userManager = $userManager;
        $this->groupManager = $groupManager;
    }

    /**
     * Send a mail signaling end of files migration to user
     * @param int $requesterUid (the "from migration account" id)
     */
    public function mailUser($requesterUid)
    {
        $toAddress = $toName = $requesterUid;

        $theme = new \OC_Defaults;

        $subject = (string) $this->l->t('%s - Files Migration processed', array($theme->getTitle()));
        $html = new \OCP\Template($this->appName, "mail_user_html", "");
        $html->assign('requester', $requesterUid);
        $htmlMail = $html->fetchPage();

        $alttext = new \OCP\Template($this->appName, "mail_user_text", "");
        $alttext->assign('requester', $requesterUid);
        $altMail = $alttext->fetchPage();

        $fromAddress = $fromName = \OCP\Util::getDefaultEmailAddress('owncloud');

        try {
            \OCP\Util::sendMail($toAddress, $toName, $subject, $htmlMail, $fromAddress, $fromName, 1, $altMail);
        } catch (\Exception $e) {
            \OCP\Util::writeLog('user_files_migrate', "Can't send mail for user: " . $e->getMessage(), \OCP\Util::ERROR);
        }
    }

    /**
     * Send a mail signaling end of files migration to user
     * @param int $requesterUid (the "from migration account" id)
     * @param int $recipientUid (the "to migration account" id)
     */
    public function mailMonitors($requesterUid, $recipientUid)
    {
        $toAddress = $toName = $this->config->getSystemValue('monitoring_admin_email');

        $theme = new \OC_Defaults;

        $subject = (string) $this->l->t('%s - Files Migration processed', array($theme->getTitle()));
        $html = new \OCP\Template($this->appName, "mail_monitoring_html", "");
        $html->assign('requester', $requesterUid);
        $html->assign('recipient', $recipientUid);
        $htmlMail = $html->fetchPage();

        $alttext = new \OCP\Template($this->appName, "mail_monitoring_text", "");
        $alttext->assign('requester', $requesterUid);
        $html->assign('recipient', $recipientUid);
        $altMail = $alttext->fetchPage();

        $fromAddress = $fromName = \OCP\Util::getDefaultEmailAddress('owncloud');

        try {
            \OCP\Util::sendMail($toAddress, $toName, $subject, $htmlMail, $fromAddress, $fromName, 1, $altMail);
        } catch (\Exception $e) {
            \OCP\Util::writeLog('user_files_migrate', "Can't send mail for monitoring: " . $e->getMessage(), \OCP\Util::ERROR);
        }
    }

    /**
     * Send a mail signaling end of files migration to user's group admins
     * @param int $requesterUid (the "from migration account" id)
     * @param int $recipientUid (the "to migration account" id)
     */
    public function mailGroupAdmin($requesterUid, $recipientUid)
    {
        $toAddress = '';
        $groupId = '';

        // get user's groups
        $user = $this->userManager->get($recipientUid);
        $groupIds = $this->groupManager->getUserGroupIds($user);

        // search group against a pattern...
        $pattern = $this->config->getSystemValue('mainGroup_pattern');
        foreach($groupIds as $gid) {
            if (preg_match(preg_quote($pattern), $gid)) {
                $groupId = $gid;
                break;
            }
        }
        // $groupId = 'migration'; // tests

        // get group's subadmins ids
        if (!empty($groupId)) {
            $subAdminIds = \OC_SubAdmin::getGroupsSubAdmins($groupId);
            $toAddress = join(', ', $subAdminIds);
        }

        if (empty($toAddress)) {
            $toAddress = $this->config->getSystemValue('custom_admin_email');
        }

        $theme = new \OC_Defaults;

        $subject = (string) $this->l->t('%s - Files Migration processed', array($theme->getTitle()));
        $html = new \OCP\Template($this->appName, "mail_subadmins_html", "");
        $html->assign('requester', $requesterUid);
        $html->assign('recipient', $recipientUid);
        $htmlMail = $html->fetchPage();

        $alttext = new \OCP\Template($this->appName, "mail_subadmins_text", "");
        $alttext->assign('requester', $requesterUid);
        $html->assign('recipient', $recipientUid);
        $altMail = $alttext->fetchPage();

        $fromAddress = $fromName = \OCP\Util::getDefaultEmailAddress('owncloud');

        try {
            \OCP\Util::sendMail($toAddress, $toName, $subject, $htmlMail, $fromAddress, $fromName, 1, $altMail);
        } catch (\Exception $e) {
            \OCP\Util::writeLog('user_files_migrate', "Can't send mail for subadmins: " . $e->getMessage(), \OCP\Util::ERROR);
        }
    }
}
