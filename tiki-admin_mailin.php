<?php
// Initialization

require_once('tiki-setup.php');
include_once('lib/mailin/mailinlib.php');

// Add a new mail account for the user here  
if(!isset($_REQUEST["accountId"])) $_REQUEST["accountId"]=0;  
$smarty->assign('accountId',$_REQUEST["accountId"]);  
if(isset($_REQUEST["new_acc"])) {    
  $mailinlib->replace_mailin_account($_REQUEST["accountId"],$_REQUEST["account"],$_REQUEST["pop"],$_REQUEST["port"],$_REQUEST["username"],$_REQUEST["pass"],$_REQUEST["smtp"],$_REQUEST["useAuth"],$_REQUEST["smtpPort"],$_REQUEST["type"],$_REQUEST["active"]);
  $_REQUEST["accountId"]=0;  
}  
if(isset($_REQUEST["remove"])) {    
  $mailinlib->remove_mailin_account($_REQUEST["remove"]);  
}  
if($_REQUEST["accountId"]) {    
  $info = $mailinlib->get_mailin_account($_REQUEST["accountId"]); 
} else {    
  $info["account"]='';    
  $info["username"]='';    
  $info["pass"]='';    
  $info["pop"]='';    
  $info["smtp"]='';    
  $info["useAuth"]='n';    
  $info["port"]=110;    
  $info["smtpPort"]=25;    
  $info["type"]='wiki-get';  
  $info["active"]='y';
}  
$smarty->assign('info',$info);  
// List  
$accounts = $mailinlib->list_mailin_accounts(0,-1,'account_asc','');  
$smarty->assign('accounts',$accounts["data"]);

$smarty->assign('mid','tiki-admin_mailin.tpl');
$smarty->display("styles/$style_base/tiki.tpl");
?>
