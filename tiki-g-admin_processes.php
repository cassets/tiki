<?php
require_once('tiki-setup.php');
include_once('lib/Galaxia/ProcessManager.php');


// The galaxia process manager PHP script.

if($feature_workflow != 'y') {
  $smarty->assign('msg',tra("This feature is disabled"));
  $smarty->display("styles/$style_base/error.tpl");
  die;  
}

if($tiki_p_admin_workflow != 'y') {
  $smarty->assign('msg',tra("Permission denied"));
  $smarty->display("styles/$style_base/error.tpl");
  die;  
}

// Check if we are editing an existing process
// if so retrieve the process info and assign it.
if(!isset($_REQUEST['pid'])) $_REQUEST['pid'] = 0;
if($_REQUEST["pid"]) {
  $info = $processManager->get_process($_REQUEST["pid"]);
  $info['graph']="lib/Galaxia/Processes/".$info['normalized_name']."/graph/".$info['normalized_name'].".png"; 
} else {
  $info = Array(
    'name' => '',
    'description' => '',
    'version' => '1.0',
    'isActive' => 'n',
    'pId' => 0
  );
}

$smarty->assign_by_ref('proc_info',$info);
$smarty->assign('pid',$_REQUEST['pid']);
$smarty->assign('info',$info);

//Check here for an uploaded process
if(isset($_FILES['userfile1'])&&is_uploaded_file($_FILES['userfile1']['tmp_name'])) {
  $fp = fopen($_FILES['userfile1']['tmp_name'],"rb");
  $data = '';
  $fhash='';
  while(!feof($fp)) {
    $data .= fread($fp,8192*16);
  }
  fclose($fp);
  $size = $_FILES['userfile1']['size'];
  $name = $_FILES['userfile1']['name'];
  $type = $_FILES['userfile1']['type'];
	  
  $process_data = $processManager->unserialize_process($data);
  if($processManager->process_name_exists($process_data['name'],$process_data['version'])) {
    $smarty->assign('msg',tra("The process name already exists"));
  	$smarty->display("styles/$style_base/error.tpl");
  	die;  
  } else {
	$processManager->import_process($process_data);
  }

}


if(isset($_REQUEST["delete"])) {
  foreach(array_keys($_REQUEST["process"]) as $item) {      	
    $processManager->remove_process($item);
  }
}

if(isset($_REQUEST['newminor'])) {
  $processManager->new_process_version($_REQUEST['newminor']);
}
if(isset($_REQUEST['newmajor'])) {
  $processManager->new_process_version($_REQUEST['newmajor'],false);
}


if(isset($_REQUEST['save'])) {
	$vars = Array(
	  'name' => $_REQUEST['name'],
	  'description' => $_REQUEST['description'],
	  'version' => $_REQUEST['version'],
	  'isActive' => 'n'
	);
	if($processManager->process_name_exists($_REQUEST['name'],$_REQUEST['version']) && $_REQUEST['pid']==0) {
	  $smarty->assign('msg',tra("Process already exists"));
	  $smarty->display("styles/$style_base/error.tpl");
	  die;  
	}
	if(isset($_REQUEST['isActive'])&&$_REQUEST['isActive']=='on') {
		$vars['isActive'] = 'y';
	}
	$pid = $processManager->replace_process($_REQUEST['pid'],$vars);
	
    $valid = $activityManager->validate_process_activities($pid);
    if(!$valid) {
      $processManager->deactivate_process($pid);
    }  
	
	$info = Array(
    'name' => '',
    'description' => '',
    'version' => '1.0',
    'isActive' => 'n',
    'pId' => 0
    );	
    $smarty->assign('info',$info);
}

$where = ''; 
$wheres=Array();
if(isset($_REQUEST['filter'])) {
  if($_REQUEST['filter_name']) {
   $wheres[]=" name='".$_REQUEST['filter_name']."'";
  }
  if($_REQUEST['filter_active']) {
   $wheres[]=" isActive='".$_REQUEST['filter_active']."'";
  }
  $where = implode('and',$wheres);
}
if(isset($_REQUEST['where'])) {
  $where = $_REQUEST['where'];
}

if(!isset($_REQUEST["sort_mode"])) {  $sort_mode = 'lastModif_desc'; } else {  $sort_mode = $_REQUEST["sort_mode"];} 
if(!isset($_REQUEST["offset"])) {  $offset = 0;} else {  $offset = $_REQUEST["offset"]; }$smarty->assign_by_ref('offset',$offset);
if(isset($_REQUEST["find"])) { $find = $_REQUEST["find"];  } else {  $find = ''; } $smarty->assign('find',$find);
$smarty->assign('where',$where); $smarty->assign_by_ref('sort_mode',$sort_mode);

$items = $processManager->list_processes($offset,$maxRecords,$sort_mode,$find,$where);
$smarty->assign('cant',$items['cant']);

$cant_pages = ceil($items["cant"] / $maxRecords);$smarty->assign_by_ref('cant_pages',$cant_pages);$smarty->assign('actual_page',1+($offset/$maxRecords));
if($items["cant"] > ($offset+$maxRecords)) {  $smarty->assign('next_offset',$offset + $maxRecords);} else {  $smarty->assign('next_offset',-1); }
if($offset>0) {  $smarty->assign('prev_offset',$offset - $maxRecords);  } else {  $smarty->assign('prev_offset',-1); }
$smarty->assign_by_ref('items',$items["data"]);

if($_REQUEST['pid']) {
  $valid = $activityManager->validate_process_activities($_REQUEST['pid']);
  $errors = Array();
  if(!$valid) {
    $processManager->deactivate_process($_REQUEST['pid']);
    $errors = $activityManager->get_error();
  }
  $smarty->assign('errors',$errors);
}

$sameurl_elements = Array('offset','sort_mode','where','find','filter_name','filter_active');

$all_procs = $items = $processManager->list_processes(0,-1,'name_desc','','');
$smarty->assign_by_ref('all_procs',$all_procs['data']);

$smarty->assign('mid','tiki-g-admin_processes.tpl');
$smarty->display("styles/$style_base/tiki.tpl");
?>