<?php
// Initialization
require_once('tiki-setup.php');
setcookie('tiki-user','',-3600);
$userlib->user_logout($user);
session_unregister("user");
unset($_SESSION['user']);
session_destroy();
unset($user);
header("location: $tikiIndex");
exit;
?>