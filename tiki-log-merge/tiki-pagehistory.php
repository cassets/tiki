<?php
// (c) Copyright 2002-2009 by authors of the Tiki Wiki/CMS/Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id: /cvsroot/tikiwiki/tiki/tiki-pagehistory.php,v 1.45.2.5 2008-01-28 19:03:04 lphuberdeau Exp $
$section = 'wiki page';
$section_class = "tiki_wiki_page manage";	// This will be body class instead of $section
require_once ('tiki-setup.php');
include_once ('lib/wiki/histlib.php');
if ($prefs['feature_wiki'] != 'y') {
	$smarty->assign('msg', tra('This feature is disabled') . ': feature_wiki');
	$smarty->display('error.tpl');
	die;
}
if (!isset($_REQUEST["source"])) {
	if ($prefs['feature_history'] != 'y') {
		$smarty->assign('msg', tra('This feature is disabled') . ': feature_history');
		$smarty->display('error.tpl');
		die;
	}
} else {
	if ($prefs['feature_source'] != 'y') {
		$smarty->assign('msg', tra('This feature is disabled') . ': feature_source');
		$smarty->display('error.tpl');
		die;
	}
}
// Get the page from the request var or default it to HomePage
if (!isset($_REQUEST["page"])) {
	$smarty->assign('msg', tra("No page indicated"));
	$smarty->display("error.tpl");
	die;
} else {
	$page = $_REQUEST["page"];
	$smarty->assign_by_ref('page', $_REQUEST["page"]);
}

$auto_query_args = array('page', 'oldver', 'newver', 'compare', 'diff_style', 'show_translation_history', 'show_all_versions');

$tikilib->get_perm_object( $_REQUEST['page'], 'wiki page' );

// Now check permissions to access this page
if (!isset($_REQUEST["source"])) {
	if ($tiki_p_wiki_view_history != 'y') {
		$smarty->assign('errortype', 401);
		$smarty->assign('msg', tra("Permission denied you cannot browse this page history"));
		$smarty->display("error.tpl");
		die;
	}
} else {
	if ($tiki_p_wiki_view_source != 'y') {
		$smarty->assign('errortype', 401);
		$smarty->assign('msg', tra("Permission denied you cannot view the source of this page"));
		$smarty->display("error.tpl");
		die;
	}
}
$info = $tikilib->get_page_info($page);
$smarty->assign_by_ref('info', $info);
// If the page doesn't exist then display an error
//check_page_exits($page);
if (isset($_REQUEST["delete"]) && isset($_REQUEST["hist"]) && $info["flag"] != 'L') {
	check_ticket('page-history');
	foreach(array_keys($_REQUEST["hist"]) as $version) {
		$histlib->remove_version($_REQUEST["page"], $version);
	}
}
if ($prefs['feature_contribution'] == 'y') {
	global $contributionlib;
	include_once ('lib/contribution/contributionlib.php');
	$contributions = $contributionlib->get_assigned_contributions($page, 'wiki page');
	$smarty->assign_by_ref('contributions', $contributions);
	if ($prefs['feature_contributor_wiki'] == 'y') {
		$contributors = $logslib->get_wiki_contributors($info);
		$smarty->assign_by_ref('contributors', $contributors);
	}
}

