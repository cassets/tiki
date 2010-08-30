<?php
// (c) Copyright 2002-2010 by authors of the Tiki Wiki/CMS/Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

require_once 'lib/core/DeclFilter/Rule.php';

abstract class DeclFilter_UnsetRule implements DeclFilter_Rule
{
	function apply( array &$data, $key )
	{
		unset($data[$key]);
	}
}
