<?php

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"],basename(__FILE__)) !== false) {
  header("location: index.php");
  exit;
}

include_once('lib/smarty_tiki/block.self_link.php');

abstract class Quicktag
{
	protected $wysiwyg;
	protected $icon;
	protected $label;

	private $requiredPrefs = array();

	public static function getTag( $tagName ) // {{{
	{
		if( $tag = QuicktagInline::fromName( $tagName ) )
			return $tag;
		elseif( $tag = QuicktagBlock::fromName( $tagName ) )
			return $tag;
		elseif( $tag = QuicktagLineBased::fromName( $tagName ) )
			return $tag;
		elseif( $tag = QuicktagFckOnly::fromName( $tagName ) )
			return $tag;
		elseif( $tag = QuicktagWikiplugin::fromName( $tagName ) )
			return $tag;
		elseif( $tag = QuicktagPicker::fromName( $tagName ) )
			return $tag;
		elseif( $tagName == 'fullscreen' )
			return new QuicktagFullscreen;
		elseif( $tagName == 'enlarge' )
			return new QuicktagTextareaResize( 'enlarge' );
		elseif( $tagName == 'reduce' )
			return new QuicktagTextareaResize( 'reduce' );
		elseif( $tagName == 'help' )
			return new QuicktagHelptool;
		elseif( $tagName == '-' )
			return new QuicktagSeparator;
	} // }}}

	public static function getList() // {{{
	{
		global $tikilib;
		$plugins = $tikilib->plugin_get_list();
		foreach( $plugins as & $name )
			$name = "wikiplugin_$name";

		return array_merge( array(
			'-',
			'bold',
			'italic',
			'strike',
			'sub',
			'sup',
			'tikilink',
			'link',
			'anchor',
			'color',
			'bgcolor',
			'center',
			'table',
			'rule',
			'pagebreak',
			'blockquote',
			'h1',
			'h2',
			'h3',
			'toc',
			'image',
			'list',
			'numlist',
			'specialchar',
			'smiley',
			'templates',
			'cut',
			'copy',
			'paste',
			'pastetext',
			'pasteword',
			'print',
			'spellcheck',
			'undo',
			'redo',
			'find',
			'replace',
			'selectall',
			'removeformat',
			'showblocks',
			'left',
			'right',
			'full',
			'indent',
			'outdent',
			'underline',
			'unlink',
			'style',
			'fontname',
			'fontsize',
			'source',
			'fullscreen',
			'enlarge',
			'reduce',
			'help',
		), $plugins );
	} // }}}

	abstract function getWikiHtml( $areaName );

	function isAccessible() // {{{
	{
		global $prefs;

		foreach( $this->requiredPrefs as $prefName )
			if( ! isset($prefs[$prefName]) || $prefs[$prefName] != 'y' )
				return false;

		return true;
	} // }}}

	function getWysiwygToken() // {{{
	{
		return $this->wysiwyg;
	} // }}}

	protected function addRequiredPreference( $prefName ) // {{{
	{
		$this->requiredPrefs[] = $prefName;
	} // }}}

	protected function setIcon( $icon ) // {{{
	{
		$this->icon = $icon;

		return $this;
	} // }}}

	protected function setLabel( $label ) // {{{
	{
		$this->label = $label;

		return $this;
	} // }}}

	protected function setWysiwygToken( $token ) // {{{
	{
		$this->wysiwyg = $token;

		return $this;
	} // }}}

