<?php

// Displays a snippet of code
// Parameters: bgcolor (optional background color)
// Example:
// {CODE()}
// print("foo");
// {CODE}

function wikiplugin_code($data,$params) {
  global $tikilib;
  extract($params);
  $code=htmlentities(htmlspecialchars(trim($data)));
  if(!isset($bgcolor)) {$bgcolor='#EEEEEE';}
  $data = '<div style="background-color:$bgcolor;">'.$code.'</div>';
  return $data;
}


?>

