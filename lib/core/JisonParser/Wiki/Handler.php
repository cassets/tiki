<?php
// (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

/**
 * Wiki Handler for the JisonParser_Wiki parser.
 *
 * @category    JisonParser_Wiki_Handler
 * @author      Robert Plummer <robert@tiki.org>
 * @version     CVS: $Id$
 */

class JisonParser_Wiki_Handler extends JisonParser_Wiki
{
	/* parser tracking */
	private $parsing = false;
	private static $spareParsers = array();
	public $parseDepth = 0;

	/* the root parser, where many variables need to be tracked from, maintained on any hierarchy of children parsers */
	public $Parser;

	/* parser debug */
	public $parserDebug = false;
	public $lexerDebug = false;

	/* plugin tracking */
	public $pluginStack = array();
	public $pluginStackCount = 0;
	public $pluginEntries = array();
	public $plugins = array();
	public static $pluginIndexes = array();
	public $pluginNegotiator;

	/* track syntax that is broken */
	public $repairingStack = array();

	/* np tracking */
	public $npStack = false; //There can only be 1 active np stack

	/* pp tracking */
	public $ppStack = false; //There can only be 1 active np stack

	/* link tracking*/
	public $linkStack = false; //There can only be 1 active link stack

	/* used in block level items, should be set to true if the next line needs skipped of a <br />
	The next break sets it back to false; */
	public $skipBr = false;
	public $tableStack = array();

	/* header tracking */
	public $header;
	public $headerStack = false;

	/* list tracking and parser */
	public $list;

	/* autoLink parser */
	public $autoLink;

	/* wiki link parser */
	public $link;

	/*hotWords parser */
	public $hotWords;

	/* smiley parser */
	public $smileys;

	/* dynamic var parser */
	public $dynamicVar;

	/* special character */
	public $specialCharacter;

	/* html tag tracking */
	public $nonBreakingTagDepth = 0;

	/* line tracking */
	public $isFirstBr = false;
	public $line = 0;

	public $user;
	public $prefs;
	public $page;

	public $isHtmlPurifying = false;
	private $pcreRecursionLimit;

	public $option = array();
	public $optionDefaults = array(
		'skipvalidation'=>  false,
		'is_html'=> false,
		'absolute_links'=> false,
		'language' => '',
		'noparseplugins' => false,
		'stripplugins' => false,
		'noheaderinc' => false,
		'page' => '',
		'print' => false,
		'parseimgonly' => false,
		'preview_mode' => false,
		'suppress_icons' => false,
		'parsetoc' => true,
		'inside_pretty' => false,
		'process_wiki_paragraphs' => true,
		'min_one_paragraph' => false,
		'parseBreaks' => true,
		'parseLists' =>   true,
		'parseNps' => true,
		'parseSmileys'=> true,
		'namespace' => null,
		'skipPageCache' => false,
	);

	/**
	 * Change options
	 *
	 * @access  public
	 * @param   array  $option an array of options, key being the option name and value being the value to be set
	 */
	public function setOption($option = array())
	{
		global $parserlib;

		if (!empty($this->Parser->option)) {
			$this->Parser->option = array_merge($this->Parser->option, $option);
		} else {
			$this->resetOption();
			$this->Parser->option = array_merge($this->optionDefaults, $option);
		}

		if (isset($parserlib->option)) {
			$parserlib->option = $this->Parser->option;
		}
	}

	/**
	 * Access single option
	 *
	 * @access  public
	 * @param   string  $name name/key of option
	 * @return  mixed   value of option or false if not set
	 */
	public function getOption($name = '')
	{
		if (isset($this->Parser->option[$name])) {
			return $this->Parser->option[$name];
		} else {
			return false;
		}
	}

	/**
	 * Reset all options to default value
	 *
	 * @access  public
	 */
	public function resetOption()
	{
		global $prefs, $parserlib;
		$page = (isset($_REQUEST['page']) ? $_REQUEST['page'] : $prefs['site_wikiHomePage']);

		$this->Parser->option['page'] = $page;
		$this->Parser->option = $this->optionDefaults;

		if (isset($parserlib->option)) {
			$parserlib->option = $this->Parser->option;
		}
	}

