<?php

// $Header: /cvsroot/tikiwiki/tiki/copyrights.php,v 1.3 2003-11-17 15:44:27 mose Exp $

// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// This file sets up the information needed to display
// the copyrights information box
require_once ('lib/tikilib.php');

require_once ('tiki-setup.php');

if ($wiki_feature_copyrights != 'y') {
	$smarty->assign('msg', tra("The copyright management feature is not enabled."));

	$smarty->display("error.tpl");
	die;
}

if (!((isset($tiki_p_edit_copyrights)) && ($tiki_p_edit_copyrights == 'y'))) {
	$smarty->assign('msg', tra("You do not have permission to use this feature."));

	$smarty->display("error.tpl");
	die;
}

include_once ("lib/copyrights/copyrightslib.php");
global $dbTiki;
$copyrightslib = new CopyrightsLib($dbTiki);

if (!isset($_REQUEST["page"])) {

// redirect back to home page
}

$smarty->assign('page', $_REQUEST["page"]);
$page = $_REQUEST["page"];

if (isset($_REQUEST['addcopyright'])) {
	if ($wiki_feature_copyrights == 'y' && isset($_REQUEST['copyrightTitle']) && isset($_REQUEST['copyrightYear'])
		&& isset($_REQUEST['copyrightAuthors']) && !empty($_REQUEST['copyrightYear']) && !empty($_REQUEST['copyrightTitle'])) {
		$copyrightYear = $_REQUEST['copyrightYear'];

		$copyrightTitle = $_REQUEST['copyrightTitle'];
		$copyrightAuthors = $_REQUEST['copyrightAuthors'];
		$copyrightslib->add_copyright($page, $copyrightTitle, $copyrightYear, $copyrightAuthors, $user);
	} else {
		$smarty->assign('msg', tra("You must supply all the information, including title and year."));

		$smarty->display("error.tpl");
		die;
	}
}

if (isset($_REQUEST['editcopyright'])) {
	if ($wiki_feature_copyrights == 'y' && isset($_REQUEST['copyrightTitle']) && isset($_REQUEST['copyrightYear'])
		&& isset($_REQUEST['copyrightAuthors']) && !empty($_REQUEST['copyrightYear']) && !empty($_REQUEST['copyrightTitle'])) {
		$copyrightId = $_REQUEST['copyrightId'];

		$copyrightYear = $_REQUEST['copyrightYear'];
		$copyrightTitle = $_REQUEST['copyrightTitle'];
		$copyrightAuthors = $_REQUEST['copyrightAuthors'];
		$copyrightslib->edit_copyright($copyrightId, $copyrightTitle, $copyrightYear, $copyrightAuthors, $user);
	} else {
		$smarty->assign('msg', tra("You must supply all the information, including title and year."));

		$smarty->display("error.tpl");
		die;
	}
}

if (isset($_REQUEST['action']) && isset($_REQUEST['copyrightId'])) {
	if ($_REQUEST['action'] == 'up') {
		$copyrightslib->up_copyright($_REQUEST['copyrightId']);
	} elseif ($_REQUEST['action'] == 'down') {
		$copyrightslib->down_copyright($_REQUEST['copyrightId']);
	} elseif ($_REQUEST['action'] == 'delete') {
		$copyrightslib->remove_copyright($_REQUEST['copyrightId']);
	}
}

$copyrights = $copyrightslib->list_copyrights($_REQUEST["page"]);
$smarty->assign('copyrights', $copyrights["data"]);

// Display the template
$smarty->assign('mid', 'copyrights.tpl');
$smarty->display("tiki.tpl");

?>