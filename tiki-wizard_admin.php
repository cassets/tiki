<?php
/**
 * @package tikiwiki
 */
// (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

require 'tiki-setup.php';

require_once('lib/headerlib.php');
$headerlib->add_cssfile('css/admin.css');

require_once('lib/wizard/wizardlib.php');
$wizardlib = new WizardLib();

$accesslib = TikiLib::lib('access');
$accesslib->check_permission('tiki_p_admin');

if (!isset($_REQUEST['url'])) {
	$smarty->assign('msg', tra("No return URL specified"));
	$smarty->display("error.tpl");
	die;
}

// Create the template instances
$pages = array();

/////////////////////////////////////
// BEGIN Wizard page section
/////////////////////////////////////

require_once('lib/wizard/pages/admin_wizard.php'); 
$pages[0] = new AdminWizard();

require_once('lib/wizard/pages/admin_date_time.php'); 
$pages[1] = new AdminWizardDateTime();

require_once('lib/wizard/pages/admin_editor.php'); 
$pages[2] = new AdminWizardEditor();

require_once('lib/wizard/pages/admin_wiki.php'); 
$pages[3] = new AdminWizardWiki();

require_once('lib/wizard/pages/admin_files.php'); 
$pages[4] = new AdminWizardFiles();

require_once('lib/wizard/pages/admin_profiles.php'); 
$pages[5] = new AdminWizardProfiles();


/////////////////////////////////////
// END Wizard page section
/////////////////////////////////////


// Assign the return URL
$homepageUrl = $_REQUEST['url'];
$smarty->assign('homepageUrl', $homepageUrl);

$stepNr = intval($_REQUEST['wizard_step']);
if (isset($_REQUEST['wizard_step'])) {

	$pages[$stepNr]->onContinue();
	if (count($pages) > $stepNr+1) {
		$stepNr += 1;
		if (count($pages) == $stepNr+1) {
			$smarty->assign('lastWizardPage', 'y');
		}
		$pages[$stepNr]->onSetupPage($homepageUrl);
	} else {
		// Return to homepage, when we get to the end
		header('Location: '.$homepageUrl);
		exit;
	}
} else {
	$pages[0]->onSetupPage($homepageUrl);
}

$showOnLogin = $tikilib->get_preference('wizard_admin_hide_on_login') !== 'y';
$smarty->assign('showOnLogin', $showOnLogin);

$smarty->assign('wizard_step', $stepNr);

// disallow robots to index page:
$smarty->assign('metatag_robots', 'NOINDEX, NOFOLLOW');

$smarty->assign('mid', 'tiki-wizard_admin.tpl');
$smarty->display("tiki.tpl");