	/**
	 * construct
	 *
	 * @access  public
	 * @param   JisonParser_Wiki_Handler  $Parser Filename to be used
	 */
	public function __construct(JisonParser_Wiki_Handler &$Parser = null)
	{
		global $user;

		$this->user = (isset($user) ? $user : tra('Anonymous'));

		if (empty($Parser)) {
			$this->Parser = &$this;
		} else {
			$this->Parser = &$Parser;
		}

		if (isset($this->pluginNegotiator) == false) {
			$this->pluginNegotiator = new WikiPlugin_Negotiator_Wiki($this->Parser);
		}

		if (isset($this->Parser->header) == false) {
			$this->Parser->header = new JisonParser_Wiki_Header($this->Parser);
		}

		if (isset($this->Parser->list) == false) {
			$this->Parser->list = new JisonParser_Wiki_List($this->Parser);
		}

		if (isset($this->Parser->autoLink) == false) {
			$this->Parser->autoLink = new JisonParser_Wiki_AutoLink($this->Parser);
		}

		if (isset($this->Parser->hotWords) == false) {
			$this->Parser->hotWords = new JisonParser_Wiki_HotWords($this->Parser);
		}

		if (isset($this->Parser->smileys) == false) {
			$this->Parser->smileys = new JisonParser_Wiki_Smileys();
		}

		if (isset($this->Parser->dynamicVar) == false) {
			$this->Parser->dynamicVar = new JisonParser_Wiki_DynamicVariables($this->Parser);
		}

		if (isset($this->specialCharacter) == false) {
			$this->specialCharacter = new JisonParser_Wiki_SpecialChar($this->Parser);
		}

		if (empty($this->Parser->option) == true) {
			$this->resetOption();
		}

		parent::__construct();
	}

/*
	function parser_performAction(&$thisS, $yytext, $yyleng, $yylineno, $yystate, $S, $_S, $O)
	{
		$result = parent::parser_performAction($thisS, $yytext, $yyleng, $yylineno, $yystate, $S, $_S, $O);
		if ($this->parserDebug == true) {
			$thisS = "{" . $thisS . ":" . $yystate ."," . $this->skipBr . "}";
		}
		return $result;
	}

	function lexer_performAction(&$yy, $yy_, $avoiding_name_collisions, $YY_START = null) {
		$result = parent::lexer_performAction($yy, $yy_, $avoiding_name_collisions, $YY_START);
		if ($this->lexerDebug == true) {
			echo "{" . $result . ":" .$avoiding_name_collisions . "," . $this->skipBr . "}" . $yy_->yytext . "\n";
		}
		return $result;
	}

	function parseError($error, $info)
	{
		echo $error;
		die;
	}
*/

	/**
	 * Where a parse generally starts.  Can be self-called, as this is detected, and if nested, a new parser is instantiated
	 *
	 * @access  private
	 * @param   string  $input Wiki syntax to be parsed
	 * @return  string  $output Parsed wiki syntax
	 */

	function parse($input)
	{
		if (empty($input)) {
			return $input;
		}

		if ($this->parsing == true) {
			$class = get_class($this->Parser);
			$parser = new $class($this->Parser);
			$output = $parser->parse($input);
			unset($parser);
		} else {
			$this->parsing = true;

			$this->preParse($input);

			$this->Parser->parseDepth++;
			$output = parent::parse($input);
			$this->Parser->parseDepth--;

			$this->parsing = false;
			$this->postParse($output);
		}

		return $output;
	}

	/**
	 * Parse a plugin's body.  public so that negotiator can use.  option 'noparseplugins' makes this function return the body without parse.
	 *
	 * @access  public
	 * @param   string  $input Plugin body
	 * @return  string  $output Parsed plugin body or $input if not parsed
	 */
	public function parsePlugin($input)
	{
		if (empty($input)) return "";

		if ($this->getOption('noparseplugins') == false) {

			$is_html = $this->getOption('is_html');

			if ($is_html == false) {
				$this->setOption(array('is_html' => true));
			}

			$output = $this->parse($input);

			if ($is_html == false) {
				$this->setOption(array('is_html' => $is_html));
			}

			return $output;
		} else {
			return $input;
		}
	}


	/**
	 * Event just before JisonParser_Wiki->parse(), used to ready parser, ensuring defaults needed for parsing are set.
	 * <p>
	 * pcre.recursion_limit is temporarily changed here. php default is 100,000 which is just too much for this type of
	 * parser. The reason for this code is the use of preg_* functions using pcre library.  Some of the regex needed is
	 * just too much for php to handle, so by limiting this for regex we speed up the parser and allow it to safely
	 * lex/parse a string more here: http://stackoverflow.com/questions/7620910/regexp-in-preg-match-function-returning-browser-error
	 *
	 * @access  private
	 * @param   string  &$input input that will be parsed
	 */
	private function preParse(&$input)
	{
		if ($this->Parser->parseDepth == 0) {
			$this->pcreRecursionLimit = ini_get("pcre.recursion_limit");
			ini_set("pcre.recursion_limit", "524");

			$this->Parser->list->reset();
		}

		$this->line = 0;
		$this->isFirstBr = false;
		$this->skipBr = false;
		$this->tableStack = array();
		$this->nonBreakingTagDepth = 0;
		$this->npStack = false;
		$this->ppStack = false;
		$this->linkStack = false;

		if ($input{0} == "\n" && $this->isBlockStartSyntax($input{1})) {
			$this->isFirstBr = true;
		}

		$input = "\n" . $input . "≤REAL_EOF≥"; //here we add 2 lines, so the parser doesn't have to do special things to track the first line and last, we remove these when we insert breaks, these are dynamically removed later
		$input = str_replace("\r", "", $input);
		$input = $this->specialCharacter->protect($input);
	}

