<?php
// Initialization
require_once('tiki-setup.php');
require_once('lib/tikilib.php'); # httpScheme()
include_once('lib/registration/registrationlib.php');
include_once('lib/notifications/notificationlib.php');

// Permission: needs p_register
if($allowRegister != 'y') {
  header("location: index.php");
  die;
}

$smarty->assign('showmsg','n');
// novalidation is set to yes if a user confirms his email is correct after tiki fails to validate it
if (!isset($_REQUEST['novalidation'])) {
	$novalidation = '';
} else {
	$novalidation = $_REQUEST['novalidation'];
}

if(isset($_REQUEST["register"])) {
  if($novalidation != 'yes' and ($_REQUEST["pass"] <> $_REQUEST["pass2"])) {
    $smarty->assign('msg',tra("The passwords don't match"));
    $smarty->display("styles/$style_base/error.tpl");
    die;
  }
  if($userlib->user_exists($_REQUEST["name"])) {
    $smarty->assign('msg',tra("User already exists"));
    $smarty->display("styles/$style_base/error.tpl");
    die;
  }
  
  if($rnd_num_reg == 'y') {
  	if($novalidation != 'yes' and(!isset($_SESSION['random_number']) || $_SESSION['random_number']!=$_REQUEST['regcode'])) {
    $smarty->assign('msg',tra("Wrong registration code"));
    $smarty->display("styles/$style_base/error.tpl");
    die;	
  	}
  }
  
  // VALIDATE NAME HERE
  if(strtolower($_REQUEST["name"])=='admin') {
    $smarty->assign('msg',tra("Invalid username"));
    $smarty->display("styles/$style_base/error.tpl");
    die;
  }
  
  if(strlen($_REQUEST["name"])>37) {
    $smarty->assign('msg',tra("Username is too long"));
    $smarty->display("styles/$style_base/error.tpl");
    die;
  }
  
  if(strstr($_REQUEST["name"],' ')) {
    $smarty->assign('msg',tra("Username cannot contain whitespace"));
    $smarty->display("styles/$style_base/error.tpl");
    die; 	
  }
  
  //Validate password here
  if(strlen($_REQUEST["pass"])<$min_pass_length) {
    $smarty->assign('msg',tra("Password should be at least").' '.$min_pass_length.' '.tra("characters long"));
    $smarty->display("styles/$style_base/error.tpl");
    die; 	
  }
  
  // Check this code
  if($pass_chr_num == 'y') {
    if(!preg_match_all("/[0-9]+/",$_REQUEST["pass"],$foo) || !preg_match_all("/[A-Za-z]+/",$_REQUEST["pass"],$foo)) {
      $smarty->assign('msg',tra("Password must contain both letters and numbers"));
      $smarty->display("styles/$style_base/error.tpl");
      die; 	
    }
  }
  
  if(!preg_match_all("/[A-Z0-9a-z\_\-]+/",$_REQUEST["name"],$matches)) {
    $smarty->assign('msg',tra("Invalid username"));
    $smarty->display("styles/$style_base/error.tpl");
    die;
  }
  
  // Check the mode
  if($useRegisterPasscode == 'y') {
    if(($_REQUEST["passcode"]!=$tikilib->get_preference("registerPasscode",md5($tikilib->genPass()))))
    {
      $smarty->assign('msg',tra("Wrong passcode you need to know the passcode to register in this site"));
      $smarty->display("styles/$style_base/error.tpl");
      die;
    }
  }
  

    if($validateUsers=='y') {
      $ret = $registrationlib->SnowCheckMail($_REQUEST["email"],$sender_email,$novalidation);
      $email_valid = 'yes';
      if(!$ret[0]) {
      	if($ret[1] == 'not_recognized') {
			$smarty->assign('notrecognized','y');
			$smarty->assign('email',$_REQUEST['email']);
			$smarty->assign('login',$_REQUEST['name']);
			$smarty->assign('password',$_REQUEST['pass']);
			$email_valid = 'no';
      	} else {
//			$smarty->assign('msg',"$ret[1]");
	        $smarty->assign('msg',tra("Invalid email address. You must enter a valid email address"));
	        $smarty->display("styles/$style_base/error.tpl");
	        $email_valid = 'no';
	        die;
      	}
      }
    }

  
  if($validateUsers == 'y' and $email_valid != 'no') {
    //$apass = addslashes(substr(md5($tikilib->genPass()),0,25));
    $apass = addslashes(md5($tikilib->genPass()));
    $foo = parse_url($_SERVER["REQUEST_URI"]);
    $foo1=str_replace("tiki-register","tiki-login_validate",$foo["path"]);
    $machine =httpPrefix().$foo1;
    $userlib->add_user($_REQUEST["name"],$apass,$_REQUEST["email"],$_REQUEST["pass"]);
    $emails = $notificationlib->get_mail_events('user_registers','*');
    foreach($emails as $email) {
      $smarty->assign('mail_user',$_REQUEST["name"]);
      $smarty->assign('mail_date',date("U"));
      $smarty->assign('mail_site',$_SERVER["SERVER_NAME"]);
      $mail_data = $smarty->fetch('mail/new_user_notification.tpl');
      mail($email, tra('New user registration'),$mail_data,"From: $sender_email\r\nContent-type: text/plain;charset=utf-8\r\n");
    }
    // Send the mail
    $smarty->assign('msg',tra('You will receive an email with information to login for the first time into this site'));
    $smarty->assign('mail_machine',$machine);
    $smarty->assign('mail_site',$_SERVER["SERVER_NAME"]);
    $smarty->assign('mail_user',$_REQUEST["name"]);
    $smarty->assign('mail_apass',$apass);
    $mail_data = $smarty->fetch('mail/user_validation_mail.tpl');
    mail($_REQUEST["email"], tra('Your Tiki information registration'),$mail_data,"From: $sender_email\r\nContent-type: text/plain;charset=utf-8\r\n");
    
    $smarty->assign('showmsg','y');
  } elseif ($email_valid != 'no') {
    $userlib->add_user($_REQUEST["name"],$_REQUEST["pass"],$_REQUEST["email"],'');
    $smarty->assign('msg',tra("Thank you for you registration. You may log in now."));
    $smarty->assign('showmsg','y');
  }

}

$smarty->assign('mid','tiki-register.tpl');
$smarty->display("styles/$style_base/tiki.tpl");
?>