// fetch page history, but omit the actual page content (to save memory)
$history = $histlib->get_page_history($page, false);
if (!isset($_REQUEST['show_all_versions'])) {
	$_REQUEST['show_all_versions'] = "y";
}
$sessions = array();
if (count($history) > 0) {
	$lastuser = '';		// calculate edit session info
	$lasttime = 0;		// secs
	$idletime = 1800; 	// max gap between edits in sessions 30 mins? Maybe should use a pref?
	for($i = 0, $cnt = count($history); $i < $cnt; $i++) {
		
		if ($history[$i]['user'] != $lastuser || $lasttime - $history[$i]['lastModif'] > $idletime) {
			$sessions[] = $history[$i];
			//$history[$i]['session'] = $history[$i]['version'];
		} else if (count($sessions) > 0) {
			$history[$i]['session'] = $sessions[count($sessions)-1]['version'];
		}
		$lastuser = $history[$i]['user'];
		$lasttime = $history[$i]['lastModif'];
	}
	$csesh = count($sessions) + 1;
	foreach($history as &$h) {	// move ending 'version' into starting 'session'
		if (!empty($h['session'])) {
			foreach($history as &$h2) {
				if ($h2['version'] == $h['session']) {
					$h2['session'] = $h['version'];
				}
			}
			$h['session'] = '';
		}
	}
	if ($_REQUEST['show_all_versions'] == "n") {
		for($i = 0, $cnt = count($history); $i < $cnt; $i++) {	// remove versions inside sessions
			if (!empty($history[$i]['session']) && $i < $cnt - 1) {
				$seshend = $history[$i]['session'];
				$i++;
				for ($i; $i < $cnt; $i++) {
					if ($history[$i]['version'] >= $seshend) {
						unset($history[$i]);
					} else {
						break;
					}
				}
			}
		}
	}
}
$smarty->assign('show_all_versions', $_REQUEST['show_all_versions']);
$history_versions = array();
$history_sessions = array();
reset($history);
foreach($history as &$h) {	// as $h has been used by reference before it needs to be so again (it seems)
	$history_versions[] = (int)$h['version'];
	$history_sessions[] = isset($h['session']) ? (int)$h['session'] : 0;
}
$history_versions = array_reverse($history_versions);
$history_sessions = array_reverse($history_sessions);
$history_versions[] = $info["version"];	// current is last one
$history_sessions[] = 0;
$smarty->assign_by_ref('history', $history);

// for pagination
$smarty->assign('cant', count($history_versions));

// calculate version and offset
if (isset($_REQUEST['newver_idx'])) {
	$newver = $history_versions[$_REQUEST['newver_idx']];
} else {
	if (isset($_REQUEST['newver']) && $_REQUEST['newver'] > 0) {
		$newver = (int)$_REQUEST["newver"];
		if (in_array($newver, $history_versions)) {
			$_REQUEST['newver_idx'] = array_search($newver, $history_versions);
		} else {
			$_REQUEST['newver_idx'] = array_search($newver, $history_sessions);
		}
	} else {
		$newver = $history_versions[count($history_versions)];
		$_REQUEST['newver_idx'] = count($history_versions);
	}
}
if (isset($_REQUEST['oldver_idx'])) {
	$oldver = $history_versions[$_REQUEST['oldver_idx']];
	if ($oldver == $newver && !empty($history_sessions[$_REQUEST['oldver_idx']])) {
		$oldver = $history_sessions[$_REQUEST['oldver_idx']];
	}
} else {
	if (isset($_REQUEST['oldver']) && $_REQUEST['oldver'] > 0) {
		$oldver = (int)$_REQUEST["oldver"];
		if (in_array($oldver, $history_versions)) {
			$_REQUEST['oldver_idx'] = array_search($oldver, $history_versions);
		} else {
			$_REQUEST['oldver_idx'] = array_search($oldver, $history_sessions);
		}
	} else {
		$oldver = $history_versions[count($history_versions)];
		$_REQUEST['oldver_idx'] = count($history_versions);
	}
}
// source view
if (isset($_REQUEST['source_idx'])) {
	$source = $history_versions[$_REQUEST['source_idx']];
} else {
	if (isset($_REQUEST['source']) && $_REQUEST['source'] > 0) {
		$source = (int)$_REQUEST["source"];
		$_REQUEST['source_idx'] = array_search($source, $history_versions);
	} else {
		$source = $history_versions[count($history_versions)];
		$_REQUEST['source_idx'] = count($history_versions);
	}
}
if (isset($_REQUEST['preview_idx'])) {
	$preview = $history_versions[$_REQUEST['preview_idx']];
} else {
	if (isset($_REQUEST['preview']) && $_REQUEST['preview'] > 0) {
		$preview = (int)$_REQUEST["preview"];
		$_REQUEST['preview_idx'] = array_search($preview, $history_versions);
	} else {
		$preview = $history_versions[count($history_versions)];
		$_REQUEST['preview_idx'] = count($history_versions);
	}
}

if (isset($_REQUEST['version'])) $rversion = $_REQUEST['version'];

