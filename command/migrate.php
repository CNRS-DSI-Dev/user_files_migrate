<?php

/**
 * ownCloud - User Files Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2015 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Files_Migrate\Command;

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
    protected $output;

    public function __construct(\OCA\User_Files_Migrate\Db\RequestMapper $requestMapper, \OCA\User_Files_Migrate\Service\MailService $mailService)
    {
        $this->requestMapper = $requestMapper;
        $this->mailService = $mailService;
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
            $this->consoleDisplay('Migration request #' . $request->getId() . ': from uid "' . $request->getRequesterUid() . '"" to uid "' . $request->getRecipientUid() .'"');

            // copy files
            $this->recursiveCopy($request->getRequesterUid(), $request->getRecipientUid());

            // put old account in special group

            // send mails
            $this->mailService->mailUser($request->getRequesterUid());
            $this->mailService->mailGroupAdmin($request->getRequesterUid(), $request->getRecipientUid());
            $this->mailService->mailMonitors($request->getRequesterUid(), $request->getRecipientUid());

// $this->requestMapper->closeRequest($request->getId());
        }
    }

    protected function recursiveCopy($requesterUid, $recipientUid)
    {
        $rootDir = \OCP\Config::getSystemValue('datadirectory', '/var/www/owncloud/data');
        $source = $rootDir . '/' . $requesterUid;
        $dest= $rootDir . '/' . $recipientUid;

        // http://stackoverflow.com/a/7775949/1997849
        foreach (
         $iterator = new \RecursiveIteratorIterator(
          new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
          \RecursiveIteratorIterator::SELF_FIRST) as $item
        ) {
          if ($item->isDir()) {
            if (!file_exists($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName())) {
                mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
          } else {
            copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
          }
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
}
