<?php
/**
 * ownCloud - User_Files_Migrate
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2015 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

    $l = $_['overwriteL10N'];

        print_unescaped($l->t("Hello,", array($_['user'])));
        print_unescaped('<br><br>');
	print_unescaped($l->t("A file migration request from account %s to account %s has been processed.", array(
        $_['requester'],
        $_['recipient'],
    )));
    print_unescaped('<br><br>');
?>
--
<?php print_unescaped('<br><br>'); ?>
<?php p($theme->getEntity() . ' - ' . $theme->getSlogan()); ?>
<?php print_unescaped('<br><br>'); ?>
<?php print_unescaped("\n".$theme->getBaseUrl()); ?>
