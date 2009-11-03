<?php
// CVS: $Id: userslib.php,v 1.247.2.30 2008-03-22 12:21:02 sylvieg Exp $
//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"],basename(__FILE__)) !== false) {
  header("location: index.php");
  exit;
}

// Lib for user administration, groups and permissions
// This lib uses pear so the constructor requieres
// a pear DB object

// some definitions for helping with authentication
define("USER_VALID", 2);

define("SERVER_ERROR", -1);
define("PASSWORD_INCORRECT", -3);
define("USER_NOT_FOUND", -5);
define("ACCOUNT_DISABLED", -6);
define("ACCOUNT_WAITING_USER", -9);
define ("USER_AMBIGOUS", -7);
define('USER_NOT_VALIDATED', -8);

//added for Auth v1.3 support
define ("AUTH_LOGIN_OK", 0);

class UsersLib extends TikiLib {
# var $db;  // The PEAR db object used to access the database

	// change this to an email address to receive debug emails from the LDAP code
	var $debug = false;

	var $usergroups_cache;
	var $groupperm_cache;
	var $groupinclude_cache;
	var $userobjectperm_cache; // used to cache queries in object_has_one_permission()
	var $get_object_permissions_for_user_cache;

	function __construct() {
		parent::__construct();

		// Initialize caches
		$this->usergroups_cache = array();
		$this->groupperm_cache = array(array());
		$this->groupinclude_cache = array();
		$this->get_object_permissions_for_user_cache = array();
	}

