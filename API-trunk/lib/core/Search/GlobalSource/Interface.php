<?php
// (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

interface Search_GlobalSource_Interface
{
	function getData($objectType, $objectId, Search_Type_Factory_Interface $typeFactory, array $data = array());

	function getProvidedFields();
	
	function getGlobalFields();
}