	function getIconHtml() // {{{
	{
		return '<img src="' . htmlentities($this->icon, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlentities($this->label, ENT_QUOTES, 'UTF-8') . '" title="' . htmlentities($this->label, ENT_QUOTES, 'UTF-8') . '" class="icon"/>';
	} // }}}
	
	function getSelfLink( $click, $title, $class ) { // {{{
		global $smarty;
		
		$params = array();
		$params['_onclick'] = $click . (substr($click, strlen($click)-1) != ';' ? ';' : '') . 'return false;';
		$params['_class'] = 'quicktag ' . (!empty($class) ? ' '.$class : '');
		$params['_ajax'] = 'n';
		$content = $title;
		$params['_icon'] = $this->icon;
			
		if (strpos($class, 'qt-plugin') !== false) {
			$params['_menu_text'] = 'y';
			$params['_menu_icon'] = 'y';
		} else {
		}
		return smarty_block_self_link($params, $content, $smarty);
	} // }}}
	
	function getLabel() // {{{
	{
		return $this->label;
	} // }}}

}

class QuicktagSeparator extends Quicktag
{
	function __construct() // {{{
	{
		$this->setWysiwygToken('-');
		$this->setIcon('pics/icons/tree_vertline.png');
	} // }}}

	function getWikiHtml( $areaName ) // {{{
	{
		return '|';
	} // }}}
}

class QuicktagFckOnly extends Quicktag
{ 
	private function __construct( $token, $icon = 'pics/icons/shading.png' ) // {{{
	{
		$this->setWysiwygToken( $token );
		$this->setIcon($icon);
	} // }}}
	
	public static function fromName( $name ) // {{{
	{
		switch( $name ) {
		case 'templates':
			return new self( 'Templates', 'pics/icons/page_white_stack.png' );
		case 'cut':
			return new self( 'Cut', 'pics/icons/cut.png' );
		case 'copy':
			return new self( 'Copy', 'pics/icons/page_copy.png' );
		case 'paste':
			return new self( 'Paste', 'pics/icons/page_paste.png' );
		case 'pastetext':
			return new self( 'PasteText' );
		case 'pasteword':
			return new self( 'PasteWord' );
		case 'print':
			return new self( 'Print' );
		case 'spellcheck':
			return new self( 'SpellCheck' );
		case 'undo':
			return new self( 'Undo' );
		case 'redo':
			return new self( 'Redo' );
		case 'find':
			return new self( 'Find' );
		case 'replace':
			return new self( 'Replace' );
		case 'selectall':
			return new self( 'SelectAll' );
		case 'removeformat':
			return new self( 'RemoveFormat' );
		case 'showblocks':
			return new self( 'ShowBlocks' );
		case 'left':
			return new self( 'JustifyLeft' );
		case 'right':
			return new self( 'JustifyRight' );
		case 'full':
			return new self( 'JustifyFull' );
		case 'indent':
			return new self( 'Indent' );
		case 'outdent':
			return new self( 'Outdent' );
		case 'underline':
			return new self( 'Underline' );
		case 'unlink':
			return new self( 'Unlink' );
		case 'style':
			return new self( 'Style' );
		case 'fontname':
			return new self( 'FontName' );
		case 'fontsize':
			return new self( 'FontSize' );
		case 'source':
			return new self( 'Source' );
		case 'autosave':
			return new self( 'ajaxAutoSave' );
		}
	} // }}}

	function getWikiHtml( $areaName ) // {{{
	{
		return null;
	} // }}}
}

class QuicktagInline extends Quicktag
{
	protected $syntax;