	/**
	 * Event just after JisonParser_Wiki->parse(), used to ready parser, ensuring defaults needed for parsing are set.
	 * <p>
	 * pcre.recursion_limit is reset here if parser depth is 0 (ie, no nested parsing)
	 *
	 * @access  private
	 * @param   string  &$output parsed output of wiki syntax
	 */
	function postParse(&$output)
	{
		//remove comment artifacts
		$output = str_replace("<!---->", "", $output);

		//Replace special end tag
		$this->removeEOF($output);

		if ( $this->getOption('parseLists') == true) {
			$lists = $this->Parser->list->toHtml();
			if (!empty($lists)) {
				$lists = array_reverse($lists);
				foreach ($lists as $key => &$list) {

						$output = str_replace($key, $list, $output);
						unset($list);

				}
			}
		}

		if (isset($this->Parser->smileys) && $this->getOption('parseSmileys')) {
			$this->Parser->smileys->parse($output);
		}

		$this->restorePluginEntities($output);

		if (isset($this->Parser->autoLink)) {
			$this->Parser->autoLink->parse($output);
		}

		if (isset($this->Parser->hotWords)) {
			$this->Parser->hotWords->parse($output);
		}

		if (isset($this->Parser->dynamicVar)) {
			$this->Parser->dynamicVar->makeForum($output);
		}

		if ($this->Parser->parseDepth == 0) {
			ini_set("pcre.recursion_limit", $this->pcreRecursionLimit);
			$output = $this->specialCharacter->unprotect($output);
		}
	}

	/**
	 * Handles plugins directly from the wiki parser.  A plugin can be on a different level of the current parser, and
	 * if so, the execution is delayed until the parser reaches that level.
	 *
	 * @access  private
	 * @param   array  &$pluginDetails plugins details in an array
	 * @return  string  either returns $key or block from execution message
	 */
	public function plugin(&$pluginDetails)
	{
		$pluginDetails['body'] = $this->specialCharacter->unprotect($pluginDetails['body'], true);
		$negotiator =& $this->pluginNegotiator;

		$negotiator->setDetails($pluginDetails);

		if ( $this->getOption('skipvalidation') == false) {
			$status = $negotiator->canExecute();
		} else {
			$status = true;
		}

		if ($status === true) {
			/*$plugins is a bit different that pluginEntries, an entry will be popped later, $plugins is more for
			tracking, although their values may be the same for a time, the end result will be an empty entries, but
			$plugins will have all executed plugin in it*/
			$this->plugins[$negotiator->key] = $negotiator->body;

			$executed = $negotiator->execute();

			if ($negotiator->ignored == true) {
				return $executed;
			} else {
				$this->pluginEntries[$negotiator->key] = $this->parsePlugin($executed);
				return $negotiator->key;
			}
		} else {
			return $negotiator->blockFromExecution($status);
		}
	}

	/**
	 * Increments the plugin index, but on a plugin type by type basis, for example, html1, html2, div1, div2.  indexes
	 * are static, so that all index are unique
	 *
	 * @access  private
	 * @param   string  $name plugin name
	 * @return  string  $index
	 */
	private function incrementPluginIndex($name)
	{
		$name = strtolower($name);

		if (isset(self::$pluginIndexes[$name]) == false) self::$pluginIndexes[$name] = 0;

		self::$pluginIndexes[$name]++;

		return self::$pluginIndexes[$name];
	}

	/**
	 * Key of the plugin, an md5 signature of  ('§' . $name . $index . '§').  This technique is used so that line breaks
	 * can be inserted without distorting the content found in the plugin, and to limit what is parser, thus speeding
	 * the parser up, less syntax to analyse
	 *
	 * @access  private
	 * @param   string  $name plugin name
	 * @return  string  $key
	 */
	private function pluginKey($name)
	{
		return '§' . md5('plugin:' . $name . '_' . $this->incrementPluginIndex($name)) . '§';
	}

	function inlinePlugin($yytext)
	{
		$pluginName = $this->match('/^\{([a-z]+)/', $yytext);
		$pluginArgs = rtrim(str_replace('{'.$pluginName .' ', '', $yytext), '}');

		return array(
			'name' => $pluginName,
			'args' => $pluginArgs,
			'body' => '',
			'key' => $this->pluginKey($pluginName),
			'syntax' => $yytext,
			'closing' => ''
		);
	}

	/**
	 * Stacks plugins for execution, since plugins can be called within each other.  Public because called directly by
	 * the lexer of the wiki parser
	 *
	 * @access  public
	 * @param   string  $yytext The analysed text from the wiki parser
	 */
	public function stackPlugin($yytext)
	{
		$pluginName = $this->match('/^\{([A-Z]+)/', $yytext);
		$pluginArgs = rtrim(str_replace('{' . $pluginName . '(', '', $yytext), ')}');

		$this->pluginStack[] = array(
			'name' => $pluginName,
			'args' => $pluginArgs,
			'body' => '',
			'key' => $this->pluginKey($pluginName),
			'syntax' => $yytext,
			'closing' => '{' . $pluginName . '}'
		);
		$this->pluginStackCount++;
	}

