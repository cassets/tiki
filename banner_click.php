<?php

// $Header: /cvsroot/tikiwiki/tiki/banner_click.php,v 1.6 2003-12-15 00:08:03 redflo Exp $

// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

# $Header: /cvsroot/tikiwiki/tiki/banner_click.php,v 1.6 2003-12-15 00:08:03 redflo Exp $

include_once("lib/init/initlib.php");
// Receive URI and id
include_once ('db/tiki-db.php');

include_once ('lib/tikilib.php');
$tikilib = new Tikilib($dbTiki);
include_once ('lib/banners/bannerlib.php');

if (!isset($bannerlib)) {
	$bannerlib = new BannerLib($dbTiki);
}

$bannerlib->add_click($_REQUEST["id"]);
$url = urldecode($_REQUEST["url"]);
header ("location: $url");

?>