	public static function fromName( $tagName ) // {{{
	{
		switch( $tagName ) {
		case 'bold':
			$label = tra('Bold');
			$icon = tra('pics/icons/text_bold.png');
			$wysiwyg = 'Bold';
			$syntax = '__text__';
			break;
		case 'italic':
			$label = tra('Italic');
			$icon = tra('pics/icons/text_italic.png');
			$wysiwyg = 'Italic';
			$syntax = "''text''";
			break;
		case 'strike':
			$label = tra('Strikethrough');
			$icon = tra('pics/icons/text_strikethrough.png');
			$wysiwyg = 'StrikeThrough';
			$syntax = '--text--';
			break;
		case 'sub':
			$label = tra('Subscript');
			$icon = tra('pics/icons/text_subscript.png');
			$wysiwyg = 'Subscript';
			$syntax = '{SUB()}text{SUB}';
			break;
		case 'sup':
			$label = tra('Superscript');
			$icon = tra('pics/icons/text_superscript.png');
			$wysiwyg = 'Superscript';
			$syntax = '{SUP()}text{SUP}';
			break;
		case 'tikilink':
			$label = tra('Wiki Link');
			$icon = tra('pics/icons/page_link.png');
			$wysiwyg = 'tikilink';
			$syntax = '((text))';
			break;
		case 'link':
			$label = tra('Link');
			$icon = tra('pics/icons/world_link.png');
			$wysiwyg = 'Link';
			$syntax = '[http://example.com|text]';
			break;
		case 'anchor':
			$label = tra('Anchor');
			$icon = tra('pics/icons/anchor.png');
			$wysiwyg = 'Anchor';
			$syntax = '{ANAME()}text{ANAME}';
			break;
		case 'color':
			$label = tra('Text Color');
			$icon = tra('pics/icons/palette.png');
			$wysiwyg = 'TextColor';
			$syntax = '~~red:text~~';
			break;
		case 'bgcolor':
			$label = tra('Background Color');
			$icon = tra('pics/icons/palette.png');
			$wysiwyg = 'BGColor';
			$syntax = '~~white,black:text~~';
			break;
		default:
			return;
		}

		$tag = new self;
		$tag->setLabel( $label )
			->setWysiwygToken( $wysiwyg )
			->setIcon( !empty($icon) ? $icon : 'pics/icons/shading.png' )
			->setSyntax( $syntax );
		
		return $tag;
	} // }}}

	protected function setSyntax( $syntax ) // {{{
	{
		$this->syntax = $syntax;

		return $this;
	} // }}}

	function getWikiHtml( $areaName ) // {{{
	{
		return $this->getSelfLink('needToConfirm=false;insertAt(\'' . $areaName . '\', \'' . addslashes(htmlentities($this->syntax, ENT_COMPAT, 'UTF-8')) . '\');return false;',
							htmlentities($this->label, ENT_QUOTES, 'UTF-8'), 'qt-inline');

		//return '<a href="javascript:insertAt(\'' . $areaName . '\', \'' . addslashes(htmlentities($this->syntax, ENT_COMPAT, 'UTF-8')) . 
		//		'\'); return false;" onclick="needToConfirm=false;" title="' . htmlentities($this->label, ENT_QUOTES, 'UTF-8') . '" class="quicktags qt-inline">' . $this->getIconHtml() . '</a>';
	} // }}}

}

class QuicktagBlock extends QuicktagInline // Will change in the future
{
	protected $syntax;

	public static function fromName( $tagName ) // {{{
	{
		switch( $tagName ) {
		case 'center':
			$label = tra('Align Center');
			$icon = tra('pics/icons/text_align_center.png');
			$wysiwyg = 'JustifyCenter';
			$syntax = "::text::";
			break;
		case 'table':
			$label = tra('Table');
			$icon = tra('pics/icons/table.png');
			$wysiwyg = 'Table';
			$syntax = '||r1c1|r1c2\nr2c1|r2c2||';
			break;
		case 'rule':
			$label = tra('Horizontal Bar');
			$icon = tra('pics/icons/page.png');
			$wysiwyg = 'Rule';
			$syntax = '---';
			break;
		case 'pagebreak':
			$label = tra('Page Break');
			$icon = tra('pics/icons/page.png');
			$wysiwyg = 'PageBreak';
			$syntax = '---';
			break;
		case 'blockquote':
			$label = tra('Block Quote');
			$icon = tra('pics/icons/box.png');
			$wysiwyg = 'Blockquote';
			$syntax = '^text^';
			break;
		case 'h1':
		case 'h2':
		case 'h3':
			$label = tra('Heading') . ' ' . $tagName{1};
			$icon = 'pics/icons/text_heading_' . $tagName{1} . '.png';
			$wysiwyg = null;
			$syntax = str_repeat('!', $tagName{1}) . 'text';
			break;
		case 'image':
			$label = tra('Image');
			$icon = tra('pics/icons/picture.png');
			$wysiwyg = 'tikiimage';
			$syntax = '{img src= width= height= link= }';
			break;
		case 'toc':
			$label = tra('Table of contents');
			$icon = tra('pics/icons/book.png');
			$wysiwyg = 'TOC';
			$syntax = '{maketoc}';
			break;
		default:
			return;
		}

		$tag = new self;
		$tag->setLabel( $label )
			->setWysiwygToken( $wysiwyg )
			->setIcon( !empty($icon) ? $icon : 'pics/icons/shading.png' )
			->setSyntax( $syntax );
		
		return $tag;
	} // }}}

