<?php

/**
 * ownCloud - User Files Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

use \OCA\User_Files_Migrate\App\User_Files_Migrate;

$app = new User_Files_Migrate;
$c = $app->getContainer();
$requestMapper = $c->query('RequestMapper');

$application->add(new OCA\User_Files_Migrate\Command\Migrate($requestMapper));
