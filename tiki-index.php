<?php
// $Header: /cvsroot/tikiwiki/tiki/tiki-index.php,v 1.127 2004-08-26 19:23:08 mose Exp $

// Copyright (c) 2002-2004, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// Initialization

require_once('tiki-setup.php');
include_once('lib/structures/structlib.php');
include_once('lib/wiki/wikilib.php');
if ($feature_categories == 'y') {
	global $categlib;
	if (!is_object($categlib)) {
		include_once('lib/categories/categlib.php');
	}
}

if($feature_wiki != 'y') {
    $smarty->assign('msg', tra("This feature is disabled").": feature_wiki");
    $smarty->display("error.tpl");
    die;  
}

if(!isset($_SESSION["thedate"])) {
    $thedate = date("U");
} else {
    $thedate = $_SESSION["thedate"];
}

if (isset($_REQUEST["page_id"])) {
	$_REQUEST["page"] = $tikilib->get_page_name_from_id($_REQUEST["page_id"]);
//TODO: introduce a get_info_from_id to save a sql request
}

if (!isset($_REQUEST["page"])) {
	if ($useGroupHome == 'y') { 
		$group = $userlib->get_user_default_group($user);
		$groupHome = $userlib->get_group_home($group);
		if ($groupHome) {
			$_REQUEST["page"] = $groupHome;
		} else {
			$_REQUEST["page"] = $wikiHomePage;
		}
	} else {
		$_REQUEST["page"] = $wikiHomePage;
	}
	if(!$tikilib->page_exists($wikiHomePage)) {
		$tikilib->create_page($wikiHomePage,0,'',date("U"),'Tiki initialization');
	}

}
$page = $_REQUEST["page"];

$info = null;
if ($feature_multilingual == 'y' && (isset($_REQUEST["bl"]) || isset($_REQUEST["best_lang"]))) { // chose the best language page
	global $multilinguallib;
	include_once("lib/multilingual/multilinguallib.php");
	$info = $tikilib->get_page_info($page);
	$bestLangPageId = $multilinguallib->selectLangObj('wiki page', $info['page_id']);
	if ($info['page_id'] != $bestLangPageId) {
		$page = $tikilib->get_page_name_from_id($bestLangPageId);
//TODO: introduce a get_info_from_id to save a sql request
		$info = null;
	}
}


$smarty->assign('structure','n');

//If we requested a structure page
if (isset($_REQUEST["page_ref_id"])) {
    $page_ref_id = $_REQUEST["page_ref_id"];
}
//else check if page is a structure head page 
else {
    $page_ref_id = $structlib->get_struct_ref_if_head($page);
}

//If a structure page isnt going to be displayed
if (!isset($page_ref_id)) {
    //Check to see if its a member of any structures
    if (isset($_REQUEST['structure']) && !empty($_REQUEST['structure'])) {
      $structure=$_REQUEST['structure'];
    } else {
      $structure='';
    }
    $structs = $structlib->get_page_structures($_REQUEST["page"],$structure);
    //If page is only member of one structure, display if requested
    $single_struct = count($structs) == 1; 
    if ($feature_wiki_open_as_structure == 'y' and $single_struct ) {
      $page_ref_id=$structs[0]['req_page_ref_id'];
      $_REQUEST["page_ref_id"]=$page_ref_id;
    }
    //Otherwise, populate a list of structures
    else {
      $smarty->assign('showstructs', $structs);
    }

}

if(isset($page_ref_id)) {
    $smarty->assign('structure','y');
    $page_info = $structlib->s_get_page_info($page_ref_id);
    $smarty->assign('page_info', $page_info);
    $navigation_info = $structlib->get_navigation_info($page_ref_id);
    $smarty->assign('next_info', $navigation_info["next"]);
    $smarty->assign('prev_info', $navigation_info["prev"]);
    $smarty->assign('parent_info', $navigation_info["parent"]);
    $smarty->assign('home_info', $navigation_info["home"]);
    $page = $page_info["pageName"];
    $info = null;
    // others still need a good set page name or they will get confused.
    // comments of home page were all visible on every structure page
    $_REQUEST["page"]=$page;
    $structure_path = $structlib->get_structure_path($page_ref_id);
    $smarty->assign('structure_path', $structure_path);
} else {
    $page_ref_id = '';
}
$smarty->assign_by_ref('page',$page);
$smarty->assign('page_ref_id', $page_ref_id);


// Get page data, if available
if (!$info)
	$info = $tikilib->get_page_info($page);

