<?php
// (c) Copyright 2002-2010 by authors of the Tiki Wiki/CMS/Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

// This is not part of the traditional installer. At the moment this is only used to finish the install when using the Microsoft Web Platform Installer.

require_once('tiki-setup.php');
if ($password = $tikilib->getOne("SELECT `password` FROM `users_users` WHERE `userId`=1 AND `hash`='DummyHashForInstallation'")) {
	// Hashes the admin password
	$query = "UPDATE `users_users` SET `hash`=?, `password`=NULL WHERE `userId`=1 AND `hash`='DummyHashForInstallation'";
	$tikilib->query($query, array($userlib->hash_pass($password)));

	// Sets dbversion_tiki
	$db = fopen('db/local.php', 'a');
	fwrite($db, "\n\$dbversion_tiki='" . $TWV->getVersion() . "';\n");
}
header("location: tiki-index.php");