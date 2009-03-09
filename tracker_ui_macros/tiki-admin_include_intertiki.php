<?php
// $Id: /cvsroot/tikiwiki/tiki/tiki-admin_include_intertiki.php,v 1.9.2.1 2008-03-22 05:12:47 mose Exp $

// Copyright (c) 2002-2007, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.

// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"],basename(__FILE__)) !== false) {
  header("location: index.php");
  exit;
}

if (!isset($_REQUEST['interlist'])) $_REQUEST['interlist']= array();
if (!isset($_REQUEST['known_hosts'])) $_REQUEST['known_hosts']= array();

if (isset($_REQUEST['del'])) {
	check_ticket('admin-inc-intertiki');
	$_REQUEST["intertikiclient"] = true;
	foreach ($prefs['interlist'] as $k=>$i) {
		if ($k != $_REQUEST['del']) {
			$_REQUEST['interlist'][$k] = $i;
		}
	}
}

if (isset($_REQUEST['delk'])) {
	check_ticket('admin-inc-intertiki');
	foreach ($prefs['known_hosts'] as $k=>$i) {
		if ($k != $_REQUEST['delk']) {
			$_REQUEST['known_hosts'][$k] = $i;
		}
	simple_set_value('known_hosts');
	}
}

if (isset($_REQUEST["intertikiclient"])) {
	check_ticket('admin-inc-intertiki');
	if (isset($_REQUEST['new']) and is_array($_REQUEST['new']) and $_REQUEST['new']['name']) {
		$new["{$_REQUEST['new']['name']}"] = $_REQUEST['new'];
		$_REQUEST['interlist'] += $new;
	}
	simple_set_value('interlist');
	simple_set_value('tiki_key');
	simple_set_value('feature_intertiki_mymaster');

	simple_set_toggle('feature_intertiki_sharedcookie');
	simple_set_toggle('feature_intertiki_import_preferences');
	simple_set_toggle('feature_intertiki_import_groups');
	simple_set_value('feature_intertiki_imported_groups');
}

if (isset($_REQUEST["intertikiserver"])) {
	check_ticket('admin-inc-intertiki');
	simple_set_toggle('feature_intertiki_sharedcookie');
	simple_set_toggle('feature_intertiki_server');
	simple_set_value('intertiki_logfile');
	simple_set_value('intertiki_errfile');
	if (isset($_REQUEST['newhost']) and is_array($_REQUEST['newhost']) and $_REQUEST['newhost']['key']) {
		$newhost["{$_REQUEST['newhost']['key']}"] = $_REQUEST['newhost'];
		$_REQUEST['known_hosts'] += $newhost;
	}
	simple_set_value('known_hosts');
}

ask_ticket('admin-inc-intertiki');
