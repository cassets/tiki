<?php
/**
 * About Tiki.
 * 
 * This page is used as doxygen output mainpage, content is pasted in from README.
 *
 * @package Tiki
 * @copyright (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project. All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * @license   LGPL. See license.txt for details.
 */
// $Id$

/**
\mainpage

Tiki! The wiki with a lot of features!
Version 10.0


DOCUMENTATION

 * The documentation for 10.0 version is ever evolving at http://doc.tiki.org.
You're encouraged to contribute.

 * It is highly recommended that you refer to the online documentation:
 * http://doc.tiki.org/Installation for a setup guide

 * Notes about this release are accessible from http://tiki.org/ReleaseNotes100
 * Tikiwiki has an active IRC channel, #tikiwiki on irc.freenode.net

INSTALLATION

 * There is a file INSTALL in this directory with notes on how to setup and
configure Tiki. Again, see http://doc.tiki.org/Installation for the latest install help.

UPGRADES

 * Read the online instructions if you want to upgrade your Tiki from a previous release http://doc.tiki.org/Upgrade

COPYRIGHT

Copyright (c) 2002-2013, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
All Rights Reserved. See copyright.txt for details and a complete list of authors.
Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

... Have fun!
*/

// I call index.php because tiki may not be setup when people attempt to call this.
header("location: index.php");
die;