	function getWikiHtml( $areaName ) // {{{
	{
		return $this->getSelfLink('needToConfirm=false;insertAt(\'' . $areaName . '\', \'' . addslashes(htmlentities($this->syntax, ENT_COMPAT, 'UTF-8')) . '\', true);return false;',
							htmlentities($this->label, ENT_QUOTES, 'UTF-8'), 'qt-block');
		//return '<a href="javascript:insertAt(\'' . $areaName . '\', \'' . addslashes(htmlentities($this->syntax, ENT_COMPAT, 'UTF-8')) . '\', true); return false;" onclick="needToConfirm=false;" title="' . htmlentities($this->label, ENT_QUOTES, 'UTF-8') . '" class="quicktag qt-block">' . $this->getIconHtml() . '</a>';
	} // }}}
}

class QuicktagLineBased extends QuicktagInline // Will change in the future
{
	protected $syntax;

	public static function fromName( $tagName ) // {{{
	{
		switch( $tagName ) {
		case 'list':
			$label = tra('Unordered List');
			$icon = tra('pics/icons/text_list_bullets.png');
			$wysiwyg = 'UnorderedList';
			$syntax = '*text';
			break;
		case 'numlist':
			$label = tra('Ordered List');
			$icon = tra('pics/icons/text_list_numbers.png');
			$wysiwyg = 'OrderedList';
			$syntax = '#text';
			break;
		default:
			return;
		}

		$tag = new self;
		$tag->setLabel( $label )
			->setWysiwygToken( $wysiwyg )
			->setIcon( !empty($icon) ? $icon : 'pics/icons/shading.png' )
			->setSyntax( $syntax );
		
		return $tag;
	} // }}}

	function getWikiHtml( $areaName ) // {{{
	{
		return $this->getSelfLink('needToConfirm=false;insertAt(\'' . $areaName . '\', \'' . addslashes(htmlentities($this->syntax, ENT_COMPAT, 'UTF-8')) . '\', true, true);return false;',
							htmlentities($this->label, ENT_QUOTES, 'UTF-8'), 'qt-line');
		//return '<a href="javascript:insertAt(\'' . $areaName . '\', \'' . addslashes(htmlentities($this->syntax, ENT_COMPAT, 'UTF-8')) . '\', true, true); return false;" onclick="needToConfirm=false;" title="' . htmlentities($this->label, ENT_QUOTES, 'UTF-8') . '" class="quicktag qt-line">' . $this->getIconHtml() . '</a>';
	} // }}}
}

class QuicktagPicker extends Quicktag
{
	private $list;

