<?php
/**
 * This redirects to the site's root to prevent directory browsing.
 *  
 * @ignore 
 * @package    Tiki
 * @subpackage doc
 * @copyright  (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project
 * @license Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
 */
// $Id$

header("location: ../tiki-index.php");
die;