<?php
// Initialization
require_once('tiki-setup.php');

//if($feature_wiki != 'y') {
//  die;
//}

//Permissions
if($tiki_p_view != 'y') {
  $smarty->assign('msg',tra("Permission denied you cannot view this page"));
  $smarty->display("styles/$style_base/error.tpl");
  die;
}

//feature
$feature_wiki_pdf=$tikilib->get_preference('feature_wiki_pdf','n');
if($feature_wiki_pdf != 'y') {
  $smarty->assign('msg',tra("This feature is disabled"));
  $smarty->display("styles/$style_base/error.tpl");
  die;
}

//defaults

if(!isset($_REQUEST["font"])){
  $_REQUEST["font"]="Helvetica";
}

if(!isset($_REQUEST["textheight"])){
  $_REQUEST["textheight"]=10;
}

if(!isset($_REQUEST["h1height"])){
  $_REQUEST["h1height"]=16;
}

if(!isset($_REQUEST["h2height"])){
  $_REQUEST["h2height"]=14;
}

if(!isset($_REQUEST["h3height"])){
  $_REQUEST["h3height"]=12;
}

if(!isset($_REQUEST["tbheight"])){
  $_REQUEST["tbheight"]=14;
}

if(!isset($_REQUEST["imagescale"])){
  $_REQUEST["imagescale"]=0.4;
}

if(!isset($_REQUEST["convertpages"])) {
  $convertpages = Array();
  if(isset($_REQUEST["page"]) && $tikilib->page_exists($_REQUEST["page"])) {
    $convertpages[]=$_REQUEST["page"];
  }
} else {
  $convertpages = unserialize(urldecode($_REQUEST['convertpages']));
}

if(isset($_REQUEST["find"])) {
  $find = $_REQUEST["find"];
} else {
  $find = '';
}

// assign to smarty
$smarty->assign('font',$_REQUEST["font"]);
$smarty->assign('textheight',$_REQUEST["textheight"]);
$smarty->assign('h1height',$_REQUEST["h1height"]);
$smarty->assign('h2height',$_REQUEST["h2height"]);
$smarty->assign('h3height',$_REQUEST["h3height"]);
$smarty->assign('tbheight',$_REQUEST["tbheight"]);
$smarty->assign('imagescale',$_REQUEST["imagescale"]);
$smarty->assign('find',$find);

//add pages
if(isset($_REQUEST["addpage"])) {
  foreach(array_keys($_REQUEST["addpageName"]) as $item) {
    if(!in_array($_REQUEST["addpageName"]["$item"],$convertpages)) {
      $convertpages[] = $_REQUEST["addpageName"]["$item"];
    }
  }
}

//remove pages
if(isset($_REQUEST["rempage"])) {
  foreach(array_keys($_REQUEST["rempageName"]) as $item) {
    if($key=array_search($_REQUEST["rempageName"]["$item"],$convertpages)) {
      unset($convertpages[$key]);
    }
  }
}
//clear
if(isset($_REQUEST["clearpages"])) {
  $convertpages = Array();
}

$smarty->assign('convertpages',$convertpages);
$form_convertpages = urlencode(serialize($convertpages));
$smarty->assign('form_convertpages',$form_convertpages);

// insert pdfcreation code here

$pages = $tikilib->list_pages(0, -1,  'pageName_asc',$find);
$smarty->assign_by_ref('pages',$pages["data"]);

$smarty->assign('mid','tiki-config_pdf.tpl');
$smarty->display("styles/$style_base/tiki.tpl");
?>