	/**
	 * Detects if we are in a state that we can call the lexed grammer 'content'.  Since the execution technique from
	 * the parser is inside-out, this helps us reverse the execution from outside-in in some cases.
	 *
	 * @access  public
	 * @param   array  $skipTypes List of different ignourable stack types found on $this, like npStack, ppStack, or lineStack
	 * @return  string  true if content is current not parse-able
	 */
	public function isContent($skipTypes = array())
	{
		//These types will be found in $this.  If any of these states are active, we should NOT parse wiki syntax
		$types = array(
			'npStack' => true,
			'ppStack' => true,
			'linkStack' => true
		);

		foreach ($skipTypes as $skipType) {
			if (isset($types[$skipType])) {
				unset($types[$skipType]);
			}
		}

		//first off, if in plugin
		if ($this->pluginStackCount > 0) {
			return true;
		}

		//second, if we are not in a plugin, check if we are in content, ie, non-parse-able wiki syntax
		foreach ($types as $type => $value) {
			if ($this->$type == $value) {
				return true;
			}
		}

		//lastly, if we are not in content, return null, which allows cases to continue lexing
		return null;
	}

	/**
	 * Removed any entity (plugin, list, header) from an input
	 *
	 * @param   string  $input The analysed text from the wiki parser
	 */
	static function deleteEntities(&$input)
	{
		$input = preg_replace('/§[a-z0-9]{32}§/', '', $input);
	}

	/**
	 * restores the plugins back into the string being parsed.
	 *
	 * @access  private
	 * @param   string  $output Parsed syntax
	 */
	private function restorePluginEntities(&$output)
	{
		//use of array_reverse, jison is a reverse bottom-up parser, if it doesn't reverse jison doesn't restore the plugins in the right order, leaving the some nested keys as a result
		array_reverse($this->pluginEntries);
		$iterations = 0;
		$limit = 100;

		while (!empty($this->pluginEntries) && $iterations <= $limit) {
			$iterations++;
			foreach ($this->pluginEntries as $key => $entity) {
				if (strstr($output, $key)) {
					if ($this->getOption('stripplugins') == true) {
						$output = str_replace($key, '', $output);
					} else {
						$output = str_replace($key, $entity, $output);
					}
				}
			}
		}

		if ($this->Parser->parseDepth == 0) {
			$this->pluginNegotiator->executeAwaiting($output);
		}
	}


