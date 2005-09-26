<?php

// $Header: /cvsroot/tikiwiki/tiki/tiki-install.php,v 1.76 2005-09-26 12:56:13 michael_davey Exp $

// Copyright (c) 2002-2005, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

if (!file_exists("installer/tiki-installer.php")) {
	header ("Status: 410 Gone"); /* PHP3 */
	header ("HTTP/1.0 410 Gone"); /* PHP4 */
	header ('location: index.php');
	die('TikiWiki installer has been disabled.');
} else {
	include_once("installer/tiki-installer.php");
}
?>
