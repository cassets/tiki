<?php

// $Header: /cvsroot/tikiwiki/tiki/tiki-login_validate.php,v 1.16 2005-08-16 14:44:45 sylvieg Exp $

// Copyright (c) 2002-2005, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// Initialization
require_once ('tiki-setup.php');

$isvalid = false;
if (isset($_REQUEST["user"])) {
	if (isset($_REQUEST["pass"])) {
		list($isvalid, $_REQUEST["user"], $error) = $userlib->validate_user($_REQUEST["user"], $_REQUEST["pass"],'','');
	}
}	
if ($isvalid) {
	//session_register("user",$_REQUEST["user"]); 
	$userlib->confirm_user($_REQUEST['user']);
	if ($tikilib->get_preference('validateRegistration','n') == 'y') {
		$email = $userlib->get_user_email($user);
		include_once("lib/webmail/tikimaillib.php");
		$mail = new TikiMail();
		$mail->setText($smarty->fetch('mail/moderate_validation_mail.tpl'));					
		$mail->setSubject($smarty->fetch('mail/moderate_validation_mail_subject.tpl'));					
		$mail->send(array($email));
		$logslib->add_log('register','validated account '.$user);
	} else {
		$_SESSION["$user_cookie_site"] = $_REQUEST["user"];
	}
	$smarty->assign('msg', tra("Account validated successfully."));
	$smarty->display("information.tpl");
} else {
	if ($error == PASSWORD_INCORRECT)
		$error = tra("Invalid password");
	else if ($error == USER_NOT_FOUND)
		$error = tra("Invalid username");
	else if ($error == ACCOUNT_DISABLED)
		$error = tra("Account disabled");
	else if ($error == USER_AMBIGOUS)
		$error = tra("You must use the right case for your user name");
	else
		$error= tra('Invalid username or password');
	$smarty->assign('msg', $error);
	$smarty->display("error.tpl");
}

?>
