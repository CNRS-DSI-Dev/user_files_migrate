<?php
/**
 * ownCloud - User_Files_Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2015 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

    $l = $_['overwriteL10N'];

    print_unescaped($l->t("Your file migration request from account %s has been processed.", array($_['requester'])));
?>


--
<?php p($theme->getName() . ' - ' . $theme->getSlogan()); ?>
<?php print_unescaped("\n".$theme->getBaseUrl());