	function assign_object_permission($groupName, $objectId, $objectType, $permName) {
		$objectId = md5($objectType . strtolower($objectId));

		$query = "delete from `users_objectpermissions`
			where `groupName` = ? and
			`permName` = ? and
			`objectId` = ?";
		$result = $this->query($query, array($groupName, $permName,
			$objectId), -1, -1, false);

		$query = "insert into `users_objectpermissions`(`groupName`,
			`objectId`, `objectType`, `permName`)
			values(?, ?, ?, ?)";
		$result = $this->query($query, array($groupName, $objectId,
			$objectType, $permName));
		if ($objectType == 'file gallery') {
			global $cachelib; require_once('lib/cache/cachelib.php');
			$cachelib->empty_type_cache('fgals_perms_'.$objectId."_");
		}
		return true;
	}

	function object_has_permission($user, $objectId, $objectType, $permName) {
		$groups = $this->get_user_groups($user);
		$objectId = md5($objectType . strtolower($objectId));
		$mid = implode(',',array_fill(0,count($groups),'?'));
		$query = "select count(*) from `users_objectpermissions` where `groupName` in ($mid) and `objectId` = ? and `objectType` = ? and `permName` = ?";
		$bindvars = array_merge($groups, array($objectId, $objectType, $permName));
		$result = $this->getOne($query, $bindvars);
		if ($result > 0) {
			return true;
		} else {
			return false;
		}
	}

	function remove_object_permission($groupName, $objectId, $objectType, $permName) {
		$objectId = md5($objectType . strtolower($objectId));

		$query = "delete from `users_objectpermissions`
			where `groupName` = ? and `objectId` = ?
			and `objectType` = ? and `permName` = ?";
		$bindvars = array($groupName, $objectId, $objectType,
			$permName);
		$result = $this->query($query, $bindvars);
		if ($objectType == 'file gallery') {
			global $cachelib; require_once('lib/cache/cachelib.php');
			$cachelib->empty_type_cache('fgals_perms_'.$objectId."_");
		}
		return true;
	}

	function copy_object_permissions($objectId,$destinationObjectId,$objectType) {
		$objectId = md5($objectType . strtolower($objectId));

		$query = "select `permName`, `groupName`
			from `users_objectpermissions`
			where `objectId` =? and
			`objectType` = ?";
		$bindvars = array($objectId, $objectType);
		$result = $this->query($query, $bindvars);
		while($res = $result->fetchRow()) {
			$this->assign_object_permission($res["groupName"],$destinationObjectId,$objectType,$res["permName"]);
		}
		return true;
	}

	// assign permissions for an individual object according to the global permissions for that object type
	function inherit_global_permissions($objectId, $objectType) {
		global $cachelib;

		$groups = $this->get_groups();
		if (!$cachelib->isCached($objectType . "_permission_names")) {
			$perms = $this->get_permissions(0, -1, 'permName_desc', '', $objectType);
			$cachelib->cacheItem($objectType . "_permission_names",serialize($perms));
		} else {
			$perms = unserialize($cachelib->getCached($objectType . "_permission_names"));
		}
		foreach ($groups['data'] as $group) {
			foreach ($perms['data'] as $perm) {
				if (in_array($perm['permName'], $group['perms'])) {
					$this->assign_object_permission($group['groupName'], $objectId, $objectType, $perm['permName']);
				}
			}
		}
	}

	function get_object_permissions($objectId, $objectType, $group='', $perm='') {
		$objectId = md5($objectType . strtolower($objectId));

		$query = "select `groupName`, `permName`
			from `users_objectpermissions`
			where `objectId` = ? and
			`objectType` = ?";
		$bindvars = array($objectId, $objectType);
		if (!empty($group)) {
			$query .= " and `groupName`=?";
			$bindvars[] = $group;
		}
		if (!empty($perm)) {
			$query .= " and `permName`=?";
			$bindvars[] = $perm;
		}
		$result = $this->query($query, $bindvars);
		$ret = array();

		while ($res = $result->fetchRow()) {
			$ret[] = $res;
		}

		return $ret;
	}

	function get_object_permissions_for_user ($objectId, $objectType, $user) {
		$params = md5($objectId . $objectType . $user);
		//Check the cache for these parameters
		if (array_key_exists($params, $this->get_object_permissions_for_user_cache)) {
			return $this->get_object_permissions_for_user_cache[$params];
		}
		$objectId = md5($objectType . strtolower($objectId));
		$bindvars = array($objectId, $objectType);
		$groups = $this->get_user_groups($user);
		$bindvars = array_merge($bindvars, $groups);
		$query = 'select `permName` from `users_objectpermissions`  where `objectId` = ? and `objectType` = ?  and `groupName` in ('.implode(',', array_fill(0, count($groups),'?')).')';
		$result = $this->query($query, $bindvars);
		$ret = array();
		while ($res = $result->fetchRow()) {
			$ret[] = $res['permName'];
		}
		//Cache the result for this set of parameters
		$this->get_object_permissions_for_user_cache[$params] = $ret;
		return $ret;
	}

	function object_has_one_permission($objectId, $objectType) {
		$objectId = md5($objectType . strtolower($objectId));

		if(!isset($this->userobjectperm_cache) || !is_array($this->userobjectperm_cache)
			|| !isset($this->userobjectperm_cache[$objectId])) {
			// i think, we really dont need the "and `objectType`=?" because the objectId should be unique due to the md5()
			$query = "select count(*) from `users_objectpermissions` where `objectId`=? and `objectType`=?";
			$this->userobjectperm_cache[$objectId]= $this->getOne($query, array(
				$objectId,
				$objectType
				));
		}

		return $this->userobjectperm_cache[$objectId];
	}

	function user_exists($user) {
		static $rv = array();
		if (!isset($rv[$user])) {
			$query = "select count(*) from `users_users` where upper(`login`) = ?";
			$result = $this->getOne($query, array(strtoupper($user)));
			$rv[$user] = $result;
		}
		return $rv[$user];
	}

	function group_exists($group) {
		return in_array($group, $this->list_all_groups());
	}

	function user_logout($user) {
		global $prefs;
		$query = 'delete from `tiki_user_preferences` where `prefName`=? and `user`=?';
		$user = $this->query($query, array('cookie',(string)$user));
		if ($prefs['feature_intertiki'] == 'y' and $prefs['feature_intertiki_sharedcookie'] == 'y' and !empty($prefs['feature_intertiki_mymaster'])) {
			include_once('XML/RPC.php');
			$remote = $prefs['interlist'][$prefs['feature_intertiki_mymaster']];
			$remote['path'] = preg_replace("/^\/?/","/",$remote['path']);
			$client = new XML_RPC_Client($remote['path'], $remote['host'], $remote['port']);
			$client->setDebug(0);
			$msg = new XML_RPC_Message(
				   'intertiki.logout',
						 array(
						 new XML_RPC_Value($prefs['tiki_key'], 'string'),
						 new XML_RPC_Value($user, 'string')
						 ));
			$client->send($msg);
		}
	}

    function genPass() {
	// AWC: enable mixed case and digits, don't return too short password
	global $prefs;                                          //AWC

	$vocales = "AaEeIiOoUu13580";                                     //AWC
	$consonantes = "BbCcDdFfGgHhJjKkLlMmNnPpQqRrSsTtVvWwXxYyZz24679"; //AWC
	$r = '';
	$passlen = ($prefs['min_pass_length'] > 5) ? $prefs['min_pass_length'] : 5;         //AWC

	for ($i = 0; $i < $passlen; $i++) {                               //AWC
	    if ($i % 2) {
		$r .= $vocales{rand(0, strlen($vocales) - 1)};
	    } else {
		$r .= $consonantes{rand(0, strlen($consonantes) - 1)};
	    }
	}

	return $r;
    }

    function generate_challenge() {
	$val = md5($this->genPass());

	return $val;
    }

    function validate_hash($user, $hash) {
	return $this->getOne(
		"select count(*) from `users_users` where " . $this->convertBinary(). " `login` = ? and `hash`=?",
		array($user, $hash)
		);
    }

	// For each auth method, validate user in auth, if valid, verify tiki user exists and create if necessary (as configured)
	// Once complete, update_lastlogin and return result, username and login message.
	function validate_user($user, $pass, $challenge, $response, $validate_phase=false) {
	global $tikilib, $prefs, $user_ldap_attributes;

	if ($user != 'admin' && $prefs['feature_intertiki'] == 'y' && !empty($prefs['feature_intertiki_mymaster'])) {
	    // slave intertiki sites should never check passwords locally, just for admin
	    return false;
	}

	if (strlen($pass) < $prefs['min_pass_length']) {
		return false;
	}
	// these will help us keep tabs of what is going on
	$userTiki = false;
	$userTikiPresent = false;
	$userLdap = false;
	$userLdapPresent = false;

	// read basic pam options
	$auth_pam = ($prefs['auth_method'] == 'pam');
	$pam_create_tiki = ($prefs['pam_create_user_tiki'] == 'y');
	$pam_skip_admin = ($prefs['pam_skip_admin'] == 'y');

	// read basic LDAP options
	$auth_ldap = ($prefs['auth_method'] == 'ldap');
	$ldap_create_tiki = ($prefs['ldap_create_user_tiki'] == 'y');
	$create_auth = ($prefs['ldap_create_user_ldap'] == 'y');
	$skip_admin = ($prefs['ldap_skip_admin'] == 'y');

	// read basic cas options
	global $phpcas_enabled;
	if ($phpcas_enabled == 'y') {
		$auth_cas = ($prefs['auth_method'] == 'cas');
		$cas_create_tiki = ($prefs['cas_create_user_tiki'] == 'y');
		$cas_skip_admin = ($prefs['cas_skip_admin'] == 'y');
	} else {
		$auth_cas = $cas_create_tiki = $cas_skip_admin = false;
	}

	// see if we are to use Shibboleth
	$auth_shib = ($prefs['auth_method'] == 'shib');
	$shib_create_tiki = ($prefs['shib_create_user_tiki'] == 'y');
	$shib_skip_admin = ($prefs['shib_skip_admin'] == 'y');

	// first attempt a login via the standard Tiki system
	//
	if (!($auth_shib || $auth_cas) || $user == 'admin') { //redflo: does this mean, that users in cas and shib are not replicated to tiki tables? Does this work well?
		list($result, $user) = $this->validate_user_tiki($user, $pass, $challenge, $response, $validate_phase);
	} else {
		$result = NULL;
	}
	switch ($result) {
	    case USER_VALID:
		$userTiki = true;

		$userTikiPresent = true;
		break;

	    case PASSWORD_INCORRECT:
		$userTikiPresent = true;

		break;
	}

	// if we aren't using LDAP this will be quick
	// if we are using tiki auth or if we're using an alternative auth except for admin
	if ((!$auth_ldap && !$auth_pam && !$auth_cas && !$auth_shib) || ((($auth_ldap && $skip_admin) || ($auth_shib && $shib_skip_admin) || ($auth_pam && $pam_skip_admin) || ($auth_cas && $cas_skip_admin)) && $user == "admin") || ($auth_ldap && ($prefs['auth_ldap_permit_tiki_users']=='y' && $userTiki))) { // todo: bad hack. better search for a more general solution here
	    // if the user verified ok, log them in
	    if ($userTiki)  //user validated in tiki, update lastlogin and be done
		return array($this->update_lastlogin($user), $user, $result);
	    // if the user password was incorrect but the account was there, give an error
	    elseif ($userTikiPresent)  //user ixists in tiki but bad password
		return array(false, $user, $result);
	    // if the user was not found, give an error
	    // this could be for future uses
	    else
		return array(false, $user, $result);
	}

	// For the alternate auth methods, attempt to validate user
	// return back one of two conditions
	// Valid User or Bad password
	// next see if we need to check PAM
	elseif ($auth_pam) {

		$result = $this->validate_user_pam($user, $pass);
		switch ($result) {
		case USER_VALID:
			$userPAM = true;

			break;
		case PASSWORD_INCORRECT:
			$userPAM = false;

			break;
		}

    	// start off easy
	    // if the user verified in Tiki and PAM, log in
	    if ($userPAM && $userTiki) {
			return array($this->update_lastlogin($user), $user, $result);
	    }
	    // if the user wasn't found in either system, just fail
	    elseif (!$userTikiPresent && !$userPAM) {
			return array(false, $user, $result);
	    }
	    // if the user was logged into PAM but not found in Tiki
	    elseif ($userPAM && !$userTikiPresent) {
			// see if we can create a new account
			if ($pam_create_tiki) {
			    // need to make this better! *********************************************************
			    $result = $this->add_user($user, $pass, '');

			    // if it worked ok, just log in
			    if ($result == USER_VALID)
					// before we log in, update the login counter
					return array($this->update_lastlogin($user), $user, $result);
			    // if the server didn't work, do something!
			    elseif ($result == SERVER_ERROR) {
					// check the notification status for this type of error
					return array(false, $user, $result);
			    }
			    // otherwise don't log in.
			    else
					return array(false, $user, $result);
			}
			// otherwise
			else
			    // just say no!
			    return array(false, $user, $result);
	    }
	    // if the user was logged into PAM and found in Tiki (no password in Tiki user table necessary)
	    elseif ($userPAM && $userTikiPresent)
			return array($this->update_lastlogin($user), $user, $result);
	}

	// next see if we need to check CAS
	elseif ($auth_cas) {
		$result = $this->validate_user_cas($user);
		switch ($result) {
		case USER_VALID:
			$userCAS = true;

			break;
		case PASSWORD_INCORRECT:
			$userCAS = false;

			break;
		}
		if ($this->user_exists($user)) {
			$userTikiPresent = true;
		} else {
			$userTikiPresent = false;
		}

    	// start off easy
	    // if the user verified in Tiki and by CAS, log in
	    if ($userCAS && $userTiki) {
			return array($this->update_lastlogin($user), $user, $result);
	    }
	    // if the user wasn't authenticated through CAS, just fail
	    elseif (!$userCAS) {
			return array(false, $user, $result);
	    }
	    // if the user was authenticated by CAS but not found in Tiki
	    elseif ($userCAS && !$userTikiPresent) {
			// see if we can create a new account
			if ($cas_create_tiki) {
			    // need to make this better! *********************************************************
			    $randompass = $this->genPass();
			    // in case CAS auth is turned off accidentally;
			    // we don't want ppl to be able to login as any user with blank passwords
			    $result = $this->add_user($user, $randompass, '');

			    // if it worked ok, just log in
			    if ($result == USER_VALID)
					// before we log in, update the login counter
					return array($this->update_lastlogin($user), $user, $result);
			    // if the server didn't work, do something!
			    elseif ($result == SERVER_ERROR) {
					// check the notification status for this type of error
					return array(false, $user, $result);
			    }
			    // otherwise don't log in.
			    else
					return array(false, $user, $result);
			}
			// otherwise
			else
			    // just say no!
			    return array(false, $user, $result);
	    }
	    // if the user was authenticated by CAS and found in Tiki (no password in Tiki user table necessary)
	    elseif ($userCAS && $userTikiPresent)
			return array($this->update_lastlogin($user), $user, $result);
	}

	// next see if we need to check Shibboleth
	elseif ($auth_shib) {
		if ($this->user_exists($user)) {
			$userTikiPresent = true;
		} else {
			$userTikiPresent = false;
		}

		// Shibboleth login was not successful
		if (!isset($_SERVER['HTTP_SHIB_IDENTITY_PROVIDER'])){
			return false;
		}

		// Collect the shibboleth related attributes.
		$shibmail = $_SERVER['HTTP_MAIL'];
		$shibaffiliation = $_SERVER['HTTP_SHIB_EP_UNSCOPEDAFFILIATION'];
		$shibproviderid = $_SERVER['HTTP_SHIB_IDENTITY_PROVIDER'];

		// Get the affiliation information to log in
		$shibaffiliarray = split(";",strtoupper($shibaffiliation));
		$validaffiliarray = split(",",strtoupper($prefs['shib_affiliation']));
		$validafil=false;
		foreach($shibaffiliarray as $affil){
		   if(in_array($affil, $validaffiliarray)){
			   $validafil=true;
		   }
	   }

    	// start off easy
	    // if the user verified in Tiki and by Shibboleth, log in
	    if ($userTikiPresent AND $validafil) {
			return array($this->update_lastlogin($user), $user, USER_VALID);
	    }
	    else {
			global $smarty;
			// see if we can create a new account
			if ($shib_create_tiki) {

				if(!(strlen($user) > 0 AND strlen($shibmail) > 0 AND strlen($shibaffiliation) > 0))
				{
					$errmsg = "User registration error: You do not have the required shibboleth attributes (";

					if (strlen($user) == 0){
						$errmsg = $errmsg . "User ";
					}

					if (strlen($shibmail) == 0){
						$errmsg = $errmsg . "Mail ";
					}

					if (strlen($shibaffiliation) == 0){
						$errmsg = $errmsg . "Affiliation ";
					}

					$errmsg = $errmsg . "). For further information on this error goto the ((ShibReg)) Page";

					$smarty->assign('msg',$errmsg );
					$smarty->display('error.tpl');
					exit;
				}
				else
				{

					if($validafil)
					{

						// Create the user
						// need to make this better! *********************************************************
						$randompass = $this->genPass();
						// in case Shibboleth auth is turned off accidentally;
						// we don't want ppl to be able to login as any user with blank passwords

						$result = $this->add_user($user, $randompass, $shibmail);

						// if it worked ok, just log in
						if ($result == USER_VALID){
							// Add to the default Group
							if ($prefs['shib_usegroup'] == 'y'){
								$result = $this->assign_user_to_group($user, $prefs['shib_group']);
							}

							// before we log in, update the login counter
							return array($this->update_lastlogin($user), $user, $result);
						}
						// if the server didn't work, do something!
						elseif ($result == SERVER_ERROR) {
							// check the notification status for this type of error
							return array(false, $user, $result);
						}
						// otherwise don't log in.
						else{
							return array(false, $user, $result);
						}
					}
					else
					{
						foreach($validaffiliarray as $vaffil){
							$vaffils = $vaffils  . $vaffil . ", ";
						}
						$vaffils = rtrim($vaffils,", ");
						$errmsg = "<H1 align=center>User login error</H1><BR/><BR/>You must have one of the following affiliations to get into this wiki.<BR/><BR/><B>" . $vaffils . "</B><BR><BR/><BR/>For further information on this error goto the <a href=./tiki-index.php?page=ShibReg>Shibreg</a> Page";
						$smarty->assign('msg',$errmsg );
						$smarty->display('error.tpl');
						exit;
					}
				}
			}
			else{
				$smarty->assign('msg',"The user [ " . $user . " ] is not registered with this wiki.");
				$smarty->display('error.tpl');
				exit;
			}

	    }

	}

	// next see if we need to check LDAP
	else if($auth_ldap){
	    // check the user account
	    $result = $this->validate_user_ldap($user, $pass);

	    switch ($result) {
		case USER_VALID:
		    $userLdap = true;

		    $userLdapPresent = true;
		    break;

		case PASSWORD_INCORRECT:
		    $userLdapPresent = true;

		    break;
	    }

	    // start off easy
	    // if the user is in Tiki and password is verified in LDAP, log in
	    if ($userLdap && $userTikiPresent) {
		if( $res2=$this->ldap_sync_user_and_groups($user,$pass)) {
			return array($this->update_lastlogin($user), $user, $result);
		} else {
			return array(false,$user,USER_UNKNOWN);
		}
	    }
	    // if the user wasn't found in either system, just fail
	    elseif (!$userTikiPresent && !$userLdapPresent) {
		return array(false, $user, $result);
	    }
	    // if the user was logged into Tiki but not found in LDAP
	    elseif ($userTiki && !$userLdapPresent) {
		// see if we can create a new account
		if ($create_auth) {
		    // need to make this better! *********************************************************
		    $result = $this->create_user_ldap($user, $pass);

		    // if it worked ok, just log in
		    if ($result == USER_VALID)
			// before we log in, update the login counter
			return array($this->update_lastlogin($user), $user, $result);
		    // if the server didn't work, do something!
		    elseif ($result == SERVER_ERROR) {
			// check the notification status for this type of error
			return array(false, $user, $result);
		    }
		    // otherwise don't log in.
		    else
			return array(false, $user, $result);
		}
		// otherwise
		else
		    // just say no!
		    return array(false, $user, $result);
	    }

	    // if the user was logged into Auth but not found in Tiki
	    elseif ($userLdap && !$userTikiPresent) {
		// see if we are allowed to create a new account
		if ($ldap_create_tiki) {
		    // need to make this better! *********************************************************
		    //$userinfo = $this->get_ldap_user_info($user,$pass);
		    $result = $this->add_user($user, $pass, '');
		    $this->disable_tiki_auth($user); //disable password in tiki - use ldap


		    // if it worked ok, just log in
		    if ($result == USER_VALID) {
			// before we log in, update the login counter
			if( $res2=$this->ldap_sync_user_and_groups($user,$pass)) {
				return array($this->update_lastlogin($user), $user, $result);
			} else {
				return array(false,$user,USER_UNKNOWN);
			}
		    }
		    // if the server didn't work, do something!
		    elseif ($result == SERVER_ERROR) {
			// check the notification status for this type of error
			return array(false, $user, $result);
		    }
		    // otherwise don't log in.
		    else
			return array(false, $user, $result);
		}
		// otherwise
		else
		    // just say no!
		    return array(false, $user, $result);
	    }
	    // if the user was logged into Auth and found in Tiki (no password in Tiki user table necessary)
	    elseif ($userLdap && $userTikiPresent)
		return array($this->update_lastlogin($user), $user, $result);
	}

	// we will never get here
	return array(false, $user, $result);
    }

  // validate the user through PAM
    function validate_user_pam($user, $pass) {
	global $tikilib, $prefs;

	// just make sure we're supposed to be here
	if ($prefs['auth_method'] != "pam")
	    return false;

// Read page AuthPAM at tw.o, it says about a php module required.
// maybe and if extension line could be added here... module requires $error
// as reference.
	if (pam_auth($user, $pass, $error)) {
		return USER_VALID;
	} else {
	// Uncomment the following to see errors on that
	// error_log("TIKI ERROR PAM:  $error User: $user Pass: $pass");
		return PASSWORD_INCORRECT;
	}
    }

	// validate the user through CAS
	function validate_user_cas(&$user) {
		global $tikilib, $phpcas_enabled, $prefs;
		if ($phpcas_enabled != 'y') {
			return SERVER_ERROR;
		}
		// just make sure we're supposed to be here
		if ($prefs['auth_method'] != 'cas') {
		    return false;
		}

		// import phpCAS lib
		require_once('lib/phpcas/source/CAS/CAS.php');

		phpCAS::setDebug();

		// initialize phpCAS
		phpCAS::client($prefs['cas_version'], ''.$prefs['cas_hostname'], (int) $prefs['cas_port'], ''.$prefs['cas_path']);

		// check CAS authentication
		phpCAS::forceAuthentication();

		// at this step, the user has been authenticated by the CAS server
		// and the user's login name can be read with phpCAS::getUser().

		$user = phpCAS::getUser();

		if (isset($user)) {
			return USER_VALID;
		} else {
			return PASSWORD_INCORRECT;
		}
    }


    // validate the user via ldap and get a ldap connection
    function validate_user_ldap($user, $pass) {
        global $prefs;
	global $logslib;
		require_once('auth/ldap.php');
		$ldap_options=array('host' => $prefs['auth_ldap_host'],
				'port' => $prefs['auth_ldap_port'],
				'version' => $prefs['auth_ldap_version'],
				'starttls' => $prefs['auth_ldap_starttls'],
				'ssl' => $prefs['auth_ldap_ssl'],
				'basedn' => $prefs['auth_ldap_basedn'],
				'scope' => $prefs['auth_ldap_scope'],
				'bind_type' => $prefs['auth_ldap_type'],
				'username' => $user,
				'password' => $pass,
				'userdn' => $prefs['auth_ldap_userdn'],
				'useroc' => $prefs['auth_ldap_useroc'],
				'userattr' => $prefs['auth_ldap_userattr'],
				'fullnameattr' => $prefs['auth_ldap_nameattr'],
				'emailattr' => $prefs['auth_ldap_emailattr'],
				'countryattr' => $prefs['auth_ldap_countryattr'],
				'groupdn' => $prefs['auth_ldap_groupdn'],
				'groupattr' => $prefs['auth_ldap_groupattr'],
				'groupoc' => $prefs['auth_ldap_groupoc'],
				'groupnameattr' => $prefs['auth_ldap_groupnameatr'],
				'groupdescattr' => $prefs['auth_ldap_groupdescatr'],
				'groupmemberattr' => $prefs['auth_ldap_memberattr'],
				'groupmemberisdn' => $prefs['auth_ldap_memberisdn'],
				'usergroupattr' => $prefs['auth_ldap_usergroupattr'],
				'groupgroupattr' => $prefs['auth_ldap_groupgroupattr'],
				'debug' => $prefs['auth_ldap_debug']
				);
		// print_r($ldap_options);
		$this->ldap=new TikiLdapLib($ldap_options);
		switch($this->ldap->bind()) {
			case LDAP_INVALID_CREDENTIALS:
				return PASSWORD_INCORRECT;
				break;
			case LDAP_INVALID_SYNTAX:
			case LDAP_NO_SUCH_OBJECT:
			case LDAP_INVALID_DN_SYNTAX:
				return USER_NOT_FOUND;
				break;
			case LDAP_SUCCESS:
				if($prefs['auth_ldap_debug']=='y') $logslib->add_log('ldap','Bind successful.');
				return USER_VALID;
				break;
			default:
				return SERVER_ERROR;
		}

	// this should never happen
	die('Assertion failed '.__FILE__.':'.__LINE__);
    }


    // Help function to disable the user password. Used, whenever the user password 
    // shall not be hold in the tiki db but in LDAP or somewhere else
    function disable_tiki_auth($user) {
	global $tiki;
	$query = "update `users_users` set `password`=?, `hash`=? where " . $this->convertBinary(). " `login` = ?";
	$result = $this->query($query, array('','',$user));
    }

    // called after create user or login from ldap
    function ldap_sync_user_and_groups() {
	global $prefs;
	global $logslib;
	$ret=true;
	
        if($prefs['auth_ldap_debug']=='y') $logslib->add_log('ldap','Syncing user and group with ldap');
	$userattributes=$this->ldap->get_user_attributes();
	//print("<pre>");print_r($userattributes);print("</pre>");
	$user=$userattributes[$prefs['auth_ldap_userattr']];

	// sync user information
	$this->disable_tiki_auth($user);

	
	$u=array('login'=>$user);
	if(isset($userattributes[$prefs['auth_ldap_nameattr']])) {
		$u['realName']=$userattributes[$prefs['auth_ldap_nameattr']];
	}

	if(isset($userattributes[$prefs['auth_ldap_emailattr']])) {
		$u['email']=$userattributes[$prefs['auth_ldap_emailattr']];
	}
	
	if(isset($userattributes[$prefs['auth_ldap_countryattr']])) {
		$u['country']=$userattributes[$prefs['auth_ldap_countryattr']];
	}

	if(count($u)>1) {
		$this->set_user_fields($u);
	}

	// sync external group information of user
	$ldapgroups=$this->ldap->get_groups();
	$ldapgroups_simple=array();
	$tikigroups=$this->get_user_groups($user);
	foreach($ldapgroups as $group) {
		$gname=$group[$prefs['auth_ldap_groupattr']];
		$ldapgroups_simple[]=$gname; // needed later
		if($this->group_exists($gname) && !$this->group_is_external($gname)) { // group exists
			//check if we need to sync group information
			if(isset($group[$prefs['auth_ldap_groupdescattr']])) {
				$ginfo=$this->get_group_info($gname);
				if($group[$prefs['auth_ldap_groupdescattr']] != $ginfo['groupDesc']) {
					$this->set_group_description($gname,$group[$prefs['auth_ldap_groupdescattr']]);
				}
			}

		} else if(!$this->group_exists($gname)){ // create group
			if(isset($group[$prefs['auth_ldap_groupdescattr']])) {
				$gdesc=$group[$prefs['auth_ldap_groupdescattr']];
			} else {
				$gdesc='';
			}
			$logslib->add_log('ldap','Creating external group '.$gname);
			$this->add_group($gname,$gdesc,'',0,0,'','',0,'',0,0,'y');
		}

		// add user
		if(!in_array($gname,$tikigroups)) {
			$logslib->add_log('ldap','Adding user '.$user.' to external group '.$gname);
			$this->assign_user_to_group($user,$gname);
		}
	}

	// now clean up group membership if user has been unassigned from a group in ldap
	$extgroups=$this->get_user_external_groups($user);
	foreach($extgroups as $eg) {
		if(!in_array($eg,$ldapgroups_simple)) {
			$logslib->add_log('ldap','Removing user '.$user.' from external group '.$eg);
			$this->remove_user_from_group($user, $eg);
		}
	}

	return($ret);
    }

	function set_group_description($group,$description) {
		$query = "update `users_groups` set `groupDesc`=? where `groupName`=?";
		$result = $this->query($query, array($description,$group));

	}

	function group_is_external($group) {
		$gi=$this->get_group_info($group);
		if($gi['isExternal']=='y') {
			return true;
		}
		return false;
	}

	// simple function - no group inclusion or intertiki
	function get_user_external_groups($user) {
		$userid = $this->get_user_id($user);
		$query = "select u.`groupName` from `users_usergroups` u, `users_groups` g where u.`groupName`=g.`groupName` and u.`userId`=? and g.`isExternal`=?";
		$result=$this->query($query,array((int) $userid,'y'));
		$ret = array();
		while ($res = $result->fetchRow()) {
			 $ret[] = $res["groupName"];
		}
		return $ret;
	}

    // validate the user in the Tiki database
    function validate_user_tiki($user, $pass, $challenge, $response, $validate_phase=false) {
	global $prefs;

	// first verify that the user exists
	$query = "select * from `users_users` where " . $this->convertBinary(). " `login` = ?";
	$result = $this->query($query, array($user) );

	if (!$result->numRows())
	{
	    $query = "select * from `users_users` where upper(`login`) = ?";
	    $result = $this->query($query, array(strtoupper( $user )));
	    switch ($result->numRows()) {
	        case 0: return array(USER_NOT_FOUND, $user);
	        case 1: break;
	        default: return array(USER_AMBIGOUS, $user);
	    }
	}


	$res = $result->fetchRow();
	$user = $res['login'];

	// Temporary escape of the process until the email confirmation gets repaired
	// In the case the account was created with OpenID, no need to confirm the account
	// beyond this point.
	if( !empty( $res['openid_url'] ) && empty( $res['hash'] ) )
		return array(USER_VALID, $user);

	// next verify the password with every hashes methods
	if ($prefs['feature_challenge'] == 'n' || empty($response)) {
	    if ($res['hash'] == md5($user.$pass.trim($res['email']))) // very old method md5(user.pass.email), for compatibility
 		return array(USER_VALID, $user);

	    if ($res['hash'] == md5($user.$pass)) // old method md5(user.pass), for compatibility
		return array(USER_VALID, $user);

	    if ($res['hash'] == md5($pass)) // normal method md5(pass)
		return array(USER_VALID, $user);

	    if ($this->hash_pass($pass, $res['hash']) == $res['hash']) // new method (crypt-md5) and tikihash method (md5(pass))
		return array(USER_VALID, $user);

		if ($res['valid'] > '' && $pass == $res['valid']) // used for validation of user account before activation
		return array(USER_VALID, $user);

		if (!empty($res['valid']) && $res['waiting'] == 'u')
			return array(ACCOUNT_WAITING_USER, $user);
		if (!empty($res['valid']))
			return array(ACCOUNT_DISABLED, $user);
	    return array(PASSWORD_INCORRECT, $user);
	} else {
	    // Use challenge-reponse method
	    // Compare pass against md5(user,challenge,hash)
	    $hash = $this->getOne("select `hash`  from `users_users` where " . $this->convertBinary(). " `login`=?",
		    array($user) );

	    if (!isset($_SESSION["challenge"]))
		return array(false, $user);

	    //print("pass: $pass user: $user hash: $hash <br />");
	    //print("challenge: ".$_SESSION["challenge"]." challenge: $challenge<br />");
	    //print("response : $response<br />");
	    if ($response == md5($user . $hash . $_SESSION["challenge"])) {
			$this->update_lastlogin($user);
			return array(USER_VALID, $user);
	    } else {
			return array(false, $user);
	    }
	}

	return array(PASSWORD_INCORRECT, $user);
    }

    // update the lastlogin status on this user
    function update_lastlogin($user) {
	// Check
	$current = $this->getOne("select `currentLogin` from `users_users` where `login`= ?", array($user));

	if (is_null($current)) {
	    // First time
	    $current = $this->now;
	}

	$query = "update `users_users` set `lastLogin`=?, `currentLogin`=?, `unsuccessful_logins`=? where `login`=?";
	$result = $this->query($query, array(
		    (int)$current,
			(int)$this->now,
			0,
		    $user
		    ));

	return true;
    }

    // create a new user in the ldap directory
    function create_user_ldap($user, $pass) {
// todo: kein pear::auth mehr! alles in pead::ldap2 abbilden
	global $tikilib, $prefs;

	$options = array();
	$options['url'] = $prefs['auth_ldap_url'];
	$options['host'] = $prefs['auth_ldap_host'];
	$options['port'] = $prefs['auth_ldap_port'];
	$options['scope'] = $prefs['auth_ldap_scope'];
	$options['basedn'] = $prefs['auth_ldap_basedn'];
	$options['userdn'] = $prefs['auth_ldap_userdn'];
	$options['userattr'] = $prefs['auth_ldap_userattr'];
	$options['useroc'] = $prefs['auth_ldap_useroc'];
	$options['groupdn'] = $prefs['auth_ldap_groupdn'];
	$options['groupattr'] = $prefs['auth_ldap_groupattr'];
	$options['groupoc'] = $prefs['auth_ldap_groupoc'];
	$options['memberattr'] = $prefs['auth_ldap_memberattr'];
	$options['memberisdn'] = ($prefs['auth_ldap_memberisdn'] == 'y');
	$options['binduser'] = $prefs['auth_ldap_adminuser'];
	$options['bindpw'] = $prefs['auth_ldap_adminpass'];

	// set additional attributes here
	$userattr = array();
	$userattr['email'] = ( $prefs['login_is_email'] == 'y' ) ? $user : $this->getOne("select `email` from `users_users` where `login`=?", array($user));

	// set the Auth options
	$a = new Auth("LDAP", $options);

	// check if the login correct
	if ($a->addUser($user, $pass, $userattr) === true)
	    $status = USER_VALID;

	// otherwise use the error status given back
	else
	    $status = $a->getStatus();


	return $status;
    }

    function get_users_names($offset = 0, $maxRecords = -1, $sort_mode = 'login_asc', $find = '') {

	// Return an array of users indicating name, email, last changed pages, versions, lastLogin
	if ($find) {
	    $findesc = '%' . $find . '%';
	    $mid = " where `login` like ?";
	    $bindvars=array($findesc);
	} else {
	    $mid = '';
	    $bindvars=array();
	}

	$query = "select `login` from `users_users` $mid order by ".$this->convertSortMode($sort_mode);
	$result = $this->query($query,$bindvars,$maxRecords,$offset);
	$ret = array();

	while ($res = $result->fetchRow()) {
	    $ret[] = $res["login"];
	}

	return ($ret);
    }

