<?php
/**
 * This redirects to the site's root to prevent directory browsing. 
 *
 * @package TikiWiki
 * @subpackage lib
 * @ignore
 * @copyright (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project. All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * @licence Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
 */
// $Id: index.php 39469 2012-01-12 21:13:48Z changi67 $

header("location: ../index.php");
die;