	public static function fromName( $tagName ) // {{{
	{
		$prefs = array();

		switch( $tagName ) {
		case 'specialchar':
			$wysiwyg = 'SpecialChar';
			$label = tra('Special Characters');
			$icon = tra('pics/icons/world_edit.png');
			// Line taken from DokuWiki
            $list = explode(' ','À à Á á Â â Ã ã Ä ä Ǎ ǎ Ă ă Å å Ā ā Ą ą Æ æ Ć ć Ç ç Č č Ĉ ĉ Ċ ċ Ð đ ð Ď ď È è É é Ê ê Ë ë Ě ě Ē ē Ė ė Ę ę Ģ ģ Ĝ ĝ Ğ ğ Ġ ġ Ĥ ĥ Ì ì Í í Î î Ï ï Ǐ ǐ Ī ī İ ı Į į Ĵ ĵ Ķ ķ Ĺ ĺ Ļ ļ Ľ ľ Ł ł Ŀ ŀ Ń ń Ñ ñ Ņ ņ Ň ň Ò ò Ó ó Ô ô Õ õ Ö ö Ǒ ǒ Ō ō Ő ő Œ œ Ø ø Ŕ ŕ Ŗ ŗ Ř ř Ś ś Ş ş Š š Ŝ ŝ Ţ ţ Ť ť Ù ù Ú ú Û û Ü ü Ǔ ǔ Ŭ ŭ Ū ū Ů ů ǖ ǘ ǚ ǜ Ų ų Ű ű Ŵ ŵ Ý ý Ÿ ÿ Ŷ ŷ Ź ź Ž ž Ż ż Þ þ ß Ħ ħ ¿ ¡ ¢ £ ¤ ¥ € ¦ § ª ¬ ¯ ° ± ÷ ‰ ¼ ½ ¾ ¹ ² ³ µ ¶ † ‡ · • º ∀ ∂ ∃ Ə ə ∅ ∇ ∈ ∉ ∋ ∏ ∑ ‾ − ∗ √ ∝ ∞ ∠ ∧ ∨ ∩ ∪ ∫ ∴ ∼ ≅ ≈ ≠ ≡ ≤ ≥ ⊂ ⊃ ⊄ ⊆ ⊇ ⊕ ⊗ ⊥ ⋅ ◊ ℘ ℑ ℜ ℵ ♠ ♣ ♥ ♦ 𝛼 𝛽 𝛤 𝛾 𝛥 𝛿 𝜀 𝜁 𝛨 𝜂 𝛩 𝜃 𝜄 𝜅 𝛬 𝜆 𝜇 𝜈 𝛯 𝜉 𝛱 𝜋 𝛳 𝜍 𝛴 𝜎 𝜏 𝜐 𝛷 𝜑 𝜒 𝛹 𝜓 𝛺 𝜔 𝛻 𝜕 ★ ☆ ☎ ☚ ☛ ☜ ☝ ☞ ☟ ☹ ☺ ✔ ✘ × „ “ ” ‚ ‘ ’ « » ‹ › — – … ← ↑ → ↓ ↔ ⇐ ⇑ ⇒ ⇓ ⇔ © ™ ® ′ ″');
			$list = array_combine( $list, $list );
			break;
		case 'smiley':
			$wysiwyg = 'Smiley';
			$label = tra('Smileys');
			$icon = tra('img/smiles/icon_smile.gif');
			$rawList = array( 'biggrin', 'confused', 'cool', 'cry', 'eek', 'evil', 'exclaim', 'frown', 'idea', 'lol', 'mad', 'mrgreen', 'neutral', 'question', 'razz', 'redface', 'rolleyes', 'sad', 'smile', 'surprised', 'twisted', 'wink', 'arrow', 'santa' );
			$prefs[] = 'feature_smileys';

			$list = array();
			foreach( $rawList as $smiley ) {
				$tra = htmlentities( tra($smiley), ENT_QUOTES, 'UTF-8' );
				$list["(:$smiley:)"] = '<img src="img/smiles/icon_' .$smiley . '.gif" alt="' . $tra . '" title="' . $tra . '" border="0" width="15" height="15" />';
			}
			break;
		default:
			return;
		}

		$tag = new self;
		$tag->setWysiwygToken( $wysiwyg )
			->setLabel( $label )
			->setIcon( !empty($icon) ? $icon : 'pics/icons/shading.png' )
			->setList( $list );
		foreach( $prefs as $pref ) {
			$tag->addRequiredPreference( $pref );
		}

		return $tag;
	} // }}}