function get_users($offset = 0, $maxRecords = -1, $sort_mode = 'login_asc', $find = '', $initial = '', $inclusion=false, $group='', $email='') {

	$mid = '';
	$bindvars = array();
	$mmid = '';
	$mbindvars = array();
	// Return an array of users indicating name, email, last changed pages, versions, lastLogin

	//TODO : recurse included groups
	if(!empty($group)) {
		if (!is_array($group)) {
			$group = array($group);
		}
		$mid = ', `users_usergroups` uug where uu.`userId`=uug.`userId` and uug.`groupName` in ('.implode(',',array_fill(0, count($group),'?')).')';
		$mmid = $mid;
		$bindvars = $group;
		$mbindvars = $bindvars;
	}
	if($email) {
		$mid.= $mid == '' ? ' where' : ' and';
		$mid.= ' uu.`email` like ?';
		$mmid = $mid;
	    	$bindvars[] = '%'.$email.'%';
		$mbindvars[] = '%'.$email.'%';
	}

	if ($find) {
	    $mid.= $mid == '' ? ' where' : ' and';
	    $mid.= " uu.`login` like ?";
			$mmid = $mid;
	    $bindvars[] = '%'.$find.'%';
			$mbindvars[] = '%'.$find.'%';
	}

	if ($initial) {
		$mid = " where `login` like ?";
		$mmid = $mid;
		$bindvars = array($initial.'%');
		$mbindvars = $bindvars;
	}

	$query = "select uu.* from `users_users` uu $mid order by ".$this->convertSortMode($sort_mode);
	$query_cant = "select count(*) from `users_users` uu $mmid";
	$result = $this->query($query, $bindvars, $maxRecords, $offset);
	$cant = $this->getOne($query_cant, $mbindvars);
	$ret = array();

	while ($res = $result->fetchRow()) {
	    $aux = array();

	    $res["user"] = $res["login"];
	    $user = $res["user"];
	    if ($inclusion) {
	    	$groups = $this->get_user_groups_inclusion($user);
	    } else {
	    	$groups = $this->get_user_groups($user);
	    }
	    $res["groups"] = $groups;
	    $res["age"] = $this->now - $res["registrationDate"];
            $res['user_information'] = $this->get_user_preference($user, 'user_information', 'public');

	    $ret[] = $res;
	}

	$retval = array();
	$retval["data"] = $ret;
	$retval["cant"] = $cant;
	return $retval;
}

    function group_inclusion($group, $include) {
	$query = "insert into `tiki_group_inclusion`(`groupName`,`includeGroup`)
		values(?,?)";
	$result = $this->query($query, array($group, $include));
    }

