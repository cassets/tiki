<?php

// $Header: /cvsroot/tikiwiki/tiki/tiki-remind_password.php,v 1.12 2003-11-17 15:44:29 mose Exp $

// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// Initialization
require_once ('tiki-setup.php');

if ($forgotPass != 'y') {
	$smarty->assign('msg', tra("This feature is disabled").": forgotPass");

	$smarty->display("error.tpl");
	die;
}

$smarty->assign('showmsg', 'n');
$smarty->assign('showfrm', 'y');

if (isset($_REQUEST["remind"])) {
	if ($tikilib->user_exists($_REQUEST["username"])) {
		if ($feature_clear_passwords == 'y') {
			$pass = $userlib->get_user_password($_REQUEST["username"]);
		} else {
			$pass = $userlib->renew_user_password($_REQUEST["username"]);
		}

		$email = $tikilib->get_user_email($_REQUEST["username"]);
		$smarty->assign('mail_site', $_SERVER["SERVER_NAME"]);
		$smarty->assign('mail_user', $_REQUEST["username"]);
		$smarty->assign('mail_same', $feature_clear_passwords);
		$smarty->assign('mail_pass', $pass);
		$mail_data = $smarty->fetch('mail/password_reminder.tpl');
		$tmp = tra("Your Tiki account information for");
		$tmp .= " " . $_SERVER["SERVER_NAME"];
		@mail($email, $tmp, $mail_data, "From: $sender_email\r\nContent-type: text/plain;charset=utf-8\r\n");
		// Just show "success" message and no form
		$smarty->assign('showmsg', 'y');
		$smarty->assign('showfrm', 'n');

		if ($feature_clear_passwords == 'y') {
			$tmp = tra("A password reminder email has been sent ");
		} else {
			$tmp = tra("A new password has been sent ");
		}

		$tmp .= tra("to the registered email address for");
		$tmp .= " " . $_REQUEST["username"] . ".";
		$smarty->assign('msg', $tmp);
	} else {
		// Show error message (and leave form visible so user can fix problem)
		$smarty->assign('showmsg', 'e');

		$tmp = tra("Invalid or unknown username");
		$tmp .= ": " . $_REQUEST["username"];
		$smarty->assign('msg', $tmp);
	}
}

// Display the template
$smarty->assign('mid', 'tiki-remind_password.tpl');
$smarty->display("tiki.tpl");

?>
