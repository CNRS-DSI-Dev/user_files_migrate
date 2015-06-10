<?php

/**
 * ownCloud - User Files Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Files_Migrate;

use \OCA\User_Files_Migrate\App\User_Files_Migrate;

$application = new User_Files_Migrate();
$application->registerRoutes($this, array(
    'routes' => array(
        // REQUEST API
        array(
            'name' => 'request#get',
            'url' => '/api/1.0/request/{uid}',
            'verb' => 'GET',
        ),
        array(
            'name' => 'request#confirm',
            'url' => '/api/1.0/confirm/{request_id}',
            'verb' => 'GET',
        ),
        array(
            'name' => 'request#ask',
            'url' => '/api/1.0/request',
            'verb' => 'POST',
        ),

        // CORS
        array(
            'name' => 'request#preflighted_cors',
            'url' => '/api/1.0/request/{path}',
            'verb' => 'OPTIONS',
            'requirements' => array('path' => '.+'),
        ),
    ),
));