	function setList( $list ) // {{{
	{
		$this->list = $list;
	} // }}}

	function getWikiHtml( $areaName ) // {{{
	{
		static $pickerAdded = false;
		static $index = -1;
		global $headerlib;

		if( ! $pickerAdded ) {
			$headerlib->add_js( <<<JS
var pickerData = [];
var pickerDiv;

function displayPicker( closeTo, list, areaname ) {
	var pickerDiv = document.createElement('div');
	document.body.appendChild( pickerDiv );

	var coord;
	if (typeof closeTo.getCoordinates == 'function') {	// moo
		coord = closeTo.getCoordinates();
	} else if (\$jq) {									// jq
		coord = \$jq(closeTo).offset();
		coord.bottom = coord.top + \$jq(closeTo).height();
	}
	pickerDiv.className = 'quicktags-picker';
	pickerDiv.style.left = coord.left + 'px';
	pickerDiv.style.top = (coord.bottom + 8) + 'px';

	var prepareLink = function( link, ins, disp ) {
		link.innerHTML = disp;
		link.href = 'javascript:void(0)';
		link.onclick = function() {
			insertAt( areaname, ins );
			if (typeof pickerDiv.dispose == 'function') {
				pickerDiv.dispose();
			} else if (\$jq) {
				\$jq('div.quicktags-picker').remove();
			}
		}
	};

	for( var i in pickerData[list] ) {
		var char = pickerData[list][i];
		var link = document.createElement( 'a' );

		pickerDiv.appendChild( link );
		pickerDiv.appendChild( document.createTextNode(' ') );
		prepareLink( link, i, char );
	}
}

JS
, 0 );
		}

		++$index;
		$headerlib->add_js( "pickerData.push( " . json_encode($this->list) . " );", 1 );

		return $this->getSelfLink('displayPicker( this, ' . $index . ', \'' . $areaName . '\'); needToConfirm=false',
							htmlentities($this->label, ENT_QUOTES, 'UTF-8'), 'qt-picker');
		//return '<a href="javascript:void(0)" onclick="displayPicker( this, ' . $index . ', \'' . $areaName . '\'); needToConfirm=false;" title="' . htmlentities($this->label, ENT_QUOTES, 'UTF-8') . '" class="quicktag qt-picker">' . $this->getIconHtml() . '</a>';
	} // }}}
}

class QuicktagFullscreen extends Quicktag
{
	function __construct() // {{{
	{
		$this->setLabel( tra('Full Screen Edit') )
			->setIcon( 'pics/icons/application_get.png' )
			->setWysiwygToken( 'FitWindow' );
	} // }}}

	function getWikiHtml( $areaName ) // {{{
	{
		$name = 'zoom';
		if( isset($_REQUEST['zoom']) )
			$name = 'preview';
		return '<input type="image" name="'.$name.'" alt="' . htmlentities($this->label, ENT_QUOTES, 'UTF-8') . '" class="quicktag qt-fullscreen" title="' . htmlentities($this->label, ENT_QUOTES, 'UTF-8') . '" value="wiki_edit" onclick="needToConfirm=false;" title="" class="icon" src="' . htmlentities($this->icon, ENT_QUOTES, 'UTF-8') . '"/>';
	} // }}}
}

class QuicktagTextareaResize extends Quicktag
{
	private $diff;

	function __construct( $type ) // {{{
	{
		switch( $type ) {
		case 'reduce':
			$this->setLabel( tra('Reduce area height') )
				->setIcon( tra('pics/icons/arrow_in.png') );
			$this->diff = '-10';
			break;

		case 'enlarge':
			$this->setLabel( tra('Enlarge area height') )
				->setIcon( tra('pics/icons/arrow_out.png') );
			$this->diff = '+10';
			break;

		default:
			throw new Exception('Unknown resize icon type type');
		}
	} // }}}