	//end state handlers
	//Wiki Syntax Objects Parsing Start
	/**
	 * syntax handler: noparse, ~np~$content~/np~
	 *
	 * @access  public
	 * @param   $content string parsed string found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	public function noParse($content)
	{
		if ( $this->getOption('parseNps') == true) {
			$content = $this->specialCharacter->unprotect($content);
		}

		return $content;
	}

	/**
	 * syntax handler: pre, ~pp~$content~/pp~
	 *
	 * @access  public
	 * @param   $content string parsed string found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function preFormattedText($content)
	{
		return $this->createWikiTag("preFormattedText", "pre", $content);
	}

	/**
	 * syntax handler: generic html
	 * <p>
	 * Used in detecting if we need a break, and line number in some cases
	 *
	 * @access  public
	 * @param   $content string parsed string found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function htmlTag($content)
	{
		$parts = preg_split("/[ >]/", substr($this->specialCharacter->unprotect($content, true), 1)); //<tag> || <tag name="">
		$name = strtolower(trim($parts[0]));

		switch ($name) {
			//start block level
			case 'h1':
			case 'h2':
			case 'h3':
			case 'h4':
			case 'h5':
			case 'h6':
			case 'pre':
			case 'ul':
			case 'li':
			case 'dl':
			case 'div':
			case 'table':
			case 'p':
				$this->skipBr = true;
			case 'script':
				$this->nonBreakingTagDepth++;
				$this->line++;
				break;

			//end block level
			case '/h1':
			case '/h2':
			case '/h3':
			case '/h4':
			case '/h5':
			case '/h6':
			case '/pre':
			case '/ul':
			case '/li':
			case '/dl':
			case '/div':
			case '/table':
			case '/p':
				$this->skipBr = true;
			case '/script':
				$this->nonBreakingTagDepth--;
				$this->nonBreakingTagDepth = max($this->nonBreakingTagDepth, 0);
				$this->line++;
				break;

			//skip next block level
			case 'hr':
			case 'br':
				$this->skipBr = true;
				break;
		}

		return $content;
	}

	/**
	 * syntax handler: double dynamic variable, %%$content%%
	 *
	 * @access  public
	 * @param   $content string parsed string found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function doubleDynamicVar($content)
	{
		global $prefs;

		if ( $prefs['wiki_dynvar_style'] != 'double') {
			return $content;
		}


		return $this->Parser->dynamicVar->ui(substr($content, 2, 2), $this->getOption('language'), true);
	}

	/**
	 * syntax handler: single dynamic variable, %$content%
	 *
	 * @access  public
	 * @param   $content string parsed string found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function singleDynamicVar($content)
	{
		global $prefs;

		if ( $prefs['wiki_dynvar_style'] != 'single') {
			return $content;
		}

		return $this->Parser->dynamicVar->ui(substr($content, 1, 1), $this->getOption('language'));
	}

	/**
	 * syntax handler: argument variable, {{$content}}
	 *
	 * @access  public
	 * @param   $content string parsed content  found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function argumentVar($content)
	{
		$content = substr($content, 2, -2); //{{page}}

		global $user, $page;
		$parts = explode('|', $content);
		$value = '';
		$name = '';

		if (isset($parts[0])) {
			$name = $parts[0];
		}

		if (isset($parts[1])) {
			$value = $parts[1];
		}

		switch( $name ) {
			case 'user':
				$value = $user;
				break;
			case 'page':
				$value = $this->getOption('page');
				break;
			default:
				if ( isset($_REQUEST[$name]) ) {
					$value = $_REQUEST[$name];
				}
				break;
		}

		return $value;
	}

	/**
	 * syntax handler: bold/strong, __$content__
	 *
	 * @access  public
	 * @param   $content string parsed content  found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function bold($content) //__content__
	{
		return $this->createWikiTag("bold", "strong", $content);
	}

	/**
	 * syntax handler: simple box, ^$content^
	 *
	 * @access  public
	 * @param   $content string parsed content  found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function box($content) //^content^
	{
		return $this->createWikiTag("box", "div", $content, array("class" => "well"));
	}

	/**
	 * syntax handler: center, ::$content::
	 *
	 * @access  public
	 * @param   $content string parsed content  found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function center($content) //::content::
	{
		return $this->createWikiTag(
			"center",
			"div",
			$content,
			array(
				"style" => "text-align: center;"
			)
		);
	}

	/**
	 * syntax handler: code, -+$content+-
	 *
	 * @access  public
	 * @param   $content string parsed content  found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function code($content)
	{
		return $this->createWikiTag("code", "code", $content);
	}

	/**
	 * syntax handler: text color, ~~$color:$content~~
	 *
	 * @access  public
	 * @param   $content string parsed content  found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function color($content)
	{
		$text = explode(':', $content);
		$color = $text[0];
		$content = $text[1];

		return $this->createWikiTag(
			"color", "span", $content,
			array(
				"style" => "color:" . $color .';'
			)
		);
	}

	/**
	 * syntax handler: italic/emphasis, ''$content''
	 *
	 * @access  public
	 * @param   $content string parsed content  found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function italic($content) //''content''
	{
		return $this->createWikiTag("italic", "em", $content);
	}

	/**
	 * syntax handler: left to right, \n{l2r}$content
	 *
	 * @access  public
	 * @param   $content string parsed content  found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function l2r($content)
	{
		$content = substr($content, 5);
		return $this->createWikiTag(
			"l2r", "div", $content,
			array(
				"dir" => "ltr"
			)
		);
	}

	/**
	 * syntax handler: right to left, \n{r2l}$content
	 *
	 * @access  public
	 * @param   $content string parsed content  found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function r2l($content)
	{
		$content = substr($content, 5);

		return $this->createWikiTag(
			"r2l", "div", $content,
			array(
				"dir" => "rtl"
			)
		);
	}

	/**
	 * syntax handler: header, \n!$content
	 * <p>
	 * Uses $this->Parser->header as a processor.  Is called from $this->block().
	 *
	 * @access  public
	 * @param   $content string parsed content  found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function header($content, $trackExclamationCount = false) //!content
	{
		global $prefs;
		$exclamationCount = 0;
		$headerLength = strlen($content);
		for ($i = 0; $i < $headerLength; $i++) {
			if ($content[$i] == '!') {
				$exclamationCount++;
			} else {
				break;
			}
		}

		$content = substr($content, $exclamationCount);
		$this->removeEOF($content);

		$hNum = min(6, $exclamationCount); //html doesn't support 7+ header level
		$id = $this->Parser->header->stack($hNum, $content);
		$button = '';
		global $section, $tiki_p_edit;
		if (
			$prefs['wiki_edit_section'] === 'y' &&
			$section === 'wiki page' &&
			$tiki_p_edit === 'y' &&
			(
				$prefs['wiki_edit_section_level'] == 0 ||
				$hNum <= $prefs['wiki_edit_section_level']
			) &&
			! $this->getOption('print') &&
			! $this->getOption('suppress_icons') &&
			! $this->getOption('preview_mode')
		) {
			$button = $this->createWikiHelper("header", "span", $this->Parser->header->button($prefs['wiki_edit_icons_toggle']));
		}

		$this->skipBr = true;

		//expanding headers
		$expandingHeaderClose = '';
		$expandingHeaderOpen = '';

		if ($this->headerStack == true) {
			$this->headerStack = false;
			$expandingHeaderClose = $this->createWikiHelper("header", "div", "", array(), "close");
		}

		if ($content{0} == '-') {
			$content = substr($content, 1);
			$this->headerStack = true;
			$expandingHeaderOpen =
				$this->createWikiHelper(
					"header", "a", "[+]",
					array(
						"id" => "flipperflip" . $id,
						"href" => "javascript:flipWithSign(\'flip' . $id .'\')"
					)
				) .
				$this->createWikiHelper(
					"header", "div", "",
					array(
						"id" => "flip". $id,
						"class" => "showhide_heading",
					), "open"
				);
		}

		$params = array(
			"id" => $id,
		);

		if ($trackExclamationCount) {
			$params['data-count'] = $exclamationCount;
		}

		$result =
			$expandingHeaderClose .
			$button .
			$this->createWikiTag(
				"header", 'h' . $hNum, $content, $params
			) .
			$expandingHeaderOpen;

		return $result;
	}

	/**
	 * syntax handler: list, \n*$content
	 * <p>
	 * List types: * (unordered), # (ordered), + (line break), - (expandable), ; (definition list)
	 * <p>
	 * Uses $this->Parser->list as a processor. Is called from $this->block().
	 *
	 * @access  public
	 * @param   $content string parsed content  found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function stackList($content)
	{
		$level = 0;
		$listLength = strlen($content);
		$type = '';
		$noiseLength = 0;

		for ($i = 0; $i < $listLength; $i++) {
			if (empty($type)) {
				//This will be the start of the string
				$type = $content{$i};
				$level++;

				//definitions are only 1 in depth
				if ($type == ';') {
					break;
				}
			} else if (
				$content{$i} == "*" ||
				$content{$i} == "#" ||
				$content{$i} == "+"
			) {
				$level++;
			} elseif ($content{$i} == '-') {
				$type .= $content{$i};
				$noiseLength++;
				break;
			} else {
				break;
			}
		}

		$content = substr($content, ($level + $noiseLength));
		$this->removeEOF($content);
		$result = $this->Parser->list->stack($this->line, $level, $content, $type);

		if (isset($result)) {
			$this->skipBr = true;
			return $result;
		}
		return '';
	}

	/**
	 * syntax handler: horizontal row, ---
	 *
	 * @access  public
	 * @return  string  html hr element
	 */
	function hr() //---
	{
		$this->line++;
		$this->skipBr = true;
		return $this->createWikiTag("horizontalRow", "hr", "", array(), "inline");
	}