function get_included_groups($group, $recur=true) {
	$engroup = urlencode($group);
	if (!$recur || !isset($this->groupinclude_cache[$engroup])) {
		$query = "select `includeGroup`  from `tiki_group_inclusion` where `groupName`=?";
		$result = $this->query($query, array($group));
		$ret = array();
		while ($res = $result->fetchRow()) {
			$ret[] = $res["includeGroup"];
			if ($recur) {
				$ret2 = $this->get_included_groups($res["includeGroup"]);
				$ret = array_merge($ret, $ret2);
			}
		}
		$back = array_unique($ret);
		if ($recur) {
			$this->groupinclude_cache[$engroup] = $back;
		}
		return $back;
	} else {
		return $this->groupinclude_cache[$engroup];
	}
}
	function get_including_groups($group) {
		$query = 'select `groupName` from `tiki_group_inclusion` where `includeGroup`=?';
		$result = $this->query($query, array($group));
		$ret = array();
		while ($res = $result->fetchRow()) {
			$ret[] = $res['groupName'];
			$ret = array_merge($ret, $this->get_including_groups($res['groupName']));
		}
		return $ret;
	}
	function user_is_in_group($user, $group) {
		$user_details = $this->get_user_details($user);
		if (in_array($group, $user_details['groups'])) {
			return true;
		} else {
			return false;
		}
	}

    function remove_user_from_group($user, $group) {
	global $cachelib; require_once("lib/cache/cachelib.php");
	global $tikilib;
	$cachelib->invalidate('user_details_'.$user);
	$tikilib->invalidate_usergroups_cache($user);

	$userid = $this->get_user_id($user);

	$query = "delete from `users_usergroups` where `userId` = ? and
		`groupName` = ?";
	$result = $this->query($query, array($userid, $group));
	$query = "update `users_users` set `default_group`=? where `login`=? and `default_group`=?";
	$this->query($query, array('Registered', $user, $group));
	$_SESSION['u_info']['group'] = 'Registered';
    }

    function remove_user_from_all_groups($user) {
	$userid = $this->get_user_id($user);
	$query = "delete from `users_usergroups` where `userId` = ?";
	$result = $this->query($query, array($userid));
    }

    function get_groups($offset = 0, $maxRecords = -1, $sort_mode = 'groupName_asc', $find = '', $initial = '', $details="y", $inGroups='', $userChoice='') {

	$mid = "";
	$bindvars = array();
	if ($find) {
	    $mid = " where `groupName` like ?";
	    $bindvars[] = "%" . $find . "%";
	}

	if ($initial) {
	    $mid = " where `groupName` like ?";
	    $bindvars = array($initial . "%");
	}
	if ($inGroups) {
		$mid .= $mid? ' and ': ' where ';
		$mid .= '`groupName` in (';
		$cpt = 0;
		foreach ($inGroups as $grp=>$value) {
			if ($cpt++)
				$mid .= ',';
			$mid .= '?';
			$bindvars[] = $grp;
		}
		$mid .= ')';
	}
	if ($userChoice) {
		$mid .= $mid? ' and ': ' where ';
		$mid .= '`userChoice` = ?';
		$bindvars[] = 'y';
	}

	$query = "select * from `users_groups` $mid order by ".$this->convertSortMode($sort_mode);
	$query_cant = "select count(*) from `users_groups` $mid";
	$result = $this->query($query, $bindvars, $maxRecords, $offset);
	$cant = $this->getOne($query_cant, $bindvars);
	$ret = array();

	while ($res = $result->fetchRow()) {
	    if ($details == "y") {
	    	$perms = $this->get_group_permissions($res['groupName']);
	    	$res['perms'] = $perms;
		$res['permcant'] = count($perms);
	    	$groups = $this->get_included_groups($res['groupName']);
	    	$res['included'] = $groups;
	    }
	    $ret[] = $res;
	}

	$retval = array();
	$retval["data"] = $ret;
	$retval["cant"] = $cant;
	return $retval;
    }

	function list_all_users() {
		global $cachelib;
		if (!$cachelib->isCached("userslist")) {
			$users = array();
			$result = $this->query("select `login`,`userId` from `users_users` order by `login`", array());
			while ($res = $result->fetchRow()) {
				$users["{$res['userId']}"] = $res['login'];
			}
			$cachelib->cacheItem("userslist",serialize($users));
			return $users;
		} else {
			return unserialize($cachelib->getCached("userslist"));
		}
	}

	function list_all_groups() {
		global $cachelib;
		if (!$cachelib->isCached("grouplist")) {
			$groups = array();
			$result = $this->query("select `groupName` from `users_groups` order by `groupName`", array());
			while ($res = $result->fetchRow()) {
				$groups[] = $res['groupName'];
			}
			$cachelib->cacheItem("grouplist",serialize($groups));
			return $groups;
		} else {
			return unserialize($cachelib->getCached("grouplist"));
		}
	}
	function list_all_groupIds() {
		global $cachelib;
		if (!$cachelib->isCached("groupIdlist")) {
			$groups = array();
			$result = $this->query("select `id`, `groupName` from `users_groups` order by `groupName`", array());
			while ($res = $result->fetchRow()) {
				$groups[] = $res;
			}
			$cachelib->cacheItem("groupIdlist",serialize($groups));
			return $groups;
		} else {
			return unserialize($cachelib->getCached("groupIdlist"));
		}
	}

	function list_can_include_groups($group) {

		$list = array();
    		$query = "select `groupName` from `users_groups`";
		$result = $this->query($query);
		while($res = $result->fetchRow()) {
			if($res['groupName'] != $group) {
				$includedGroups = $this->get_included_groups($res['groupName']);
				if(!in_array($group, $includedGroups)) {
					$list[] = $res['groupName'];
				}
			}
		}
		return $list;
	}


    function remove_user($user) {
	global $cachelib;
	if ( $user == 'admin' ) return false;

	$userId = $this->getOne("select `userId`  from `users_users` where `login` = ?", array($user));

	$groupTracker = $this->get_tracker_usergroup( $user );
	if( $groupTracker && $groupTracker['usersTrackerId'] ) {
		global $trklib;
		if( ! $trklib ) require_once 'lib/trackers/trackerlib.php';

		$itemId = $trklib->get_item_id( $groupTracker['usersTrackerId'], $groupTracker['usersFieldId'], $user );
		if( $itemId )
			$trklib->remove_tracker_item( $itemId );
	}

	$tracker = $this->get_usertracker( $userId );
	if( $tracker && $tracker['usersTrackerId'] ) {
		global $trklib;
		if( ! $trklib ) require_once 'lib/trackers/trackerlib.php';

		$itemId = $trklib->get_item_id( $tracker['usersTrackerId'], $tracker['usersFieldId'], $user );
		if( $itemId )
			$trklib->remove_tracker_item( $itemId );
	}

	$query = "delete from `users_users` where ". $this->convertBinary()." `login` = ?";
	$result = $this->query($query, array( $user ) );
	$query = "delete from `users_usergroups` where `userId`=?";
	$result = $this->query($query, array( $userId ) );
	$query = "delete from `tiki_user_watches` where ". $this->convertBinary()." `user`=?";
	$result = $this->query($query, array($user));
	$query = "delete from `tiki_user_preferences` where ". $this->convertBinary()." `user`=?";
	$result = $this->query($query, array($user));
	$query = "delete from `tiki_newsletter_subscriptions` where ". $this->convertBinary()." `email`=? and `isUser`=?";
	$result = $this->query($query, array($user, 'y'));

	$cachelib->invalidate('userslist');
	return true;
    }

	function change_login($from,$to) {
		global $cachelib;
		if ( $from == 'admin' ) return false;

		$userId = $this->getOne("select `userId`  from `users_users` where `login` = ?", array($from));
		if ($userId) {
			$this->query("update `users_users` set `login`=? where `userId` = ?", array($to,(int)$userId));
			$this->query("update `tiki_wiki_attachments` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_webmail_messages` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_webmail_contacts` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_webmail_contacts_fields` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_users` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_userpoints` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_userfiles` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_user_watches` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_user_votings` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_user_tasks` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_user_tasks` set `creator`=? where `creator`=?", array($to,$from));
			$this->query("update `tiki_user_tasks_history` set `lasteditor`=? where `lasteditor`=?", array($to,$from));
			$this->query("update `tiki_user_taken_quizzes` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_user_quizzes` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_user_preferences` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_user_postings` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_user_notes` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_user_menus` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_user_mail_accounts` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_user_bookmarks_urls` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_user_bookmarks_folders` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_user_assigned_modules` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_tags` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_suggested_faq_questions` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_submissions` set `author`=? where `author`=?", array($to,$from));
			$this->query("update `tiki_shoutbox` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_sessions` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_semaphores` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_received_pages` set `receivedFromUser`=? where `receivedFromUser`=?", array($to,$from));
			$this->query("update `tiki_received_articles` set `author`=? where `author`=?", array($to,$from));
			$this->query("update `tiki_private_messages` set `poster`=? where `poster`=?", array($to,$from));
			$this->query("update `tiki_private_messages` set `toNickname`=? where `toNickname`=?", array($to,$from));
			$this->query("update `tiki_pages` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_pages` set `creator`=? where `creator`=?", array($to,$from));
			$this->query("update `tiki_page_footnotes` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_newsletters` set `author`=? where `author`=?", array($to,$from));
			$this->query("update `tiki_minical_events` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_minical_topics` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_mailin_accounts` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_live_support_requests` set `operator`=? where `operator`=?", array($to,$from));
			$this->query("update `tiki_live_support_requests` set `tiki_user`=? where `tiki_user`=?", array($to,$from));
			$this->query("update `tiki_live_support_requests` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_live_support_operators` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_live_support_messages` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_live_support_messages` set `username`=? where `username`=?", array($to,$from));
			$this->query("update `tiki_images` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_history` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_galleries` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_forums_reported` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_forums_queue` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_forums` set `moderator`=? where `moderator`=?", array($to,$from));
			$this->query("update `tiki_forum_reads` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_files` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_files` set `lastModifUser`=? where `lastModifUser`=?", array($to,$from));
			$this->query("update `tiki_files` set `lockedby`=? where `lockedby`=?", array($to,$from));
			$this->query("update `tiki_file_galleries` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_copyrights` set `userName`=? where `userName`=?", array($to,$from));
			$this->query("update `tiki_comments` set `userName`=? where `userName`=?", array($to,$from));
			$this->query("update `tiki_chat_users` set `nickname`=? where `nickname`=?", array($to,$from));
			$this->query("update `tiki_chat_messages` set `poster`=? where `poster`=?", array($to,$from));
			$this->query("update `tiki_chat_channels` set `moderator`=? where `moderator`=?", array($to,$from));
			$this->query("update `tiki_calendars` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_calendar_roles` set `username`=? where `username`=?", array($to,$from));
			$this->query("update `tiki_calendar_items` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_blogs` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_blog_posts` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_banning` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_banners` set `client`=? where `client`=?", array($to,$from));
			$this->query("update `tiki_articles` set `author`=? where `author`=?", array($to,$from));
			$this->query("update `tiki_actionlog` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `messu_messages` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `messu_messages` set `user_from`=? where `user_from`=?", array($to,$from));
			$this->query("update `tiki_newsletter_subscriptions` set `email`=? where `email`=? and `isUser`=?", array($to,$from, 'y'));
			$this->query("update `tiki_friends` set `user`=? where `user`=?", array($to,$from));
			$this->query("update `tiki_friends` set `friend`=? where `friend`=?", array($to,$from));
			$this->query("update `tiki_friendship_requests` set `userFrom`=? where `userFrom`=?", array($to,$from));
			$this->query("update `tiki_friendship_requests` set `userTo`=? where `userTo`=?", array($to,$from));
			$this->query("update `tiki_freetagged_objects` set `user`=? where `user`=?", array($to,$from));

			$this->query("update `tiki_tracker_item_comments` set `user`=? where `user`=?", array($to,$from));

			$result =  $this->query("select `fieldId`, `itemChoices` from `tiki_tracker_fields` where `type`='u'");

			while($res = $result->fetchRow())
			{
				$this->query("update `tiki_tracker_item_fields` set `value`=? where `value`=? and `fieldId`=?", array($to,$from,$res['fieldId']));

				$u = ($res['itemChoices'] != '' ) ? unserialize($res['itemChoices']) : array();

				if($value=array_search($from, $u))
				{
					$u[$value] = $to ;
					$u = serialize($u);
					$this->query("update `tiki_tracker_fields` set `itemChoices`=? where `fieldId`=?", array($u,$res['fieldId']));
				}
			}
			$cachelib->invalidate('userslist');
			return true;
		} else {
			return false;
		}
	}

    function remove_group($group) {
	if ( $group == 'Anonymous' || $group == 'Registered' ) return false;

	$query = "delete from `tiki_group_inclusion` where `groupName` = ? or `includeGroup` = ?";
	$result = $this->query($query, array($group, $group));

	$query = array();
	$query[] = "delete from `users_groups` where `groupName` = ?";
	$query[] = "delete from `users_usergroups` where `groupName` = ?";
	$query[] = "delete from `users_grouppermissions` where `groupName` = ?";
	$query[] = "delete from `users_objectpermissions` where `groupName` = ?";
	$query[] = "delete from `tiki_newsletter_groups` where `groupName` = ?";
	$query[] = "delete from `tiki_group_watches` where `group` = ?";
	foreach ( $query as $q )
		$this->query($q, array($group));
	$this->query("update `users_users` set `default_group`=? where `default_group`=?", array('Registered', $group));

	global $cachelib;
	$cachelib->invalidate('grouplist');
	$cachelib->invalidate('group_theme_'.$group);

	return true;
    }

	function get_user_default_group($user) {
		if (!isset($user)) {
			return 'Anonymous';
		}
		if ($user == $_SESSION['u_info']['login']) {
			if (isset($_SESSION['u_info']['group']) && is_string($_SESSION['u_info']['group'])) {
				return $_SESSION['u_info']['group'];
			} elseif (isset($_SESSION['u_info']['group']['groupName']) && is_string($_SESSION['u_info']['group']['groupName'])) {
				return $_SESSION['u_info']['group']['groupName'];
			}
		}
		$query = "select `default_group` from `users_users` where `login` = ?";
		$result = $this->getOne($query, array($user));
		$ret = '';
		if (!is_null($result) && $result != "") {
			$ret = $result;
		} else {
			$groups = $this->get_user_groups($user);
			foreach ($groups as $gr) {
				if ($gr != "Anonymous" and $gr != "Registered" and $gr != "") {
					$ret = $gr;
					break;
				}
			}
			if (!$ret) {
				$ret = "Registered";
			}
		}
		return $ret;
	}
	function get_user_default_homepage($user) {
		$result = $this->get_user_default_group($user);
		if (!is_null($result)) {
			$home = $this->get_group_home($result);
			if ($home != '')
				return $home;
		}
		$query = "select g.`groupHome`, g.`groupName` from `users_usergroups` as gu, `users_users` as u, `users_groups`as g where gu.`userId`= u.`userId` and u.`login`=? and gu.`groupName`= g.`groupName` and g.`groupHome` != '' and g.`groupHome` is not null";
		$result = $this->query($query,array($user));
		$home = '';
		while ($res = $result->fetchRow()) {
			if ($home != '') {
				$groups = $this->get_included_groups($res["groupName"]);
				if (in_array($group, $groups)) {
					$home = $res["groupHome"];
					$group = $res["groupName"];
				}
			}
			$home = $res["groupHome"];
			$group = $res["groupName"];
		}
		return $home;
	}
	function get_user_default_homepage2($user) {
		global $prefs;
		if ($prefs['useGroupHome'] == 'y') {
			$groupHome = $this->get_user_default_homepage($user);
			if ($groupHome)
				$p = $groupHome;
 			else
				$p = $prefs['wikiHomePage'];
		} else {
			$p = $prefs['wikiHomePage'];
		}
		return $p;
	}

	/* Returns a theme/style for this ithe default group of the current user. */
	function get_user_group_theme() {
		global $user;
		$group = $this->get_user_default_group($user);

		global $cachelib; require_once("lib/cache/cachelib.php");
		$k = 'group_theme_'.$group;

		if ( $cachelib->isCached($k) ) {
			$return = $cachelib->getCached($k);
		} elseif ( ! empty($group) ) {
			$query = 'select `groupTheme` from `users_groups` where `groupName` = ?';
			$return = $this->getOne($query, array($group));
			$cachelib->cacheItem($k, $return);
		}
		return $return;
	}

	/* Returns a default category for user's default_group
	*/
	function get_user_group_default_category($user) {
		$query = "select `groupDefCat` from `users_groups` ug, `users_users` uu where `login` = ? and ug.`groupName` = uu.`default_group`";
		$result = $this->getOne($query, array($user));
		return $result;
	}

  	//modified get_user_groups() to know if the user is part of the group directly or through groups inclusion
    function get_user_groups_inclusion($user) {
	    $userid = $this->get_user_id($user);

	    $query = "select `groupName` from `users_usergroups` where `userId`=?";
	    $result = $this->query($query, array((int)$userid));
	    $real = array(); //really assigned groups (not (only) included)
	    $ret = array();
	    while ($res = $result->fetchRow()) {
		$real[] = $res["groupName"];
		foreach ($this->get_included_groups($res["groupName"]) as $group) {
			$ret[$group] = "included";
	        }
	    }
	    foreach ($real as $group) {
		$ret[$group] = "real";
	    }
	    return $ret;
    }

	function get_group_home($group) {
		$query = "select `groupHome` from `users_groups` where `groupName`=?";
		$result = $this->getOne($query,array($group));
		$ret ='';
		if (!is_null($result)) {
			$ret = $result;
		}
		return $ret;
	}

    function get_group_users($group, $offset) {
		global $prefs;
	$query = "select `login`  from `users_users` uu, `users_usergroups` ug where uu.`userId`=ug.`userId` and `groupName`=?";
	$result = $this->query($query,array($group), $prefs['maxRecords'], $offset);
	$ret = array();
	while ($res = $result->fetchRow()) {
	    $ret[] = $res["login"];
	}
	return $ret;
    }

    function get_user_info($user, $inclusion = false, $field = 'login') {
    	global $prefs;
	if ( $field == 'userId' ) $user = (int)$user;
	elseif ( $field != 'login' ) return false;

	$result = $this->query("select * from `users_users` where `$field`=?", array($user));
	$res = $result->fetchRow();

	$res['groups'] = ( $inclusion ) ? $this->get_user_groups_inclusion($res['login']) : $this->get_user_groups($res['login']);
	$res['age'] = ( ! isset($res['registrationDate']) ) ? 0 : $this->now - $res['registrationDate'];
	if ( $prefs['login_is_email'] == 'y' && isset($res['login']) && $res['login'] != 'admin' ) $res['email'] = $res['login'];

	return $res;
    }

    function get_userid_info($user, $inclusion = false) { return $this->get_user_info($user, $inclusion, 'userId'); }

    // this is not being used anywhere until now in remote.php
    // refactoring to use new cachelib instead of global var in memory - batawata 2006-02-07
    function get_user_details($login, $item = false) {
	global $cachelib;
	require_once("lib/cache/cachelib.php");

	$cacheKey = 'user_details_'.$login;

	$user_details = array();

	if ($cachelib->isCached($cacheKey)) {
	    return unserialize($cachelib->getCached($cacheKey));
	} else {
	    $query  = 'SELECT `userId` , `login`, `email` , `lastLogin` , `currentLogin` , `registrationDate` , `created` ,  `avatarName` , `avatarSize` , `avatarFileType` , `avatarLibName` , `avatarType` FROM `users_users` WHERE `login` = ?';

	    $result = $this->query($query, array($login));

	    $user_details['info'] = $result->fetchRow();

	    $query  = 'SELECT `prefName` , `value` FROM `tiki_user_preferences` WHERE `user` = ?';
	    $result = $this->query($query, array($login));

	    $user_details['preferences'] = array();
	    $aUserPrefs = array('realName','homePage','country');
	    while ( $row = $result->fetchRow() ) {
		$user_details['preferences'][$row['prefName']] = $row['value'];

		// atention: this is redundant, for intertiki slave mode
		// we insert, delete and insert again this information,
		// because of nature of user information as being preferences
		if (in_array($row['prefName'], $aUserPrefs)) {
		    $user_details['info'][$row['prefName']] = $row['value'];
		}

	    }

	    $user_details['groups'] = $this->get_user_groups($login);

	    $cachelib->cacheItem($cacheKey, serialize($user_details));

	    global $user_preferences;
	    $user_preferences[$login] = $user_details['preferences'];

	    return $user_details;
	}
    }

    function set_default_group($user,$group) {
    	// if user is not in group, assign user to group before setting default group
    	$user_groups = $this->get_user_groups($user);
		if (!in_array($group, $user_groups) && !empty($group)) {
			$this->assign_user_to_group($user, $group);
		}
	$query = "update `users_users` set `default_group` = ?
		where `login` = ?";
	$this->query($query, array($group, $user));
    }

	function change_permission_level($perm, $level) {
		$query = "update `users_permissions` set `level` = ? where `permName` = ?";
		$this->query($query, array($level, $perm));

		global $cachelib;
		$cachelib->invalidate("allperms");
		$cachelib->empty_type_cache("fgals_perms");

		global $menulib; include_once('lib/menubuilder/menulib.php');
		$menulib->empty_menu_cache();
	}

	function assign_level_permissions($group, $level) {
		$query = "select `permName` from `users_permissions` where `level` = ?";
		$result = $this->query($query, array($level));
		$ret = array();

		while ($res = $result->fetchRow()) {
			$this->assign_permission_to_group($res['permName'], $group);
		}

		global $cachelib;
		$cachelib->invalidate("allperms");
		$cachelib->empty_type_cache("fgals_perms");
		$cachelib->invalidate("groupperms_$group");

		global $menulib; include_once('lib/menubuilder/menulib.php');
		$menulib->empty_menu_cache();
	}

	function remove_level_permissions($group, $level) {
		$query = "select `permName` from `users_permissions` where `level` = ?";
		$result = $this->query($query, array($level));
		$ret = array();

		while ($res = $result->fetchRow()) {
			$this->remove_permission_from_group($res['permName'], $group);
		}

		global $cachelib;
		$cachelib->invalidate("allperms");
		$cachelib->empty_type_cache("fgals_perms");
		$cachelib->invalidate("groupperms_$group");

		global $menulib; include_once('lib/menubuilder/menulib.php');
		$menulib->empty_menu_cache();
	}

	function create_dummy_level($level) {
		$query = "delete from `users_permissions` where `permName` = ?";
		$result = $this->query($query, array(''));
		$query = "insert into `users_permissions`(`permName`, `permDesc`, `type`, `level`) values('','','',?)";
		$this->query($query, array($level));

		global $cachelib;
		$cachelib->invalidate("allperms");
		$cachelib->empty_type_cache("fgals_perms");

		global $menulib; include_once('lib/menubuilder/menulib.php');
		$menulib->empty_menu_cache();
	}

    function get_permission_levels() {
	$query = "select distinct(`level`) from `users_permissions`";

	$result = $this->query($query);
	$ret = array();

	while ($res = $result->fetchRow()) {
	    $ret[] = $res['level'];
	}

	return $ret;
    }

	function get_tracker_usergroup($user) {
		$lastRes = '';
		$group = $this->get_user_default_group($user);
		if (!empty($group)) {
			$lastRes = $this->get_usertrackerid($group);
		}
		if (!$lastRes) {
			$groups = $this->get_user_groups($user);
			$query = 'select `groupName`, `usersTrackerId`, `usersFieldId` from `users_groups` where `groupName` in ( '.implode(' , ',array_fill(0,count($groups),'?')).' ) and `groupName` != ? and `usersTrackerId` > 0';
			$groups[] = 'Anonymous';
			$result = $this->query($query, $groups);
			while ($res = $result->fetchRow()) {
				$lastRes = $res;
				if ($res['groupName'] != 'Registered')
					return 	$res ;
			}
		}
		return $lastRes;
	}

	function get_grouptrackerid($group) {
		$res = $this->query("select `groupTrackerId`,`groupFieldId` from `users_groups` where `groupName`=?",array($group));
		$ret = $res->fetchRow();
		if (!$ret['groupTrackerId'] or !$ret['groupFieldId']) {
			$groups = $this->get_included_groups($group);
			foreach ($groups as $gr) {
				$res = $this->query("select `groupTrackerId`,`groupFieldId` from `users_groups` where `groupName`=?",array($gr));
				$ret = $res->fetchRow();
				if ($ret['groupTrackerId'] and $ret['groupFieldId']) {
					return $ret;
				}
			}
		} else {
			return $ret;
		}
		return false;
	}

	function get_usertrackerid($group) {
		$res = $this->query("select `usersTrackerId`,`usersFieldId` from `users_groups` where `groupName`=?",array($group));
		$ret = $res->fetchRow();
		if (!$ret['usersTrackerId'] or !$ret['usersFieldId']) {
			$groups = $this->get_included_groups($group);
			foreach ($groups as $gr) {
				$res = $this->query("select `usersTrackerId`,`usersFieldId` from `users_groups` where `groupName`=?",array($gr));
				$ret = $res->fetchRow();
				if ($ret['usersTrackerId'] and $ret['usersFieldId']) {
					return $ret;
				}
			}
		} else {
			return $ret;
		}
		return false;
	}


	function get_usertracker($uid) {
		$utr = $this->get_userid_info($uid);
		$utr["usersTrackerId"] = '';
		foreach ($utr['groups']  as $gr) {
			$utrid = $this->get_usertrackerid($gr);
			if ($utrid['usersTrackerId'] and $utrid['usersFieldId']) {
				$utrid['group'] = $gr;
				$utrid['user'] = $utr['login'];
				$utr = $utrid;
				break;
			}
		}
		return $utr;
	}

	function get_permissions($offset = 0, $maxRecords = -1, $sort_mode = 'permName_asc', $find = '', $type = '', $group = '', $enabledOnly = false) {
		global $prefs;

		$values = array();
		$sort_mode = $this->convertSortMode($sort_mode);
		$mid = '';
		if ($type && $type != 'all') {
			$mid = ' where `type`= ? ';
			$values[] = $type;
		}

		if ($find) {
			if ($mid) {
				$mid .= " and `permName` like ?";
				$values[] = '%'.$find.'%';
			} else {
				$mid .= " where `permName` like ?";
				$values[] = '%'.$find.'%';
			}
		} else {
			if ($mid) {
				$mid .= " and `permName` > ''";
			} else {
				$mid .= " where `permName` > ''";
			}
		}
		$query = "select * from `users_permissions` $mid order by $sort_mode ";
		$result = $this->query($query, $values, $maxRecords, $offset);
		$cant = 0;
		$ret = array();

		while ($res = $result->fetchRow()) {
			if( $enabledOnly && $res['feature_check'] ) {	// only list enabled features
				$feats = split(',', $res['feature_check']);
				$got_one = false;
				foreach ($feats as $feat) {
					if ( $prefs[ trim($feat) ] == 'y') {
						$got_one = true;
					}
				}
				if (!$got_one) {
					continue;
				}
			}

			$cant++;
			if ($group) {
				if (is_string($group)) {
					if ($this->group_has_permission($group, $res['permName'])) {
						$res['hasPerm'] = 'y';
						$res[count($res)/2] = 'y';	// keep indexed key too
					} else {
						$res['hasPerm'] = 'n';
						$res[count($res)/2] = 'n';
					}
				} else if (is_array($group)) {
					foreach( $group as $groupName) {
						if ($this->group_has_permission($groupName, $res['permName'])) {
							$res[$groupName.'_hasPerm'] = 'y';
							$res[count($res)/2] = 'y';
						} else {
							$res[$groupName.'_hasPerm'] = 'n';
							$res[count($res)/2] = 'n';
						}
					}
				}
			}

			$ret[] = $res;
		}

		return array(
			'data' => $ret,
			'cant' => $cant,
		);
	}

	function get_permission_types() {
		global $prefs;

		$query = "select distinct `type` from `users_permissions`";
		$result = $this->query($query);
		$cant = 0;
		$ret = array();

		while ($res = $result->fetchRow()) {
			$cant++;
			$ret[] = $res;
		}

		return array(
			'data' => $ret,
			'cant' => $cant,
		);
	}

	function get_group_permissions($group) {
    		global $cachelib;
		if ( ! $cachelib->isCached("groupperms_$group") ) {

			$query = "select `permName` from `users_grouppermissions` where `groupName`=?";
			$result = $this->query($query, array($group));
			$ret = array();

			while ( $res = $result->fetchRow() ) {
				$ret[] = $res["permName"];
			}

			$cachelib->cacheItem("groupperms_$group",serialize($ret));

		} else {
			$ret = unserialize($cachelib->getCached("groupperms_$group"));
		}

		return $ret;
	}

    function get_user_detailled_permissions($user) {

	$groups = $this->get_user_groups($user);

	// Use group cache if only one group
	//if ( count($groups) == 1 ) return $this->get_group_permissions($groups[0]);

	$ret = array();
	$query = 'select distinct up.* from `users_permissions` as up, `users_grouppermissions` as ug where ug.`groupName` in ('.implode(',',array_fill(0,count($groups),'?')).') and up.`permName`=ug.`permName`';
	$result = $this->query($query, $groups);

	while ( $res = $result->fetchRow() ) {
		$ret[] = $res;
	}
	return $ret;
    }

	function assign_permission_to_group($perm, $group) {
		$query = "delete from `users_grouppermissions` where `groupName` = ? and `permName` = ?";
		$result = $this->query($query, array($group, $perm));
		$query = "insert into `users_grouppermissions`(`groupName`, `permName`) values(?, ?)";
		$result = $this->query($query, array($group, $perm));

		global $cachelib;
		$cachelib->invalidate("allperms");
		$cachelib->empty_type_cache("fgals_perms");
		$cachelib->invalidate("groupperms_$group");

		global $menulib; include_once('lib/menubuilder/menulib.php');
		$menulib->empty_menu_cache();

		return true;
	}

	function get_user_permissions($user) {
		$groups = $this->get_user_groups($user);

		$ret = array();
		foreach ($groups as $group) {
			$perms = $this->get_group_permissions($group);

			foreach ($perms as $perm) {
				$ret[] = $perm;
			}
		}

		return $ret;
	}

	function user_has_permission($user, $perm) {

		// Get user_groups ?
		$groups = $this->get_user_groups($user);

		foreach ($groups as $group) {
			if ($this->group_has_permission($group, $perm) || $this->group_has_permission($group, 'tiki_p_admin')) {
				return true;
			}
		}

		return false;
	}

	function group_has_permission($group, $perm) {
		if ( empty($perm) || empty($group) ) return 0;

		$engroup = urlencode($group);
		if ( ! isset($this->groupperm_cache[$engroup]) ) {
			$this->groupperm_cache[$engroup] = array();
			$groupperms = $this->get_group_permissions($group);
			foreach ( $groupperms as $gp ) {
				$this->groupperm_cache[$engroup][$gp] = 1;
			}
		}

		return isset($this->groupperm_cache[$engroup][$perm]) ? 1 : 0;
	}

	function remove_permission_from_group($perm, $group) {
		$query = "delete from `users_grouppermissions` where `permName` = ? and `groupName` = ?";
		$result = $this->query($query, array($perm, $group));

		global $cachelib;
		$cachelib->invalidate("allperms");
		$cachelib->empty_type_cache("fgals_perms");
		$cachelib->invalidate("groupperms_$group");

		global $menulib; include_once('lib/menubuilder/menulib.php');
		$menulib->empty_menu_cache();

		return true;
	}

    function get_group_info($group) {
	$query = "select * from `users_groups` where `groupName`=?";

	$result = $this->query($query, array($group));
	$res = $result->fetchRow();
	$perms = $this->get_group_permissions($group);
	$res["perms"] = $perms;
	return $res;
    }
	function get_groupId_info($groupId) {
	$query = "select * from `users_groups` where `id`=?";

	$result = $this->query($query, array($groupId));
	$res = $result->fetchRow();
	$perms = $this->get_group_permissions($res['groupName']);
	$res["perms"] = $perms;
	return $res;
    }

    function assign_user_to_group($user, $group) {
	global $cachelib; require_once("lib/cache/cachelib.php");
	global $tikilib;
	$cachelib->invalidate('user_details_'.$user);
	$tikilib->invalidate_usergroups_cache($user);

	$group_ret = false;
	$userid = $this->get_user_id($user);

	if ( $userid > 0 ){
	    $query = "insert into `users_usergroups`(`userId`,`groupName`, `created`) values(?,?,?)";
	    $result = $this->query($query, array($userid, $group, $tikilib->now), -1, -1, false);
	    $group_ret = true;
	}
	return $group_ret;
    }

    function assign_user_to_groups($user, $groups) {
	global $cachelib;
	require_once("lib/cache/cachelib.php");
	$cachelib->invalidate('user_details_'.$user);

	$userid = $this->get_user_id($user);

	$query = "delete from `users_usergroups` where `userId`=?";
	$this->query($query, array($userid));

	foreach ($groups as $grp) {
	    $this->assign_user_to_group($user, $grp);
	}

    }

    function hash_pass($pass, $salt = NULL) {
	global $prefs;

	$hashmethod=$prefs['feature_crypt_passwords'];

	if (!is_null($salt)) {
	    $len=strlen($salt);
	    if ($len == 13) { // CRYPT_STD_DES
		$hashmethod='crypt-des';
	    } else if ($len == 34) { // CRYPT_MD5
		$hashmethod='crypt-md5';
	    } else if ($len == 32) { // md5()
		$hashmethod='tikihash';
	    } else if ($len == 0) { // password is disabled in tiki -> external authentification
		$hashmethod='pass_disabled';
	    } else {
		die("Unknown password format");
	    }
	}

	switch($hashmethod) {

	case 'crypt':
	    return crypt($pass);

	case 'crypt-des':
	    if (CRYPT_STD_DES != 1) die("CRYPT_STD_DES not implemented on this system");
	    if (is_null($salt)) {
		$letters="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789./";
		$salt='';
		for ($i=0; $i<2; $i++) $salt.=$letters[rand(0, strlen($letters) - 1)];
	    }
	    return crypt($pass, $salt);

	case 'crypt-md5':
	    if (CRYPT_MD5 != 1) die("CRYPT_MD5 not implemented on this system");
	    if (is_null($salt)) {
		$letters="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789./";
		$salt='$1$';
		for ($i=0; $i<8; $i++) $salt.=$letters[rand(0, strlen($letters) - 1)];
		$salt.='$';
	    }
	    return crypt($pass, $salt);

	case 'pass_disabled': // md5(someting) is not empty string
	case 'tikihash':
	default:
	    return md5($pass);
	}
    }

	function confirm_user($user) {
		global $prefs,$cachelib;

		$query = "select `provpass`, `login` from `users_users` where `login`=?";
		$result = $this->query($query, array($user));
		$res = $result->fetchRow();
		$hash = $this->hash_pass($res['provpass']);
		$provpass = $res["provpass"];

		if ($prefs['feature_clear_passwords'] == 'n') {
			$provpass = '';
		}

		$query = "update `users_users` set `password`=? ,`hash`=? ,`provpass`=?, valid=?, `email_confirm`=?, `waiting`=?, `registrationDate`=? where `login`=?";
		$result = $this->query($query, array(
				$provpass,
				$hash,
				'',
				NULL,
				$this->now,
				NULL,
				$this->now,
				$user
				));
		$cachelib->invalidate('userslist');
	}

	function change_user_waiting($user, $who) {
		$query = 'update `users_users` set `waiting`=?, `currentLogin`=?, `lastLogin`=? where `login`=?';
		$this->query($query, array($who, NULL, NULL, $user));
	}

    function add_user($user, $pass, $email, $provpass = '', $pass_first_login = false, $valid = NULL, $openid_url = NULL) {
	global $tikilib, $cachelib, $prefs;

	if ($this->user_exists($user) || empty($user) || (!empty($prefs['username_pattern']) && !preg_match($prefs['username_pattern'], $user)) || strtolower($user) == 'anonymous' || strtolower($user) == 'registered') {
	    return false;
	}

	// Generate a unique hash; this is also done below in set_user_fields()
	$lastLogin = null;
	if (empty($openid_url))
	{
		$hash = $this->hash_pass($pass);
	}
	else
	{
		$hash = '';
		if (!isset($prefs['validateRegistration']) || $prefs['validateRegistration'] != 'y')  $lastLogin = time();
	}

	if ($valid == 'n') {
		$valid = $pass;
	}

	if ( $prefs['feature_clear_passwords'] == 'n' ) $pass = '';

	if ( $pass_first_login ) {
		$new_pass_confirm = 0;
	} else {
		$new_pass_confirm = $this->now;
	}
	$new_email_confirm = $this->now;
	$query = "insert into
	    `users_users`(`login`, `password`, `email`, `provpass`,
		    `registrationDate`, `hash`, `pass_confirm`, `email_confirm`, `created`, `valid`, `openid_url`, `lastLogin`, `waiting`)
	    values(?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$result = $this->query($query, array(
		    $user,
		    $pass,
		    $email,
		    $provpass,
		    (int) $this->now,
		    $hash,
		    (int) $new_pass_confirm,
			(int) $new_email_confirm,
		    (int) $this->now,
			$valid,
			$openid_url,
			$lastLogin,
			(empty($_GLOBALS['user']) && $prefs['validateRegistration'] == 'y')? 'a': ((empty($_GLOBALS['user']) && $prefs['validateUsers'] == 'y')? 'u': NULL)
		    ));

	$this->assign_user_to_group($user, 'Registered');

	if( $prefs['eponymousGroups'] == 'y' )
	{
	    // Create a group just for this user, for permissions
	    // assignment.
	    $this->add_group($user, "Personal group for $user.",'',0,0,0,'');

	    $this->assign_user_to_group($user, $user);
	}

	$this->set_user_default_preferences($user);

	$cachelib->invalidate('userslist');
	return true;
    }

    function set_user_default_preferences($user) {
    	global $prefs;
	foreach( $prefs as $pref => $value ) {
		if ( ! ereg('^users_prefs_', $pref) ) continue;
		if ($pref == 'users_prefs_email_is_public') {
			$pref_name = 'email is public';
		} else {
			$pref_name = substr( $pref, 12 );
		}
		$this->set_user_preference($user, $pref_name, $value);
	}
    }

    function change_user_email($user, $email, $pass=null) {
    // Need to change the email-address for notifications, too
	global $notificationlib; include_once('lib/notifications/notificationlib.php');
    $oldMail = $this->get_user_email($user);
    $notificationlib->update_mail_address($user, $oldMail, $email);

	$query = "update `users_users` set `email`=? where " . $this->convertBinary(). " `login`=?";

	$result = $this->query($query, array(
		    $email,
		    $user
		    ));

	// that block stays here for a time (compatibility)
	// lfagundes - only if pass is provided, admin doesn't need it
	// is this still necessary?
	if (!empty($pass)) {
	    $hash = $this->hash_pass($pass);
	    $query = "update `users_users` set `hash`=?  where " . $this->convertBinary(). " `login`=?";
	    $result = $this->query($query, array(
						 $hash,
						 $user
						 ));
	}

	$query = "update `tiki_user_watches` set `email`=? where " . $this->convertBinary(). " `user`=?";
	$result = $this->query($query, array( $email, $user));

	$query = "update `tiki_live_support_requests` set `email`=? where " . $this->convertBinary(). " `user`=?";
	$result = $this->query($query, array( $email, $user));
				return true;
    }

    function get_user_password($user) {
	$query = "select `password`,`provpass`  from `users_users` where " . $this->convertBinary(). " `login`=?";

	$result = $this->query($query, array($user));
	$res = $result->fetchRow();
	if (empty($res['provpass']))
		return $res['password'];
	else
		return $res['provpass'];
    }

    function get_user_email($user) {
    	global $prefs;
        return ( $prefs['login_is_email'] == 'y' && $user != 'admin' ) ? $user : $this->getOne("select `email` from `users_users` where " . $this->convertBinary(). " `login`=?", array($user));
    }

    /**
     *  Returns the contact users' email if set and permitted by Admin->Features settings
     */
    function get_admin_email() {
        global $user, $prefs, $tikilib;
        if (( !isset($user) && isset($prefs['contact_anon']) && $prefs['contact_anon'] == 'y' ) ||
                ( isset($user) && $user != '' && isset($prefs['feature_contact']) && $prefs['feature_contact'] == 'y' )) {
            return isset($prefs['sender_email']) ? $prefs['sender_email'] : $this->get_user_email($prefs['contact_user']);
        }
    }

    function get_user_hash($user) {
	$query = "select `hash`  from `users_users` where " .  $this->convertBinary(). " `login` = ?";
	$pass = $this->getOne($query, array($user));
	return $pass;
    }

    function get_user_by_hash($hash) {
	$query = "select `login` from `users_users` where `hash`=?";
	$pass = $this->getOne($query, array($hash));
	return $pass;
    }

	function create_user_cookie($user,$hash=false) {
		global $prefs;
		if (!$hash) {
			$hash = $this->get_cookie_check() . ".". ($this->now + $prefs['remembertime']);
		}
		$this->delete_user_cookie($user);
		$this->set_user_preference($user,'cookie',$hash);
		return $hash;
	}

	function delete_user_cookie($user) {
		$query = 'delete from `tiki_user_preferences` where `prefName`=? and `user`=?';
		$this->query($query, array('cookie',$user));
	}

	function get_cookie_check() {
		global $prefs;
		if ($prefs['remembermethod'] == 'simple') {
			// this only makes sense in setting the cookie - it will always be different if checked
			return md5(session_id() . uniqid(mt_rand(), true));
		} else {
			return md5($this->get_ip_address().$_SERVER['HTTP_USER_AGENT']);
		}
	}

	function get_user_by_cookie($hash,$bypasscheck=false) {
		global $prefs;
		list($check,$expire,$userCookie) = explode('.',$hash, 3);
		if ($check == $this->get_cookie_check() or $bypasscheck or $prefs['remembermethod'] == 'simple') {
			$query = 'select `user` from `tiki_user_preferences` where `prefName`=? and `value` like ? and `user`=?';
			$user = $this->getOne($query, array('cookie',"$check.%",$userCookie));
			// $fp=fopen('temp/interlogtest','a+');fputs($fp,"main gubc -- $check.$expire.$userCookie -- $user --\n");fclose($fp);
			if ($user) {
				if ($expire < $this->now) {
					$query = 'delete from `tiki_user_preferences` where `prefName`=? and `value`=?';
					$user = $this->query($query, array('cookie',$hash));
					return false;
				} else {
					return $user;
				}
			}
		}
		return false;
	}

    function get_user_by_email($email) {
    $query = "select `login` from `users_users` where `email`=?";
    $pass = $this->getOne($query, array($email));
    return $pass;
    }

    function is_due($user) {
    	global $prefs, $phpcas_enabled;
    	// if CAS auth is enabled, don't check if password is due since CAS does not use local Tiki passwords
    	if (($phpcas_enabled == 'y' and $prefs['auth_method'] == 'cas') || $prefs['change_password'] != 'y') {
    		return false;
    	}
		$confirm = $this->getOne("select `pass_confirm`  from `users_users` where " . $this->convertBinary(). " `login`=?", array($user));
		if (!$confirm) {
			return true;
		}
		if ($prefs['pass_due'] < 0) {
			return false;
		}
		if ($confirm + (60 * 60 * 24 * $prefs['pass_due']) < $this->now) {
		    return true;
		}
		return false;
    }

    function is_email_due($user) {
    	global $prefs;
		if ($prefs['email_due'] < 0) {
			return false;
		}
		$confirm = $this->getOne("select `email_confirm`  from `users_users` where " . $this->convertBinary(). " `login`=?", array($user));
		if ($confirm + (60 * 60 * 24 * $prefs['email_due']) < $this->now) {
		    return true;
		}
		return false;
    }

	function unsuccessful_logins($user) {
		return $this->getOne('select `unsuccessful_logins` from `users_users` where ' . $this->convertBinary(). ' `login`=?', array($user));
	}

    function renew_user_password($user) {
		$pass = $this->genPass();
		// Note that tiki-generated passwords are due inmediatley
		// Note: ^ not anymore. old pw is usable until the URL in the password reminder mail is clicked
		$query = "update `users_users` set `provpass` = ? where `login`=?";
		$result = $this->query($query, array($pass, $user));
		return $pass;
    }

    function activate_password($user, $actpass) {
		// move provpass to password and generate new hash, afterwards clean provpass
		$query = "select `provpass`  from `users_users` where `login`=?";
		$pass = $this->getOne($query, array($user));
		if (($pass <> '') && ($actpass == md5($pass))) {
			$hash = $this->hash_pass($pass);
			$query = "update `users_users` set `password`=?, `hash`=?, `pass_confirm`=? where `login`=?";
			$result = $this->query($query, array("", $hash, (int)$this->now, $user));
			return $pass;
		}
		return false;
    }

	/* Tests the password against policy enforcement (Admin->Login), namelly:
	* $min_pass_length
	* $pass_chr_num
	* $pass_ud_chr_num
	*
	* returns an empty string if password is ok, or the error string otherwise
	*/
	function check_password_policy($pass) {
		global $prefs, $user;

		// Validate password here
		if ( ( $prefs['auth_method'] != 'cas' || $user == 'admin' ) && strlen($pass) < $prefs['min_pass_length'] ) {
			return tra("Password should be at least").' '.$prefs['min_pass_length'].' '.tra("characters long");
		}

		// Check this code
		if ($prefs['pass_chr_num'] == 'y') {
			if (!preg_match_all("/[0-9]+/", $pass, $foo) || !preg_match_all("/[A-Za-z]+/", $pass, $foo)) {
				return tra("Password must contain both letters and numbers");
			}
		}

		return "";
	}




    function change_user_password($user, $pass) {
	global $prefs;

	$hash = $this->hash_pass($pass);
	$new_pass_confirm = $this->now;

	if ($prefs['feature_clear_passwords'] == 'n') {
	    $pass = '';
	}

	$query = "update `users_users` set `hash`=? ,`password`=? ,`pass_confirm`=?, `provpass`=? where " . $this->convertBinary(). " `login`=?";
	$result = $this->query($query, array(
		    $hash,
		    $pass,
		    $new_pass_confirm,
		    "",
		    $user
		    ));
	// invalidate the cache so that after a fresh install, the admin (who has no user details at the install) can log in
	global $cachelib; require_once('lib/cache/cachelib.php');
	$cachelib->invalidate('user_details_'.$user);
	return true;
	}

	function add_group($group, $desc='', $home='', $utracker=0, $gtracker=0, $rufields='', $userChoice='', $defcat=0, $theme='', $ufield=0, $gfield=0,$isexternal='n', $expireAfter=0) {
		global $tikilib;
		$group = trim($group);
		if ( $this->group_exists($group) ) return false;

		$query = "insert into `users_groups` (`groupName`, `groupDesc`, `groupHome`,`groupDefCat`,`groupTheme`,`usersTrackerId`,`groupTrackerId`, `registrationUsersFieldIds`, `userChoice`, `usersFieldId`, `groupFieldId`,`isExternal`, `expireAfter`) values(?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$this->query($query, array($group, $desc, $home, $defcat, $theme, (int)$utracker, (int)$gtracker, $rufields, $userChoice, (int)$ufield, (int)$gfield,$isexternal, $expireAfter) );

		global $cachelib; require_once('lib/cache/cachelib.php');
		$cachelib->invalidate('grouplist');
		$cachelib->invalidate('groupIdlist');

		$query = "select `id` from `users_groups` where `groupName`=?";
		return $this->getOne($query, array($group));
	}

	function change_group($olgroup,$group,$desc,$home,$utracker=0,$gtracker=0,$ufield=0,$gfield=0,$rufields='',$userChoice='',$defcat=0,$theme='',$isexternal='n', $expireAfter=0) {

		if ( $olgroup == 'Anonymous' || $olgroup == 'Registered' ) {
			// Changing group name of 'Anonymous' and 'Registered' is not allowed.
			if ( $group != $olgroup ) return false;
		}

		if ( ! $this->group_exists($olgroup) ) {
			return $this->add_group($group, $desc, $home, $utracker,$gtracker, $userChoice, $defcat, $theme, $isExternal, $expireAfter);
		}

		global $cachelib;

		$query = "update `users_groups` set `groupName`=?, `groupDesc`=?, `groupHome`=?, `groupDefCat`=?, `groupTheme`=?, `usersTrackerId`=?, `groupTrackerId`=?, `usersFieldId`=?, `groupFieldId`=? , `registrationUsersFieldIds`=?, `userChoice`=?, `isExternal`=?, `expireAfter`=? where `groupName`=?";
		$result = $this->query($query, array($group, $desc, $home, $defcat, $theme, (int)$utracker, (int)$gtracker, (int)$ufield, (int)$gfield, $rufields, $userChoice, $isexternal, $expireAfter, $olgroup));

		if ( $olgroup != $group ) {
			$query = array();
			$query[] = "update `users_usergroups` set `groupName`=? where `groupName`=?";
			$query[] = "update `users_grouppermissions` set `groupName`=? where `groupName`=?";
			$query[] = "update `users_objectpermissions` set `groupName`=? where `groupName`=?";
			$query[] = "update `tiki_group_inclusion` set `groupName`=? where `groupName`=?";
			$query[] = "update `tiki_group_inclusion` set `includeGroup`=? where `includeGroup`=?";
			$query[] = "update `tiki_newsletter_groups` set `groupName`=? where `groupName`=?";
			foreach ( $query as $q ) $this->query($q, array($group, $olgroup));

			// must unserialize before replacing the groups
			$query = "select `name`, `groups` from `tiki_modules` where `groups` like ?";
			$result = $this->query($query, array('%'.$olgroup.'%'));
			while ( $res = $result->fetchRow() ) {
				$aux = array();
				$aux["name"] = $res["name"];
				$aux["groups"] = unserialize($res["groups"]);
				$aux["groups"] = str_replace($olgroup, $group, $aux["groups"]);
				$aux["groups"] = serialize($aux["groups"]);
				$query = "update `tiki_modules` set `groups`=? where `name`=?";
				$this->query($query, array($aux["groups"], $aux["name"]));
			}

			$query = 'select * from `tiki_tracker_fields` where `visibleBy` like ?';
			$result = $this->query($query, array('%"'.$olgroup.'"%'));
			$query = 'update `tiki_tracker_fields` set `visibleBy`=? where `visibleBy`=?';
			while ( $res = $result->fetchRow() ) {
				$g = unserialize($res['visibleBy']);
				$g = str_replace($olgroup, $group, $g);
				$g = serialize($g);
				$this->query($query, array($g, $res['visibleBy']));
			}

			$query = 'select * from `tiki_tracker_fields` where `editableBy` like ?';
			$result = $this->query($query, array('%"'.$olgroup.'"%'));

			$query = 'update `tiki_tracker_fields` set `editableBy`=? where `editableBy`=?';
			while ( $res = $result->fetchRow() ) {
				$g = unserialize($res['editableBy']);
				$g = str_replace($olgroup, $group, $g);
				$g = serialize($g);
				$this->query($query, array($g, $res['editableBy']));
			}

			$cachelib->invalidate('grouplist');
			$cachelib->invalidate('group_theme_'.$group);
		}
		$cachelib->invalidate('group_theme_'.$olgroup);

		return true;
	}

    function remove_all_inclusions($group) {
	if (!$this->group_exists($group))
	    return false;

	$query = "delete from `tiki_group_inclusion` where `groupName` = ?";
	$result = $this->query($query, array($group));
	global $cachelib; require_once('lib/cache/cachelib.php');
	$cachelib->empty_type_cache('group_inclusion_'.$group);
	$this->groupinclude_cache = array();
	return true;
    }

    function set_user_fields($u) {
	global $prefs;

	$q = array();
	$bindvars = array();

	if (isset($u['password'])) {
	    if ($prefs['feature_clear_passwords'] == 's') {
		$q[] = "`password` = ?";
		$bindvars[] = strip_tags($u['password']);
	    }

	    // I don't think there are currently cases where login and email are undefined
	    //$hash = md5($u['login'] . $u['password'] . $u['email']);
	    $hash = $this->hash_pass($u['password']);
	    $q[] = "`hash` = ?";
	    $bindvars[] = $hash;
	}

	if (isset($u['email'])) {
	    $q[] = "`email` = ?";
	    $bindvars[] = strip_tags($u['email']);
	}

    if (isset($u['openid_url'])) {
	    if (isset($_SESSION['openid_url'])) {
		$q[] = "`openid_url` = ?";
		$bindvars[] = $u['openid_url'];
	    }
    }

	if (sizeof($q) > 0) {
	    $query = "update `users_users` set " . implode(",", $q). " where " .
		$this->convertBinary(). " `login` = ?";
	    $bindvars[] = $u['login'];
	    $result = $this->query($query, $bindvars);
	}

	$aUserPrefs = array('realName','homePage','country');
	foreach ($aUserPrefs as $pref){
		if (isset($u[$pref])) {
		    $bindvars = array();

		    $bindvars[] = strip_tags($u[$pref]);
		    $bindvars[] = $u['login'];
		    $bindvars[] = $pref;

		    if ($this->getOne("select `user` from `tiki_user_preferences` where `user`=? and `prefName`=?",array($u['login'],$pref))) {
			$query = "UPDATE `tiki_user_preferences` set `value`=? where `user`=? and `prefName`=?";
		    } else {
			$query = "INSERT INTO `tiki_user_preferences` (`value`,`user`,`prefName`) VALUES (?,?,?)";
		    }
		    $this->query($query, $bindvars);
		}
	}

	return $result;
    }

    // damian aka damosoft
    function count_users($group) {
        static $rv = array();

        if (!isset($rv[$group])) {
            if ($group == '') {
                $query = "select count(login) from `users_users`";
                $result = $this->getOne($query);
            } else {
                $query = "select count(userId) from `users_usergroups` where `groupName` = ?";
                $result = $this->getOne($query, array($group));
            }
            $rv[$group] = $result;
        }

        return $rv[$group];
    }

    function related_users($user, $max=10, $type='wiki') {
	if(!isset($user) || empty($user)) {
	    return array();
	}

	// This query was written using a double join for PHP. If you're trying to eke
	// additional performance and are running MySQL 4.X, you might want to try a
	// subselect and compare perf numbers.

	if ($type == 'wiki') {
	    $query = "SELECT u1.`login`, COUNT( p1.`pageName` ) AS quantity
			FROM `tiki_history` p1
			INNER JOIN `users_users` u1 ON ( u1.`login` = p1.`user` )
			INNER JOIN `tiki_history` p2 ON ( p1.`pageName` = p2.`pageName` )
			INNER JOIN `users_users` u2 ON ( u2.`login` = p2.`user` )
			WHERE u2.`login` = ? AND u1.`login` <> ?
			GROUP BY p1.`pageName`
			ORDER BY quantity DESC
			";
	} else {
	    return array();
	}

	$bindvals = array($user, $user);

	$result = $this->query($query, $bindvals, $max, 0);

	$ret = array();
	while ($row = $result->fetchRow()) {
	    $ret[] = $row;
	}

	return $ret;
    }

    // Friends methods
    function request_friendship($user, $friend)
    {
		if (empty($user) || empty($friend) || $user == $friend) {
		    return false;
		}

		$query = "delete from `tiki_friendship_requests` where `userFrom`=? and `userTo`=?";
		$this->query($query, array($user, $friend));

		$query = "insert into `tiki_friendship_requests` (`userFrom`, `userTo`) values (?, ?)";
		$result = $this->query($query, array($user, $friend));

		if (!$result)
		    return false;

		return true;
    }

    function accept_friendship($user, $friend)
    {
		$exists = $this->getOne("select count(*) from `tiki_friendship_requests` where `userTo`=? and `userFrom`=?",
					array($user, $friend));

		if (!$exists)
		    return false;

		if (empty($user) || empty($friend)) {
		    return false;
		}

		$query = "delete from `tiki_friends` where `user`=? and `friend`=?";
		$this->query($query, array($user, $friend));
		$this->query($query, array($friend, $user));

		$query = "insert into `tiki_friends` (`user`, `friend`) values (?,?)";
		$this->query($query, array($user, $friend));
		$this->query($query, array($friend, $user));

		$query = "delete from `tiki_friendship_requests` where `userFrom`=? and `userTo`=?";
		$this->query($query, array($user, $friend));
		$this->query($query, array($friend, $user));

		$this->score_event($user,'friend_new',$friend);
		$this->score_event($friend,'friend_new',$user);

		global $cachelib;
		$cachelib->invalidate('friends_count_'.$user);
		$cachelib->invalidate('friends_count_'.$friend);

		return true;
    }

    function refuse_friendship($user, $friend)
    {
		$exists = $this->getOne("select count(*) from `tiki_friendship_requests` where `userTo`=? and `userFrom`=?",
					array($user, $friend));

		if (!$exists)
		    return false;

		$query = "delete from `tiki_friendship_requests` where `userFrom`=? and `userTo`=?";
		$this->query($query, array($user, $friend));
		$this->query($query, array($friend, $user));

		return true;
    }

    function list_pending_friendship_requests($user)
    {

		$query = "select * from `tiki_friendship_requests` where `userTo`=? order by tstamp";
		$result = $this->query($query, array($user));

		$requests = array();
		while ($res = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
		    $requests[$res['userFrom']] = $res['tstamp'];
		}

		return $requests;
    }

    function list_waiting_friendship_requests($user)
    {
		$query = "select * from `tiki_friendship_requests` where `userFrom`=? order by tstamp";
		$result = $this->query($query, array($user));

		$requests = array();
		while ($res = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
		    $requests[$res['userTo']] = $res['tstamp'];
		}

		return $requests;
    }



    function break_friendship($user, $friend)
    {

	$query = "delete from `tiki_friends` where `user`=? and `friend`=?";
	$this->query($query, array($user, $friend));
	$this->query($query, array($friend, $user));

	global $cachelib;
	$cachelib->invalidate('friends_count_'.$user);
	$cachelib->invalidate('friends_count_'.$friend);
    }


		// Case-sensitivity regression only. used for patching
	function get_object_case_permissions($objectId, $objectType) {
		$query = "select `groupName`, `permName` from `users_objectpermissions` where `objectId` = ? and `objectType` = ?";
		$result = $this->query($query, array(md5($objectType . $objectId),$objectType));
		$ret = array();
		while ($res = $result->fetchRow()) {
			$ret[] = $res;
		}
		return $ret;
	}

	function object_has_one_case_permission($objectId, $objectType) {
		$query = "select count(*) from `users_objectpermissions` where `objectId`=? and `objectType`=?";
		$result = $this->getOne($query, array( md5($objectType . $objectId), $objectType));
		return $result;
	}

	function remove_object_case_permission($groupName, $objectId, $objectType, $permName) {
		$query = "delete from `users_objectpermissions` where `groupName` = ? and `objectId` = ?  and `objectType` = ? and `permName` = ?";
		$result = $this->query($query, array($groupName, md5($objectType . $objectId), $objectType, $permName));
		return true;
	}

	function get_permissions_types() {
		$query = "select `type` from `users_permissions` group by `type`";
		$result = $this->query($query,array());
		$ret = array();
		while ($res = $result->fetchRow()) { $ret[] = $res['type']; }
		return $ret;
	}
	function send_validation_email($name, $apass, $email, $again='', $second='', $chosenGroup='', $mailTemplate = '', $pass = '') {
		global $tikilib, $prefs, $smarty;
		$foo = parse_url($_SERVER['REQUEST_URI']);
		$foo1 = str_replace(array('tiki-send_mail', 'tiki-register', 'tiki-remind_password', 'tiki-adminusers'), 'tiki-login_validate', $foo['path']);
		$machine = $tikilib->httpPrefix() . $foo1;
		$smarty->assign('mail_machine',$machine);
		$smarty->assign('mail_site', $_SERVER['SERVER_NAME']);
		$smarty->assign('mail_user', $name);
		$smarty->assign('mail_apass', $apass);
		$smarty->assign('mail_email', $email);
		$smarty->assign('mail_again', $again);
		include_once('lib/webmail/tikimaillib.php');
		if ($second == 'y') {
			$mail_data = $smarty->fetch('mail/confirm_user_email_after_approval.tpl');
			$mail = new TikiMail();
			$mail->setText($mail_data);
			$mail_data = sprintf($smarty->fetch('mail/confirm_user_email_after_approval_subject.tpl'), $_SERVER['SERVER_NAME']);
			$mail->setSubject($mail_data);
			if (!$mail->send(array($email))) {
				$smarty->assign('msg', tra("The registration mail can't be sent. Contact the administrator"));
				return false;
			}
		} elseif ($prefs['validateRegistration'] == 'y') {
			if (!empty($chosenGroup)) {
				$smarty->assign_by_ref('chosenGroup', $chosenGroup);
				if ($prefs['userTracker'] == 'y') {
					global $trklib; include_once('lib/trackers/trackerlib.php');
					$re = $this->get_group_info(isset($chosenGroup)? $chosenGroup: 'Registered');
					$fields = $trklib->list_tracker_fields($re['usersTrackerId'], 0, -1, 'position_asc', '', true, array('fieldId'=>explode(':',$re['registrationUsersFieldIds'])));
					$listfields = array();
					foreach ($fields['data'] as $field) {
						$listfields[$field['fieldId']] = $field;
					}
					$items = $trklib->list_items($re['usersTrackerId'], 0, 1, '',  $listfields, $trklib->get_field_id_from_type($re['usersTrackerId'], 'u', '1%'), '', '', '', $name);
					if (isset($items['data'][0]))
						$smarty->assign_by_ref('item', $items['data'][0]);
				}
			}
			$mail_data = $smarty->fetch('mail/moderate_validation_mail.tpl');
			$mail_subject = $smarty->fetch('mail/moderate_validation_mail_subject.tpl');
			$emails = !empty($prefs['validator_emails'])?split(',', $prefs['validator_emails']): (!empty($prefs['sender_email'])? array($prefs['sender_email']): '');
			if (empty($emails)) {
				if ($prefs['feature_messages'] != 'y') {
					$smarty->assign('msg', tra("The registration mail can't be sent because there is no server email address set, and this feature is disabled").": feature_messages");
					return false;
				}
				include_once('lib/messu/messulib.php');
				$messulib->post_message($prefs['contact_user'], $prefs['contact_user'], $prefs['contact_user'], '', $mail_subject, $mail_data, 5);
				$smarty->assign('msg', $smarty->fetch('mail/user_validation_waiting_msg.tpl'));
			} else {
				$mail = new TikiMail();
				$mail->setText($mail_data);
				$mail->setSubject($mail_subject);
				if (!$mail->send($emails)) {
					$smarty->assign('msg', tra("The registration mail can't be sent. Contact the administrator"));
					return false;
				} elseif (empty($again)) {
					$smarty->assign('msg', $smarty->fetch('mail/user_validation_waiting_msg.tpl'));
				} else {
					$smarty->assign('msg', tra('The administrator has not yet validated your account. Please wait.'));
				}
			}
		} elseif ($prefs['validateUsers'] == 'y') {
			if ( $mailTemplate == '' ) $mailTemplate = 'user_validation_mail';
			$smarty->assign('mail_pass', $pass);
			$mail_data = $smarty->fetch("mail/$mailTemplate.tpl");
			$mail = new TikiMail();
			$mail->setText($mail_data);
			$mail_data = $smarty->fetch("mail/{$mailTemplate}_subject.tpl");
			$mail->setSubject($mail_data);
			if (!$mail->send(array($email))) {
				$smarty->assign('msg', tra("The registration mail can't be sent. Contact the administrator"));
				return false;
			} elseif (empty($again)) {
				$smarty->assign('msg', $smarty->fetch('mail/user_validation_msg.tpl'));
			} else {
				$smarty->assign('msg', tra('You must validate your account first. An email has been sent to you'));
			}
		}
		return true;
	}

	function set_registrationChoice($groups, $flag) {
		$bindvars = array();
		$bindvars[] = $flag;
		if (is_array($groups)) {
			$mid = implode(',',array_fill(0,count($groups),'?'));
			$bindvars = array_merge($bindvars, $groups);
		} else {
			$bindvars[] = $groups;
			$mid = 'like ?';
		}
		$query = "update `users_groups` set `registrationChoice`= ? where `groupName` in ($mid)";
		$result = $this->query($query, $bindvars);
	}

	function get_registrationChoice($group) {
		$query = "select `registrationChoice` from `users_groups` where `groupName` = ?";
		return ($this->getOne($query, array($group)));
	}
	function reset_email_due($user) {
		$query = 'update `users_users` set `email_confirm`=?, `waiting`=? where `login`=?';
		$this->query($query, array(0, 'u', $user));
	}

	function confirm_email($user, $pass) {
		global $prefs, $tikilib;
		$query = 'select `provpass`, `login`, `unsuccessful_logins` from `users_users` where `login`=?';
		$result = $this->query($query, array($user));
		if (!($res = $result->fetchRow())) {
			return false;
		}
		if (md5($res['provpass']) == $pass){
			$this->confirm_user($user);
			$query = 'update `users_users` set `provpass`=?, `email_confirm`=?, `unsuccessful_logins`=?, `registrationDate`=? where `login`=? and `provpass`=?';
			$this->query($query, array('', $tikilib->now, 0, $this->now, $user, $res['provpass']));
			if (!empty($GLOBALS['user'])) {
				global $logslib; include_once('lib/logs/logslib.php');
				$logslib->add_log('login', 'confirm email '.$user);
			}
			return true;
		}
		return false;
	}

	function set_unsuccessful_logins($user, $nb) {
 		$query = 'update `users_users` set `unsuccessful_logins`=? where `login` = ?';
		$this->query($query, array($nb, $user));
	}

	function send_confirm_email($user,$tpl='confirm_user_email') {
		global $smarty, $prefs, $tikilib;
		include_once ('lib/webmail/tikimaillib.php');
		$languageEmail = $this->get_user_preference($_REQUEST["username"], "language", $prefs['site_language']);
		$apass = $this->renew_user_password($user);
		$apass = md5($apass);
		$smarty->assign('mail_apass',$apass);
		$smarty->assign('mail_pass', $_REQUEST['pass']);
		$smarty->assign('mail_ip', $tikilib->get_ip_address()); 
		$smarty->assign('user', $user);
		$mail = new TikiMail();
		$mail_data = $smarty->fetchLang($languageEmail, "mail/$tpl"."_subject.tpl");
		$mail_data = sprintf($mail_data, $_SERVER['SERVER_NAME']);
		$mail->setSubject($mail_data);
		$foo = parse_url($_SERVER["REQUEST_URI"]);
		$mail_machine = $tikilib->httpPrefix().str_replace('tiki-login.php', 'tiki-confirm_user_email.php', $foo['path']);
		$smarty->assign('mail_machine', $mail_machine);
		$mail_data = $smarty->fetchLang($languageEmail, "mail/$tpl.tpl");
		$mail->setText($mail_data);
		if (!($email = $this->get_user_email($user)) || !$mail->send(array($email))) {
			$smarty->assign('msg', tra("The user email confirmation can't be sent. Contact the administrator"));
			return false;
		} else {
			$smarty->assign('msg', 'It is time to confirm your email. You will receive an mail with the instruction to follow');
			return true;
		}
	}

	function assign_openid( $username, $openid ) {
		// This won't update the database unless the openid is different
		$this->query("UPDATE `users_users` SET openid_url = ? WHERE login = ? AND ( openid_url <> ? OR openid_url IS NULL )", array( $openid, $username, $openid ));
	}

	function intervalidate($remote,$user,$pass,$get_info = false) {
		global $prefs;
		include_once('XML/RPC.php');
		$hashkey = $this->get_cookie_check() . ".". ($this->now + $prefs['remembertime']);
		$remote['path'] = preg_replace("/^\/?/","/",$remote['path']);
		$client = new XML_RPC_Client($remote['path'], $remote['host'], $remote['port']);
		$client->setDebug(0);
		$msg = new XML_RPC_Message(
				   'intertiki.validate',
				   array(
					 new XML_RPC_Value($prefs['tiki_key'], 'string'),
					 new XML_RPC_Value($user, 'string'),
					 new XML_RPC_Value($pass, 'string'),
					 new XML_RPC_Value($get_info, 'boolean'),
					 new XML_RPC_Value($hashkey, 'string')
					 ));
		$result = $client->send($msg);
		return $result;
    }
	/* send request + interpret email/login */
	function interGetUserInfo($remote, $user, $email) {
		global $prefs;
		include_once('XML/RPC.php');
		$remote['path'] = preg_replace("/^\/?/","/",$remote['path']);
		$client = new XML_RPC_Client($remote['path'], $remote['host'], $remote['port']);
		$client->setDebug(0);
		$params = array();
		$params[] = new XML_RPC_Value($prefs['tiki_key'], 'string');
		$params[] = new XML_RPC_Value($user, 'string');
		$params[] = new XML_RPC_Value($email, 'string');
		$msg = new XML_RPC_Message('intertiki.getUserInfo', $params);
		$rpcauth = $client->send($msg);
		if (!$rpcauth || $rpcauth->faultCode()) {
			return false;
		}
		$response_value = $rpcauth->value();
		for (;;) {
			list($key, $value) = $response_value->structeach();
			if ($key == '') {
				break;
			} elseif ($key == 'login') {
				$u['login'] = $value->scalarval();
			} elseif ($key == 'email') {
				$u['email'] = $value->scalarval();
			}
		}
		return $u;
	}
	/* send via XML_RPC user info to the main */
	function interSendUserInfo($remote, $user) {
		global $prefs, $userlib;
		include_once('XML/RPC.php');
		$remote['path'] = preg_replace("/^\/?/","/",$remote['path']);
		$client = new XML_RPC_Client($remote['path'], $remote['host'], $remote['port']);
		$client->setDebug(0);
		$params = array();
		$params[] = new XML_RPC_Value($prefs['tiki_key'], 'string');
		$params[] = new XML_RPC_Value($user, 'string');
		$user_details = $userlib->get_user_details($user);
		$user_info = $userlib->get_user_info($user);
		$ret['avatarData'] = new XML_RPC_Value($user_info['avatarData'], 'base64');
		$ret['user_details'] = new XML_RPC_Value(serialize($user_details), 'string');
		$params[] = new XML_RPC_Value($ret, 'struct');
		$msg = new XML_RPC_Message('intertiki.setUserInfo', $params);
		$result = $client->send($msg);
		return $result;
	}
	/* interpret the XML_RPC answer about user info */
	function interSetUserInfo($user, $response_value) {
		global $userlib, $tikilib;
		if ($response_value->kindOf() == 'struct') {
			for (;;) {
				list($key, $value) = $response_value->structeach();
				if ($key == '') {
					break;
				} elseif ($key == 'user_details') {
					$user_details = unserialize($value->scalarval());
				} elseif ($key == 'avatarData') {
					$avatarData = $value->scalarval();
				}
			}
		} else {
			$user_details = unserialize($response_value->scalarval());
		}
		$userlib->set_user_fields($user_details['info']);
		$tikilib->set_user_preferences($user, $user_details['preferences']);
		if (isset($avatarData)) {
			global $userprefslib; include_once('lib/userprefs/userprefslib.php');
			$userprefslib->set_user_avatar($user, 'u', '', $user_details['avatarName'], $user_details['avatarSize'], $user_details['avatarFileType'], $avatarData);
		}
	}

	function get_remote_user_by_cookie($hash) {
		global $prefs;
		include_once('XML/RPC.php');
		$prefs['interlist'] = unserialize($prefs['interlist']);
		$remote = $prefs['interlist'][$prefs['feature_intertiki_mymaster']];
		// $fp=fopen('temp/interlogtest','a+');fputs($fp,"slave     -- ".$hash." --\n");fclose($fp);
		$client = new XML_RPC_Client($remote['path'], $remote['host'], $remote['port']);
		$client->setDebug(0);
		$msg = new XML_RPC_Message(
		       'intertiki.cookiecheck',
					 array(
					 new XML_RPC_Value($prefs['tiki_key'], 'string'),
					 new XML_RPC_Value($hash, 'string')
					 ));
		$result = $client->send($msg);
		return $result;
	}
	function update_expired_groups() {
		global $tikilib;
		$query = 'SELECT uu.* FROM `users_usergroups` uu, `users_groups` ug WHERE uu.`groupName`= ug.`groupName` AND ug.`expireAfter` > 0 AND uu.`created` IS NOT NULL AND uu.`created` + ug.`expireAfter`*24*60*60 < ?';
		$result = $this->query($query, array($tikilib->now));
		$query = 'DELETE FROM `users_usergroups` WHERE `groupName`=? AND `userId`=?';
		while ($res = $result->fetchrow()) {
			$this->query($query, array($res['groupName'], $res['userId']));
		}
	}
	function get_users_created_group($group, $user=null) {
		if (!empty($user)) {
			$query = 'SELECT `users_usergroups`.`created` FROM `users_usergroups` LEFT JOIN `users_users` on (`users_users`.`userId`=`users_usergroups`.`userId`) WHERE `groupName`=? AND `user`=?';
			$bindvars = array($group, $user);
		} else {
			$query = 'SELECT `login`, `users_usergroups`.`created` FROM `users_usergroups` LEFT JOIN `users_users` on (`users_users`.`userId`=`users_usergroups`.`userId`) WHERE `groupName`=?';
			$bindvars = array($group);
		}
		$result = $this->query($query, $bindvars);
		$ret = array();
		while($res = $result->fetchRow()) {
			$ret[$res['login']]= $res['created'];
		}
		return $ret;
	}
	function nb_users_in_group($group=null) {
		if (!empty($group)) {
			$query = 'SELECT count(*) FROM `users_usergroups` WHERE `groupName`=?';
			return $this->getOne($query, array($group));
		} else {
			$query = 'SELECT count(*) FROM `users_users`';
			return $this->getOne($query, array());
		}
	}

}

/* For the emacs weenies in the crowd.
Local Variables:
   c-basic-offset: 4
End:
*/