$smarty->assign('source', false);
if (isset($source)) {
	if ($source == '' && isset($rversion)) {
		$source = $rversion;
	}
	if ($source == $info["version"] || $source == 0) {
		if ($info['is_html'] == 1) {
			$smarty->assign('sourced', $info["data"]);
		} else {
			$smarty->assign('sourced', nl2br($info["data"]));
		}
		$smarty->assign('source', $info['version']);
	} else {
		$version = $histlib->get_version($page, $source);
		if ($version) {
			if ($version['is_html'] == 1) {
				$smarty->assign('sourced', $version['data']);
			} else {
				$smarty->assign('sourced', nl2br($version["data"]));
			}
			$smarty->assign('source', $source);
		}
	}
	if ($source == 0) {
		$smarty->assign('noHistory', true);
	}
}
$smarty->assign('preview', false);
if (isset($preview)) {
	if ($preview == '' && isset($rversion)) {
		$preview = $rversion;
	}
	if ($preview == $info["version"] || $preview == 0) {
		$previewd = $tikilib->parse_data($info["data"], array('preview_mode' => true));
		$smarty->assign_by_ref('previewd', $previewd);
		$smarty->assign('preview', $info['version']);
	} else {
		$version = $histlib->get_version($page, $preview);
		if ($version) {
			$previewd = $tikilib->parse_data($version["data"], array('preview_mode' => true));
			$smarty->assign_by_ref('previewd', $previewd);
			$smarty->assign('preview', $preview);
		}
	}
	if ($preview == 0) {
		$smarty->assign('noHistory', true);
	}
}
if ($preview) {
	$smarty->assign('current', $preview);
} else if ($source) {
	$smarty->assign('current', $source);
} else if ($newver) {
	$smarty->assign('current', $newver);
} else if ($oldver) {
	$smarty->assign('current', $oldver);
} else {
	$smarty->assign('current', 0);
}
if ($prefs['feature_multilingual'] == 'y' && isset($_REQUEST['show_translation_history'])) {
	include_once ("lib/multilingual/multilinguallib.php");
	$smarty->assign('show_translation_history', 1);
	$sources = $multilinguallib->getSourceHistory($info['page_id']);
	$targets = $multilinguallib->getTargetHistory($info['page_id']);
} else {
	$sources = array();
	$targets = array();
}
$smarty->assign_by_ref('translation_sources', $sources);
$smarty->assign_by_ref('translation_targets', $targets);
if (isset($_REQUEST["diff2"])) { // previous compatibility
	if ($_REQUEST["diff2"] == '' && isset($rversion)) {
		$_REQUEST["diff2"] = $rversion;
	}
	$_REQUEST["compare"] = "y";
	$oldver = (int)$_REQUEST["diff2"];
}
if (!isset($newver)) {
	$newver = 0;
}
if ($prefs['feature_multilingual'] == 'y') {
	include_once ("lib/multilingual/multilinguallib.php");
	$languages = $tikilib->list_languages();
	$smarty->assign_by_ref('languages', $languages);
	if (isset($_REQUEST["update_translation"])) {
		// Update translation button clicked. Forward request to edit page of translation.
		if (isset($_REQUEST['tra_lang'])) {
			$target = $_REQUEST['tra_lang'];
		} else {
			die('Invalid call to this page. Specify tra_lang');
		}
		// Find appropriate translation page
		$langs = $multilinguallib->getTranslations('wiki page', $info['page_id'], $info['pageName'], true);
		$pageName = '';
		foreach($langs as $pageInfo) if ($target == (string)$pageInfo['lang']) {
			$pageName = $pageInfo['objName'];
		}
		// Build URI / Redirect
		$diff_style = isset($_REQUEST['diff_style']) ? rawurlencode($_REQUEST['diff_style']) : rawurlencode($prefs['default_wiki_diff_style']);
		$comment = rawurlencode("Updating from $page at version {$info['version']}");
		if ($newver == 0) {
			$newver = $info['version'];
		}
		if ($pageName) {
			$uri = "tiki-editpage.php?page=$pageName&source_page=$page&diff_style=$diff_style&oldver=$oldver&newver=$newver&comment=$comment";
		} else {
			$uri = "tiki-edit_translation.php?page=$page";
		}
		header("Location: $uri");
		exit;
	}
}
if (isset($_REQUEST["compare"])) histlib_helper_setup_diff($page, $oldver, $newver);
else $smarty->assign('diff_style', $prefs['default_wiki_diff_style']);
if ($info["flag"] == 'L') $smarty->assign('lock', true);
else $smarty->assign('lock', false);
$smarty->assign('page_user', $info['user']);
ask_ticket('page-history');
// disallow robots to index page:
$smarty->assign('metatag_robots', 'NOINDEX, NOFOLLOW');
include_once ('tiki-section_options.php');
// Display the template
$smarty->assign('mid', 'tiki-pagehistory.tpl');
$smarty->display("tiki.tpl");