// If the page doesn't exist then display an error
if(empty($info)) {
    $smarty->assign('msg',tra("Sorry, \"$page\" has not been created."));
    $smarty->display("error.tpl");
    die;
}

// Update the pagename with the canonical name
$page = $info['pageName'];

$creator = $wikilib->get_creator($page);
$smarty->assign('creator',$creator);

// Let creator set permissions
if($wiki_creator_admin == 'y') {
    if ($creator && $user && ($creator==$user)) {
	$tiki_p_admin_wiki = 'y';
	$smarty->assign( 'tiki_p_admin_wiki', 'y' );
    }
}

require_once('tiki-pagesetup.php');

$objId = urldecode($page);
if ($tiki_p_admin != 'y' && $feature_categories == 'y' && !$object_has_perms) {
	// Check to see if page is categorized
	$perms_array = $categlib->get_object_categories_perms($user, 'wiki page', $objId);
   	if (is_array($perms_array)) {
   		$is_categorized = TRUE;
    	foreach ($perms_array as $perm => $value) {
    		$$perm = $value;
    	}
   	} else {
   		$is_categorized = FALSE;
   	}
	if ($is_categorized && isset($tiki_p_view_categories) && $tiki_p_view_categories != 'y') {
		if (!isset($user) && $tikilib->get_preference('auth_method', 'tiki') == 'cas') {
			header('location: tiki-login.php');
			exit;
		} elseif (!isset($user)) {
			$smarty->assign('msg',$smarty->fetch('modules/mod-login_box.tpl'));
			$smarty->assign('errortitle',tra("Please login"));
		} else {
			$smarty->assign('msg',tra("Permission denied you cannot view this page"));
    	}
	    $smarty->display("error.tpl");
		die;
	}
} elseif ($feature_categories == 'y') {
	$is_categorized = $categlib->is_categorized('wiki page',$objId);
} else {
	$is_categorized = FALSE;
}


// Now check permissions to access this page
if($tiki_p_view != 'y') {
    if (!isset($user) && $tikilib->get_preference('auth_method', 'tiki') == 'cas') {
		header('location: tiki-login.php');
		exit;
    } elseif (!isset($user)) {
	$smarty->assign('msg',$smarty->fetch('modules/mod-login_box.tpl'));
	$smarty->assign('errortitle',tra("Please login"));
    } else {
	$smarty->assign('msg',tra("Permission denied you cannot view this page"));
    } 
    $smarty->display("error.tpl");
    die;  
}

// Get translated page
if ($feature_multilingual == 'y' && $info['lang'] && $info['lang'] != "NULL") { //NULL is a temporary patch
	global $multilinguallib;
	include_once("lib/multilingual/multilinguallib.php");
	$trads = $multilinguallib->getTranslations('wiki page', $info['page_id'], $page, $info['lang']);
	$smarty->assign('trads', $trads);
	$pageLang = $info['lang'];
}

if(isset($_REQUEST["copyrightpage"])) {
  $smarty->assign_by_ref('copyrightpage',$_REQUEST["copyrightpage"]); 
}

// Get the backlinks for the page "page"
$backlinks = $wikilib->get_backlinks($page);
$smarty->assign_by_ref('backlinks', $backlinks);

// BreadCrumbNavigation here
// Get the number of pages from the default or userPreferences
// Remember to reverse the array when posting the array
$anonpref = $tikilib->get_preference('userbreadCrumb',4);
if($user) {
    $userbreadCrumb = $tikilib->get_user_preference($user,'userbreadCrumb',$anonpref);
} else {
    $userbreadCrumb = $anonpref;
}
if(!isset($_SESSION["breadCrumb"])) {
    $_SESSION["breadCrumb"]=Array();
}
if(!in_array($page,$_SESSION["breadCrumb"])) {
    if(count($_SESSION["breadCrumb"])>$userbreadCrumb) {
	array_shift($_SESSION["breadCrumb"]);
    } 
    array_push($_SESSION["breadCrumb"],$page);
} else {
    // If the page is in the array move to the last position
    $pos = array_search($page, $_SESSION["breadCrumb"]);
    unset($_SESSION["breadCrumb"][$pos]);
    array_push($_SESSION["breadCrumb"],$page);
}
//print_r($_SESSION["breadCrumb"]);


// Now increment page hits since we are visiting this page
if($count_admin_pvs == 'y' || $user!='admin') {
    $tikilib->add_hit($page);
}

