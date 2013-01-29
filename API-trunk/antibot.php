<?php
/**
 * provides CAPTCHA security in Tiki.
 *
 * @package   Tiki
 * @copyright (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project. All Rights Reserved. See copyright.txt for details and a complete list of authors.
* @license   LGPL. See license.txt for details.
 */
// $Id$

/** @package Tiki */
require_once ('tiki-setup.php');
if (!$prefs['feature_antibot'] == 'y') {
	die;
}

/** @package Tiki */
require_once('lib/captcha/captchalib.php');

$captchalib->generate();
$captcha = array('captchaId' => $captchalib->getId(), 'captchaImgPath' => $captchalib->getPath());

echo json_encode($captcha);