	/**
	 * syntax handler: new line, \n
	 * <p>
	 * Detects if a line break is needed and returns it. If $this->skipBr is set to true, skips output of <br /> and
	 * sets it back to false for the next line to process
	 *
	 * @access  public
	 * @param   $ch line line character
	 * @return  string  $result of line process
	 */
	function line($ch)
	{
		$this->line++;
		$skipBr = $this->skipBr;
		$this->skipBr = false; //skipBr must always must be false when done processing line

		//The first \n was inserted just before parse
		if ($this->isFirstBr == false) {
			$this->isFirstBr = true;
			return '';
		}

		$result = '';

		if ($skipBr == false && $this->nonBreakingTagDepth == 0) {
			$result = $this->createWikiTag("line", "br", "", array(), "inline");
		}

		return $result . $ch;
	}

	/**
	 * syntax handler: forced line end, %%%
	 * <p>
	 * Note: does not affect line number
	 *
	 * @access  public
	 * @return  string  html break, <br />
	 */
	function forcedLineEnd()
	{
		return $this->createWikiTag("forcedLineEnd", "br", "", array(), "inline");
	}

	/**
	 * syntax handler: unlink, [[$content|$content]]
	 *
	 * @access  public
	 * @param   $content string parsed content  found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function unlink($content) //[[content|content]
	{
		$contentLength = strlen($content);

		if ($content[$contentLength - 3] == "@" &&
			$content[$contentLength - 2] == "n" &&
			$content[$contentLength - 1] == "p"
		) {
			$content = substr($content, 0, -3);
		}

		$contentLength = strlen($content);

		if ($content[$contentLength - 1] != "]" && strstr($content, "[[")) {
			$content = substr($content, 1);
		} else if (!strstr($content, "]]")) {
			$content = substr($content, 1);
		}

		return $this->createWikiTag("unlink", "span", $content);
	}

	/**
	 * syntax handler: link, [$content|$content], ((Page)), ((Page|$content)), (type(Page)), (type(Page|$content)), ((external:Page)), ((external:Page|$content))
	 *
	 * @access  public
	 * @param   $type string type, np, wiki, alias (or whatever is "(here(", word
	 * @param   $content string found inside detected syntax
	 * @param   $includePageAsDataAttribute bool includes the page as an attribute in the link "data-page"
	 * @return  string  $content desired output from syntax
	 */
	function link($type, $page, $includePageAsDataAttribute = false) //[content|content]
	{
		global $tikilib, $prefs;

		if ($type == 'word' && $prefs['feature_wikiwords'] != 'y') {
			return $page;
		}

		$this->removeEOF($page);

		$wikiExternal = '';
		$parts = explode(':', $page);
		if (isset($parts[1]) && $type != 'external') {
			$wikiExternal = array_shift($parts);
			$page = implode(':', $parts);
		}

		$description = '';
		$parts = explode('|', $page);
		if (isset($parts[1])) {
			$page = array_shift($parts);
			$description = implode('|', $parts);
		}

		if (!empty($description)) {
			$feature_wikiwords = $prefs['feature_wikiwords'];
			$prefs['feature_wikiwords'] = 'n';
			$description = $this->parse($description);
			$this->removeEOF($description);
			$prefs['feature_wikiwords'] = $feature_wikiwords;
		}

		return JisonParser_Wiki_Link::page($page, $this->Parser)
			->setNamespace($this->getOption('namespace'))
			->setDescription($description)
			->setType($type)
			->setSuppressIcons($this->getOption('suppress_icons'))
			->setSkipPageCache($this->getOption('skipPageCache'))
			->setWikiExternal($wikiExternal)
			->includePageAsDataAttribute($includePageAsDataAttribute)
			->getHtml();
	}