$smarty->assign('page_user',$info['user']);

// Check if we have to perform an action for this page
// for example lock/unlock
if( 
	($tiki_p_admin_wiki == 'y') 
	|| 
	($user and ($tiki_p_lock == 'y') and ($feature_wiki_usrlock == 'y'))
  ) {
    if(isset($_REQUEST["action"])) {
	check_ticket('index');
	if($_REQUEST["action"]=='lock') {
	    $wikilib->lock_page($page);
	    $info["flag"] = 'L';
	    $smarty->assign('lock',true);  
	}  
    }
}

if( 
	($tiki_p_admin_wiki == 'y') 
	|| 
	($user and ($user == $info['user']) and ($tiki_p_lock == 'y') and ($feature_wiki_usrlock == 'y'))
  ) {
    if(isset($_REQUEST["action"])) {
	check_ticket('index');
	if ($_REQUEST["action"]=='unlock') {
	    $wikilib->unlock_page($page);
	    $smarty->assign('lock',false);  
	    $info["flag"] = 'U';
	}  
    }
}


// Save to notepad if user wants to
if($user 
	&& $tiki_p_notepad == 'y' 
	&& $feature_notepad == 'y' 
	&& isset($_REQUEST['savenotepad'])) {
    check_ticket('index');
    $tikilib->replace_note($user,0,$page,$info['data']);
}

// Verify lock status
if($wikilib->is_locked($page, $info)) {
    $smarty->assign('lock',true);  
} else {
    $smarty->assign('lock',false);
}
$smarty->assign('editable', $wikilib->is_editable($page, $user, $info));

// If not locked and last version is user version then can undo
$smarty->assign('canundo','n');	
if($info["flag"]!='L' && ( ($tiki_p_edit == 'y' && $info["user"]==$user)||($tiki_p_remove=='y') )) {
    $smarty->assign('canundo','y');	
}
if($tiki_p_admin_wiki == 'y') {
    $smarty->assign('canundo','y');		
}

// Process an undo here
if(isset($_REQUEST["undo"])) {
    if($tiki_p_admin_wiki == 'y' || ($info["flag"]!='L' && ( ($tiki_p_edit == 'y' && $info["user"]==$user)||($tiki_p_remove=='y')) )) {
	$area = 'delundopage';
	if (isset($_POST['daconfirm']) and isset($_SESSION["ticket_$area"])) {
	    key_check($area);

	    // Remove the last version	
	    $wikilib->remove_last_version($page);
	    // If page was deleted then re-create
	    if(!$tikilib->page_exists($page)) {
		$tikilib->create_page($page,0,'',date("U"),'Tiki initialization'); 
	    }
	    // Restore page information
	    $info = $tikilib->get_page_info($page);  	
	} else {
	    key_get($area);
	}
    }	
}

if ($wiki_uses_slides == 'y') {
    $slides = split("-=[^=]+=-",$info["data"]);
    if(count($slides)>1) {
	$smarty->assign('show_slideshow','y');
    } else {
	$slides = explode("...page...",$info["data"]);
	if(count($slides)>1) {
	    $smarty->assign('show_slideshow','y');
	} else {
	    $smarty->assign('show_slideshow','n');
	}
    }
} else {
    $smarty->assign('show_slideshow','n');
}