	function getWikiHtml( $areaName ) // {{{
	{
		return $this->getSelfLink('textareasize(\'' . $areaName . '\', ' . $this->diff . ', 0);needToConfirm = false;',
							htmlentities($this->label, ENT_QUOTES, 'UTF-8'), 'qt-resize');
		//return '<a href="javascript:textareasize(\'' . $areaName . '\', ' . $this->diff . ', 0)" onclick="needToConfirm = false;" title="' . htmlentities($this->label, ENT_QUOTES, 'UTF-8') . '" class="quicktag qt-resize">' . $this->getIconHtml() . '</a>';
	} // }}}

	function isAccessible() // {{{
	{
		return parent::isAccessible() && ! isset($_REQUEST['zoom']);
	} // }}}
}

class QuicktagHelptool extends Quicktag
{
	function __construct() // {{{
	{
		$this->setLabel( tra('Wiki Help') )
			->setIcon( 'pics/icons/help.png' );
	} // }}}
	
	function getWikiHtml( $areaName ) // {{{
	{

		global $wikilib, $smarty, $plugins;
		if (!isset($plugins)) {
			include_once ('lib/wiki/wikilib.php');
			$plugins = $wikilib->list_plugins(true);
		}
		$smarty->assign_by_ref('plugins', $plugins);
		return $smarty->fetch("tiki-edit_help.tpl");
		
//		return $this->getSelfLink('needToConfirm=false;flip(\'help_sections\')',
//							htmlentities($this->label, ENT_QUOTES, 'UTF-8'), 'qt-resize');
	} // }}}

	function isAccessible() // {{{
	{
		return parent::isAccessible();
	} // }}}
}

class QuicktagWikiplugin extends Quicktag
{
	private $pluginName;

	public static function fromName( $name ) // {{{
	{
		global $tikilib;
		if( substr( $name, 0, 11 ) == 'wikiplugin_'  ) {
			$name = substr( $name, 11 );
			if( $info = $tikilib->plugin_info( $name ) ) {
				if (isset($info['icon']) and $info['icon'] != '') {
					$icon = $info['icon'];
				} else {
					$icon = 'pics/icons/plugin.png';
				}

				$tag = new self;
				$tag->setLabel( str_ireplace('wikiplugin_', '', $info['name'] ))
					->setIcon( $icon )
					->setWysiwygToken( self::getToken( $name ) )
					->setPluginName( $name );

				return $tag;
			}
		}
	} // }}}

	function setPluginName( $name ) // {{{
	{
		$this->pluginName = $name;

		return $this;
	} // }}}

	function isAccessible() // {{{
	{
		global $tikilib;
		return parent::isAccessible() && $tikilib->plugin_enabled( $this->pluginName );
	} // }}}

	private static function getIcon( $name ) // {{{
	{
		// This property could be added to the plugin definition
		switch($name) {
		default:
			return 'pics/icons/plugin.png';
		}
	} // }}}

	private static function getToken( $name ) // {{{
	{
		switch($name) {
		case 'flash': return 'Flash';
		}
	} // }}}

	function getWikiHtml( $areaName ) // {{{
	{
		return $this->getSelfLink('popup_plugin_form(\'' . $areaName . '\',\'' . $this->pluginName . '\');needToConfirm=false;',
							htmlentities($this->label, ENT_QUOTES, 'UTF-8'), 'qt-plugin');
		//return '<a href="javascript:popup_plugin_form(\'' . $this->pluginName . '\')" onclick="needToConfirm=false;" title="' . htmlentities($this->label, ENT_QUOTES, 'UTF-8') . '" class="quicktag">' . $this->getIconHtml() . '</a>';
	} // }}}
}

class QuicktagsList
{
	private $lines = array();