	/**
	 * syntax handler: smile, :)
	 *
	 * @access  public
	 * @param   $content string parsed content  found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function smile($content) //TODO: add all smile handling in parser
	{
		//this needs more tlc too
		return '<img src="img/smiles/icon_' . $content . '.gif" alt="' . $content . '" />';
	}

	/**
	 * syntax handler: strike, --$content--
	 *
	 * @access  public
	 * @param   $content string parsed content found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function strike($content) //--content--
	{
		return $this->createWikiTag("strike", "strike", $content);
	}

	/**
	 * syntax handler: double dash, --
	 *
	 * @access  public
	 * @return  dash characters
	 */
	function doubleDash()
	{
		return $this->createWikiTag("doubleDash", "span", " &mdash; ");
	}

	/**
	 * syntax handler: characters
	 *
	 * @access  public
	 * @param   $content char handler, upper or lower case
	 * @return  string output of char
	 */
	function char($content)
	{
		if ($this->isContent() || $this->Parser->parseDepth > 1) return $content;

		switch (strtolower($content)) {
			case "&":
				$result = '&amp;';
				break;
			case "~bs~":
				$result = '&#92;';
				break;
			case "~hs~":
				$result = '&nbsp;';
				break;
			case "~amp~":
				$result = '&amp;';
				break;
			case "~ldq~":
				$result = '&ldquo;';
				break;
			case "~rdq~":
				$result = '&rdquo;';
				break;
			case "~lsq~":
				$result = '&lsquo;';
				break;
			case "~rsq~":
				$result = '&rsquo;';
				break;
			case "~c~":
				$result = '&copy;';
				break;
			case "~--~":
				$result = '&mdash;';
				break;
			case "=>":
				$result = '=&gt;';
				break;
			case "~lt~":
				$result = '&lt;';
				break;
			case "~gt~":
				$result = '&gt;';
				break;
			case "{rm}":
				$result = '&rlm;';
				break;
		}

		//if it has not been caught, it is a number, ie ~([0-9]+)~
		if (!isset($result)) {
			$result = '';
			$possibleNumber = substr($content, 1, -1);
			if (is_numeric($possibleNumber)) {
				$result = "&#" . $possibleNumber . ";";
			}
		}

		return $this->createWikiTag("char", "span", $result);
	}

	/**
	 * syntax handler: table, ||$content|$content\n$content|$content||
	 *
	 * @access  public
	 * @param   $content string parsed content  found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function tableParser($content, $incomplete = false) /*|| | \n | ||*/
	{
		$tableContents = '';
		$rows = explode("\n", $content);

		if ($incomplete) {
			$result = '';
			end($rows);
			$lastKey = key($rows);
			foreach ($rows as $key => $row) {
				$result .= $row;
				if ($key < $lastKey) {
					$result .= $this->line("\n");
				}
			}
			return $result;
		}

		for ($i = 0, $count_rows = count($rows); $i < $count_rows; $i++) {
			$row = '';

			$cells = explode('|', $rows[$i]);
			for ($j = 0, $count_cells = count($cells); $j < $count_cells; $j++) {
				$row .= $this->table_td($cells[$j]);
			}
			$tableContents .= $this->table_tr($row);
		}

		$tbody = $this->createWikiTag('tableBody', 'tbody', $tableContents);

		return $this->createWikiTag(
			"table", "table", $tbody,
			array(
				"class" => "wikitable"
			)
		);
	}