if($feature_wiki_attachments == 'y') {
    if(isset($_REQUEST["removeattach"])) {
	check_ticket('index');
	$owner = $wikilib->get_attachment_owner($_REQUEST["removeattach"]);
	if( ($user && ($owner == $user) ) || ($tiki_p_wiki_admin_attachments == 'y') ) {
	    $wikilib->remove_wiki_attachment($_REQUEST["removeattach"]);
	}
    }
    if(isset($_REQUEST["attach"]) && ($tiki_p_wiki_admin_attachments == 'y' || $tiki_p_wiki_attach_files == 'y')) {
	check_ticket('index');
	// Process an attachment here
	if(isset($_FILES['userfile1'])&&is_uploaded_file($_FILES['userfile1']['tmp_name'])) {

	    $file_name = $_FILES['userfile1']['name'];	
	    $file_tmp_name = $_FILES['userfile1']['tmp_name'];
	    $tmp_dest = $tmpDir . "/" . $file_name;
	    if (!move_uploaded_file($file_tmp_name, $tmp_dest)) {
		$smarty->assign('msg', tra('Errors detected'));
		$smarty->display("error.tpl");
		die();
	    }

	    $fp = fopen($tmp_dest, "rb");
	    $data = '';
	    $fhash='';
	    if($w_use_db == 'n') {
		$fhash = md5($name = $_FILES['userfile1']['name']);    
		$fw = fopen($w_use_dir.$fhash,"wb");
		if(!$fw) {
		    $smarty->assign('msg',tra('Cannot write to this file:').$fhash);
		    $smarty->display("error.tpl");
		    die;  
		}
	    }
	    while(!feof($fp)) {
		if($w_use_db == 'y') {
		    $data .= fread($fp,8192*16);
		} else {
		    $data = fread($fp,8192*16);
		    fwrite($fw,$data);
		}
	    }
	    fclose($fp);
	    if($w_use_db == 'n') {
		fclose($fw);
		$data='';
	    }
	    $size = $_FILES['userfile1']['size'];
	    $name = $_FILES['userfile1']['name'];
	    $type = $_FILES['userfile1']['type'];
	    $wikilib->wiki_attach_file($page,$name,$type,$size, $data, $_REQUEST["attach_comment"], $user,$fhash);
	}
    }

    $atts = $wikilib->list_wiki_attachments($page,0,-1,'created_desc','');
    $smarty->assign('atts',$atts["data"]);
    $smarty->assign('atts_count',count($atts["data"]));
}


if(isset($_REQUEST['refresh'])) {
    check_ticket('index');
    $tikilib->invalidate_cache($page);	
}

$smarty->assign('cached_page','n');
if(isset($info['wiki_cache'])) {$wiki_cache=$info['wiki_cache'];}
if($wiki_cache>0) {
    $cache_info = $wikilib->get_cache_info($page);
    $now = date('U');
    if($cache_info['cache_timestamp']+$wiki_cache > $now) {
	$pdata = $cache_info['cache'];
	$smarty->assign('cached_page','y');
    } else {
	$pdata = $tikilib->parse_data($info["data"]);
	$wikilib->update_cache($page,$pdata);
    }
} else {
    $pdata = $tikilib->parse_data($info["data"]);
}

$smarty->assign_by_ref('parsed',$pdata);

if(!isset($_REQUEST['pagenum'])) $_REQUEST['pagenum']=1;


$pages = $wikilib->get_number_of_pages($pdata);

$pdata=$wikilib->get_page($pdata,$_REQUEST['pagenum']);

$smarty->assign('pages',$pages);

if($pages>$_REQUEST['pagenum']) {
    $smarty->assign('next_page',$_REQUEST['pagenum']+1);
} else {
    $smarty->assign('next_page',$_REQUEST['pagenum']);
}
if($_REQUEST['pagenum']>1) {
    $smarty->assign('prev_page',$_REQUEST['pagenum']-1);
} else {
    $smarty->assign('prev_page',1);
}

$smarty->assign('first_page',1);

$smarty->assign('last_page',$pages);

$smarty->assign('pagenum',$_REQUEST['pagenum']);


//$smarty->assign_by_ref('lastModif',date("l d of F, Y  [H:i:s]",$info["lastModif"]));
$smarty->assign_by_ref('lastModif',$info["lastModif"]);
if(empty($info["user"])) {
    $info["user"]='anonymous';  
}
$smarty->assign_by_ref('lastUser',$info["user"]);
$smarty->assign_by_ref('description',$info["description"]);
/*
// force enable wiki comments (for development)
$feature_wiki_comments = 'y';
$smarty->assign('feature_wiki_comments','y');
 */

// Comments engine!
if($feature_wiki_comments == 'y') {
    $comments_per_page = $wiki_comments_per_page;
    $comments_default_ordering = $wiki_comments_default_ordering;
    $comments_vars=Array('page');
    $comments_prefix_var='wiki page:';
    $comments_object_var='page';
    include_once("comments.php");
}

$section='wiki';
include_once('tiki-section_options.php');

$smarty->assign('footnote','');
$smarty->assign('has_footnote','n');
if($feature_wiki_footnotes == 'y') {
    if($user) {
	$x = $wikilib->get_footnote($user,$page);
	$footnote=$wikilib->get_footnote($user,$page);
	$smarty->assign('footnote',$tikilib->parse_data($footnote));
	if($footnote) $smarty->assign('has_footnote','y');
    }
}

$smarty->assign('wiki_extras','y');

if($feature_theme_control == 'y') {
    $cat_type='wiki page';
    $cat_objid = $page;
    include('tiki-tc.php');
}

