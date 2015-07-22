<?php

/**
 * ownCloud - User Files Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2015 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Files_Migrate;

use \OCA\User_Files_Migrate\App\User_Files_Migrate;
$app = new User_Files_Migrate;
$c = $app->getContainer();

/**
 * register personnal settings section
 */
\OCP\App::registerPersonal($c->query('AppName'), 'settings/settings-personal');

