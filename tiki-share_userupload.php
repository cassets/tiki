<?php
// (c) Copyright 2002-2010 by authors of the Tiki Wiki/CMS/Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

$section = 'mytiki';

require_once ('tiki-setup.php');
if ( $prefs['feature_use_fgal_for_user_files'] == 'y' && $user != '' ) {
	global $filegallib; require_once('lib/filegals/filegallib.php');
	$idGallery = $filegallib->get_user_file_gallery();

	// redirect user to the 'share this' feature with the upload file url, using the user gallery
	header('location: tiki-share.php?url=' . urlencode($tikiroot.'tiki-upload_file.php?galleryId='.$idGallery ) );
}