// Watches
if($feature_user_watches == 'y') {
    if($user && isset($_REQUEST['watch_event'])) {
	check_ticket('index');
	if($_REQUEST['watch_action']=='add') {
	    $tikilib->add_user_watch($user,$_REQUEST['watch_event'],$_REQUEST['watch_object'],tra('Wiki page'),$page,"tiki-index.php?page=$page");
	} else {
	    $tikilib->remove_user_watch($user,$_REQUEST['watch_event'],$_REQUEST['watch_object']);
	}
    }
    $smarty->assign('user_watching_page','n');
    if($user && $watch = $tikilib->get_user_event_watches($user,'wiki_page_changed',$page)) {
	$smarty->assign('user_watching_page','y');
    }
}


$sameurl_elements=Array('pageName','page');
//echo $info["data"];

if(isset($_REQUEST['mode']) && $_REQUEST['mode']=='mobile') {
    /*
       require_once("lib/hawhaw/hawhaw.inc");
       require_once("lib/hawhaw/hawiki_cfg.inc");
       require_once("lib/hawhaw/hawiki_parser.inc");
       require_once("lib/hawhaw/hawiki.inc");
       $myWiki = new HAWIKI_page($info["data"],"tiki-index.php?mode=mobile&page=");

       $myWiki->set_navlink(tra("Home Page"), "tiki-index.php?mode=mobile", HAWIKI_NAVLINK_TOP | HAWIKI_NAVLINK_BOTTOM);
       $myWiki->set_navlink(tra("Menu"), "tiki-mobile.php", HAWIKI_NAVLINK_TOP | HAWIKI_NAVLINK_BOTTOM);
       $myWiki->set_smiley_dir("img/smiles");
       $myWiki->set_link_jingle("lib/hawhaw/link.wav");
       $myWiki->set_hawimconv("lib/hawhaw/hawimconv.php");

       $myWiki->display();
       die;
     */
    include_once("lib/hawhaw/hawtikilib.php");
    HAWTIKI_index($info);
}

// Display category path or not (like {catpath()})
if (isset($is_categorized) && $is_categorized) {
    $smarty->assign('is_categorized','y');
    if(isset($feature_categorypath) and $feature_categories == 'y') {
	if ($feature_categorypath == 'y') {
	    $cats = $categlib->get_object_categories('wiki page',$objId);
	    $display_catpath = $categlib->get_categorypath($cats);
	    $smarty->assign('display_catpath',$display_catpath);
	}
    }
    // Display current category objects or not (like {category()})
    if (isset($feature_categoryobjects) and $feature_categories == 'y') {
	if ($feature_categoryobjects == 'y') {
	    $catids = $categlib->get_object_categories('wiki page', $objId);
	    $display_catobjects = $categlib->get_categoryobjects($catids);
	    $smarty->assign('display_catobjects',$display_catobjects);
	}
    }
} else {
    $smarty->assign('is_categorized','n');
}

// Flag for 'page bar' that currently 'Page view' mode active
// so it is needed to show comments & attachments panels
$smarty->assign('show_page','y');
ask_ticket('index');

global $feature_wiki_dblclickedit, $feature_wiki_page_footer, $tiki_p_view_wiki_history;
$smarty->assign('feature_wiki_dblclickedit',$feature_wiki_dblclickedit);
$smarty->assign('tiki_p_view_wiki_history',$tiki_p_view_wiki_history);
$smarty->assign('is_a_wiki_page', 'y');

if ($feature_wiki_page_footer == 'y') {
	$smarty->assign('feature_wiki_page_footer', 'y');
	global $wiki_page_footer_content;
	$current_url = 'http://' . $_REQUEST['HTTP_HOST'] . $_REQUEST['REQUEST_URI'];
	$content = str_replace('{url}', $current_url, $wiki_page_footer_content);
	$smarty->assign('wiki_page_footer_content', $content);
} else {
	$smarty->assign('feature_wiki_page_footer', 'n');
}

// Display the Index Template
$smarty->assign('print_page','n');
$smarty->assign('mid','tiki-show_page.tpl');
$smarty->assign('show_page_bar','y');
$smarty->assign('categorypath',$feature_categorypath);
$smarty->assign('categoryobjects',$feature_categoryobjects);
$smarty->assign('feature_wiki_pageid', $feature_wiki_pageid);
$smarty->assign('page_id',$info['page_id']);
$smarty->display("tiki.tpl");

// xdebug_dump_function_profile(XDEBUG_PROFILER_CPU);

?>
