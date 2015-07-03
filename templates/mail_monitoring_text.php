<?php
/**
 * ownCloud - User_Files_Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2015 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

    print_unescaped($l->t("A file migration request from account %s to account %s has been processed.\n", array(
        $_['requester'],
        $_['recipient'],
    )));
?>

--
<?php p($theme->getName() . ' - ' . $theme->getSlogan()); ?>
<?php print_unescaped("\n".$theme->getBaseUrl());
