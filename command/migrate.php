<?php

/**
 * ownCloud - User Files Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Files_Migrate\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use OC\DB\Connection;

class Migrate extends Command
{
    protected $requestMapper;

    public function __construct(\OCA\Dashboard\Db\RequestMapper $requestMapper)
    {
        $this->requestMapper = $requestMapper;
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
        $output->writeln('Beginning migration');

        if ($input->getOption('list')) {
            $this->listRequests($output);
            return true;
        }

        try {
            $requests = $this->requestMapper->findConfirmedRequest();
        }
        catch (\Exception $e) {
            $output->writeln('Server error: ' . $e->getMessage());
            return false;
        }

        if (empty($requests)) {
            $output->writeln('No migration request to process.');
            return true;
        }

        foreach($requests as $request) {
            $output->writeln('Migration request #' . $request->getId() . ': from uid "' . $request->getRequesterUid() . '"" to uid "' . $request->getRecipientUid() .'"');
            $this->recursiveCopy($request->getRequesterUid(), $request->getRecipientUid());

            $this->requestMapper->closeRequest($request->getId());
        }

        $output->writeln('The End');
    }

    /**
     * Displays list of confirmed migration requests
     * @param OutputInterface $output
     */
    protected function listRequests(OutputInterface $output)
    {
        try {
            $requests = $this->requestMapper->findConfirmedRequest();
        }
        catch (\Exception $e) {
            $output->writeln('Server error: ' . $e->getMessage());
            return false;
        }

        if (empty($requests)) {
            $output->writeln('No migration request to process.');
            return true;
        }

        foreach($requests as $request) {
            $output->writeln('Migration request #' . $request->getId() . ': from uid "' . $request->getRequesterUid() . '"" to uid "' . $request->getRecipientUid() .'"');
        }

        $output->writeln('End');
    }

    protected function recursiveCopy($requesterUid, $recipientUid)
    {
        $rootDir = \OCP\Config::getSystemValue('datadirectory', '/var/www/owncloud/data');
        $source = $rootDir . '/' . $requesterUid . '/files';
        $dest= $rootDir . '/' . $recipientUid . '/files';

        // http://stackoverflow.com/a/7775949/1997849
        mkdir($dest, 0755);
        foreach (
         $iterator = new \RecursiveIteratorIterator(
          new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
          \RecursiveIteratorIterator::SELF_FIRST) as $item
        ) {
          if ($item->isDir()) {
            mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
          } else {
            copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
          }
        }
    }
}
