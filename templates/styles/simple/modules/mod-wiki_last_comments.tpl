{* $Header: /cvsroot/tikiwiki/tiki/templates/styles/simple/modules/mod-wiki_last_comments.tpl,v 1.2 2005-05-18 11:03:57 mose Exp $ *}
{if $feature_wiki eq 'y'}
	{if $nonums eq 'y'}
		{eval var="{tr}Last `$module_rows` wiki comments{/tr}" assign="tpl_module_title"}
	{else}
		{eval var="{tr}Last wiki comments{/tr}" assign="tpl_module_title"}
	{/if}
	{tikimodule title=$tpl_module_title name="wiki_last_comments" flip=$module_params.flip decorations=$module_params.decorations}
		{if $nonums != 'y'}
			<ol>
		{else}
			<ul>
		{/if}
		{section name=ix loop=$comments}
				<li><a class="linkmodule" href="tiki-index.php?page={$comments[ix].page|escape:"url"}&amp;comzone=show#comments" title="{$comments[ix].commentDate|tiki_short_datetime}, {tr}by{/tr} {$comments[ix].user}{if $moretooltips eq 'y'} on page {$comments[ix].page}{/if}">{if $moretooltips ne 'y'}<b>{$comments[ix].page}</b> &#8211; {/if}{$comments[ix].title}</a></li>
		{/section}
		{if $nonums != 'y'}
			</ol>
		{else}
			</ul>
		{/if}
	{/tikimodule}
{/if}
