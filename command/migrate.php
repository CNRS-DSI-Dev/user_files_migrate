<?php

/**
 * ownCloud - User Files Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2015 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Files_Migrate\Command;

use OC\Files\Utils\Scanner;
use OCP\Files\NotFoundException;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use OC\DB\Connection;

class Migrate extends Command
{
    const INFO = 1; // green text (from symfony doc)
    const COMMENT = 2; // yellow text
    const QUESTION = 3; // black text on a cyan background
    const ERROR = 4; // white text on a red background

    protected $requestMapper;
    protected $mailService;
    protected $userManager;
    protected $groupManager;
    protected $output;

    public function __construct(\OCA\User_Files_Migrate\Db\RequestMapper $requestMapper, \OCA\User_Files_Migrate\Service\MailService $mailService, $userManager, $groupManager)
    {
        $this->requestMapper = $requestMapper;
        $this->mailService = $mailService;
        $this->userManager = $userManager;
        $this->groupManager = $groupManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('user_files_migrate:migrate')
            ->setDescription('Process confirmed migration requests.')
            ->addOption('list', 'l', InputOption::VALUE_NONE, 'List requests instead of process them.');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        // --list option
        if ($input->getOption('list')) {
            $this->consoleDisplay('List migration requests');
            $this->listRequests($output);
            return true;
        }

        // migration
        $this->consoleDisplay('Beginning migration');
        $this->processMigration();

        $this->consoleDisplay('The End');
    }

    protected function processMigration()
    {
        // get pending requests
        try {
            $requests = $this->requestMapper->findConfirmedRequests();
        }
        catch (\Exception $e) {
            $this->consoleDisplay('Server error: ' . $e->getMessage(), self::ERROR);
            return false;
        }

        if (empty($requests)) {
            $this->consoleDisplay('No migration request to process.');
            return true;
        }

        // go
        foreach($requests as $request) {
            $requester = $request->getRequesterUid();
            $recipient = $request->getRecipientUid();

            $this->consoleDisplay('Migration request #' . $request->getId() . ': from uid "' . $requester . '" to uid "' . $recipient .'"');

	    	// copy files
            // -- mantis 59262            
            shell_exec("./occ files:transfer-ownership ".$requester." ".$recipient);
            
            // démarrer une transaction
            \OC::$server->getDatabaseConnection()->beginTransaction();
            // Move shared files with requester to the recipient
            try{
                $sql = "UPDATE *PREFIX*share SET share_with = :newName WHERE share_with = :oldName";
                $st = \OC_DB::prepare($sql);
                $st->execute(array(
                    ':newName' => $recipient,
                    ':oldName' => $requester,
                ));
                // finir la transaction (commit)
                \OC::$server->getDatabaseConnection()->commit();
            }
            catch(\Exception $e) {
                \OC::$server->getDatabaseConnection()->rollback();
                return false;
            }

            // put old account in special group
            // -- search groups for requester
            $requesterUser = $this->userManager->get($requester);
            $groupIds = $this->groupManager->getUserGroupIds($requesterUser);
            // -- search main group
            $exclusionGroupConf =  \OCP\config::getSystemValue('migration_exclusion_groups');
            if (is_array($exclusionGroupConf)) {
                foreach($exclusionGroupConf as $mainGroupId => $exclusionGroupId) {
                    if (in_array($mainGroupId, $groupIds)) {
                        $toGroupId = $exclusionGroupId;
                        break;
                    }
                }
            }
            if (empty($toGroupId)) {
                $toGroupId = \OCP\config::getSystemValue('migration_default_exclusion_group');
            }
            if (empty($toGroupId)) {
                $this->consoleDisplay('Error: unable to find an exclusion group for the request ' . $request->getId() . '.');
                return false;
            }
            $toGroup = $this->groupManager->get($toGroupId);
            // try catch does not work here...
            if ($toGroup instanceof \OC\Group\Group) {
                $toGroup->addUser($requesterUser);
            }
            else {
                $this->consoleDisplay('Error: exclusion group found in config.php not existing in My CoRe.');

                $toAddress = $toName = \OCP\Config::getSystemValue('migration_default_admin_email');
                $fromAddress = $fromName = \OCP\Util::getDefaultEmailAddress('owncloud');
                $subject = "My CoRe - Files Migration Error";
                $text = "Error in files migration process due to configured exclusion group not found.\n";
                $text .= "Therefore, the user " . $recipient . " has not been added in any exclusion group. ";
                $text .= "Please verify that all exclusion groups configured (in config.php) exist in My CoRe.\n".

                \OCP\Util::sendMail($toAddress, $toName, $subject, $text, $fromAddress, $fromName, 1);
            }

            // send mails
            $this->mailService->mailUser($requester, $recipient);
            $this->mailService->mailGroupAdmin($requester, $recipient);
            $this->mailService->mailMonitors($requester, $recipient);

//no need since we use API            $this->scan($recipient);
            $this->requestMapper->closeRequest($request->getId());
        }
    }

    /**
     * Displays list of confirmed migration requests
     * @param OutputInterface $output
     */
    protected function listRequests()
    {
        foreach($this->listUnconfirmedRequests() as $request) {
            $this->consoleDisplay('Unconfirmed migration request #' . $request->getId() . ': from uid "' . $request->getRequesterUid() . '" to uid "' . $request->getRecipientUid() .'" (created on ' . $request->getDateRequest() . ')');
        }

        foreach($this->listConfirmedRequests() as $request) {
            $this->consoleDisplay('Confirmed migration request #' . $request->getId() . ': from uid "' . $request->getRequesterUid() . '" to uid "' . $request->getRecipientUid() .'" (created on ' . $request->getDateRequest() . ')');
        }

        foreach($this->listClosedRequests() as $request) {
            $this->consoleDisplay('Closed migration request #' . $request->getId() . ': from uid "' . $request->getRequesterUid() . '" to uid "' . $request->getRecipientUid() .'" (created on ' . $request->getDateRequest() . ', closed on ' . $request->getDateEnd() . ')');
        }

        $this->consoleDisplay('End');
    }

    protected function listConfirmedRequests()
    {
        try {
            $requests = $this->requestMapper->findConfirmedRequests();
            return $requests;
        }
        catch (\Exception $e) {
            $this->consoleDisplay('Server error: ' . $e->getMessage(), self::ERROR);
            return array();
        }
    }

    protected function listUnconfirmedRequests()
    {
        try {
            $requests = $this->requestMapper->findUnconfirmedRequests();
            return $requests;
        }
        catch (\Exception $e) {
            $this->consoleDisplay('Server error: ' . $e->getMessage(), self::ERROR);
            return array();
        }
    }

    protected function listClosedRequests()
    {
        try {
            $requests = $this->requestMapper->findClosedRequests();
            return $requests;
        }
        catch (\Exception $e) {
            $this->consoleDisplay('Server error: ' . $e->getMessage(), self::ERROR);
            return array();
        }
    }

    protected function consoleDisplay($msg = '', $type = self::INFO)
    {
        $now = date('Ymd_His');
        switch($type) {
            case self::COMMENT: {
                $this->output->writeln('<comment>' . $now . ' ' . $msg . '</comment>');
                break;
            }
            case self::QUESTION: {
                $this->output->writeln('<question>' . $now . ' ' . $msg . '</question>');
                break;
            }
            case self::ERROR: {
                $this->output->writeln('<error>' . $now . ' ' . $msg . '</error>');
                break;
            }
            default: {
                $this->output->writeln('<info>' . $now . ' ' . $msg . '</info>');
            }
        }
    }

    /**
     * Scan user filesystem
     * @param  string $uid User id
     * @return json
     */
    public function scan($uid){
        $uid = htmlspecialchars($uid);
        $arrayStatus = 'status';
        $arrayData = 'data';
        $arrayMsg = 'msg';
        $jsonReturn = array(
                            $arrayStatus => 'error',
                            $arrayData => array(
                                $arrayMsg => '',
                            ),
                        );
        try {

            $user = $this->userManager->get($uid);

            if($user == null){
                throw new NotFoundException('incorrect uid');
            }
            else{
                $scanner = new Scanner(
                    $user->getUID(),
                    \OC::$server->getDatabaseConnection(),
                    \OC::$server->getLogger()
                );

                $scanner->scan('', true);

                $jsonReturn[$arrayStatus] = 'success';
                $jsonReturn[$arrayData][$arrayMsg] = 'Scan successful';
            }
        } catch (\Exception $e) {

            $jsonReturn[$arrayStatus] = 'error';
            $jsonReturn[$arrayData][$arrayMsg] = $e->getMessage();

            return $jsonReturn;
        }

        return $jsonReturn;
    }  
}
