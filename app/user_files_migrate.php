<?php

/**
 * ownCloud - User Files Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\User_Files_Migrate\App;

use \OCP\AppFramework\App;
use \OCA\User_Files_Migrate\Controller\RequestController;
use \OCA\User_Files_Migrate\Service\RequestService;
use \OCA\User_Files_Migrate\Db\RequestMapper;

class User_Files_Migrate extends App {

    /**
     * Define your dependencies in here
     */
    public function __construct(array $urlParams=array()){
        parent::__construct('user_files_migrate', $urlParams);

        $container = $this->getContainer();

        /**
         * Controllers
         */
        $container->registerService('RequestController', function($c){
            return new PageController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('RequestMapper'),
                $c->query('RequestService'),
                $c->query('UserId')
            );
        });

        /**
         * Services
         */
        $container->registerService('RequestService', function($c){
            return new RequestService(
                $c->query('RequestMapper'),
                $c->query('UserId')
            );
        });

        /**
         * Database Layer
         */
        $container->registerService('RequestMapper', function($c) {
            return new RequestMapper(
                $c->query('ServerContainer')->getDb()
            );
        });

        /**
         * Core
         */
        $container->registerService('UserId', function($c) {
            return \OCP\User::getUser();
        });

        $container->registerService('L10N', function($c) {
            return $c->query('ServerContainer')->getL10N($c->query('AppName'));
        });

    }


}