	private function __construct() {}
	
	public static function fromPreference( $section ) // {{{
	{
		global $tikilib;

		$global = $tikilib->get_preference( 'toolbar_global' . (strpos($section, '_comments') !== false ? '_comments' : ''));
		$local = $tikilib->get_preference( 'toolbar_'.$section, $global );

		return self::fromPreferenceString( $local );
	} // }}}

	public static function fromPreferenceString( $string ) // {{{
	{
		$list = new self;

		$string = preg_replace( '/\s+/', '', $string );

		foreach( explode( '/', $string ) as $line ) {
			$list->addLine( explode( ',', $line ) );
		}

		return $list;
	} // }}}

	public	function addTag ( $name, $unique = false ) {
		if ( $unique && $this->contains($name) ) {
			return false;
		}
		array_push($this->lines[0][sizeof($this->lines)-1], Quicktag::getTag( $name ));
		return true;
	}

	public	function insertTag ( $name, $unique = false ) {
		if ( $unique && $this->contains($name) ) {
			return false;
		}
		array_unshift($this->lines[0][0], Quicktag::getTag( $name ));	
		return true;
	}

	private function addLine( array $tags ) // {{{
	{
		$elements = array();
		$group = array();

		foreach( $tags as $tagName ) {
			if( $tagName == '-' ) {
				if( count($group) ) {
					$elements[] = $group;
					$group = array();
				}
			} else {
				if( ( $tag = Quicktag::getTag( $tagName ) ) 
					&& $tag->isAccessible() ) {

					$group[] = $tag;
				}
			}
		}

		if( count($group) )
			$elements[] = $group;

		if( count( $elements ) )
			$this->lines[] = $elements;
	} // }}}

	function getWysiwygArray() // {{{
	{
		$lines = array();
		foreach( $this->lines as $line ) {
			$lineOut = array();

			foreach( $line as $group ) {
				foreach( $group as $tag ) {

					if( $token = $tag->getWysiwygToken() )
						$lineOut[] = $token;
				}

				$lineOut[] = '-';
			}

			$lineOut = array_slice( $lineOut, 0, -1 );

			if( count($lineOut) )
				$lines[] = array($lineOut);
		}

		return $lines;
	} // }}}

	function getWikiHtml( $areaName ) // {{{
	{
		global $tiki_p_admin, $tiki_p_admin_quicktags, $smarty, $section;
		$html = '';

		if ($tiki_p_admin == 'y' or $tiki_p_admin_quicktags == 'y') {
			$params = array('_script' => 'tiki-admin_quicktags.php', '_onclick' => 'needToConfirm = true;', '_class' => 'quicktag', '_icon' => 'wrench', '_ajax' => 'n');
			if (isset($section)) { $params['section'] = $section; }
			$content = tra('Admin Quicktags');
			$html .= '<div class="helptool-admin">';
			$html .= smarty_block_self_link($params, $content, $smarty);
			$html .= '</div>';
		}
		
		foreach( $this->lines as $line ) {
			$lineHtml = '';

			foreach( $line as $group ) {
				$groupHtml = '';
				foreach( $group as $tag ) {
					$groupHtml .= $tag->getWikiHtml( $areaName );
				}
				
				if( ! empty($groupHtml) ) {
					$param = empty($lineHtml) ? '' : ' class="quicktag-list"';
					$lineHtml .= "<span$param>$groupHtml</span>";
				}
			}
			if( ! empty($lineHtml) ) {
				$html .= "<div>$lineHtml</div>";
			}
		}

		return $html;
	} // }}}
	
	function contains($name) { // {{{
		foreach( $this->lines as $line ) {
			foreach( $line as $group ) {
				foreach( $group as $tag ) {
					if ($tag->getLabel() == $name) {
						return true;
					}
				}
			}
		}
		return false;
	} // }}}
}
