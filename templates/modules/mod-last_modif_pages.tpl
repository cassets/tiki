{* $Header: /cvsroot/tikiwiki/tiki/templates/modules/mod-last_modif_pages.tpl,v 1.27 2007-05-31 18:29:22 nyloth Exp $ *}

{if $feature_wiki eq 'y'}
{if !isset($tpl_module_title)}
{if $nonums eq 'y'}
{eval var="<a href=\"tiki-lastchanges.php\">{tr}Last `$module_rows` Page Changes{/tr}</a>" assign="tpl_module_title"}
{else}
{eval var="<a href=\"tiki-lastchanges.php\">{tr}Last Page Changes{/tr}</a>" assign="tpl_module_title"}
{/if}
{/if}
{tikimodule title=$tpl_module_title name="last_modif_pages" flip=$module_params.flip decorations=$module_params.decorations}
   <table  border="0" cellpadding="0" cellspacing="0">
    {section name=ix loop=$modLastModif}
     <tr>
      {if $nonums != 'y'}
        <td class="module" valign="top">{$smarty.section.ix.index_next})</td>
      {/if}
      <td class="module">
		{if $absurl == 'y'}
          <a class="linkmodule" href="{$base_url}tiki-index.php?page={$modLastModif[ix].pageName|escape:"url"}" title="{$modLastModif[ix].lastModif|tiki_short_datetime}, {tr}by{/tr} {if $modLastModif[ix].user ne ''}{$modLastModif[ix].user|username}{else}{tr}Anonymous{/tr}{/if}{if (strlen($modLastModif[ix].pageName) > $maxlen) && ($maxlen > 0)}, {$modLastModif[ix].pageName}{/if}">
        {if $maxlen > 0}{* 0 is default value for maxlen eq to 'no truncate' *}
         {$modLastModif[ix].pageName|escape|truncate:$maxlen:"...":true}
        {else}
         {$modLastModif[ix].pageName|escape}
        {/if}
          </a>
		  {else}
       <a class="linkmodule" href="tiki-index.php?page={$modLastModif[ix].pageName|escape:"url"}" title="{$modLastModif[ix].lastModif|tiki_short_datetime}, {tr}by{/tr} {if $modLastModif[ix].user ne ''}{$modLastModif[ix].user|username}{else}{tr}Anonymous{/tr}{/if}{if (strlen($modLastModif[ix].pageName) > $maxlen) && ($maxlen > 0)}, {$modLastModif[ix].pageName}{/if}">
        {if $maxlen > 0}{* 0 is default value for maxlen eq to 'no truncate' *}
         {$modLastModif[ix].pageName|escape|truncate:$maxlen:"...":true}
        {else}
         {$modLastModif[ix].pageName|escape}
        {/if}
       </a>
	   {/if}
      </td>
     </tr>
    {/section}
   </table>
{/tikimodule}
{/if}