	/**
	 * syntax handler table helper for tr
	 *
	 * @access  public
	 * @param   $content string parsed content  found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	private function table_tr($content)
	{
		return $this->createWikiTag("tableRow", "tr", $content);
	}

	/**
	 * syntax handler table helper for td
	 *
	 * @access  public
	 * @param   $content string parsed content  found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	private function table_td($content)
	{
		return $this->createWikiTag(
			"tableData", "td", $content,
			array(
				"class" => "wikicell"
			)
		);
	}

	/**
	 * syntax handler: titlebar, -=$content=-
	 *
	 * @access  public
	 * @param   $content string parsed content  found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function titleBar($content) //-=content=-
	{
		$this->skipBr = true;
		return $this->createWikiTag(
			"titleBar", "div", $content,
			array(
				"class" => "titlebar"
			)
		);
	}

	/**
	 * syntax handler: underscore, ===$content===
	 *
	 * @access  public
	 * @param   $content string parsed content  found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function underscore($content) //===content===
	{
		return $this->createWikiTag("underscore", "u", $content);
	}


	/**
	 * syntax handler: tiki comment, ~tc~$content~/tc~
	 *
	 * @access  public
	 * @param   $content string parsed content  found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function comment($content)
	{
		return '<!---->';
	}

	public $blocks = array(
		"header" => array('!'),

		"stackList" => array('*','#','+',';'),

		"r2l" => array('{r2l}'),
		"l2r" => array('{l2r}'),
	);

	/**
	 * syntax handler: block, \n$content\n
	 *
	 * @access  public
	 * @param   $content string parsed content  found inside detected syntax
	 * @return  string  $content desired output from syntax
	 */
	function block($content)
	{
		$this->line++;
		$this->skipBr = false;
		$this->isFirstBr = true;

		$newLine = $content{0};
		$content = substr($content, 1);

		foreach ($this->blocks as $function => &$set) {
			foreach ($set as &$startsWith) {
				if ($this->beginsWith($content, $startsWith)) {
					return $this->$function($content);
				}
			}
		}

		return $newLine . $content;
	}

	/**
	 * tag helper creation, noise items that will be disposed
	 *
	 * @access  public
	 * @param   $syntaxType string from what syntax type
	 * @param   $tagType string what output tag type
	 * @param   $content string what is inside the tag
	 * @param   $params array what params to add to the tag, array, key = param, value = value
	 * @param   $type default is "standard", of types : standard, inline, open, close
	 * @return  string  $tag desired output from syntax
	 */
	public function createWikiHelper($syntaxType, $tagType, $content = "", $params = array(), $type = "standard")
	{
		$tag = "<" . $tagType;

		if (!empty($params)) {
			foreach ($params as $param => $value) {
				$tag .= " " . $param . "='" . $value . "'";
			}
		}

		switch ($type) {
			case "inline": $tag .= "/>";
				break;
			case "standard":
				$tag .= ">" . $content . "</" . $tagType . ">";
				break;
			case "open": $tag .= ">";
				break;
			case "close":
				return '</' .$tagType . '>';
		}

		return $tag;
	}

	/**
	 * tag creation, should only be used with items that are directly related to wiki syntax, buttons etc, should use createWikiHelper
	 *
	 * @access  public
	 * @param   $syntaxType string from what syntax type
	 * @param   $tagType string what output tag type
	 * @param   $content string what is inside the tag
	 * @param   $params array what params to add to the tag, array, key = param, value = value
	 * @param   $inline bool the content to be ignored and for tag to close, ie <tag />
	 * @return  string  $tag desired output from syntax
	 */
	public function createWikiTag($syntaxType, $tagType, $content = "", $params = array(), $type = "standard")
	{
		$this->isRepairing($syntaxType, true);

		$tag = "<" . $tagType;

		if (!empty($params)) {
			foreach ($params as $param => $value) {
				$tag .= " " . $param . "='" . trim($value) . "'";
			}
		}

		switch ($type) {
			case "inline": $tag .= "/>";
				break;
			case "standard":
				$tag .= ">" . $content . "</" . $tagType . ">";
				break;
			case "open": $tag .= ">";
				break;
			case "close":
				return '</' .$tagType . '>';
		}

		return $tag;
	}


	/**
	 * helper function to detect what is at the beginning of a string
	 *
	 * @access  public
	 * @param   $haystack
	 * @param   $needle
	 * @return  bool  true if found at beginning, false if not
	 */
	function beginsWith($haystack, $needle)
	{
		return (strncmp($haystack, $needle, strlen($needle)) === 0);
	}

	/**
	 * helper function to detect a match in string
	 *
	 * @access  public
	 * @param   $pattern
	 * @param   $subject
	 * @return  bool  true if found at beginning, false if not
	 */
	function match($pattern, $subject)
	{
		preg_match($pattern, $subject, $match);

		return (!empty($match[1]) ? $match[1] : false);
	}

	function isRepairing($syntaxType, $pop = false)
	{
		$isRepairing = false;
		end($this->repairingStack);
		$key = key($this->repairingStack);


		if (isset($this->repairingStack[$key])) {
			$lastRepaired = $this->repairingStack[$key];

			if ($lastRepaired == $syntaxType) {
				$isRepairing = true;
				if ($pop == true) {
					array_pop($this->repairingStack);
				}
			}
		}

		return $isRepairing;
	}

	function isBlockStartSyntax($char)
	{
		if (
			$char == "*" ||
			$char == "#" ||
			$char == "+" ||
			$char == ";" ||
			$char == "!"
		) {
			return true;
		}
	}

	function removeEOF( &$output )
	{
		$output = str_replace("≤REAL_EOF≥", "", $output);
	}
}
