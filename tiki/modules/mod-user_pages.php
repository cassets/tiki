<?php

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"],basename(__FILE__)) !== false) {
  die("This script cannot be called directly");
}

$ranking = $tikilib->get_user_pages($user, $module_rows);

$smarty->assign('modUserPages', $ranking);
$smarty->assign('nonums', isset($module_params["nonums"]) ? $module_params["nonums"] : 'n');

?>
