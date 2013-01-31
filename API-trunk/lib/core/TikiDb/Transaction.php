<?php
/**
 * provides TikiDb functions
 *
 * @package   Tiki
 * @subpackage Core\TikiDb
 * @copyright (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project. All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * @license   LGPL. See license.txt for more details
 */
// $Id$

/**
 * The class does not actually handle transactions at this time, this requires InnoDB, which
 * is experimental as of writing this, but the transaction concept is still useful to limit
 * the amount of unified index commits on incremental update.
 * @package Tiki\Core\TikiDb
 */
class TikiDb_Transaction
{
	private $token;

	function __construct()
	{
		$this->token = TikiLib::lib('unifiedsearch')->startBatch();
	}

	function commit()
	{
		TikiLib::lib('unifiedsearch')->endBatch($this->token);
	}
}
